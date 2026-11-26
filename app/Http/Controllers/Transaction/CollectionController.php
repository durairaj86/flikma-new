<?php

namespace App\Http\Controllers\Transaction;

use App\Services\DocumentNumber;
use App\Enums\CollectionEnum;
use App\Http\Controllers\Controller;
use App\Models\Finance\Account\Account;
use App\Models\Finance\Collection\Collection;
use App\Models\Finance\Collection\CollectionInvoice;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Finance\Finance;
use App\Models\Finance\FinanceSub;
use App\Models\Job\Job;
use App\Models\Customer\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CollectionController extends Controller
{
    /**
     * Display a listing of the collections.
     */
    public function index()
    {
        return view('modules.transaction.collection.list');
    }

    /**
     * Show the form for creating a new collection.
     *
     * "Record Payment" on a customer invoice (invoiceId query param — the
     * plain invoice id, same as the row's data-id used by every other row
     * action on that list, not hashids-encoded) gets its own dedicated,
     * single-invoice view — recordPaymentModal() below — since it only ever
     * concerns the one invoice the user clicked from. It still posts to this
     * same controller's store()/updateStatus(), unchanged.
     */
    public function modal(Request $request)
    {
        if ($request->filled('invoiceId')) {
            $targetInvoice = CustomerInvoice::with('customer:id,name_en,row_no')->find((int) $request->get('invoiceId'));

            if ($targetInvoice) {
                return $this->collectionModalForInvoice($targetInvoice);
            }
        }

        $collection = new Collection();
        $customers = Customer::select('id', 'name_en')->orderBy('name_en')->get();
        $parents = Account::whereIn('id', [3, 4])->orderBy('name')->get();//2=>Bank & Cash sub accounts

        $subAccounts = Account::whereIn('parent_id', [3, 4])->where('is_grouped', 0)->orderBy('name')->get();
        $customerInvoices = collect();
        $selectedInvoice = [];
        $creditSources = collect();
        $creditChosen = [];

        $advanceMode = $request->boolean('advance');
        if ($advanceMode && ($denied = $this->deniedRight('transactions.collection-advances'))) return $denied;

        return view('modules.transaction.collection.collection-form', compact('collection', 'customers', 'parents', 'subAccounts', 'customerInvoices', 'selectedInvoice', 'creditSources', 'creditChosen', 'advanceMode'));
    }

    /**
     * Dedicated "Record Payment" layout for a single invoice — a fresh,
     * purpose-built view (not the multi-invoice collection-form table), but
     * submitting the exact same field names so store()/updateStatus() need
     * no changes at all.
     */
    /**
     * A collection is paid in the invoice's own currency: its amount is added straight to the invoice's paid amount, so
     * an amount in another currency would be counted at face value (1000 SAR + 1000 USD = 2000).
     */
    private function assertSameCurrency(?string $currency, CustomerInvoice $invoice): void
    {
        if (strtoupper((string) $currency) !== strtoupper((string) $invoice->currency)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['currency' => __(':no is in :cur. Collect it in :cur (a collection must be in the same currency as its invoice).', ['no' => $invoice->row_no, 'cur' => strtoupper((string) $invoice->currency)])]);
        }
    }

    /**
     * What an invoice still owes: its amount, less everything already settled on it (approved collections and credit
     * notes, and credit applied from other invoices - all kept in paid_amount), less the amounts sitting on OTHER draft
     * collections. $exceptCollectionId is the collection being edited (its own lines never reduce the figure).
     */
    private function invoiceOutstanding(CustomerInvoice $invoice, ?int $exceptCollectionId = null): float
    {
        $drafts = (float) DB::table('collection_invoices as ci')
            ->join('collections as c', 'c.id', '=', 'ci.collection_id')
            ->where('ci.customer_invoice_id', $invoice->id)->whereNull('c.deleted_at')
            ->where('c.status', CollectionEnum::DRAFT->value)
            ->when($exceptCollectionId, fn ($q) => $q->where('c.id', '!=', $exceptCollectionId))
            ->sum('ci.amount');
        $paid = (float) ($invoice->paid_amount ?? 0);
        // editing an approved collection: its own amount is already inside paid_amount, give it back
        if ($exceptCollectionId) {
            $own = Collection::find($exceptCollectionId);
            if ($own && (int) $own->status === CollectionEnum::APPROVED->value) {
                $paid -= (float) CollectionInvoice::where('collection_id', $exceptCollectionId)->where('customer_invoice_id', $invoice->id)->sum('amount');
            }
        }
        return round((float) $invoice->grand_total - $paid - $drafts, 2);
    }

    /**
     * Credit a customer holds that can be applied to other invoices: on an approved invoice that was settled for more
     * than it was worth (typically a fully paid invoice that was then credited), the excess, less what other draft
     * collections have already picked. Returns [['invoice_id', 'row_no', 'available'], ...].
     */
    private function creditSources(int $customerId, ?int $exceptCollectionId = null): \Illuminate\Support\Collection
    {
        $picked = DB::table('customer_credit_applications')->where('status', 0)
            ->when($exceptCollectionId, fn ($q) => $q->where('collection_id', '!=', $exceptCollectionId))
            ->selectRaw('from_invoice_id, SUM(amount) as amount')->groupBy('from_invoice_id')->pluck('amount', 'from_invoice_id');
        return CustomerInvoice::where('customer_id', $customerId)->where('status', 3)
            ->whereColumn('paid_amount', '>', 'grand_total')->orderBy('id')->get(['id', 'row_no', 'grand_total', 'paid_amount'])
            ->map(fn ($i) => [
                'invoice_id' => $i->id,
                'row_no' => $i->row_no,
                'available' => round((float) $i->paid_amount - (float) $i->grand_total - (float) ($picked[$i->id] ?? 0), 2),
            ])->filter(fn ($r) => $r['available'] > 0)->values();
    }

    /**
     * Remember the credit chosen on this collection: credit_to[invoice id] = amount, per invoice, so several invoices can use
     * credit at the same time. The credit is drawn from the customer's available credit, oldest invoice first (status 0 =
     * chosen; it moves when the collection is approved).
     */
    private function saveCreditChoices(Collection $collection, Request $request): void
    {
        DB::table('customer_credit_applications')->where('collection_id', $collection->id)->where('status', 0)->delete();
        $chosen = array_filter((array) $request->input('credit_to', []), fn ($a) => is_numeric($a) && (float) $a > 0);
        if (!$chosen) return;

        $lines = CollectionInvoice::where('collection_id', $collection->id)->get()->keyBy('customer_invoice_id');
        $sources = $this->creditSources((int) $collection->customer_id, (int) $collection->id)->values()->all();
        $pool = array_sum(array_column($sources, 'available'));
        $asked = array_sum(array_map('floatval', $chosen));
        if ($asked > $pool + 0.005) {
            throw \Illuminate\Validation\ValidationException::withMessages(['credit_to' => __('The customer credit chosen (:asked) is more than the credit available (:pool).', ['asked' => number_format($asked, 2), 'pool' => number_format($pool, 2)])]);
        }

        $rows = [];
        foreach ($chosen as $toId => $amount) {
            $toId = (int) $toId;
            $amount = round((float) $amount, 2);
            $line = $lines->get($toId);
            $inv = CustomerInvoice::find($toId);
            if (!$line || !$inv) {
                throw \Illuminate\Validation\ValidationException::withMessages(['credit_to' => __('Credit was chosen for an invoice that is not on this collection.')]);
            }
            // credit plus cash can never be more than what the invoice still owes
            $room = max(0, $this->invoiceOutstanding($inv, (int) $collection->id) - (float) $line->amount);
            if ($amount > $room + 0.005) {
                throw \Illuminate\Validation\ValidationException::withMessages(['credit_to' => __(':no: the credit (:credit) plus the cash is more than the invoice still owes.', ['no' => $inv->row_no, 'credit' => number_format($amount, 2)])]);
            }
            // draw it from the credit available, oldest first (never from the invoice itself)
            $left = $amount;
            foreach ($sources as &$src) {
                if ($left <= 0.004) break;
                if ($src['available'] <= 0.004 || (int) $src['invoice_id'] === $toId) continue;
                $take = round(min($left, $src['available']), 2);
                $src['available'] = round($src['available'] - $take, 2);
                $left = round($left - $take, 2);
                $rows[] = ['company_id' => companyId(), 'customer_id' => $collection->customer_id, 'collection_id' => $collection->id,
                    'from_invoice_id' => (int) $src['invoice_id'], 'to_invoice_id' => $toId, 'amount' => $take, 'status' => 0,
                    'created_by' => Auth::id(), 'created_at' => now(), 'updated_at' => now()];
            }
            unset($src);
            if ($left > 0.005) {
                throw \Illuminate\Validation\ValidationException::withMessages(['credit_to' => __('Not enough customer credit for :no.', ['no' => $inv->row_no])]);
            }
        }
        DB::table('customer_credit_applications')->insert($rows);
    }

    /** Approving the collection: the chosen credit moves from the invoice that carries it onto the invoice it was chosen for (no cash, no ledger entry). */
    private function applyCredits(Collection $collection): void
    {
        $chosen = DB::table('customer_credit_applications')->where('collection_id', $collection->id)->where('status', 0)->orderBy('id')->get();
        foreach ($chosen as $row) {
            $from = CustomerInvoice::find($row->from_invoice_id);
            $to = CustomerInvoice::find($row->to_invoice_id);
            if (!$from || !$to) { DB::table('customer_credit_applications')->where('id', $row->id)->delete(); continue; }
            // never more than the carrying invoice really holds, nor more than the receiving invoice still owes
            $take = round(min((float) $row->amount, max(0, (float) $from->paid_amount - (float) $from->grand_total), max(0, (float) $to->grand_total - (float) ($to->paid_amount ?? 0))), 2);
            if ($take <= 0.004) { DB::table('customer_credit_applications')->where('id', $row->id)->delete(); continue; }
            $from->paid_amount = (float) $from->paid_amount - $take;
            $to->paid_amount = (float) ($to->paid_amount ?? 0) + $take;
            $from->save();
            $to->save();
            DB::table('customer_credit_applications')->where('id', $row->id)->update(['amount' => $take, 'status' => 1, 'updated_at' => now()]);
        }
    }

    /** Un-approving / cancelling the collection: the credit goes back to the invoices it came from (it stays chosen on the draft). */
    private function revertCredits(Collection $collection): void
    {
        $applied = DB::table('customer_credit_applications')->where('collection_id', $collection->id)->where('status', 1)->get();
        foreach ($applied as $row) {
            $from = CustomerInvoice::find($row->from_invoice_id);
            $to = CustomerInvoice::find($row->to_invoice_id);
            if ($from) { $from->paid_amount = (float) $from->paid_amount + (float) $row->amount; $from->save(); }
            if ($to) { $to->paid_amount = max(0, (float) $to->paid_amount - (float) $row->amount); $to->save(); }
        }
        if ($applied->isNotEmpty()) {
            // the ledger allocations that recorded the move are reversed with it
            DB::table('finance_allocations')->where('settling_doc_type', 'credit_application')->whereIn('settling_doc_id', $applied->pluck('id'))
                ->where('status', 'active')->update(['status' => 'reversed', 'reversed_at' => now(), 'updated_at' => now()]);
            DB::table('customer_credit_applications')->whereIn('id', $applied->pluck('id'))->update(['status' => 0, 'updated_at' => now()]);
        }
    }

    /**
     * The normal Collection form, opened from a customer invoice: that invoice's customer is selected and the invoice is
     * ticked at its outstanding balance (the customer's other unpaid approved invoices are listed, unticked).
     */
    private function collectionModalForInvoice(CustomerInvoice $target)
    {
        $collection = new Collection();
        $collection->customer_id = $target->customer_id;
        // the collection starts in the invoice's own currency and rate
        $collection->currency = $target->currency ?: 'SAR';
        $collection->currency_rate = (float) $target->currency_rate > 0 ? $target->currency_rate : 1;
        $customers = Customer::select('id', 'name_en')->orderBy('name_en')->get();
        $parents = Account::whereIn('id', [3, 4])->orderBy('name')->get();
        $subAccounts = Account::whereIn('parent_id', [3, 4])->where('is_grouped', 0)->orderBy('name')->get();

        $customerInvoices = CustomerInvoice::where('customer_id', $target->customer_id)
            ->where('status', 3)->orderBy('invoice_date', 'desc')->get();
        foreach ($customerInvoices as $key => $invoice) {
            $outstanding = $this->invoiceOutstanding($invoice);
            $invoice->paid_amount = (float) $invoice->grand_total - $outstanding;
            $invoice->balance_amount = $outstanding;
            if ($outstanding <= 0.004) {
                $customerInvoices->forget($key);
            }
        }
        $customerInvoices = $customerInvoices->values();
        $picked = $customerInvoices->firstWhere('id', $target->id);
        $selectedInvoice = $picked ? [$target->id => (float) $picked->balance_amount] : [];
        $collection->grand_total = $selectedInvoice ? $selectedInvoice[$target->id] : 0;

        $creditSources = $this->creditSources((int) $target->customer_id);
        $creditChosen = [];

        return view('modules.transaction.collection.collection-form', compact('collection', 'customers', 'parents', 'subAccounts', 'customerInvoices', 'selectedInvoice', 'creditSources', 'creditChosen'));
    }

    private function recordPaymentModal(CustomerInvoice $invoice)
    {
        $collection = new Collection();
        $collection->customer_id = $invoice->customer_id;

        $parents = Account::whereIn('id', [3, 4])->orderBy('name')->get();
        $subAccounts = Account::whereIn('parent_id', [3, 4])->where('is_grouped', 0)->orderBy('name')->get();

        $totalPaid = CollectionInvoice::where('customer_invoice_id', $invoice->id)->sum('amount');
        $invoice->paid_amount = $totalPaid;
        $invoice->balance_amount = $invoice->grand_total - $totalPaid;

        return view('modules.transaction.collection.record-payment-form', compact('collection', 'invoice', 'parents', 'subAccounts'));
    }

    /**
     * Show the form for editing the specified collection.
     */
    public function edit($id)
    {
        $collection = Collection::with('collectionInvoices')->findOrFail($id);
        $customers = Customer::select('id', 'name_en')->orderBy('name_en')->get();
        if ($collection->doc_type === 'refund') {
            $parents = Account::whereIn('id', [3, 4])->orderBy('name')->get();
            $subAccounts = Account::whereIn('parent_id', [3, 4])->where('is_grouped', 0)->orderBy('name')->get();
            $available = \App\Services\Finance\PartyAdvance::available('customer', (int) $collection->customer_id, (int) $collection->id);

            return view('modules.transaction.collection.refund-form', compact('collection', 'customers', 'parents', 'subAccounts', 'available'));
        }

        $parents = Account::whereIn('id', [3, 4])->orderBy('name')->get();//2=>Bank & Cash sub accounts

        $subAccounts = Account::whereIn('parent_id', [3, 4])->where('is_grouped', 0)->orderBy('name')->get();

        // Get the IDs of invoices already included in this collection
        $selectedInvoiceIds = $collection->collectionInvoices->pluck('customer_invoice_id')->toArray();

        // Get customer invoices for the selected customer
        $customerInvoices = CustomerInvoice::where('customer_id', $collection->customer_id)
            ->orderBy('invoice_date', 'desc')
            ->get();

        // What each invoice still owes (settled amounts and other draft collections taken off), not counting this collection itself
        foreach ($customerInvoices as $key => $invoice) {
            $outstanding = $this->invoiceOutstanding($invoice, (int) $collection->id);
            $invoice->paid_amount = (float) $invoice->grand_total - $outstanding;
            $invoice->balance_amount = $outstanding;

            // Remove invoices with nothing left unless they are already selected in this collection
            if ($outstanding <= 0.004 && !in_array($invoice->id, $selectedInvoiceIds)) {
                $customerInvoices->forget($key);
            }
        }

        $selectedInvoice = $collection->collectionInvoices->pluck('amount', 'customer_invoice_id')->toArray();
        //$customerInvoices = $customerInvoices->values();
        // credit chosen on this collection (chosen or already applied) + what is still available to pick
        $creditSources = $this->creditSources((int) $collection->customer_id, (int) $collection->id);
        // credit chosen on this collection, per invoice it is applied to
        $creditChosen = DB::table('customer_credit_applications')->where('collection_id', $collection->id)
            ->selectRaw('to_invoice_id, SUM(amount) as amount')->groupBy('to_invoice_id')->pluck('amount', 'to_invoice_id')->toArray();
        // credit this collection already moved counts as available again while editing it
        $ownApplied = DB::table('customer_credit_applications')->where('collection_id', $collection->id)->where('status', 1)
            ->selectRaw('from_invoice_id, SUM(amount) as amount')->groupBy('from_invoice_id')->pluck('amount', 'from_invoice_id')->toArray();
        foreach ($ownApplied as $fromId => $amt) {
            if ($creditSources->firstWhere('invoice_id', $fromId)) {
                $creditSources = $creditSources->map(fn ($r) => $r['invoice_id'] == $fromId ? array_merge($r, ['available' => round($r['available'] + (float) $amt, 2)]) : $r);
            } else {
                $inv = CustomerInvoice::find($fromId);
                if ($inv) $creditSources->push(['invoice_id' => $fromId, 'row_no' => $inv->row_no, 'available' => round((float) $amt + max(0, (float) $inv->paid_amount - (float) $inv->grand_total), 2)]);
            }
        }
        $creditSources = $creditSources->values();
        $opening = ['balance' => \App\Services\Finance\OpeningSettlement::outstanding('customer', (int) $collection->customer_id, (int) $collection->id),
            'chosen' => \App\Services\Finance\OpeningSettlement::ofDocument('customer', (int) $collection->id),
            'currency' => app(\App\Services\Finance\OpeningBalanceService::class)->baseCurrency()];
        $advanceAvailable = \App\Services\Finance\PartyAdvance::available('customer', (int) $collection->customer_id, (int) $collection->id);
        // a saved advance (no invoices, no opening balance, an excess kept) opens in advance mode
        $advanceMode = $collection->collectionInvoices->isEmpty() && (float) $collection->advance_amount > 0.004 && $opening['chosen'] <= 0.004;
        return view('modules.transaction.collection.collection-form', compact('collection', 'customers', 'parents', 'subAccounts', 'customerInvoices', 'selectedInvoice', 'creditSources', 'creditChosen', 'opening', 'advanceAvailable', 'advanceMode'));
    }

    /**
     * Store a newly created collection in storage.
     */
    public function store(Request $request)
    {
        $request->merge(['customer' => decodeId($request->input('customer'))]);
        // money has two decimals: round what came in (a floating-point tail such as 101.80000000000001 must never be saved)
        foreach (['invoice_amounts', 'credit_to'] as $key) {
            if (is_array($request->input($key))) {
                $request->merge([$key => array_map(fn ($a) => is_numeric($a) ? round((float) $a, 2) : $a, $request->input($key))]);
            }
        }
        // Nothing is paid in cash when the invoices are settled entirely with customer credit: then there is no account to choose.
        $cashTotal = collect((array) $request->input('invoice_amounts', []))->sum(fn ($a) => (float) $a)
            + (float) $request->input('bank_charges', 0) + (float) $request->input('other_charges', 0);
        $typedTotal = $request->filled('total_received') ? round((float) $request->input('total_received'), 2) : null;
        $allocated = collect((array) $request->input('invoice_amounts', []))->sum(fn ($a) => (float) $a) + (float) $request->input('opening_amount', 0);
        // nothing is paid in cash when the credit or the customer's advance settles everything
        $creditOnly = (($typedTotal ?? $allocated) + (float) $request->input('bank_charges', 0) + (float) $request->input('other_charges', 0)) <= 0.004
            && (collect((array) $request->input('credit_to', []))->sum(fn ($a) => (float) $a) > 0 || $allocated > 0.004);
        $validated = $request->validate([
            'customer' => 'required|exists:customers,id',
            //'job_id' => 'nullable|exists:jobs,id',
            'collection_date' => 'required|date',
            'account' => $creditOnly ? 'nullable' : 'required',
            //'collection_method' => 'required|string',
            'reference_no' => 'nullable|string',
            'currency' => 'required|string|size:3',
            'currency_rate' => 'required|numeric|min:0',
            'bank_charges' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'customer_invoice_ids' => 'nullable|array',
            'customer_invoice_ids.*' => 'exists:customer_invoices,id',
            'invoice_amounts' => 'nullable|array',
            'invoice_amounts.*' => 'numeric|min:0',
            'opening_amount' => 'nullable|numeric|min:0',
            'total_received' => 'nullable|numeric|min:0',
        ]);
        $request->merge(['customer_invoice_ids' => (array) $request->input('customer_invoice_ids', []), 'invoice_amounts' => (array) $request->input('invoice_amounts', [])]);
        $openingAmount = round((float) $request->input('opening_amount', 0), 2);
        if (!$request->input('customer_invoice_ids') && $openingAmount <= 0 && ($typedTotal ?? 0) <= 0) {
            throw \Illuminate\Validation\ValidationException::withMessages(['customer_invoice_ids' => __('Please select at least one invoice')]);
        }

        if (!$request->filled('collection_id') && $request->boolean('advance_mode') && ($denied = $this->deniedRight('transactions.collection-advances'))) return $denied;

        $this->assertPeriodOpen($validated['collection_date'], 'collection_date');
        $this->assertPartyCurrency(\App\Models\Customer\Customer::class, $validated['customer'], $validated['currency']);

        DB::beginTransaction();
        try {
            // Create or update collection
            $isUpdate = $request->filled('collection_id');
            $oldCollectionInvoices = [];

            if ($isUpdate) {
                $collection = Collection::findOrFail($request->input('collection_id'));
                $collection->updated_by = Auth::id();

                // If this is an approved collection, we need to revert the paid_amount in customer invoices
                if ($collection->status == CollectionEnum::APPROVED->value) {
                    $this->revertCredits($collection);   // credit applied by it goes back; it is applied again below
                    $oldCollectionInvoices = CollectionInvoice::where('collection_id', $collection->id)
                        ->get()
                        ->keyBy('customer_invoice_id')
                        ->toArray();

                    // Revert paid_amount in customer invoices
                    foreach ($oldCollectionInvoices as $invoiceId => $oldCollectionInvoice) {
                        $customerInvoice = CustomerInvoice::find($invoiceId);
                        if ($customerInvoice && $customerInvoice->paid_amount) {
                            $customerInvoice->paid_amount = max(0, $customerInvoice->paid_amount - $oldCollectionInvoice['amount']);
                            $customerInvoice->save();
                        }
                    }
                }
            } else {
                $collection = new Collection();
                $collection->status = CollectionEnum::DRAFT->value;
                $collection->company_id = companyId();
                $collection->created_by = Auth::id();

                // Generate row_no: an advance (opened from "New Advance", nothing settled) has its own CAV series
                $isAdvance = $request->boolean('advance_mode') && !$request->input('customer_invoice_ids') && $openingAmount <= 0;
                $prefix = ($isAdvance ? 'CAV/' : 'COL/') . date('y') . '/';
                $collection->unique_row_no = DocumentNumber::next(Collection::class, $prefix);
                $collection->row_no = $prefix . sprintf('%04d', $collection->unique_row_no);
            }

            // Set job_no if job_id is provided
            /*if ($validated['job_id']) {
                $job = Job::select('row_no')->find($validated['job_id']);
                $collection->job_no = $job->row_no;
            }*/

            // Calculate totals
            $subTotal = 0;
            $taxTotal = 0;
            $bankCharges = $request->input('bank_charges', 0);
            $otherCharges = $request->input('other_charges', 0);

            foreach ($request->input('customer_invoice_ids') as $index => $invoiceId) {
                $invoice = CustomerInvoice::find($invoiceId);
                $amount = $request->input('invoice_amounts')[$invoiceId];
                $this->assertSameCurrency($validated['currency'], $invoice);

                // Validate that amount doesn't exceed the invoice grand_total
                if ($amount > $invoice->grand_total) {
                    return redirect()->back()->withErrors(['error' => 'Collection amount cannot exceed the invoice total amount'])->withInput();
                }

                // Calculate tax proportion
                $invoiceTotal = $invoice->grand_total;
                $taxProportion = 0;

                if ($invoiceTotal > 0) {
                    $taxProportion = ($invoice->tax_total / $invoiceTotal) * $amount;
                }

                $subTotal += $amount - $taxProportion;
                $taxTotal += $taxProportion;
            }

            // Part of the customer's opening balance settled by this collection (no VAT: it is a plain receivable)
            if ($openingAmount > 0) {
                $svcOb = \App\Services\Finance\OpeningSettlement::class;
                $left = $svcOb::outstanding('customer', (int) $validated['customer'], $isUpdate ? (int) $collection->id : null);
                if ($openingAmount > $left + 0.004) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['opening_amount' => __('The opening balance left to collect is :a.', ['a' => number_format($left, 2)])]);
                }
                $baseCur = app(\App\Services\Finance\OpeningBalanceService::class)->baseCurrency();
                if (strtoupper((string) $validated['currency']) !== $baseCur) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['currency' => __('The opening balance is in :cur. Collect it in :cur.', ['cur' => $baseCur])]);
                }
                $subTotal += $openingAmount;
            }

            // Total received typed on the form: more than the ticked invoices = the excess is an advance (kept as credit on the
            // customer's receivable); less = the difference is taken from the customer's earlier advance. Anything else is refused.
            $advExtra = 0.0;
            $advUsed = 0.0;
            $allocatedNow = round($subTotal + $taxTotal, 2);
            if ($typedTotal !== null && abs($typedTotal - $allocatedNow) >= 0.005) {
                $baseCur = app(\App\Services\Finance\OpeningBalanceService::class)->baseCurrency();
                if (strtoupper((string) $validated['currency']) !== $baseCur) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['total_received' => __('Total received must equal the ticked invoices (advance is kept in :cur only).', ['cur' => $baseCur])]);
                }
                $diff = round($typedTotal - $allocatedNow, 2);
                if ($diff > 0) {
                    $advExtra = $diff;
                } else {
                    $advUsed = -$diff;
                    $availAdv = \App\Services\Finance\PartyAdvance::available('customer', (int) $validated['customer'], $isUpdate ? (int) $collection->id : null);
                    if ($advUsed > $availAdv + 0.004) {
                        throw \Illuminate\Validation\ValidationException::withMessages(['total_received' => __('Total received :t is less than the chosen invoices :s, and the customer has only :a advance to cover the difference.', ['t' => number_format($typedTotal, 2), 's' => number_format($allocatedNow, 2), 'a' => number_format($availAdv, 2)])]);
                    }
                }
                $subTotal += $diff;
            }

            $grandTotal = $subTotal + $taxTotal + $bankCharges + $otherCharges;

            // Update collection fields
            $collection->customer_id = $validated['customer'];
            //$collection->job_id = $validated['job_id'];
            $collection->collection_date = $validated['collection_date'];
            $collection->account = is_array($validated['account'] ?? null) ? ($validated['account'][0] ?: null) : ($validated['account'] ?? null);
            //$collection->collection_method = $validated['collection_method'];
            $collection->reference_no = $validated['reference_no'] ?? null;
            $collection->currency = $validated['currency'];
            $collection->currency_rate = $validated['currency_rate'];
            $collection->sub_total = $subTotal;
            $collection->tax_total = $taxTotal;
            $collection->bank_charges = $bankCharges;
            $collection->other_charges = $otherCharges;
            $collection->grand_total = $grandTotal;
            $collection->base_sub_total = $subTotal * $validated['currency_rate'];
            $collection->base_tax_total = $taxTotal * $validated['currency_rate'];
            $collection->base_bank_charges = $bankCharges * $validated['currency_rate'];
            $collection->base_other_charges = $otherCharges * $validated['currency_rate'];
            $collection->base_grand_total = $grandTotal * $validated['currency_rate'];
            $collection->notes = $validated['notes'] ?? null;
            $collection->total_amount = $typedTotal ?? $allocatedNow;
            $collection->advance_amount = $advExtra;
            $collection->advance_used = $advUsed;
            $collection->doc_type = 'receipt';

            $collection->save();

            // Delete existing collection invoices
            CollectionInvoice::where('collection_id', $collection->id)->delete();
            \App\Services\Finance\OpeningSettlement::save('customer', (int) $validated['customer'], (int) $collection->id, $openingAmount);

            // Create collection invoices
            $collectionInvoices = [];
            foreach ($request->input('customer_invoice_ids') as $index => $invoiceId) {
                $collectionInvoices[] = [
                    'collection_id' => $collection->id,
                    'customer_invoice_id' => $invoiceId,
                    'company_id' => companyId(),
                    'amount' => $request->input('invoice_amounts')[$invoiceId],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($collectionInvoices)) {
                DB::table('collection_invoices')->insert($collectionInvoices);

                // If this is an approved collection, update the paid_amount in customer invoices
                if ($collection->status == CollectionEnum::APPROVED->value) {
                    foreach ($request->input('customer_invoice_ids') as $index => $invoiceId) {
                        $amount = $request->input('invoice_amounts')[$invoiceId];
                        $customerInvoice = CustomerInvoice::find($invoiceId);
                        if ($customerInvoice) {
                            $customerInvoice->paid_amount = ($customerInvoice->paid_amount ?? 0) + $amount;
                            $customerInvoice->save();
                        }
                    }
                }
            }

            // customer credit chosen on this collection (applied now only if the collection is approved)
            $this->saveCreditChoices($collection, $request);
            if ($collection->status == CollectionEnum::APPROVED->value) {
                $this->applyCredits($collection);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => __('Collection saved successfully'),
                'collection_id' => $collection->id,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => __('Error saving collection: ') . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified collection.
     */
    public function show($id)
    {
        $collection = Collection::with(['customer', 'job', 'collectionInvoices.customerInvoice', 'createdBy', 'approvedBy'])
            ->findOrFail($id);

        return view('modules.transaction.collection.show', compact('collection'));
    }

    /**
     * Return the drawer (offcanvas) partial for the specified collection.
     */
    public function overviewDrawer($id)
    {
        $collection = Collection::with(['customer', 'job', 'collectionInvoices.customerInvoice', 'documents', 'createdBy', 'approvedBy'])
            ->findOrFail($id);

        [$origin, $timeline] = $this->collectionTimeline($collection);

        // Customer credit used on this collection: which invoice it came from, which invoice it settled, how much
        $credits = DB::table('customer_credit_applications as ca')
            ->leftJoin('customer_invoices as f', 'f.id', '=', 'ca.from_invoice_id')
            ->leftJoin('customer_invoices as t', 't.id', '=', 'ca.to_invoice_id')
            ->where('ca.collection_id', $collection->id)->orderBy('ca.id')
            ->get(['ca.amount', 'ca.status', 'ca.from_invoice_id', 'ca.to_invoice_id', 'f.row_no as from_no', 't.row_no as to_no']);
        $creditByInvoice = $credits->groupBy('to_invoice_id')->map(fn ($g) => (float) $g->sum('amount'))->all();

        return view('modules.transaction.collection.view-overview-drawer', compact('collection', 'origin', 'timeline', 'credits', 'creditByInvoice'));
    }

    /**
     * Time frame for the collection drawer: where the job came from
     * (enquiry -> quotation -> job), the customer invoice(s) being collected,
     * then the collection's own life (created / updated / approved / cancelled).
     */
    private function collectionTimeline(Collection $collection): array
    {
        $events = [];
        $rank = 0;
        $add = function ($label, $at, $icon, $module, $by = null, $meta = null, $link = null) use (&$events, &$rank) {
            if (!$at) return;
            $events[] = ['label' => $label, 'at' => $at, 'icon' => $icon, 'module' => $module, 'by' => $by, 'meta' => $meta, 'link' => $link,
                'rank' => $rank, 'key' => \Illuminate\Support\Carbon::parse($at)->timestamp, 'seq' => count($events)];
        };

        $invoices = $collection->collectionInvoices->map->customerInvoice->filter();
        $jobId = $collection->job_id ?: $invoices->pluck('job_id')->filter()->first();
        $job = $jobId ? \App\Models\Job\Job::find($jobId) : null;
        $quotation = $job && $job->quotation_id ? \App\Models\Quotation\Quotation::find($job->quotation_id) : null;
        $enquiry = $quotation && $quotation->enquiry_id ? \App\Models\Enquiry\Enquiry::find($quotation->enquiry_id) : null;

        if ($enquiry) $add(__('Enquiry created') . ' · ' . $enquiry->row_no, $enquiry->created_at, 'bi-chat-left-text', 'enquiry', null, null, ['enquiry', $enquiry->id, $enquiry->row_no]);
        if ($quotation) $add(__('Quotation posted') . ' · ' . $quotation->row_no, $quotation->created_at, 'bi-file-earmark-text', 'quotation', null, null, ['quotation', $quotation->id, $quotation->row_no]);
        if ($job) $add(($quotation ? __('Converted to job') : __('Job created')) . ' · ' . $job->row_no, $job->created_at, 'bi-briefcase', 'job', null, null, ['job', $job->id, $job->row_no]);

        $rank = 1;
        $origin = $quotation
            ? ($enquiry ? __('Enquiry') . ' → ' : '') . __('Quotation') . ' → ' . __('Job') . ' → ' . __('Invoice') . ' → ' . __('Collection')
            : ($job ? __('Job') . ' → ' . __('Invoice') . ' → ' . __('Collection') : ($invoices->count() ? __('Invoice') . ' → ' . __('Collection') : __('Collection created directly')));

        foreach ($invoices as $inv) {
            $add(__('Invoice created') . ' · ' . $inv->row_no, $inv->created_at, 'bi-receipt', 'invoice', null,
                number_format((float) $inv->grand_total, decimals()), ['invoice', $inv->id, $inv->row_no]);
        }

        $labels = [CollectionEnum::DRAFT->value => __('Moved to Draft'), CollectionEnum::APPROVED->value => __('Approved'), CollectionEnum::CANCELLED->value => __('Cancelled')];
        $ignore = ['status', 'updated_at', 'approved_by', 'approved_at', 'approval_status', 'disapproval_reason'];
        $seenCreate = false;
        $logs = \App\Models\Log\LogHistory::where('loggable_type', Collection::class)
            ->where('loggable_id', $collection->id)->where('created_at', '>=', $collection->created_at->copy()->subSeconds(5))->orderBy('id')->get();
        foreach ($logs as $log) {
            $by = $log->user_id['name'] ?? null;
            if ($log->action === 'created') {
                $seenCreate = true;
                $add(__('Collection created') . ' · ' . $collection->row_no, $log->created_at, 'bi-cash-coin', 'collection', $by, number_format((float) $collection->grand_total, decimals()));
            } elseif ($log->action === 'updated') {
                $new = $log->changes['new'] ?? [];
                if (isset($new['status'])) {
                    $st = (int) $new['status'];
                    $add($labels[$st] ?? __('Status changed'), $log->created_at,
                        $st === CollectionEnum::APPROVED->value ? 'bi-check-circle' : ($st === CollectionEnum::CANCELLED->value ? 'bi-x-circle' : 'bi-clock'), 'collection', $by);
                } elseif (array_diff(array_keys($new), $ignore)) {
                    $add(__('Collection updated'), $log->created_at, 'bi-pencil-square', 'collection', $by);
                }
            }
        }
        if (!$seenCreate) $add(__('Collection created') . ' · ' . $collection->row_no, $collection->created_at, 'bi-cash-coin', 'collection', null, number_format((float) $collection->grand_total, decimals()));

        usort($events, fn($a, $b) => [$a['rank'], $a['rank'] ? $a['key'] : $a['seq'], $a['seq']] <=> [$b['rank'], $b['rank'] ? $b['key'] : $b['seq'], $b['seq']]);

        return [$origin, $events];
    }

    /**
     * Get customer invoices for a specific customer.
     */
    public function getCustomerInvoices($customerId)
    {
        $invoices = [];
        if ($customerId) {
            // Get all approved invoices for the customer
            $invoices = CustomerInvoice::where('customer_id', decodeId($customerId))
                ->where('status', 3) // Assuming 3 is the status for approved invoices
                ->orderBy('invoice_date', 'desc')
                ->get();

            // What each invoice still owes: settled amounts (collections, credit notes, applied credit) and draft collections taken off
            foreach ($invoices as $key => $invoice) {
                $outstanding = $this->invoiceOutstanding($invoice, request()->filled('collection') ? (int) request('collection') : null);
                $invoice->paid_amount = (float) $invoice->grand_total - $outstanding;
                $invoice->balance_amount = $outstanding;

                // Remove invoices with nothing left
                if ($outstanding <= 0.004) {
                    $invoices->forget($key);
                }
            }
        }

        return response()->json($invoices->values());
    }

    /** Advance the customer holds that can be used on invoices or refunded (for the collection and refund forms). */
    public function getCustomerAdvance($customerId)
    {
        $id = (int) decodeId($customerId);
        $coll = request()->filled('collection') ? (int) request('collection') : null;

        return response()->json(['available' => $id ? \App\Services\Finance\PartyAdvance::available('customer', $id, $coll) : 0,
            'currency' => app(\App\Services\Finance\OpeningBalanceService::class)->baseCurrency()]);
    }

    /** Refund form: pay (part of) the customer's advance back. */
    public function refundModal()
    {
        if ($denied = $this->deniedRight('transactions.collection-refunds')) return $denied;
        $collection = new Collection();
        $customers = Customer::select('id', 'name_en')->orderBy('name_en')->get();
        $parents = Account::whereIn('id', [3, 4])->orderBy('name')->get();
        $subAccounts = Account::whereIn('parent_id', [3, 4])->where('is_grouped', 0)->orderBy('name')->get();
        $available = 0.0;

        return view('modules.transaction.collection.refund-form', compact('collection', 'customers', 'parents', 'subAccounts', 'available'));
    }

    public function storeRefund(Request $request)
    {
        if (!$request->filled('collection_id') && ($denied = $this->deniedRight('transactions.collection-refunds'))) return $denied;
        $request->merge(['customer' => decodeId($request->input('customer'))]);
        $request->merge(['amount' => is_numeric($request->input('amount')) ? round((float) $request->input('amount'), 2) : $request->input('amount')]);
        $v = $request->validate([
            'customer' => 'required|exists:customers,id',
            'collection_date' => 'required|date',
            'account' => 'required',
            'amount' => 'required|numeric|gt:0',
            'reference_no' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);
        $this->assertPeriodOpen($v['collection_date'], 'collection_date');
        $cur = app(\App\Services\Finance\OpeningBalanceService::class)->baseCurrency();

        $collection = $request->filled('collection_id') ? Collection::findOrFail($request->input('collection_id')) : null;
        if ($collection && ($collection->doc_type !== 'refund' || $collection->status != CollectionEnum::DRAFT->value)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['amount' => __('Only a draft refund can be edited.')]);
        }
        $avail = \App\Services\Finance\PartyAdvance::available('customer', (int) $v['customer'], $collection?->id);
        if ($v['amount'] > $avail + 0.004) {
            throw \Illuminate\Validation\ValidationException::withMessages(['amount' => __('The customer has only :a advance to refund.', ['a' => number_format($avail, 2)])]);
        }

        DB::beginTransaction();
        try {
            if (!$collection) {
                $collection = new Collection();
                $collection->status = CollectionEnum::DRAFT->value;
                $collection->company_id = companyId();
                $collection->created_by = Auth::id();
                $prefix = 'CRF/' . date('y') . '/';
                $collection->unique_row_no = DocumentNumber::next(Collection::class, $prefix);
                $collection->row_no = $prefix . sprintf('%04d', $collection->unique_row_no);
            } else {
                $collection->updated_by = Auth::id();
            }
            $a = (float) $v['amount'];
            $collection->customer_id = $v['customer'];
            $collection->collection_date = $v['collection_date'];
            $collection->account = is_array($v['account']) ? ($v['account'][0] ?: null) : $v['account'];
            $collection->reference_no = $v['reference_no'] ?? null;
            $collection->currency = $cur;
            $collection->currency_rate = 1;
            $collection->sub_total = $a; $collection->tax_total = 0; $collection->bank_charges = 0; $collection->other_charges = 0;
            $collection->grand_total = $a;
            $collection->base_sub_total = $a; $collection->base_tax_total = 0; $collection->base_bank_charges = 0; $collection->base_other_charges = 0;
            $collection->base_grand_total = $a;
            $collection->notes = $v['notes'] ?? null;
            $collection->total_amount = $a;
            $collection->advance_amount = 0;
            $collection->advance_used = $a;      // a refund uses up the advance
            $collection->doc_type = 'refund';
            $collection->save();
            DB::commit();

            return response()->json(['status' => 'success', 'message' => __('Refund saved successfully'), 'collection_id' => $collection->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => __('Error saving refund: ') . $e->getMessage()], 500);
        }
    }

    /** What is left of the customer's opening balance to collect (for the collection form). */
    public function getCustomerOpening($customerId)
    {
        $id = (int) decodeId($customerId);
        $coll = request()->filled('collection') ? (int) request('collection') : null;

        return response()->json($id ? [
            'balance' => \App\Services\Finance\OpeningSettlement::outstanding('customer', $id, $coll),
            'total' => \App\Services\Finance\OpeningSettlement::total('customer', $id),
            'currency' => app(\App\Services\Finance\OpeningBalanceService::class)->baseCurrency(),
        ] : ['balance' => 0]);
    }

    /** Customer credit available to apply (for the collection form's customer change). */
    public function getCustomerCredit($customerId)
    {
        $id = (int) decodeId($customerId);
        return response()->json($id ? $this->creditSources($id, request()->filled('collection') ? (int) request('collection') : null) : []);
    }

    /** An approved collection that gave an advance cannot be undone while later documents have already used that advance. */
    private function advanceBlocksReversal(Collection $collection): ?\Illuminate\Http\JsonResponse
    {
        $given = (float) $collection->advance_amount;
        if ($given <= 0.004 || (int) $collection->status !== \App\Enums\CollectionEnum::APPROVED->value) {
            return null;
        }
        $left = \App\Services\Finance\PartyAdvance::available('customer', (int) $collection->customer_id) - $given;
        if ($left < -0.004) {
            return response()->json(['status' => 'error', 'message' => __('This collection gave an advance of :a that is already used or refunded. Cancel those documents first.', ['a' => number_format($given, 2)])], 422);
        }

        return null;
    }

    /**
     * Update the collection status.
     */
    public function updateStatus($id, $status)
    {
        $collection = Collection::findOrFail($id);
        $previousStatus = $collection->status;
        if ($locked = $this->refuseInClosedPeriod(formDate($collection->collection_date), $previousStatus, $status, CollectionEnum::APPROVED->value)) {
            return $locked;
        }
        if ($status != \App\Enums\CollectionEnum::APPROVED->value && ($blocked = $this->advanceBlocksReversal($collection))) {
            return $blocked;
        }

        DB::beginTransaction();
        try {
            if ($status == CollectionEnum::APPROVED->value) {
                $collection->status = CollectionEnum::APPROVED->value;
                $collection->approved_by = Auth::id();
                $collection->approved_at = now();

                // Update paid_amount in customer invoices when collection is approved
                if ($previousStatus != CollectionEnum::APPROVED->value) {
                    $collectionInvoices = CollectionInvoice::where('collection_id', $collection->id)->get();
                    foreach ($collectionInvoices as $collectionInvoice) {
                        $customerInvoice = CustomerInvoice::find($collectionInvoice->customer_invoice_id);
                        if ($customerInvoice) {
                            $this->assertSameCurrency($collection->currency, $customerInvoice);
                            $customerInvoice->paid_amount = ($customerInvoice->paid_amount ?? 0) + $collectionInvoice->amount;
                            $customerInvoice->save();
                        }
                    }
                    $this->applyCredits($collection);   // the customer credit chosen on this collection moves onto its invoices
                }
            } else {
                $collection->status = $status == CollectionEnum::CANCELLED->value ? CollectionEnum::CANCELLED->value : CollectionEnum::DRAFT->value;

                // Revert paid_amount in customer invoices when an approved collection is moved back to draft or cancelled
                if ($previousStatus == CollectionEnum::APPROVED->value) {
                    $this->revertCredits($collection);
                    $collectionInvoices = CollectionInvoice::where('collection_id', $collection->id)->get();
                    foreach ($collectionInvoices as $collectionInvoice) {
                        $customerInvoice = CustomerInvoice::find($collectionInvoice->customer_invoice_id);
                        if ($customerInvoice && $customerInvoice->paid_amount) {
                            $customerInvoice->paid_amount = max(0, $customerInvoice->paid_amount - $collectionInvoice->amount);
                            $customerInvoice->save();
                        }
                    }
                }
            }

            $collection->save();

            // Create finance entries when collection is approved
            if ($status == CollectionEnum::APPROVED->value) {
                $this->createFinanceEntries($collection);
            }

            // Delete finance entries when an approved collection is moved back to draft or cancelled
            if ($previousStatus == CollectionEnum::APPROVED->value && $status != CollectionEnum::APPROVED->value) {
                $this->deleteFinanceEntries($collection->id);
            }
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => __('Collection status updated successfully'),
                'data' => [
                    'id' => $collection->id,
                    'status' => $collection->status,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => __('Error updating collection status: ') . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Set disapproval reason for a collection.
     */
    public function setDisapprovalReason(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $collection = Collection::findOrFail($id);
        $previousStatus = $collection->status;
        if ($locked = $this->refuseInClosedPeriod(formDate($collection->collection_date), $previousStatus, CollectionEnum::CANCELLED->value, CollectionEnum::APPROVED->value)) {
            return $locked;
        }
        if ($blocked = $this->advanceBlocksReversal($collection)) {
            return $blocked;
        }
        DB::beginTransaction();
        $collection->status = CollectionEnum::CANCELLED->value;
        $collection->disapproval_reason = $validated['reason'];
        $collection->save();

        // Delete finance entries if collection was previously approved
        if ($previousStatus == CollectionEnum::APPROVED->value) {
            $this->revertCredits($collection);
            // Revert paid_amount in customer invoices
            $collectionInvoices = CollectionInvoice::where('collection_id', $collection->id)->get();
            foreach ($collectionInvoices as $collectionInvoice) {
                $customerInvoice = CustomerInvoice::find($collectionInvoice->customer_invoice_id);
                if ($customerInvoice && $customerInvoice->paid_amount) {
                    $customerInvoice->paid_amount = max(0, $customerInvoice->paid_amount - $collectionInvoice->amount);
                    $customerInvoice->save();
                }
            }

            $this->deleteFinanceEntries($collection->id);
        }
        DB::commit();

        return response()->json([
            'status' => 'success',
            'message' => __('Collection cancelled successfully'),
            'data' => [
                'id' => $collection->id,
                'status' => $collection->status,
            ],
        ]);
    }

    /**
     * Print the specified collection.
     */
    public function print($id)
    {
        $collection = Collection::with(['customer', 'job', 'collectionInvoices.customerInvoice', 'createdBy', 'approvedBy'])
            ->findOrFail($id);

        return view('modules.transaction.collection.print', compact('collection'));
    }

    /**
     * Download the specified collection as PDF.
     */
    public function download($id)
    {
        $collection = Collection::with(['customer', 'job', 'collectionInvoices.customerInvoice', 'createdBy', 'approvedBy'])
            ->findOrFail($id);

        // opens the print view and the browser's "Save as PDF" (same as Journal Voucher download)
        return view('modules.transaction.collection.print', ['collection' => $collection, 'autoPrint' => true]);
    }

    /**
     * Get data for DataTables.
     */
    public function fetchAllRows(Request $request)
    {
        $filter = $request->filterData ?? [];

        // Shared filters so the tab counts and summary cards match the visible list.
        $applyFilters = function ($q) use ($filter) {
            $q->when(isset($filter['filter-from-date'], $filter['filter-to-date']), function ($q) use ($filter) {
                $q->whereBetween('collection_date', [formDate($filter['filter-from-date']), formDate($filter['filter-to-date'])]);
            })->when(!empty($filter['customers']), function ($q) use ($filter) {
                $q->whereIn('customer_id', decodeIds($filter['customers']));
            });
        };

        $query = Collection::with(['customer:id,name_en,name_ar', 'job:id,row_no'])
            ->select([
                'collections.id',
                'row_no',
                'customer_id',
                'account',
                'collection_date',
                'reference_no',
                'currency',
                'grand_total',
                'sub_total',
                'tax_total',
                'total_amount',
                'advance_amount',
                'advance_used',
                'doc_type',
                'status',
                'company_id',
                'collections.created_at',
            ])
            // customer credit chosen / applied on the collection (not cash), shown next to the amount in the list
            ->selectSub(fn ($q) => $q->from('customer_credit_applications as cca')->whereColumn('cca.collection_id', 'collections.id')
                ->selectRaw('COALESCE(SUM(cca.amount), 0)'), 'credit_applied')
            ->when($request->tab, function ($q) use ($request) {
                if ($request->tab !== 'all') {
                    $q->where('status', CollectionEnum::fromName($request->tab));
                }
            })
            ->tap($applyFilters)
            ->orderBy('collections.id', 'desc');

        // Get counts per status
        $statusCounts = Collection::select('status', DB::raw('COUNT(*) as total'))
            ->tap($applyFilters)
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Normalize counts for all statuses
        $allCounts = [];
        foreach (CollectionEnum::cases() as $status) {
            $allCounts[$status->name] = $statusCounts[$status->value] ?? 0;
        }
        $allCounts['all'] = array_sum($allCounts);

        // Summary card totals (Draft / Approved) in company currency.
        $draft = CollectionEnum::DRAFT->value;
        $approved = CollectionEnum::APPROVED->value;
        $salesSummary = Collection::select([
            DB::raw("SUM(CASE WHEN status = {$draft} THEN base_grand_total ELSE 0 END) as total_draft_grand"),
            DB::raw("SUM(CASE WHEN status = {$draft} THEN advance_amount ELSE 0 END) as total_draft_advance"),
            DB::raw("SUM(CASE WHEN status = {$approved} THEN base_grand_total ELSE 0 END) as total_approved_grand"),
            DB::raw("SUM(CASE WHEN status = {$approved} THEN advance_amount ELSE 0 END) as total_approved_advance"),
        ])->tap($applyFilters)->first();

        // the collection stores the account's id: the list shows its name (a credit-only collection has no account)
        $accountNames = \App\Models\Finance\Account\Account::withoutGlobalScopes()
            ->where(fn ($q) => $q->where('company_id', companyId())->orWhereNull('company_id'))->pluck('name', 'id');

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->editColumn('account', fn($model) => filled($model->account) && isset($accountNames[$model->account]) ? __($accountNames[$model->account]) : '—')
            ->setRowAttr([
                'data-id' => fn($model) => $model->id,
                'data-name' => fn($model) => 'Collection #' . htmlspecialchars($model->row_no, ENT_QUOTES, 'UTF-8'),
                'class' => 'row-item',
                'id' => fn($model) => 'collection-' . strtolower($model->row_no ?? $model->id),
            ])
            ->editColumn('collection_date', fn($model) => \Carbon\Carbon::parse($model->collection_date)->format('d-m-Y'))
            ->editColumn('currency', fn($model) => strtoupper($model->currency))
            ->addColumn('customer_name', fn($model) => $model->customer->name_en ?? '-')
            ->filterColumn('credit_applied', fn () => null)
            ->addColumn('total_received', fn($model) => number_format((float) ($model->total_amount ?? ($model->sub_total + $model->tax_total)), decimals()))
            // document type badge shown under the number: refunds and advances have their own series
            ->addColumn('doc_label', function ($model) {
                if ($model->doc_type === 'refund') return '<span class="badge bg-warning text-dark">' . e(__('Refund')) . '</span>';
                if (str_starts_with((string) $model->row_no, 'CAV/')) return '<span class="badge bg-success">' . e(__('Advance')) . '</span>';
                return '';
            })
            ->addColumn('advance', function ($model) {
                $h = '';
                if ((float) $model->advance_amount > 0.004) $h .= '<div class="text-success small">+' . number_format((float) $model->advance_amount, decimals()) . ' ' . e(__('advance')) . '</div>';
                if ((float) $model->advance_used > 0.004) $h .= '<div class="small" style="color:#0891b2;">−' . number_format((float) $model->advance_used, decimals()) . ' ' . e(__('advance used')) . '</div>';
                return $h ?: '';
            })
            ->rawColumns(['advance', 'doc_label'])
            ->editColumn('grand_total', fn($model) => number_format($model->grand_total, decimals()))
            ->editColumn('status', fn($model) => CollectionEnum::from($model->status)->label())
            ->with([
                'statusCounts' => $allCounts,
                'salesSummary' => $salesSummary,
            ])
            ->toJson();
    }

    /**
     * Get context menu actions for a collection.
     */
    public function actions($id)
    {
        $collection = Collection::select('id', 'row_no', 'status')->findOrFail($id);
        $contextMenu = collect([]);

        // Status actions
        if ($collection->status === CollectionEnum::DRAFT->value) {
            $contextMenu->push([
                'label' => __('Move to'),
                'type' => 'submenu',
                'separator' => 'after',
                'icon' => 'move_to',
                'items' => [
                    [
                        'label' => __('Approved'),
                        'id' => 'row_approved',
                        'data-id' => $collection->id,
                        'data-value' => CollectionEnum::APPROVED->value,
                        'icon' => 'confirmed'
                    ],
                    [
                        'label' => __('Cancelled'),
                        'id' => 'row_cancelled',
                        'class' => 'row_cancelled',
                        'data-id' => $collection->id,
                        'data-value' => CollectionEnum::CANCELLED->value,
                        'icon' => 'rejected'
                    ]
                ]
            ]);
        } elseif ($collection->status === CollectionEnum::APPROVED->value) {
            $contextMenu->push([
                'label' => __('Move to'),
                'type' => 'submenu',
                'separator' => 'after',
                'icon' => 'move_to',
                'items' => [
                    [
                        'label' => __('Move To Draft'),
                        'id' => 'row_draft',
                        'data-id' => $collection->id,
                        'data-value' => CollectionEnum::DRAFT->value,
                        'icon' => 'pending'
                    ]
                ]
            ]);
        }

        // View, Print, Download actions
        $contextMenu->push([
            'label' => __('View'),
            'id' => 'row_view',
            'class' => 'row_view',
            'data-id' => $collection->id,
            'type' => 'item',
            'icon' => 'view',
        ]);

        $contextMenu->push([
            'label' => __('Print'),
            'id' => 'row_print',
            'class' => 'row_print',
            'data-id' => $collection->id,
            'type' => 'item',
            'icon' => 'print',
            'onclick' => 'COLLECTION.printPreview(' . $collection->id . ')',
        ]);

        $contextMenu->push([
            'label' => __('Download'),
            'id' => 'row_download',
            'class' => 'row_download',
            'data-id' => $collection->id,
            'type' => 'item',
            'icon' => 'download',
            'onclick' => 'COLLECTION.downloadPDF(' . $collection->id . ')',
            'separator' => 'after',
        ]);

        // Edit and Delete actions (only for Draft status)
        if ($collection->status === CollectionEnum::DRAFT->value) {
            $edit = [
                'label' => __('Edit'),
                'id' => 'row_edit',
                'class' => 'row_edit',
                'data-id' => $collection->id,
                'type' => 'item',
                'icon' => 'edit'
            ];

            /*$delete = [
                'label' => __('Delete'),
                'id' => 'row_delete',
                'class' => 'row_delete',
                'data-id' => $collection->id,
                'type' => 'item',
                'icon' => 'delete'
            ];*/

            $contextMenu->push([
                'label' => __('Actions'),
                'type' => 'submenu',
                'icon' => 'action',
                'items' => [$edit]
            ]);
        }

        return response()->json($contextMenu->values());
    }

    /**
     * Remove the specified collection from storage.
     */
    public function destroy($id)
    {
        $collection = Collection::findOrFail($id);
        $guard = app(\App\Services\DeletionGuard::class);
        $why = $guard->blockers('collection', (int) $id);
        if ($why) {
            return $guard->refusal(__('collection'), $why);
        }

        if ($collection->status !== CollectionEnum::DRAFT->value) {
            return response()->json([
                'status' => 'error',
                'message' => __('Only draft collections can be deleted'),
            ], 400);
        }

        // credit chosen on a draft collection is released with it
        DB::table('customer_credit_applications')->where('collection_id', $collection->id)->where('status', 0)->delete();
        \App\Services\Finance\OpeningSettlement::remove('customer', (int) $collection->id);   // the opening balance it held is released
        $collection->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('Collection deleted successfully'),
        ]);
    }

    /**
     * Create finance entries for a collection.
     *
     * Correct accounting for collection:
     *   DR  Bank/Cash account         (amount received net of charges)
     *   DR  Bank Charges Expense      (if any)
     *   DR  Miscellaneous Expenses    (other charges, if any)
     *   CR  Accounts Receivable (AR)  (invoice sub-total collected)
     *   CR  Output VAT Payable        (VAT portion collected)
     *
     * @param Collection $collection
     * @return void
     */
    private function createFinanceEntries(Collection $collection)
    {
        // Delete any existing finance entries for this collection
        $this->deleteFinanceEntries($collection->id);

        // Settled with customer credit only: no cash moved, so there is nothing to post (the credit already sits on the customer's receivable)
        if ((float) $collection->grand_total <= 0.004 && (float) $collection->advance_used <= 0.004) {
            return;
        }
        if ($collection->doc_type === 'refund') {
            $this->createRefundEntries($collection);
            return;
        }
        $advUsed = (float) $collection->advance_used;
        $advExtra = (float) $collection->advance_amount;

        // The amount credited to AR = sub_total + tax_total (full invoice amount)
        // The amount debited to Bank = grand_total - bank_charges - other_charges
        // Bank charges and other charges are DEBITED to expense accounts
        $invoiceTotal = $collection->sub_total + $collection->tax_total;
        $bankCharges  = $collection->bank_charges ?? 0;
        $otherCharges = $collection->other_charges ?? 0;
        $bankReceived = $invoiceTotal - $bankCharges - $otherCharges;

        $baseInvoiceTotal = $collection->base_sub_total + $collection->base_tax_total;
        $baseBankCharges  = $collection->base_bank_charges ?? 0;
        $baseOtherCharges = $collection->base_other_charges ?? 0;
        $baseBankReceived = $baseInvoiceTotal - $baseBankCharges - $baseOtherCharges;

        // Create a new finance entry
        $finance = new Finance();
        $finance->voucher_no = $collection->row_no;
        $finance->voucher_type = 'CV'; // Collection Voucher
        $finance->reference_no = $collection->reference_no;
        $finance->reference_date = $collection->collection_date;
        $finance->customer_id = $collection->customer_id;
        $finance->narration = $collection->notes ?: ($advExtra > 0.004 && !CollectionInvoice::where('collection_id', $collection->id)->exists() && !\App\Services\Finance\OpeningSettlement::ofDocument('customer', $collection->id) ? 'Advance received from customer' : 'Collection from customer');
        $finance->currency = $collection->currency;
        $finance->exchange_rate = $collection->currency_rate;
        $finance->total_debit = $invoiceTotal + $advUsed;
        $finance->total_credit = $invoiceTotal + $advUsed;
        $finance->base_currency = 'SAR';
        $finance->base_total_debit = $baseInvoiceTotal + $advUsed;
        $finance->base_total_credit = $baseInvoiceTotal + $advUsed;
        $finance->job_id = $collection->job_id;
        $finance->job_no = $collection->job_no ?? '';
        $finance->is_approved = 1;
        $finance->posted_at = now();
        $finance->linked_id = $collection->id;
        $finance->linked_type = Collection::class;
        $finance->company_id = $collection->company_id;
        $finance->user_id = Auth::id();
        $finance->save();

        // Get collection invoices
        $collectionInvoices = CollectionInvoice::with('customerInvoice')
            ->where('collection_id', $collection->id)
            ->get();

        // Create finance sub entries
        $financeSubs = [];

        $commonData = [
            'finance_id'       => $finance->id,
            'voucher_no'       => $finance->voucher_no,
            'voucher_type'     => $finance->voucher_type,
            'reference_no'     => $finance->reference_no,
            'customer_id'      => $collection->customer_id,
            'base_currency'    => 'SAR',
            'exchange_rate'    => $collection->currency_rate,
            'currency'         => $collection->currency,
            'job_id'           => $collection->job_id ?? null,
            'job_no'           => $collection->job_no ?? '',
            'cost_center_id'   => null,
            'is_tax_line'      => 0,
            'is_auto_generated'=> 1,
            'linked_id'        => $collection->id,
            'linked_type'      => Collection::class,
            'user_id'          => Auth::id(),
            'company_id'       => $collection->company_id,
            'created_at'       => now(),
            'updated_at'       => now(),
        ];

        // DR Bank/Cash account (amount actually received = invoice total minus charges); nothing when advance settles it all
        if ($bankReceived > 0.004 && $collection->account) {
            $financeSubs[] = array_merge($commonData, [
                'account_id'       => $collection->account,
                'reference_date'   => formDate($collection->collection_date),
                'description'      => 'Collection received from customer',
                'debit'            => $bankReceived,
                'credit'           => 0,
                'base_debit'       => $baseBankReceived,
                'base_credit'      => 0,
            ]);
        }

        // DR Accounts Receivable: the customer's earlier advance (a credit sitting on the receivable) is used up against the invoices
        if ($advUsed > 0.004) {
            $financeSubs[] = array_merge($commonData, [
                'account_id'     => 5,
                'reference_date' => formDate($collection->collection_date),
                'reference_no'   => 'Advance',
                'description'    => 'Advance applied to invoices',
                'debit'          => $advUsed,
                'credit'         => 0,
                'base_debit'     => $advUsed,
                'base_credit'    => 0,
            ]);
        }
        // CR Accounts Receivable: the excess received over the invoices stays as the customer's advance
        if ($advExtra > 0.004) {
            $financeSubs[] = array_merge($commonData, [
                'account_id'     => 5,
                'reference_date' => formDate($collection->collection_date),
                'reference_no'   => 'Advance',
                'description'    => 'Advance received from customer',
                'debit'          => 0,
                'credit'         => $advExtra,
                'base_debit'     => 0,
                'base_credit'    => $advExtra,
            ]);
        }

        // DR Bank Charges Expense (if any)
        if ($bankCharges > 0) {
            $financeSubs[] = array_merge($commonData, [
                'account_id'     => 54, // 5260 Bank Charges expense account
                'reference_date' => formDate($collection->collection_date),
                'description'    => 'Bank charges on collection',
                'debit'          => $bankCharges,
                'credit'         => 0,
                'base_debit'     => $baseBankCharges,
                'base_credit'    => 0,
            ]);
        }

        // DR Miscellaneous Expenses (other charges, if any)
        if ($otherCharges > 0) {
            $financeSubs[] = array_merge($commonData, [
                'account_id'     => 56, // 5280 Miscellaneous Expenses account
                'reference_date' => formDate($collection->collection_date),
                'description'    => 'Other charges on collection',
                'debit'          => $otherCharges,
                'credit'         => 0,
                'base_debit'     => $baseOtherCharges,
                'base_credit'    => 0,
            ]);
        }

        // CR Accounts Receivable per invoice (full amount collected).
        // The VAT liability was already recognised when the invoice was
        // approved (DR AR / CR Revenue / CR Output VAT), so the collection
        // must clear AR for the gross amount — crediting Output VAT again
        // here would double-count the tax and leave a phantom AR balance.
        // A foreign-currency invoice is cleared at the rate it was booked at; the bank gets today's rate and the
        // difference is an exchange gain (credit) or loss (debit), so the customer ends at 0 in SAR as well.
        $fxDifference = 0.0;
        foreach ($collectionInvoices as $collectionInvoice) {
            $invoice = $collectionInvoice->customerInvoice;
            $bookedRate = (float) $invoice->grand_total > 0 && (float) $invoice->base_grand_total > 0
                ? (float) $invoice->base_grand_total / (float) $invoice->grand_total
                : (float) $collection->currency_rate;
            $arBase = round($collectionInvoice->amount * $bookedRate, 2);
            $fxDifference += round($collectionInvoice->amount * $collection->currency_rate, 2) - $arBase;

            $financeSubs[] = array_merge($commonData, [
                'account_id'     => 5, // 1130 Accounts Receivable
                'reference_date' => formDate($collection->collection_date),
                'reference_no'   => $invoice->row_no,
                'description'    => 'Collection for invoice ' . $invoice->row_no,
                'debit'          => 0,
                'credit'         => $collectionInvoice->amount,
                'base_debit'     => 0,
                'base_credit'    => $arBase,
                'job_id'         => $invoice->job_id ?? null,
                'job_no'         => $invoice->job_no ?? '',
                'linked_id'      => $collectionInvoice->id,
                'linked_type'    => CollectionInvoice::class,
            ]);
        }

        // CR Accounts Receivable for the part of the customer's opening balance collected (in the company currency, no exchange rate)
        $openingPart = \App\Services\Finance\OpeningSettlement::ofDocument('customer', $collection->id);
        if ($openingPart > 0) {
            $financeSubs[] = array_merge($commonData, [
                'account_id'     => 5,
                'reference_date' => formDate($collection->collection_date),
                'reference_no'   => 'Opening balance',
                'description'    => 'Collection against opening balance',
                'debit'          => 0,
                'credit'         => $openingPart,
                'base_debit'     => 0,
                'base_credit'    => round($openingPart * (float) $collection->currency_rate, 2),
            ]);
        }

        $fxDifference = round($fxDifference, 2);
        if (abs($fxDifference) >= 0.01) {
            $financeSubs[] = array_merge($commonData, [
                'account_id'     => \App\Models\Finance\Account\Account::exchangeAccountId(), // 4180 Exchange Gain / Loss
                'reference_date' => formDate($collection->collection_date),
                'description'    => 'Exchange ' . ($fxDifference > 0 ? 'gain' : 'loss') . ' on collection ' . $collection->row_no,
                'debit'          => 0,
                'credit'         => 0,
                'base_debit'     => $fxDifference < 0 ? -$fxDifference : 0,
                'base_credit'    => $fxDifference > 0 ? $fxDifference : 0,
                'exchange_rate'  => 1,
            ]);
            $finance->base_total_debit = round($baseInvoiceTotal + max(0, -$fxDifference), 2);
            $finance->base_total_credit = $finance->base_total_debit;
            $finance->save();
        }

        // Insert all finance sub entries
        FinanceSub::insert($financeSubs);
    }

    /** A refund pays an advance back to the customer: DR Accounts Receivable (the advance credit is cleared), CR Bank. */
    private function createRefundEntries(Collection $collection): void
    {
        $amt = round((float) $collection->grand_total, 2);
        $finance = new Finance();
        $finance->voucher_no = $collection->row_no;
        $finance->voucher_type = 'CV';
        $finance->reference_no = $collection->reference_no;
        $finance->reference_date = $collection->collection_date;
        $finance->customer_id = $collection->customer_id;
        $finance->narration = $collection->notes ?: 'Refund to customer';
        $finance->currency = $collection->currency;
        $finance->exchange_rate = 1;
        $finance->total_debit = $amt;
        $finance->total_credit = $amt;
        $finance->base_currency = 'SAR';
        $finance->base_total_debit = $amt;
        $finance->base_total_credit = $amt;
        $finance->is_approved = 1;
        $finance->posted_at = now();
        $finance->linked_id = $collection->id;
        $finance->linked_type = Collection::class;
        $finance->company_id = $collection->company_id;
        $finance->user_id = Auth::id();
        $finance->save();

        $common = [
            'finance_id' => $finance->id, 'voucher_no' => $finance->voucher_no, 'voucher_type' => 'CV', 'reference_no' => $finance->reference_no,
            'customer_id' => $collection->customer_id, 'base_currency' => 'SAR', 'exchange_rate' => 1, 'currency' => $collection->currency,
            'cost_center_id' => null, 'is_tax_line' => 0, 'is_auto_generated' => 1, 'linked_id' => $collection->id, 'linked_type' => Collection::class,
            'user_id' => Auth::id(), 'company_id' => $collection->company_id, 'created_at' => now(), 'updated_at' => now(),
            'reference_date' => formDate($collection->collection_date),
        ];
        FinanceSub::insert([
            array_merge($common, ['account_id' => 5, 'description' => 'Refund of advance to customer', 'debit' => $amt, 'credit' => 0, 'base_debit' => $amt, 'base_credit' => 0]),
            array_merge($common, ['account_id' => $collection->account, 'description' => 'Refund paid to customer', 'debit' => 0, 'credit' => $amt, 'base_debit' => 0, 'base_credit' => $amt]),
        ]);
    }

    /**
     * Delete finance entries for a collection.
     *
     * @param int $collectionId
     * @return void
     */
    private function deleteFinanceEntries($collectionId)
    {
        $collection = Collection::findOrFail($collectionId);

        // Get finance entries using the relationship and delete them
        // This will also delete related finance sub entries due to the cascade delete in the database
        foreach ($collection->financeEntries as $finance) {
            // Delete finance sub entries first
            $finance->financeSubs()->delete();

            // Then delete the finance entry
            $finance->delete();
        }
    }

    /** The Advance / Refund rights (config/modules.php) have no URL of their own, so the middleware cannot check them: done here. */
    private function deniedRight(string $right)
    {
        if (userCan($right, 'create')) return null;
        $message = __('You do not have permission to do this. Contact your administrator.');

        return request()->expectsJson() || request()->ajax()
            ? response()->json(['status' => 'error', 'message' => $message], 403)
            : abort(403, $message);
    }
}

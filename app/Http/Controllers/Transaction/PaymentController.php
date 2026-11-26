<?php

namespace App\Http\Controllers\Transaction;

use App\Services\DocumentNumber;
use App\Enums\PaymentEnum;
use App\Http\Controllers\Controller;
use App\Models\Finance\Account\Account;
use App\Models\Finance\Finance;
use App\Models\Finance\FinanceSub;
use App\Models\Finance\Payment\PaymentAdditionalTransaction;
use App\Models\Finance\Payment\Payment;
use App\Models\Finance\Payment\PaymentInvoice;
use App\Models\Finance\SupplierInvoice\SupplierInvoice;
use App\Models\Job\Job;
use App\Models\Supplier\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PaymentController extends Controller
{
    /**
     * Display a listing of the payments.
     */
    public function index()
    {
        return view('modules.transaction.payment.list');
    }

    /**
     * Show the form for creating a new payment.
     */
    public function modal(Request $request)
    {
        $payment = new Payment();
        $suppliers = Supplier::select('id', 'name_en')->orderBy('name_en')->get();
        //$jobs = Job::select('id', 'row_no')->orderBy('row_no', 'desc')->get();
        //$parentAccounts = Account::whereIn('name', ['Bank Accounts', 'Cash in Hand'])->pluck('id')->toArray();
        $parents = Account::whereIn('id', [3, 4])->orderBy('name')->get();//2=>Bank & Cash sub accounts

        // Filter for accounts that are intended to be "headers" (optgroups)
        // Usually these are is_grouped = 1

        // Filter for the actual selectable accounts for Paid Through
        $paidThroughAccounts = Account::whereIn('parent_id', [3, 4])->where('is_grouped', 0)->orderBy('name')->get();

        // Get all accounts for additional transactions — excluding system-managed
        // equity accounts (Owner Capital, Retained Earnings, Current Year
        // Profit/Loss) that shouldn't be posted to manually.
        $subAccounts = Account::where('is_grouped', 0)->where('type', '!=', 'Equity')->orderBy('name')->get();
        // Initialize empty supplier invoices collection for new payment
        $supplierInvoices = collect();
        $selectedInvoice = [];

        $advanceMode = $request->boolean('advance');
        if ($advanceMode && ($denied = $this->deniedRight('transactions.payment-advances'))) return $denied;

        return view('modules.transaction.payment.payment-form', compact('payment', 'suppliers', 'parents', 'paidThroughAccounts', 'subAccounts', 'supplierInvoices', 'selectedInvoice', 'advanceMode'));
    }

    /**
     * Show the form for editing the specified payment.
     */
    public function edit($id)
    {
        $payment = Payment::with(['paymentInvoices', 'additionalTransactions'])->findOrFail($id);
        if ($payment->doc_type === 'refund') {
            $suppliers = Supplier::select('id', 'name_en')->orderBy('name_en')->get();
            $parents = Account::whereIn('id', [3, 4])->orderBy('name')->get();
            $paidThroughAccounts = Account::whereIn('parent_id', [3, 4])->where('is_grouped', 0)->orderBy('name')->get();
            $available = \App\Services\Finance\PartyAdvance::available('supplier', (int) $payment->supplier_id, (int) $payment->id);

            return view('modules.transaction.payment.refund-form', compact('payment', 'suppliers', 'parents', 'paidThroughAccounts', 'available'));
        }

        $suppliers = Supplier::select('id', 'name_en')->orderBy('name_en')->get();

        $parents = Account::whereIn('id', [3, 4])->orderBy('name')->get();//2=>Bank & Cash sub accounts

        // Filter for the actual selectable accounts for Paid Through
        $paidThroughAccounts = Account::whereIn('parent_id', [3, 4])->where('is_grouped', 0)->orderBy('name')->get();

        // Get all accounts for additional transactions — excluding system-managed
        // equity accounts (Owner Capital, Retained Earnings, Current Year
        // Profit/Loss) that shouldn't be posted to manually.
        $subAccounts = Account::where('is_grouped', 0)->where('type', '!=', 'Equity')->orderBy('name')->get(); // All accounts for additional transactions

        // Get the IDs of invoices already included in this payment
        $selectedInvoiceIds = $payment->paymentInvoices->pluck('supplier_invoice_id')->toArray();

        // Get supplier invoices for the selected supplier
        $supplierInvoices = SupplierInvoice::where('supplier_id', $payment->supplier_id)
            ->orderBy('invoice_date', 'desc')
            ->get();

        // For each invoice, calculate the total amount already paid
        foreach ($supplierInvoices as $key => $invoice) {
            // Sum all payment amounts for this invoice, excluding the current payment
            $totalPaid = PaymentInvoice::where('supplier_invoice_id', $invoice->id)
                ->where('payment_id', '!=', $payment->id)
                ->sum('amount');

            // Add the paid amount and remaining balance to the invoice object
            $invoice->paid_amount = $totalPaid;
            $invoice->balance_amount = $invoice->grand_total - $totalPaid;

            // Remove invoices with zero balance unless they are already selected in this payment
            if ($invoice->balance_amount <= 0 && !in_array($invoice->id, $selectedInvoiceIds)) {
                $supplierInvoices->forget($key);
            }
        }

        $selectedInvoice = $payment->paymentInvoices->pluck('amount', 'supplier_invoice_id')->toArray();
        $advanceAvailable = \App\Services\Finance\PartyAdvance::available('supplier', (int) $payment->supplier_id, (int) $payment->id);
        $advanceMode = $payment->paymentInvoices->isEmpty() && (float) $payment->advance_amount > 0.004 && \App\Services\Finance\OpeningSettlement::ofDocument('supplier', (int) $payment->id) <= 0.004;
        $opening = ['balance' => \App\Services\Finance\OpeningSettlement::outstanding('supplier', (int) $payment->supplier_id, (int) $payment->id),
            'chosen' => \App\Services\Finance\OpeningSettlement::ofDocument('supplier', (int) $payment->id),
            'currency' => app(\App\Services\Finance\OpeningBalanceService::class)->baseCurrency()];
        return view('modules.transaction.payment.payment-form', compact('payment', 'suppliers', 'parents', 'paidThroughAccounts', 'subAccounts', 'supplierInvoices', 'selectedInvoice', 'opening', 'advanceAvailable', 'advanceMode'));
    }

    /**
     * Store a newly created payment in storage.
     */
    public function store(Request $request)
    {
        $request->merge(['supplier' => decodeId($request->input('supplier'))]);
        $validated = $request->validate([
            'supplier' => 'required|exists:suppliers,id',
            //'job_id' => 'nullable|exists:jobs,id',
            'payment_date' => 'required|date',
            'account' => $this->paidEntirelyFromAdvance($request) ? 'nullable' : 'required',
            //'payment_method' => 'required|string',
            'reference_no' => 'nullable|string',
            'currency' => 'required|string|size:3',
            'currency_rate' => 'required|numeric|min:0',
            'bank_charges' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'supplier_invoice_ids' => 'nullable|array',
            'supplier_invoice_ids.*' => 'exists:supplier_invoices,id',
            'invoice_amounts' => 'nullable|array',
            'invoice_amounts.*' => 'numeric|min:0',
            'opening_amount' => 'nullable|numeric|min:0',
            'total_paid' => 'nullable|numeric|min:0',
            'additional_transaction_accounts' => 'nullable|array',
            'additional_transaction_accounts.*' => 'exists:accounts,id',
            'additional_transaction_descriptions' => 'nullable|array',
            'additional_transaction_amounts' => 'nullable|array',
            'additional_transaction_amounts.*' => 'numeric|min:0',
            'additional_transaction_types' => 'nullable|array',
            'additional_transaction_types.*' => 'in:debit,credit',
        ]);

        $request->merge(['supplier_invoice_ids' => (array) $request->input('supplier_invoice_ids', []), 'invoice_amounts' => (array) $request->input('invoice_amounts', [])]);
        $openingAmount = round((float) $request->input('opening_amount', 0), 2);
        $typedTotal = $request->filled('total_paid') ? round((float) $request->input('total_paid'), 2) : null;
        if (!$request->input('supplier_invoice_ids') && $openingAmount <= 0 && ($typedTotal ?? 0) <= 0) {
            throw \Illuminate\Validation\ValidationException::withMessages(['supplier_invoice_ids' => __('Please select at least one invoice')]);
        }

        if (!$request->filled('payment_id') && $request->boolean('advance_mode') && ($denied = $this->deniedRight('transactions.payment-advances'))) return $denied;

        $this->assertPeriodOpen($validated['payment_date'], 'payment_date');
        $this->assertPartyCurrency(\App\Models\Supplier\Supplier::class, $validated['supplier'], $validated['currency']);

        DB::beginTransaction();
        try {
            // Create or update payment
            $isUpdate = $request->filled('payment_id');
            $oldPaymentInvoices = [];

            if ($isUpdate) {
                $payment = Payment::findOrFail($request->input('payment_id'));
                $payment->updated_by = Auth::id();

                // If this is an approved payment, we need to revert the paid_amount in supplier invoices
                if ($payment->status == PaymentEnum::APPROVED->value) {
                    $oldPaymentInvoices = PaymentInvoice::where('payment_id', $payment->id)
                        ->get()
                        ->keyBy('supplier_invoice_id')
                        ->toArray();

                    // Revert paid_amount in supplier invoices
                    foreach ($oldPaymentInvoices as $invoiceId => $oldPaymentInvoice) {
                        $supplierInvoice = SupplierInvoice::find($invoiceId);
                        if ($supplierInvoice && $supplierInvoice->paid_amount) {
                            $supplierInvoice->paid_amount = max(0, $supplierInvoice->paid_amount - $oldPaymentInvoice['amount']);
                            $supplierInvoice->save();
                        }
                    }
                }
            } else {
                $payment = new Payment();
                $payment->status = PaymentEnum::DRAFT->value;
                $payment->company_id = companyId();
                $payment->created_by = Auth::id();

                // Generate row_no: an advance (opened from "New Advance", nothing settled) has its own PAV series
                $isAdvance = $request->boolean('advance_mode') && !$request->input('supplier_invoice_ids') && $openingAmount <= 0;
                $prefix = ($isAdvance ? 'PAV/' : 'PAY/') . date('y') . '/';
                $payment->unique_row_no = DocumentNumber::next(Payment::class, $prefix);
                $payment->row_no = $prefix . sprintf('%04d', $payment->unique_row_no);
            }

            // Set job_no if job_id is provided
            /*if ($validated['job_id']) {
                $job = Job::select('row_no')->find($validated['job_id']);
                $payment->job_no = $job->row_no;
            }*/

            // Calculate totals
            $subTotal = 0;
            $taxTotal = 0;
            $bankCharges = $request->input('bank_charges', 0);
            $otherCharges = $request->input('other_charges', 0);

            foreach ($request->input('supplier_invoice_ids') as $index => $invoiceId) {
                $invoice = SupplierInvoice::find($invoiceId);
                $amount = $request->input('invoice_amounts')[$invoiceId];

                // Validate that amount doesn't exceed the invoice grand_total
                if ($amount > $invoice->grand_total) {
                    return redirect()->back()->withErrors(['error' => 'Payment amount cannot exceed the invoice total amount'])->withInput();
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

            // Part of the supplier's opening balance settled by this payment (no VAT: it is a plain payable)
            if ($openingAmount > 0) {
                $left = \App\Services\Finance\OpeningSettlement::outstanding('supplier', (int) $validated['supplier'], $isUpdate ? (int) $payment->id : null);
                if ($openingAmount > $left + 0.004) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['opening_amount' => __('The opening balance left to pay is :a.', ['a' => number_format($left, 2)])]);
                }
                $baseCur = app(\App\Services\Finance\OpeningBalanceService::class)->baseCurrency();
                if (strtoupper((string) $validated['currency']) !== $baseCur) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['currency' => __('The opening balance is in :cur. Pay it in :cur.', ['cur' => $baseCur])]);
                }
                $subTotal += $openingAmount;
            }

            // Total paid typed on the form: more than the ticked invoices = the excess is an advance to the supplier (a debit kept on the
            // payable); less = the difference is taken from the supplier's earlier advance. Anything else is refused.
            $advExtra = 0.0;
            $advUsed = 0.0;
            $allocatedNow = round($subTotal + $taxTotal, 2);
            if ($typedTotal !== null && abs($typedTotal - $allocatedNow) >= 0.005) {
                $baseCur = app(\App\Services\Finance\OpeningBalanceService::class)->baseCurrency();
                if (strtoupper((string) $validated['currency']) !== $baseCur) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['total_paid' => __('Total paid must equal the ticked invoices (advance is kept in :cur only).', ['cur' => $baseCur])]);
                }
                $diff = round($typedTotal - $allocatedNow, 2);
                if ($diff > 0) {
                    $advExtra = $diff;
                } else {
                    $advUsed = -$diff;
                    $availAdv = \App\Services\Finance\PartyAdvance::available('supplier', (int) $validated['supplier'], $isUpdate ? (int) $payment->id : null);
                    if ($advUsed > $availAdv + 0.004) {
                        throw \Illuminate\Validation\ValidationException::withMessages(['total_paid' => __('Total paid :t is less than the chosen invoices :s, and the supplier has only :a advance to cover the difference.', ['t' => number_format($typedTotal, 2), 's' => number_format($allocatedNow, 2), 'a' => number_format($availAdv, 2)])]);
                    }
                }
                $subTotal += $diff;
            }

            // Additional transactions each post their own single-sided debit
            // or credit line (see createFinanceEntries below) — their other
            // side comes out of the same Bank/Cash account this payment
            // credits, so grand_total (what's credited to Bank/Cash) must
            // include their net effect or the posting goes out of balance.
            $additionalTransactionsNet = 0;
            if ($request->has('additional_transaction_accounts') && is_array($request->input('additional_transaction_accounts'))) {
                foreach ($request->input('additional_transaction_accounts') as $index => $accountId) {
                    $amount = $request->input('additional_transaction_amounts')[$index] ?? null;
                    if (empty($accountId) || empty($amount)) {
                        continue;
                    }
                    $isDebit = ($request->input('additional_transaction_types')[$index] ?? null) === 'debit';
                    $additionalTransactionsNet += $isDebit ? (float) $amount : -(float) $amount;
                }
            }

            $grandTotal = $subTotal + $taxTotal + $bankCharges + $otherCharges + $additionalTransactionsNet;

            // Update payment fields
            $payment->supplier_id = $validated['supplier'];
            //$payment->job_id = $validated['job_id'];
            $payment->payment_date = $validated['payment_date'];
            $payment->account = is_array($validated['account'] ?? null) ? ($validated['account'][0] ?? null) : ($validated['account'] ?? null);
            //$payment->payment_method = $validated['payment_method'];
            $payment->reference_no = $validated['reference_no'] ?? null;
            $payment->currency = $validated['currency'];
            $payment->currency_rate = $validated['currency_rate'];
            $payment->sub_total = $subTotal;
            $payment->tax_total = $taxTotal;
            $payment->bank_charges = $bankCharges;
            $payment->other_charges = $otherCharges;
            $payment->grand_total = $grandTotal;
            $payment->base_sub_total = $subTotal * $validated['currency_rate'];
            $payment->base_tax_total = $taxTotal * $validated['currency_rate'];
            $payment->base_bank_charges = $bankCharges * $validated['currency_rate'];
            $payment->base_other_charges = $otherCharges * $validated['currency_rate'];
            $payment->base_grand_total = $grandTotal * $validated['currency_rate'];
            $payment->notes = $validated['notes'] ?? null;
            $payment->total_amount = $typedTotal ?? $allocatedNow;
            $payment->advance_amount = $advExtra;
            $payment->advance_used = $advUsed;
            $payment->doc_type = 'receipt';

            $payment->save();

            // Delete existing payment invoices
            PaymentInvoice::where('payment_id', $payment->id)->delete();
            \App\Services\Finance\OpeningSettlement::save('supplier', (int) $validated['supplier'], (int) $payment->id, $openingAmount);

            // Delete existing additional transactions
            PaymentAdditionalTransaction::where('payment_id', $payment->id)->delete();

            // Create payment invoices
            $paymentInvoices = [];
            foreach ($request->input('supplier_invoice_ids') as $index => $invoiceId) {
                $paymentInvoices[] = [
                    'payment_id' => $payment->id,
                    'supplier_invoice_id' => $invoiceId,
                    'company_id' => companyId(),
                    'amount' => $request->input('invoice_amounts')[$invoiceId],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Create additional transactions if any
            $additionalTransactions = [];
            if ($request->has('additional_transaction_accounts') && is_array($request->input('additional_transaction_accounts'))) {
                foreach ($request->input('additional_transaction_accounts') as $index => $accountId) {
                    if (empty($accountId) || empty($request->input('additional_transaction_amounts')[$index])) {
                        continue;
                    }

                    $additionalTransactions[] = [
                        'payment_id' => $payment->id,
                        'account_id' => $accountId,
                        'description' => $request->input('additional_transaction_descriptions')[$index] ?? '',
                        'amount' => $request->input('additional_transaction_amounts')[$index],
                        'is_debit' => $request->input('additional_transaction_types')[$index] === 'debit',
                        'company_id' => companyId(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (!empty($paymentInvoices)) {
                DB::table('payment_invoices')->insert($paymentInvoices);

                // If this is an approved payment, update the paid_amount in supplier invoices
                if ($payment->status == PaymentEnum::APPROVED->value) {
                    foreach ($request->input('supplier_invoice_ids') as $index => $invoiceId) {
                        $amount = $request->input('invoice_amounts')[$invoiceId];
                        $supplierInvoice = SupplierInvoice::find($invoiceId);
                        if ($supplierInvoice) {
                            $supplierInvoice->paid_amount = ($supplierInvoice->paid_amount ?? 0) + $amount;
                            $supplierInvoice->save();
                        }
                    }
                }
            }

            // Insert additional transactions if any
            if (!empty($additionalTransactions)) {
                DB::table('payment_additional_transactions')->insert($additionalTransactions);
            }

            // If payment is approved, update finance entries
            if ($payment->status == PaymentEnum::APPROVED->value) {
                $this->createFinanceEntries($payment);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => __('Payment saved successfully'),
                'payment_id' => $payment->id,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => __('Error saving payment: ') . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified payment.
     */
    public function show($id)
    {
        $payment = Payment::with(['supplier', 'job', 'paymentInvoices.supplierInvoice', 'additionalTransactions.account', 'createdBy', 'approvedBy'])
            ->findOrFail($id);

        return view('modules.transaction.payment.show', compact('payment'));
    }

    /**
     * Return the drawer (offcanvas) partial for the specified payment.
     */
    public function overviewDrawer($id)
    {
        $payment = Payment::with(['supplier', 'job', 'paymentInvoices.supplierInvoice', 'additionalTransactions.account', 'documents', 'createdBy', 'approvedBy'])
            ->findOrFail($id);

        [$origin, $timeline] = $this->paymentTimeline($payment);

        return view('modules.transaction.payment.view-overview-drawer', compact('payment', 'origin', 'timeline'));
    }

    /**
     * Time frame for the payment drawer: where the job came from
     * (enquiry -> quotation -> job), the supplier invoice(s) being paid,
     * then the payment's own life (created / updated / approved / cancelled).
     */
    private function paymentTimeline(Payment $payment): array
    {
        $events = [];
        $rank = 0;
        $add = function ($label, $at, $icon, $module, $by = null, $meta = null, $link = null) use (&$events, &$rank) {
            if (!$at) return;
            $events[] = ['label' => $label, 'at' => $at, 'icon' => $icon, 'module' => $module, 'by' => $by, 'meta' => $meta, 'link' => $link,
                'rank' => $rank, 'key' => \Illuminate\Support\Carbon::parse($at)->timestamp, 'seq' => count($events)];
        };

        $invoices = $payment->paymentInvoices->map->supplierInvoice->filter();
        $jobId = $payment->job_id ?: $invoices->pluck('job_id')->filter()->first();
        $job = $jobId ? \App\Models\Job\Job::find($jobId) : null;
        $quotation = $job && $job->quotation_id ? \App\Models\Quotation\Quotation::find($job->quotation_id) : null;
        $enquiry = $quotation && $quotation->enquiry_id ? \App\Models\Enquiry\Enquiry::find($quotation->enquiry_id) : null;

        if ($enquiry) $add(__('Enquiry created') . ' · ' . $enquiry->row_no, $enquiry->created_at, 'bi-chat-left-text', 'enquiry', null, null, ['enquiry', $enquiry->id, $enquiry->row_no]);
        if ($quotation) $add(__('Quotation posted') . ' · ' . $quotation->row_no, $quotation->created_at, 'bi-file-earmark-text', 'quotation', null, null, ['quotation', $quotation->id, $quotation->row_no]);
        if ($job) $add(($quotation ? __('Converted to job') : __('Job created')) . ' · ' . $job->row_no, $job->created_at, 'bi-briefcase', 'job', null, null, ['job', $job->id, $job->row_no]);

        $rank = 1;
        $origin = $quotation
            ? ($enquiry ? __('Enquiry') . ' → ' : '') . __('Quotation') . ' → ' . __('Job') . ' → ' . __('Supplier Invoice') . ' → ' . __('Payment')
            : ($job ? __('Job') . ' → ' . __('Supplier Invoice') . ' → ' . __('Payment') : ($invoices->count() ? __('Supplier Invoice') . ' → ' . __('Payment') : __('Payment created directly')));

        foreach ($invoices as $inv) {
            $add(__('Supplier invoice created') . ' · ' . $inv->row_no, $inv->created_at, 'bi-receipt', 'supplier_invoice', null,
                number_format((float) $inv->grand_total, decimals()), ['supplier_invoice', $inv->id, $inv->row_no]);
        }

        $labels = [PaymentEnum::DRAFT->value => __('Moved to Draft'), PaymentEnum::APPROVED->value => __('Approved'), PaymentEnum::CANCELLED->value => __('Cancelled')];
        $ignore = ['status', 'updated_at', 'approved_by', 'approved_at', 'disapproval_reason'];
        $seenCreate = false;
        $logs = \App\Models\Log\LogHistory::where('loggable_type', Payment::class)
            ->where('loggable_id', $payment->id)->where('created_at', '>=', $payment->created_at->copy()->subSeconds(5))->orderBy('id')->get();
        foreach ($logs as $log) {
            $by = $log->user_id['name'] ?? null;
            if ($log->action === 'created') {
                $seenCreate = true;
                $add(__('Payment created') . ' · ' . $payment->row_no, $log->created_at, 'bi-cash-coin', 'payment', $by, number_format((float) $payment->grand_total, decimals()));
            } elseif ($log->action === 'updated') {
                $new = $log->changes['new'] ?? [];
                if (isset($new['status'])) {
                    $st = (int) $new['status'];
                    $add($labels[$st] ?? __('Status changed'), $log->created_at,
                        $st === PaymentEnum::APPROVED->value ? 'bi-check-circle' : ($st === PaymentEnum::CANCELLED->value ? 'bi-x-circle' : 'bi-clock'), 'payment', $by);
                } elseif (array_diff(array_keys($new), $ignore)) {
                    $add(__('Payment updated'), $log->created_at, 'bi-pencil-square', 'payment', $by);
                }
            }
        }
        if (!$seenCreate) $add(__('Payment created') . ' · ' . $payment->row_no, $payment->created_at, 'bi-cash-coin', 'payment', null, number_format((float) $payment->grand_total, decimals()));

        usort($events, fn($a, $b) => [$a['rank'], $a['rank'] ? $a['key'] : $a['seq'], $a['seq']] <=> [$b['rank'], $b['rank'] ? $b['key'] : $b['seq'], $b['seq']]);

        return [$origin, $events];
    }

    /**
     * Get supplier invoices for a specific supplier.
     */
    public function getSupplierInvoices($supplierId)
    {
        $invoices = [];
        if ($supplierId) {
            // Get all approved invoices for the supplier
            $invoices = SupplierInvoice::where('supplier_id', decodeId($supplierId))
                ->where('status', 3) // Assuming 3 is the status for approved invoices
                ->orderBy('invoice_date', 'desc')
                ->get();

            // For each invoice, calculate the total amount already paid
            foreach ($invoices as $key => $invoice) {
                // Sum all payment amounts for this invoice
                $totalPaid = PaymentInvoice::where('supplier_invoice_id', $invoice->id)->sum('amount');

                // Add the paid amount and remaining balance to the invoice object
                $invoice->paid_amount = $totalPaid;
                $invoice->balance_amount = $invoice->grand_total - $totalPaid;

                // Remove invoices with zero balance
                if ($invoice->balance_amount <= 0) {
                    $invoices->forget($key);
                }
            }
        }

        return response()->json($invoices->values());
    }

    /** Everything is settled from the supplier's advance: nothing is paid in cash, so no bank / cash account is needed. */
    private function paidEntirelyFromAdvance(Request $request): bool
    {
        if (!$request->filled('total_paid') || round((float) $request->input('total_paid'), 2) > 0.004) {
            return false;
        }
        $alloc = collect((array) $request->input('invoice_amounts', []))->sum(fn ($a) => (float) $a) + (float) $request->input('opening_amount', 0);
        $extras = (float) $request->input('bank_charges', 0) + (float) $request->input('other_charges', 0) + collect((array) $request->input('additional_transaction_amounts', []))->sum(fn ($a) => (float) $a);

        return $alloc > 0.004 && $extras <= 0.004;
    }

    /** Advance paid to the supplier that can be used on invoices or refunded back to us. */
    public function getSupplierAdvance($supplierId)
    {
        $id = (int) decodeId($supplierId);
        $pay = request()->filled('payment') ? (int) request('payment') : null;

        return response()->json(['available' => $id ? \App\Services\Finance\PartyAdvance::available('supplier', $id, $pay) : 0,
            'currency' => app(\App\Services\Finance\OpeningBalanceService::class)->baseCurrency()]);
    }

    /** Refund form: money the supplier returns out of the advance we paid them. */
    public function refundModal()
    {
        if ($denied = $this->deniedRight('transactions.payment-refunds')) return $denied;
        $payment = new Payment();
        $suppliers = Supplier::select('id', 'name_en')->orderBy('name_en')->get();
        $parents = Account::whereIn('id', [3, 4])->orderBy('name')->get();
        $paidThroughAccounts = Account::whereIn('parent_id', [3, 4])->where('is_grouped', 0)->orderBy('name')->get();
        $available = 0.0;

        return view('modules.transaction.payment.refund-form', compact('payment', 'suppliers', 'parents', 'paidThroughAccounts', 'available'));
    }

    public function storeRefund(Request $request)
    {
        if (!$request->filled('payment_id') && ($denied = $this->deniedRight('transactions.payment-refunds'))) return $denied;
        $request->merge(['supplier' => decodeId($request->input('supplier'))]);
        $request->merge(['amount' => is_numeric($request->input('amount')) ? round((float) $request->input('amount'), 2) : $request->input('amount')]);
        $v = $request->validate([
            'supplier' => 'required|exists:suppliers,id',
            'payment_date' => 'required|date',
            'account' => 'required',
            'amount' => 'required|numeric|gt:0',
            'reference_no' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);
        $this->assertPeriodOpen($v['payment_date'], 'payment_date');
        $cur = app(\App\Services\Finance\OpeningBalanceService::class)->baseCurrency();

        $payment = $request->filled('payment_id') ? Payment::findOrFail($request->input('payment_id')) : null;
        if ($payment && ($payment->doc_type !== 'refund' || $payment->status != PaymentEnum::DRAFT->value)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['amount' => __('Only a draft refund can be edited.')]);
        }
        $avail = \App\Services\Finance\PartyAdvance::available('supplier', (int) $v['supplier'], $payment?->id);
        if ($v['amount'] > $avail + 0.004) {
            throw \Illuminate\Validation\ValidationException::withMessages(['amount' => __('The supplier holds only :a of our advance to refund.', ['a' => number_format($avail, 2)])]);
        }

        DB::beginTransaction();
        try {
            if (!$payment) {
                $payment = new Payment();
                $payment->status = PaymentEnum::DRAFT->value;
                $payment->company_id = companyId();
                $payment->created_by = Auth::id();
                $prefix = 'PRF/' . date('y') . '/';
                $payment->unique_row_no = DocumentNumber::next(Payment::class, $prefix);
                $payment->row_no = $prefix . sprintf('%04d', $payment->unique_row_no);
            } else {
                $payment->updated_by = Auth::id();
            }
            $a = (float) $v['amount'];
            $payment->supplier_id = $v['supplier'];
            $payment->payment_date = $v['payment_date'];
            $payment->account = is_array($v['account']) ? ($v['account'][0] ?: null) : $v['account'];
            $payment->reference_no = $v['reference_no'] ?? null;
            $payment->currency = $cur;
            $payment->currency_rate = 1;
            $payment->sub_total = $a; $payment->tax_total = 0; $payment->bank_charges = 0; $payment->other_charges = 0;
            $payment->grand_total = $a;
            $payment->base_sub_total = $a; $payment->base_tax_total = 0; $payment->base_bank_charges = 0; $payment->base_other_charges = 0;
            $payment->base_grand_total = $a;
            $payment->notes = $v['notes'] ?? null;
            $payment->total_amount = $a;
            $payment->advance_amount = 0;
            $payment->advance_used = $a;      // a refund uses up the advance
            $payment->doc_type = 'refund';
            $payment->save();
            DB::commit();

            return response()->json(['status' => 'success', 'message' => __('Refund saved successfully'), 'payment_id' => $payment->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => __('Error saving refund: ') . $e->getMessage()], 500);
        }
    }

    /** What is left of the supplier's opening balance to pay (for the payment form). */
    public function getSupplierOpening($supplierId)
    {
        $id = (int) decodeId($supplierId);
        $pay = request()->filled('payment') ? (int) request('payment') : null;

        return response()->json($id ? [
            'balance' => \App\Services\Finance\OpeningSettlement::outstanding('supplier', $id, $pay),
            'total' => \App\Services\Finance\OpeningSettlement::total('supplier', $id),
            'currency' => app(\App\Services\Finance\OpeningBalanceService::class)->baseCurrency(),
        ] : ['balance' => 0]);
    }

    /** An approved payment that gave an advance cannot be undone while later documents have already used that advance. */
    private function advanceBlocksReversal(Payment $payment): ?\Illuminate\Http\JsonResponse
    {
        $given = (float) $payment->advance_amount;
        if ($given <= 0.004 || (int) $payment->status !== \App\Enums\PaymentEnum::APPROVED->value) {
            return null;
        }
        $left = \App\Services\Finance\PartyAdvance::available('supplier', (int) $payment->supplier_id) - $given;
        if ($left < -0.004) {
            return response()->json(['status' => 'error', 'message' => __('This payment gave an advance of :a that is already used or refunded. Cancel those documents first.', ['a' => number_format($given, 2)])], 422);
        }

        return null;
    }

    /**
     * Update the payment status.
     */
    public function updateStatus($id, $status)
    {
        $payment = Payment::findOrFail($id);
        $previousStatus = $payment->status;
        if ($locked = $this->refuseInClosedPeriod(formDate($payment->payment_date), $previousStatus, $status, PaymentEnum::APPROVED->value)) {
            return $locked;
        }
        if ($status != \App\Enums\PaymentEnum::APPROVED->value && ($blocked = $this->advanceBlocksReversal($payment))) {
            return $blocked;
        }

        DB::beginTransaction();
        try {
            if ($status == PaymentEnum::APPROVED->value) {
                $payment->status = PaymentEnum::APPROVED->value;
                $payment->approved_by = Auth::id();
                $payment->approved_at = now();

                // Update paid_amount in supplier invoices when payment is approved
                if ($previousStatus != PaymentEnum::APPROVED->value) {
                    $paymentInvoices = PaymentInvoice::where('payment_id', $payment->id)->get();
                    foreach ($paymentInvoices as $paymentInvoice) {
                        $supplierInvoice = SupplierInvoice::find($paymentInvoice->supplier_invoice_id);
                        if ($supplierInvoice) {
                            $supplierInvoice->paid_amount = ($supplierInvoice->paid_amount ?? 0) + $paymentInvoice->amount;
                            $supplierInvoice->save();
                        }
                    }
                }
            } else {
                $payment->status = $status == PaymentEnum::CANCELLED->value ? PaymentEnum::CANCELLED->value : PaymentEnum::DRAFT->value;

                // Revert paid_amount in supplier invoices when an approved payment is moved back to draft or cancelled
                if ($previousStatus == PaymentEnum::APPROVED->value) {
                    $paymentInvoices = PaymentInvoice::where('payment_id', $payment->id)->get();
                    foreach ($paymentInvoices as $paymentInvoice) {
                        $supplierInvoice = SupplierInvoice::find($paymentInvoice->supplier_invoice_id);
                        if ($supplierInvoice && $supplierInvoice->paid_amount) {
                            $supplierInvoice->paid_amount = max(0, $supplierInvoice->paid_amount - $paymentInvoice->amount);
                            $supplierInvoice->save();
                        }
                    }
                }
            }

            $payment->save();

            // Create finance entries when payment is approved
            if ($status == PaymentEnum::APPROVED->value) {
                $this->createFinanceEntries($payment);
            }

            // Delete finance entries when an approved payment is moved back to draft or cancelled
            if ($previousStatus == PaymentEnum::APPROVED->value && $status != PaymentEnum::APPROVED->value) {
                $this->deleteFinanceEntries($payment->id);
            }
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => __('Payment status updated successfully'),
                'data' => [
                    'id' => $payment->id,
                    'status' => $payment->status,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => __('Error updating payment status: ') . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Set disapproval reason for a payment.
     */
    public function setDisapprovalReason(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $payment = Payment::findOrFail($id);
        $previousStatus = $payment->status;
        if ($locked = $this->refuseInClosedPeriod(formDate($payment->payment_date), $previousStatus, PaymentEnum::CANCELLED->value, PaymentEnum::APPROVED->value)) {
            return $locked;
        }
        if ($blocked = $this->advanceBlocksReversal($payment)) {
            return $blocked;
        }
        DB::beginTransaction();
        $payment->status = PaymentEnum::CANCELLED->value;
        $payment->disapproval_reason = $validated['reason'];
        $payment->save();

        // Delete finance entries if payment was previously approved
        if ($previousStatus == PaymentEnum::APPROVED->value) {
            // Revert paid_amount in supplier invoices
            $paymentInvoices = PaymentInvoice::where('payment_id', $payment->id)->get();
            foreach ($paymentInvoices as $paymentInvoice) {
                $supplierInvoice = SupplierInvoice::find($paymentInvoice->supplier_invoice_id);
                if ($supplierInvoice && $supplierInvoice->paid_amount) {
                    $supplierInvoice->paid_amount = max(0, $supplierInvoice->paid_amount - $paymentInvoice->amount);
                    $supplierInvoice->save();
                }
            }

            $this->deleteFinanceEntries($payment->id);
        }
        DB::commit();

        return response()->json([
            'status' => 'success',
            'message' => __('Payment disapproved successfully'),
            'data' => [
                'id' => $payment->id,
                'status' => $payment->status,
            ],
        ]);
    }

    /**
     * Print the specified payment.
     */
    public function print($id)
    {
        $payment = Payment::with(['supplier', 'job', 'paymentInvoices.supplierInvoice', 'additionalTransactions.account', 'createdBy', 'approvedBy'])
            ->findOrFail($id);

        return view('modules.transaction.payment.print', compact('payment'));
    }

    /**
     * Download the specified payment as PDF.
     */
    public function download($id)
    {
        $payment = Payment::with(['supplier', 'job', 'paymentInvoices.supplierInvoice', 'additionalTransactions.account', 'createdBy', 'approvedBy'])
            ->findOrFail($id);

        // opens the print view and the browser's "Save as PDF" (same as Journal Voucher download)
        return view('modules.transaction.payment.print', ['payment' => $payment, 'autoPrint' => true]);
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
                $q->whereBetween('payment_date', [formDate($filter['filter-from-date']), formDate($filter['filter-to-date'])]);
            })->when(!empty($filter['suppliers']), function ($q) use ($filter) {
                $q->whereIn('supplier_id', decodeIds($filter['suppliers']));
            });
        };

        $query = Payment::with(['supplier:id,name_en,name_ar', 'job:id,row_no'])
            ->select([
                'payments.id',
                'row_no',
                'supplier_id',
                'payment_date',
                'account',
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
                'payments.created_at',
            ])
            ->when($request->tab, function ($q) use ($request) {
                if ($request->tab !== 'all') {
                    $q->where('status', PaymentEnum::fromName($request->tab));
                }
            })
            ->tap($applyFilters)
            ->orderBy('payments.id', 'desc');

        // Get counts per status
        $statusCounts = Payment::select('status', DB::raw('COUNT(*) as total'))
            ->tap($applyFilters)
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Normalize counts for all statuses
        $allCounts = [];
        foreach (PaymentEnum::cases() as $status) {
            $allCounts[$status->name] = $statusCounts[$status->value] ?? 0;
        }
        $allCounts['all'] = array_sum($allCounts);

        // Summary card totals (Draft / Approved) in company currency.
        $draft = PaymentEnum::DRAFT->value;
        $approved = PaymentEnum::APPROVED->value;
        $salesSummary = Payment::select([
            DB::raw("SUM(CASE WHEN status = {$draft} THEN base_grand_total ELSE 0 END) as total_draft_grand"),
            DB::raw("SUM(CASE WHEN status = {$draft} THEN advance_amount ELSE 0 END) as total_draft_advance"),
            DB::raw("SUM(CASE WHEN status = {$approved} THEN base_grand_total ELSE 0 END) as total_approved_grand"),
            DB::raw("SUM(CASE WHEN status = {$approved} THEN advance_amount ELSE 0 END) as total_approved_advance"),
        ])->tap($applyFilters)->first();

        // the payment stores the account's id: the list shows its name
        $accountNames = \App\Models\Finance\Account\Account::withoutGlobalScopes()
            ->where(fn ($q) => $q->where('company_id', companyId())->orWhereNull('company_id'))->pluck('name', 'id');

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->editColumn('account', fn($model) => filled($model->account) && isset($accountNames[$model->account]) ? __($accountNames[$model->account]) : '—')
            ->setRowAttr([
                'data-id' => fn($model) => $model->id,
                'data-name' => fn($model) => 'Payment #' . htmlspecialchars($model->row_no, ENT_QUOTES, 'UTF-8'),
                'class' => 'row-item',
                'id' => fn($model) => 'payment-' . strtolower($model->row_no ?? $model->id),
            ])
            ->editColumn('payment_date', fn($model) => \Carbon\Carbon::parse($model->payment_date)->format('d-m-Y'))
            ->editColumn('currency', fn($model) => strtoupper($model->currency))
            ->addColumn('supplier_name', fn($model) => $model->supplier->name_en ?? '-')
            ->addColumn('total_received', fn($model) => number_format((float) ($model->total_amount ?? ($model->sub_total + $model->tax_total)), decimals()))
            // document type badge shown under the number: refunds and advances have their own series
            ->addColumn('doc_label', function ($model) {
                if ($model->doc_type === 'refund') return '<span class="badge bg-success">' . e(__('Refund')) . '</span>';
                if (str_starts_with((string) $model->row_no, 'PAV/')) return '<span class="badge bg-warning text-dark">' . e(__('Advance')) . '</span>';
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
            ->editColumn('status', fn($model) => PaymentEnum::from($model->status)->label())
            ->with([
                'statusCounts' => $allCounts,
                'salesSummary' => $salesSummary,
            ])
            ->toJson();
    }

    /**
     * Get context menu actions for a payment.
     */
    public function actions($id)
    {
        $payment = Payment::select('id', 'row_no', 'status')->findOrFail($id);
        $contextMenu = collect([]);

        // Status actions
        if ($payment->status === PaymentEnum::DRAFT->value) {
            $contextMenu->push([
                'label' => __('Move to'),
                'type' => 'submenu',
                'separator' => 'after',
                'icon' => 'move_to',
                'items' => [
                    [
                        'label' => __('Approved'),
                        'id' => 'row_approved',
                        'data-id' => $payment->id,
                        'data-value' => PaymentEnum::APPROVED->value,
                        'icon' => 'confirmed'
                    ],
                    [
                        'label' => __('Cancelled'),
                        'id' => 'row_cancelled',
                        'class' => 'row_cancelled',
                        'data-id' => $payment->id,
                        'data-value' => PaymentEnum::CANCELLED->value,
                        'icon' => 'rejected'
                    ]
                ]
            ]);
        } elseif ($payment->status === PaymentEnum::APPROVED->value) {
            $contextMenu->push([
                'label' => __('Move to'),
                'type' => 'submenu',
                'separator' => 'after',
                'icon' => 'move_to',
                'items' => [
                    [
                        'label' => __('Move To Draft'),
                        'id' => 'row_draft',
                        'data-id' => $payment->id,
                        'data-value' => PaymentEnum::DRAFT->value,
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
            'data-id' => $payment->id,
            'type' => 'item',
            'icon' => 'view',
        ]);

        $contextMenu->push([
            'label' => __('Print'),
            'id' => 'row_print',
            'class' => 'row_print',
            'data-id' => $payment->id,
            'type' => 'item',
            'icon' => 'print',
            'onclick' => 'PAYMENT.printPreview(' . $payment->id . ')',
        ]);

        $contextMenu->push([
            'label' => __('Download'),
            'id' => 'row_download',
            'class' => 'row_download',
            'data-id' => $payment->id,
            'type' => 'item',
            'icon' => 'download',
            'onclick' => 'PAYMENT.downloadPDF(' . $payment->id . ')',
            'separator' => 'after',
        ]);

        // Edit and Delete actions (only for Draft status)
        if ($payment->status === PaymentEnum::DRAFT->value) {
            $edit = [
                'label' => __('Edit'),
                'id' => 'row_edit',
                'class' => 'row_edit',
                'data-id' => $payment->id,
                'type' => 'item',
                'icon' => 'edit'
            ];

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
     * Remove the specified payment from storage.
     */
    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);
        $guard = app(\App\Services\DeletionGuard::class);
        $why = $guard->blockers('payment', (int) $id);
        if ($why) {
            return $guard->refusal(__('payment'), $why);
        }

        if ($payment->status !== PaymentEnum::DRAFT->value) {
            return response()->json([
                'status' => 'error',
                'message' => __('Only draft payments can be deleted'),
            ], 400);
        }

        // If this is an approved payment, revert the paid_amount in supplier invoices
        if ($payment->status == PaymentEnum::APPROVED->value) {
            $paymentInvoices = PaymentInvoice::where('payment_id', $payment->id)->get();
            foreach ($paymentInvoices as $paymentInvoice) {
                $supplierInvoice = SupplierInvoice::find($paymentInvoice->supplier_invoice_id);
                if ($supplierInvoice && $supplierInvoice->paid_amount) {
                    $supplierInvoice->paid_amount = max(0, $supplierInvoice->paid_amount - $paymentInvoice->amount);
                    $supplierInvoice->save();
                }
            }
        }

        // Delete any associated finance entries
        $this->deleteFinanceEntries($payment->id);

        \App\Services\Finance\OpeningSettlement::remove('supplier', (int) $payment->id);   // the opening balance it held is released
        $payment->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('Payment deleted successfully'),
        ]);
    }

    /**
     * Create finance entries for a payment.
     *
     * @param Payment $payment
     * @return void
     */
    /**
     * Create finance entries for a payment.
     *
     * Correct accounting for supplier payment:
     *   DR  Accounts Payable (AP)   (full invoice amount settled, incl. VAT — AP was credited in full on invoice)
     *   DR  Bank Charges Expense    (if any)
     *   DR  Miscellaneous Expenses  (other charges, if any)
     *   CR  Bank/Cash account       (grand_total = invoices paid + bank_charges + other_charges)
     *
     * NOTE: Input VAT is NOT touched here — it was already claimed as a Debit on the supplier invoice.
     *       The AP balance already includes VAT, so we simply DR AP for the full amount paid.
     *
     * @param Payment $payment
     * @return void
     */
    private function createFinanceEntries(Payment $payment)
    {
        // Delete any existing finance entries for this payment
        $this->deleteFinanceEntries($payment->id);

        if ($payment->doc_type === 'refund') {
            $this->createRefundEntries($payment);
            return;
        }
        $advUsed = (float) $payment->advance_used;
        $advExtra = (float) $payment->advance_amount;

        // Create a new finance entry
        $finance = new Finance();
        $finance->voucher_no = $payment->row_no;
        $finance->voucher_type = 'PV'; // Payment Voucher
        $finance->reference_no = $payment->reference_no;
        $finance->reference_date = $payment->payment_date;
        $finance->supplier_id = $payment->supplier_id;
        $finance->narration = $payment->notes ?: ($advExtra > 0.004 && !PaymentInvoice::where('payment_id', $payment->id)->exists() && !\App\Services\Finance\OpeningSettlement::ofDocument('supplier', $payment->id) ? 'Advance paid to supplier' : 'Payment to supplier');
        $finance->currency = $payment->currency;
        $finance->exchange_rate = $payment->currency_rate;
        $finance->total_debit = $payment->grand_total + $advUsed;
        $finance->total_credit = $payment->grand_total + $advUsed;
        $finance->base_currency = 'SAR';
        $finance->base_total_debit = $payment->base_grand_total + $advUsed;
        $finance->base_total_credit = $payment->base_grand_total + $advUsed;
        $finance->job_id = $payment->job_id;
        $finance->job_no = $payment->job_no ?? '';
        $finance->is_approved = 1;
        $finance->posted_at = now();
        $finance->linked_id = $payment->id;
        $finance->linked_type = Payment::class;
        $finance->company_id = $payment->company_id;
        $finance->user_id = Auth::id();
        $finance->save();

        // Get payment invoices
        $paymentInvoices = PaymentInvoice::with('supplierInvoice')
            ->where('payment_id', $payment->id)
            ->get();

        $financeSubs = [];

        $commonData = [
            'finance_id'        => $finance->id,
            'voucher_no'        => $finance->voucher_no,
            'voucher_type'      => $finance->voucher_type,
            'reference_no'      => $finance->reference_no,
            'supplier_id'       => $payment->supplier_id,
            'base_currency'     => 'SAR',
            'exchange_rate'     => $payment->currency_rate,
            'currency'          => $payment->currency,
            'job_id'            => $payment->job_id ?? null,
            'job_no'            => $payment->job_no ?? '',
            'cost_center_id'    => null,
            'is_tax_line'       => 0,
            'is_auto_generated' => 1,
            'linked_id'         => $payment->id,
            'linked_type'       => Payment::class,
            'user_id'           => Auth::id(),
            'company_id'        => $payment->company_id,
            'created_at'        => now(),
            'updated_at'        => now(),
        ];

        // CR Bank/Cash account (full grand_total goes out — invoices + charges); nothing when the advance settles it all
        if ((float) $payment->grand_total > 0.004) {
            $financeSubs[] = array_merge($commonData, [
                'account_id'     => $payment->account,
                'reference_date' => formDate($payment->payment_date),
                'description'    => 'Payment to supplier',
                'debit'          => 0,
                'credit'         => $payment->grand_total,
                'base_debit'     => 0,
                'base_credit'    => $payment->base_grand_total,
            ]);
        }
        // CR Accounts Payable: the supplier's earlier advance (a debit sitting on the payable) is used up against the invoices
        if ($advUsed > 0.004) {
            $financeSubs[] = array_merge($commonData, [
                'account_id' => 18, 'reference_date' => formDate($payment->payment_date), 'reference_no' => 'Advance',
                'description' => 'Advance applied to invoices', 'debit' => 0, 'credit' => $advUsed, 'base_debit' => 0, 'base_credit' => $advUsed,
            ]);
        }
        // DR Accounts Payable: the excess paid over the invoices stays as the supplier's advance
        if ($advExtra > 0.004) {
            $financeSubs[] = array_merge($commonData, [
                'account_id' => 18, 'reference_date' => formDate($payment->payment_date), 'reference_no' => 'Advance',
                'description' => 'Advance paid to supplier', 'debit' => $advExtra, 'credit' => 0, 'base_debit' => $advExtra, 'base_credit' => 0,
            ]);
        }

        // DR Accounts Payable (full invoice amounts being settled, including VAT)
        // A foreign-currency bill is cleared at the rate it was booked at; the bank pays today's rate and the difference
        // is an exchange loss (debit) or gain (credit), so the supplier ends at 0 in SAR as well.
        $fxDifference = 0.0;
        foreach ($paymentInvoices as $paymentInvoice) {
            $invoice = $paymentInvoice->supplierInvoice;
            $bookedRate = (float) $invoice->grand_total > 0 && (float) $invoice->base_grand_total > 0
                ? (float) $invoice->base_grand_total / (float) $invoice->grand_total
                : (float) $payment->currency_rate;
            $apBase = round($paymentInvoice->amount * $bookedRate, 2);
            $fxDifference += round($paymentInvoice->amount * $payment->currency_rate, 2) - $apBase;   // > 0 = loss

            $financeSubs[] = array_merge($commonData, [
                'account_id'     => 18, // 2110 Accounts Payable
                'reference_date' => formDate($payment->payment_date),
                'reference_no'   => $invoice->row_no,
                'description'    => 'Payment for invoice ' . $invoice->row_no,
                'debit'          => $paymentInvoice->amount,
                'credit'         => 0,
                'base_debit'     => $apBase,
                'base_credit'    => 0,
                'job_id'         => $invoice->job_id ?? null,
                'job_no'         => $invoice->job_no ?? '',
                'linked_id'      => $paymentInvoice->id,
                'linked_type'    => PaymentInvoice::class,
            ]);
        }

        // DR Bank Charges Expense (if any)
        if (($payment->bank_charges ?? 0) > 0) {
            $financeSubs[] = array_merge($commonData, [
                'account_id'     => 54, // 5260 Bank Charges expense account
                'reference_date' => formDate($payment->payment_date),
                'description'    => 'Bank charges on payment',
                'debit'          => $payment->bank_charges,
                'credit'         => 0,
                'base_debit'     => $payment->base_bank_charges,
                'base_credit'    => 0,
            ]);
        }

        // DR Miscellaneous Expenses (other charges, if any)
        if (($payment->other_charges ?? 0) > 0) {
            $financeSubs[] = array_merge($commonData, [
                'account_id'     => 56, // 5280 Miscellaneous Expenses account
                'reference_date' => formDate($payment->payment_date),
                'description'    => 'Other charges on payment',
                'debit'          => $payment->other_charges,
                'credit'         => 0,
                'base_debit'     => $payment->base_other_charges,
                'base_credit'    => 0,
            ]);
        }

        // Add entries for additional transactions if any
        $additionalTransactions = PaymentAdditionalTransaction::where('payment_id', $payment->id)->get();
        foreach ($additionalTransactions as $transaction) {
            $financeSubs[] = array_merge($commonData, [
                'account_id'     => $transaction->account_id,
                'reference_date' => formDate($payment->payment_date),
                'description'    => $transaction->description ?: 'Additional transaction',
                'debit'          => $transaction->is_debit ? $transaction->amount : 0,
                'credit'         => !$transaction->is_debit ? $transaction->amount : 0,
                'base_debit'     => $transaction->is_debit ? $transaction->amount * $payment->currency_rate : 0,
                'base_credit'    => !$transaction->is_debit ? $transaction->amount * $payment->currency_rate : 0,
                'linked_id'      => $transaction->id,
                'linked_type'    => PaymentAdditionalTransaction::class,
            ]);
        }

        // DR Accounts Payable for the part of the supplier's opening balance paid (company currency)
        $openingPart = \App\Services\Finance\OpeningSettlement::ofDocument('supplier', $payment->id);
        if ($openingPart > 0) {
            $financeSubs[] = array_merge($commonData, [
                'account_id'     => 18,
                'reference_date' => formDate($payment->payment_date),
                'reference_no'   => 'Opening balance',
                'description'    => 'Payment against opening balance',
                'debit'          => $openingPart,
                'credit'         => 0,
                'base_debit'     => round($openingPart * (float) $payment->currency_rate, 2),
                'base_credit'    => 0,
            ]);
        }

        $fxDifference = round($fxDifference, 2);
        if (abs($fxDifference) >= 0.01) {
            $financeSubs[] = array_merge($commonData, [
                'account_id'     => \App\Models\Finance\Account\Account::exchangeAccountId(), // 4180 Exchange Gain / Loss
                'reference_date' => formDate($payment->payment_date),
                'description'    => 'Exchange ' . ($fxDifference > 0 ? 'loss' : 'gain') . ' on payment ' . $payment->row_no,
                'debit'          => 0,
                'credit'         => 0,
                'base_debit'     => $fxDifference > 0 ? $fxDifference : 0,
                'base_credit'    => $fxDifference < 0 ? -$fxDifference : 0,
                'exchange_rate'  => 1,
            ]);
            $total = round((float) $payment->base_grand_total + max(0, -$fxDifference), 2);
            \App\Models\Finance\Finance::whereKey($commonData['finance_id'])->update(['base_total_debit' => $total, 'base_total_credit' => $total]);
        }

        // Insert all finance sub entries
        FinanceSub::insert($financeSubs);
    }

    /** A refund is an advance paid to the supplier coming back: DR Bank, CR Accounts Payable (the advance debit is cleared). */
    private function createRefundEntries(Payment $payment): void
    {
        $amt = round((float) $payment->grand_total, 2);
        $finance = new Finance();
        $finance->voucher_no = $payment->row_no;
        $finance->voucher_type = 'PV';
        $finance->reference_no = $payment->reference_no;
        $finance->reference_date = $payment->payment_date;
        $finance->supplier_id = $payment->supplier_id;
        $finance->narration = $payment->notes ?: 'Refund from supplier';
        $finance->currency = $payment->currency;
        $finance->exchange_rate = 1;
        $finance->total_debit = $amt;
        $finance->total_credit = $amt;
        $finance->base_currency = 'SAR';
        $finance->base_total_debit = $amt;
        $finance->base_total_credit = $amt;
        $finance->is_approved = 1;
        $finance->posted_at = now();
        $finance->linked_id = $payment->id;
        $finance->linked_type = Payment::class;
        $finance->company_id = $payment->company_id;
        $finance->user_id = Auth::id();
        $finance->save();

        $common = [
            'finance_id' => $finance->id, 'voucher_no' => $finance->voucher_no, 'voucher_type' => 'PV', 'reference_no' => $finance->reference_no,
            'supplier_id' => $payment->supplier_id, 'base_currency' => 'SAR', 'exchange_rate' => 1, 'currency' => $payment->currency,
            'cost_center_id' => null, 'is_tax_line' => 0, 'is_auto_generated' => 1, 'linked_id' => $payment->id, 'linked_type' => Payment::class,
            'user_id' => Auth::id(), 'company_id' => $payment->company_id, 'created_at' => now(), 'updated_at' => now(),
            'reference_date' => formDate($payment->payment_date),
        ];
        FinanceSub::insert([
            array_merge($common, ['account_id' => $payment->account, 'description' => 'Refund received from supplier', 'debit' => $amt, 'credit' => 0, 'base_debit' => $amt, 'base_credit' => 0]),
            array_merge($common, ['account_id' => 18, 'description' => 'Refund of advance from supplier', 'debit' => 0, 'credit' => $amt, 'base_debit' => 0, 'base_credit' => $amt]),
        ]);
    }

    /**
     * Delete finance entries for a payment.
     *
     * @param int $paymentId
     * @return void
     */
    private function deleteFinanceEntries($paymentId)
    {
        $payment = Payment::findOrFail($paymentId);

        // Get finance entries using the relationship and delete them
        // This will also delete related finance sub entries due to the cascade delete in the database
        foreach ($payment->financeEntries as $finance) {
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

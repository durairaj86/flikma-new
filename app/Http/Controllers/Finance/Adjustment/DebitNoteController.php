<?php

namespace App\Http\Controllers\Finance\Adjustment;

use App\Enums\DebitNoteEnum;
use App\Http\Controllers\Controller;
use App\Models\Finance\Account\Account;
use App\Models\Finance\Adjustment\DebitNote;
use App\Models\Finance\Adjustment\DebitNoteSub;
use App\Models\Finance\Finance;
use App\Models\Finance\FinanceSub;
use App\Models\Finance\SupplierInvoice\SupplierInvoice;
use App\Models\Job\Job;
use App\Models\Master\Description;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

/**
 * Debit note to a supplier against one of their approved invoices: it reduces the payable.
 * Approval posts  DR Accounts Payable / CR cost (per line account) + CR Input VAT.
 */
class DebitNoteController extends Controller
{
    private function formData(DebitNote $debitNote): array
    {
        return [
            'debitNote' => $debitNote,
            'jobs' => Job::select('id', 'row_no', 'customer_id')->with('customer:id,name_en')->get(),
            'parents' => Account::where('type', '!=', 'Equity')->where('is_active', 1)->orderBy('name')->get(),
            'subAccounts' => Account::where('type', '!=', 'Equity')->where('is_grouped', 0)->orderBy('name')->get(),
            'supplierInvoices' => SupplierInvoice::where('status', 3)->orderByDesc('id')->get(['id', 'row_no', 'supplier_id', 'job_id', 'grand_total', 'currency']),
        ];
    }

    public function modal()
    {
        $debitNote = new DebitNote();
        $debitNote->setRelation('debitNoteSubs', collect([new DebitNoteSub()]));
        $debitNote->setRelation('documents', collect());

        return view('modules.finance.debit-note.debit-note-form', $this->formData($debitNote));
    }

    public function edit($id)
    {
        $debitNote = DebitNote::with(['debitNoteSubs', 'documents'])->findOrFail($id);

        return view('modules.finance.debit-note.debit-note-form', $this->formData($debitNote));
    }

    /** Shared list filters, so rows, tab counts and summary cards always agree. */
    private function applyFilters($query, array $filter)
    {
        return $query
            ->when(isset($filter['filter-from-date'], $filter['filter-to-date']), function ($q) use ($filter) {
                $q->where('posted_at', '>=', Carbon::parse($filter['filter-from-date'])->startOfDay())
                    ->where('posted_at', '<', Carbon::parse($filter['filter-to-date'])->addDay()->startOfDay());
            })
            ->when(!empty($filter['suppliers']), fn($q) => $q->whereIn('supplier_id', decodeIds($filter['suppliers'])))
            ->when(!empty($filter['invoice']), fn($q) => $q->where('invoice_id', decodeId($filter['invoice'])))
            ->when(!empty($filter['customSearch']), function ($q) use ($filter) {
                $search = $filter['customSearch'];
                $q->where(function ($q) use ($search) {
                    $q->where('row_no', 'like', "%{$search}%")
                        ->orWhere('job_no', 'like', "%{$search}%")
                        ->orWhereHas('supplier', fn($s) => $s->where('name_en', 'like', "%{$search}%")->orWhere('name_ar', 'like', "%{$search}%"))
                        ->orWhereHas('invoice', fn($i) => $i->where('row_no', 'like', "%{$search}%"));
                });
            });
    }

    public function fetchAllRows(Request $request)
    {
        $filter = $request->filterData ?? [];

        $rows = DebitNote::select('id', 'row_no', 'posted_at', 'job_id', 'job_no', 'supplier_id', 'invoice_id', 'currency',
            'sub_total', 'tax_total', 'grand_total', 'status', 'created_at', 'company_id')
            ->with(['supplier:id,name_en,name_ar,row_no', 'job:id,shipment_mode', 'invoice:id,row_no'])
            ->when($request->tab && $request->tab !== 'all', fn($q) => $q->where('status', DebitNoteEnum::fromName($request->tab)))
            ->tap(fn($q) => $this->applyFilters($q, $filter))
            ->orderByDesc('id');

        $statusCounts = $this->applyFilters(DebitNote::select('status', DB::raw('COUNT(*) as total')), $filter)
            ->groupBy('status')->pluck('total', 'status')->toArray();
        $allCounts = [];
        foreach (DebitNoteEnum::cases() as $status) {
            $allCounts[$status->name] = $statusCounts[$status->value] ?? 0;
        }
        $allCounts['all'] = array_sum($allCounts);

        $d = DebitNoteEnum::DRAFT->value;
        $a = DebitNoteEnum::APPROVED->value;
        $summary = $this->applyFilters(DebitNote::select([
            DB::raw("SUM(grand_total) as overall_sales"),
            DB::raw("SUM(CASE WHEN status = {$d} THEN grand_total ELSE 0 END) as total_draft_grand"),
            DB::raw("SUM(CASE WHEN status = {$d} THEN sub_total ELSE 0 END) as total_draft_sub"),
            DB::raw("SUM(CASE WHEN status = {$d} THEN tax_total ELSE 0 END) as total_draft_tax"),
            DB::raw("SUM(CASE WHEN status = {$a} THEN grand_total ELSE 0 END) as total_approved_grand"),
            DB::raw("SUM(CASE WHEN status = {$a} THEN sub_total ELSE 0 END) as total_approved_sub"),
            DB::raw("SUM(CASE WHEN status = {$a} THEN tax_total ELSE 0 END) as total_approved_tax"),
        ]), $filter)->first();

        $decimals = decimals();

        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($m) => $m->id,
                'data-name' => fn($m) => 'Debit Note #' . htmlspecialchars($m->row_no ?? $m->id, ENT_QUOTES, 'UTF-8'),
                'class' => 'row-item',
                'id' => fn($m) => 'debit-note-' . $m->id,
            ])
            ->editColumn('posted_at', fn($m) => Carbon::parse($m->posted_at)->format('d-m-Y'))
            ->editColumn('currency', fn($m) => strtoupper($m->currency))
            ->addColumn('invoice_no', fn($m) => $m->invoice?->row_no ?? '-')
            ->editColumn('sub_total', fn($m) => number_format($m->sub_total, $decimals))
            ->editColumn('tax_total', fn($m) => number_format($m->tax_total, $decimals))
            ->editColumn('grand_total', fn($m) => number_format($m->grand_total, $decimals))
            ->with(['statusCounts' => $allCounts, 'salesSummary' => $summary])
            ->toJson();
    }

    public function store(Request $request)
    {
        if ($request->has('unit_price')) {
            $request->merge(['unit_price' => collect($request->unit_price)->map(fn($v) => str_replace(',', '', $v))->toArray()]);
        }
        $request->merge(['supplier' => decodeId($request->input('supplier'))]);

        $validated = $request->validate([
            'invoice_id' => 'required|exists:supplier_invoices,id',
            'supplier' => 'required|exists:suppliers,id',
            'debit_note_date' => 'required|date',
            'job_id' => 'nullable|exists:jobs,id',
            'reason' => 'required',
            'terms' => 'nullable|string|max:1000',
            'description_id.*' => 'required|string|max:255',
            'account.*' => 'required',
            'comment.*' => 'nullable|string|max:500',
            'unit_id.*' => 'required|numeric',
            'quantity.*' => 'required|numeric|min:0.01',
            'unit_price.*' => 'required|min:0|regex:/^\d+(\.\d{1,2})?$/',
            'tax.*' => 'nullable|string',
        ]);

        $this->assertPeriodOpen($validated['debit_note_date'], 'debit_note_date');

        $companyId = companyId();
        $invoice = SupplierInvoice::findOrFail($validated['invoice_id']);

        if ($request->filled('data-id')) {
            $debitNote = DebitNote::findOrFail($request->input('data-id'));
            if ((int) $debitNote->status !== DebitNoteEnum::DRAFT->value) {
                return response()->json(['status' => 'error', 'message' => __('Only a draft debit note can be edited.')], 422);
            }
        } else {
            $debitNote = new DebitNote();
            $debitNote->row_no = 'DRD' . date('ydis') . rand(100, 999);
            $this->setBaseColumns($debitNote);
        }

        // Totals
        $subTotal = 0;
        $taxTotal = 0;
        foreach ($request->quantity as $i => $qty) {
            $lineTotal = $qty * ($request->unit_price[$i] ?? 0);
            $subTotal += $lineTotal;
            $taxTotal += $lineTotal * (vatPercent($request->tax[$i] ?? 0) / 100);
        }

        // All debit notes on one invoice together can't be more than the invoice itself.
        $already = (float) DebitNote::where('invoice_id', $invoice->id)->where('status', '!=', DebitNoteEnum::CANCELLED->value)
            ->when($request->filled('data-id'), fn($q) => $q->where('id', '!=', $request->input('data-id')))->sum('grand_total');
        if ($already + $subTotal + $taxTotal > (float) $invoice->grand_total + 0.005) {
            return response()->json([
                'status' => 'error',
                'message' => __('Debit notes on :invoice can total at most :max; :used is already debited.', [
                    'invoice' => $invoice->row_no, 'max' => number_format((float) $invoice->grand_total, decimals()), 'used' => number_format($already, decimals()),
                ]),
            ], 422);
        }

        $job = ($validated['job_id'] ?? null) ? Job::select('id', 'row_no')->find($validated['job_id']) : null;
        $debitNote->invoice_id = $invoice->id;
        $debitNote->invoice_no = $invoice->row_no;
        $debitNote->supplier_id = $validated['supplier'];
        $debitNote->job_id = $job?->id;
        $debitNote->job_no = $job?->row_no;
        $debitNote->posted_at = formDate($validated['debit_note_date']);
        $debitNote->reason = $validated['reason'];
        $debitNote->terms = $validated['terms'] ?? null;
        // Always the currency of the invoice it adjusts, so the payable is reduced at the right rate.
        $debitNote->currency = $invoice->currency ?? 'SAR';
        $debitNote->currency_rate = $invoice->currency_rate ?? 1;
        $debitNote->sub_total = $subTotal;
        $debitNote->tax_total = $taxTotal;
        $debitNote->grand_total = $subTotal + $taxTotal;
        $debitNote->base_sub_total = $subTotal * $debitNote->currency_rate;
        $debitNote->base_tax_total = $taxTotal * $debitNote->currency_rate;
        $debitNote->base_grand_total = $debitNote->base_sub_total + $debitNote->base_tax_total;
        $debitNote->status = DebitNoteEnum::DRAFT->value;

        DB::beginTransaction();
        try {
            $debitNote->save();

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('documents/' . $companyId . '/debit_note/' . $debitNote->id, $name, 'public');
                    $debitNote->documents()->create([
                        'document_type' => DebitNote::class, 'file_path' => $path, 'file_name' => $file->getClientOriginalName(),
                        'title' => 'debit_note', 'posted_date' => now(), 'user_id' => Auth::id(), 'company_id' => $companyId,
                    ]);
                }
            }

            $descriptions = Description::descriptions()->keyBy('id');
            $lines = [];
            foreach ($request->description_id as $i => $desc) {
                $qty = $request->quantity[$i] ?? 0;
                $price = $request->unit_price[$i] ?? 0;
                $taxRate = vatPercent($request->tax[$i] ?? 0);
                $lineTotal = $qty * $price;
                $lineTax = $lineTotal * ($taxRate / 100);
                $lines[] = [
                    'debit_note_id' => $debitNote->id,
                    'account_id' => $request->account[$i],
                    'company_id' => $companyId,
                    'description_id' => $desc,
                    'description' => $descriptions[$desc]->description ?? null,
                    'comment' => $request->comment[$i] ?? null,
                    'unit_id' => $request->unit_id[$i],
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'line_total' => $lineTotal,
                    'tax_code' => $request->tax[$i] ?? null,
                    'tax_percent' => $taxRate,
                    'tax_amount' => $lineTax,
                    'total' => $lineTotal,
                    'total_with_tax' => $lineTotal + $lineTax,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('debit_note_subs')->where('debit_note_id', $debitNote->id)->delete();
            DB::table('debit_note_subs')->insert($lines);

            DB::commit();

            return response()->json(['status' => 'success', 'message' => __('Debit note saved successfully'), 'debit_note_id' => $debitNote->id]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json(['status' => 'error', 'message' => __('Error saving debit note: ') . $e->getMessage()], 500);
        }
    }

    public function actions($id)
    {
        $dn = DebitNote::select('id', 'row_no', 'status')->findOrFail($id);
        $menu = collect();
        $draft = (int) $dn->status === DebitNoteEnum::DRAFT->value;

        if ($draft) {
            $menu->push([
                'label' => __('Move to'), 'type' => 'submenu', 'separator' => 'after', 'icon' => 'move_to',
                'items' => [
                    ['label' => __('Approved'), 'code' => '01CSBK', 'id' => 'row_approved', 'data-id' => $dn->id, 'data-value' => DebitNoteEnum::APPROVED->value, 'icon' => 'confirmed'],
                    ['label' => __('Cancelled'), 'code' => '01CSRJ', 'id' => 'row_rejected', 'class' => 'row_rejected', 'data-id' => $dn->id, 'data-value' => DebitNoteEnum::CANCELLED->value, 'icon' => 'rejected'],
                ],
            ]);
        }
        $menu->push(['label' => __('View'), 'code' => '01CSVW', 'id' => 'row_view', 'class' => 'row_view', 'data-id' => $dn->id, 'type' => 'item', 'icon' => 'view']);
        $menu->push(['label' => __('Print'), 'code' => '01CSPR', 'id' => 'row_print', 'class' => 'row_print', 'data-id' => $dn->id, 'type' => 'item', 'icon' => 'print',
            'onclick' => 'DEBIT_NOTE.printPreview(' . $dn->id . ')', 'separator' => $draft ? 'after' : null]);
        if ($draft) {
            $menu->push(['label' => __('Edit'), 'code' => '01CSED', 'id' => 'row_edit', 'class' => 'row_edit', 'data-id' => $dn->id, 'type' => 'item', 'icon' => 'edit']);
            $menu->push(['label' => __('Delete'), 'code' => '01CSDL', 'id' => 'row_delete', 'class' => 'row_delete', 'data-id' => $dn->id, 'type' => 'item', 'icon' => 'delete']);
        }

        return response()->json($menu->values());
    }

    public function updateStatus($id, $status): \Illuminate\Http\JsonResponse
    {
        $dn = DebitNote::with('debitNoteSubs')->findOrFail($id);
        $previous = (int) $dn->status;

        DB::beginTransaction();
        try {
            if ((int) $status === DebitNoteEnum::APPROVED->value) {
                if ($previous !== DebitNoteEnum::DRAFT->value) {
                    return response()->json(['status' => 'error', 'message' => __('Only a draft debit note can be approved.')], 422);
                }
                $this->assertPeriodOpen($dn->posted_at, 'debit_note_date');

                $dn->status = DebitNoteEnum::APPROVED->value;
                $dn->unique_row_no = (DebitNote::whereYear('posted_at', Carbon::parse($dn->posted_at)->year)->max('unique_row_no') ?? 0) + 1;
                $dn->draft_no = $dn->row_no;
                $dn->row_no = 'DN-' . Carbon::parse($dn->posted_at)->format('y') . '-' . sprintf('%04d', $dn->unique_row_no);
                $dn->save();

                $this->postToLedger($dn);
            } else {
                $dn->status = (int) $status;
                $dn->save();
                if ($previous === DebitNoteEnum::APPROVED->value) {
                    $this->removeFromLedger($dn);
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => __('Debit note status updated successfully!'),
            'data' => ['id' => $dn->id, 'status' => $dn->status],
        ]);
    }

    /** DR Accounts Payable / CR cost accounts (per line) + CR Input VAT; the credit is also set against the supplier invoice. */
    private function postToLedger(DebitNote $dn): void
    {
        $this->removeFromLedger($dn);

        $rate = $dn->currency_rate ?: 1;
        $grand = (float) $dn->grand_total;

        $finance = new Finance();
        $finance->voucher_no = $dn->row_no;
        $finance->voucher_type = 'DN';
        $finance->reference_no = $dn->row_no;
        $finance->reference_date = formDate($dn->posted_at);
        $finance->supplier_id = $dn->supplier_id;
        $finance->narration = 'Debit Note: ' . $dn->row_no . ($dn->invoice_no ? ' against supplier invoice ' . $dn->invoice_no : '');
        $finance->currency = $dn->currency ?? 'SAR';
        $finance->exchange_rate = $rate;
        $finance->total_debit = $grand;
        $finance->total_credit = $grand;
        $finance->base_currency = 'SAR';
        $finance->base_total_debit = $grand * $rate;
        $finance->base_total_credit = $grand * $rate;
        $finance->job_id = $dn->job_id;
        $finance->job_no = $dn->job_no ?? '';
        $finance->is_approved = 1;
        $finance->posted_at = now();
        $finance->linked_id = $dn->id;
        $finance->linked_type = DebitNote::class;
        $finance->company_id = $dn->company_id;
        $finance->user_id = Auth::id();
        $finance->save();

        $common = [
            'finance_id' => $finance->id, 'voucher_no' => $finance->voucher_no, 'voucher_type' => 'DN',
            'reference_no' => $finance->reference_no, 'reference_date' => formDate($dn->posted_at),
            'currency' => $finance->currency, 'exchange_rate' => $rate, 'base_currency' => 'SAR',
            'supplier_id' => $dn->supplier_id, 'job_id' => $dn->job_id, 'job_no' => $dn->job_no ?? '',
            'company_id' => $dn->company_id, 'user_id' => Auth::id(), 'is_tax_line' => 0, 'is_auto_generated' => 1,
            'linked_id' => $dn->id, 'linked_type' => DebitNote::class, 'created_at' => now(), 'updated_at' => now(),
        ];

        $subs = [];
        // DR Accounts Payable for the whole note
        $subs[] = array_merge($common, [
            'account_id' => 18, // 2110 Accounts Payable
            'description' => 'Payable reduction - Debit Note ' . $dn->row_no,
            'debit' => $grand, 'credit' => 0, 'base_debit' => $grand * $rate, 'base_credit' => 0,
        ]);
        // CR the cost accounts
        foreach ($dn->debitNoteSubs as $line) {
            $amount = (float) ($line->total ?? $line->line_total ?? 0);
            $subs[] = array_merge($common, [
                'account_id' => $line->account_id ?: 42, // 5110 cost of sales
                'description' => 'Debit Note - ' . ($line->description ?? $dn->row_no),
                'debit' => 0, 'credit' => $amount, 'base_debit' => 0, 'base_credit' => $amount * $rate,
            ]);
        }
        // CR Input VAT reversal
        if ((float) $dn->tax_total > 0) {
            $subs[] = array_merge($common, [
                'account_id' => 7, // 1150 Input VAT
                'description' => 'Input VAT reversal - Debit Note ' . $dn->row_no,
                'debit' => 0, 'credit' => $dn->tax_total, 'base_debit' => 0, 'base_credit' => $dn->tax_total * $rate,
                'is_tax_line' => 1,
            ]);
        }
        FinanceSub::insert($subs);

        // Settle the note against the supplier invoice so payments no longer offer the debited balance.
        if ($dn->invoice_id && ($inv = SupplierInvoice::find($dn->invoice_id))) {
            $inv->paid_amount = ($inv->paid_amount ?? 0) + $grand;
            $inv->base_paid_amount = ($inv->base_paid_amount ?? 0) + $grand * $rate;
            $inv->save();
        }
    }

    private function removeFromLedger(DebitNote $dn): void
    {
        $entries = Finance::where('linked_id', $dn->id)->where('linked_type', DebitNote::class)->get();
        if ($entries->isEmpty()) {
            return;
        }
        if ($dn->invoice_id && ($inv = SupplierInvoice::find($dn->invoice_id))) {
            $rate = $dn->currency_rate ?: 1;
            $inv->paid_amount = max(0, ($inv->paid_amount ?? 0) - $dn->grand_total);
            $inv->base_paid_amount = max(0, ($inv->base_paid_amount ?? 0) - $dn->grand_total * $rate);
            $inv->save();
        }
        foreach ($entries as $finance) {
            FinanceSub::where('finance_id', $finance->id)->delete();
            $finance->delete();
        }
    }

    public function overview($id)
    {
        $debitNote = DebitNote::with(['debitNoteSubs', 'supplier', 'job', 'invoice', 'documents'])->findOrFail($id);
        $descriptions = Description::descriptions()->pluck('description', 'id')->toArray();
        [$origin, $timeline] = $this->timeline($debitNote);

        return view('modules.finance.debit-note.view-overview', compact('debitNote', 'descriptions', 'origin', 'timeline'));
    }

    /** Time frame: the job's origin (enquiry -> quotation -> job), the supplier invoice being debited, then the debit note's own life. */
    private function timeline(DebitNote $dn): array
    {
        $events = [];
        $rank = 0;
        $add = function ($label, $at, $icon, $module, $by = null, $meta = null, $link = null) use (&$events, &$rank) {
            if (!$at) return;
            $events[] = ['label' => $label, 'at' => $at, 'icon' => $icon, 'module' => $module, 'by' => $by, 'meta' => $meta, 'link' => $link,
                'rank' => $rank, 'key' => Carbon::parse($at)->timestamp, 'seq' => count($events)];
        };

        $inv = $dn->invoice_id ? SupplierInvoice::find($dn->invoice_id) : null;
        $jobId = $dn->job_id ?: $inv?->job_id;
        $job = $jobId ? Job::find($jobId) : null;
        $quotation = $job && $job->quotation_id ? \App\Models\Quotation\Quotation::find($job->quotation_id) : null;
        $enquiry = $quotation && $quotation->enquiry_id ? \App\Models\Enquiry\Enquiry::find($quotation->enquiry_id) : null;

        if ($enquiry) $add(__('Enquiry created') . ' · ' . $enquiry->row_no, $enquiry->created_at, 'bi-chat-left-text', 'enquiry', null, null, ['enquiry', $enquiry->id, $enquiry->row_no]);
        if ($quotation) $add(__('Quotation posted') . ' · ' . $quotation->row_no, $quotation->created_at, 'bi-file-earmark-text', 'quotation', null, null, ['quotation', $quotation->id, $quotation->row_no]);
        if ($job) $add(($quotation ? __('Converted to job') : __('Job created')) . ' · ' . $job->row_no, $job->created_at, 'bi-briefcase', 'job', null, null, ['job', $job->id, $job->row_no]);

        $rank = 1;
        $origin = $quotation
            ? ($enquiry ? __('Enquiry') . ' → ' : '') . __('Quotation') . ' → ' . __('Job') . ' → ' . __('Supplier Invoice') . ' → ' . __('Debit Note')
            : ($job ? __('Job') . ' → ' . __('Supplier Invoice') . ' → ' . __('Debit Note') : ($inv ? __('Supplier Invoice') . ' → ' . __('Debit Note') : __('Debit note created directly')));

        if ($inv) {
            $add(__('Supplier invoice created') . ' · ' . $inv->row_no, $inv->created_at, 'bi-receipt', 'supplier_invoice', null,
                number_format((float) $inv->grand_total, decimals()), ['supplier_invoice', $inv->id, $inv->row_no]);
        }

        $labels = [1 => __('Moved to Draft'), 2 => __('Approved'), 3 => __('Cancelled')];
        $ignore = ['status', 'updated_at', 'unique_row_no', 'draft_no', 'row_no'];
        $seen = false;
        $logs = \App\Models\Log\LogHistory::where('loggable_type', DebitNote::class)->where('loggable_id', $dn->id)
            ->where('created_at', '>=', $dn->created_at->copy()->subSeconds(5))->orderBy('id')->get();
        foreach ($logs as $log) {
            $by = $log->user_id['name'] ?? null;
            if ($log->action === 'created') {
                $seen = true;
                $add(__('Debit note created') . ' · ' . $dn->row_no, $log->created_at, 'bi-receipt-cutoff', 'debit_note', $by, number_format((float) $dn->grand_total, decimals()));
            } elseif ($log->action === 'updated') {
                $new = $log->changes['new'] ?? [];
                if (isset($new['status'])) {
                    $st = (int) $new['status'];
                    $add($labels[$st] ?? __('Status changed'), $log->created_at, $st === 2 ? 'bi-check-circle' : ($st === 3 ? 'bi-x-circle' : 'bi-clock'), 'debit_note', $by);
                } elseif (array_diff(array_keys($new), $ignore)) {
                    $add(__('Debit note updated'), $log->created_at, 'bi-pencil-square', 'debit_note', $by);
                }
            }
        }
        if (!$seen) $add(__('Debit note created') . ' · ' . $dn->row_no, $dn->created_at, 'bi-receipt-cutoff', 'debit_note', null, number_format((float) $dn->grand_total, decimals()));

        usort($events, fn($a, $b) => [$a['rank'], $a['rank'] ? $a['key'] : $a['seq'], $a['seq']] <=> [$b['rank'], $b['rank'] ? $b['key'] : $b['seq'], $b['seq']]);

        return [$origin, $events];
    }

    public function print($id)
    {
        $debitNote = DebitNote::with(['debitNoteSubs', 'supplier', 'job', 'invoice'])->findOrFail($id);

        return view('modules.finance.debit-note.print', ['debitNote' => $debitNote, 'company' => authUserCompany()]);
    }

    public function delete($id)
    {
        $model = DebitNote::findOrFail($id);
        $guard = app(\App\Services\DeletionGuard::class);
        $why = $guard->blockers('debit_note', (int) $id);
        if ($why) {
            return $guard->refusal(__('debit note'), $why);
        }

        DB::transaction(function () use ($model, $id) {
            DB::table('debit_note_subs')->where('debit_note_id', $id)->delete();
            $model->delete();
        });

        return response()->json(['status' => 'success', 'message' => __('Debit note deleted successfully')]);
    }
}

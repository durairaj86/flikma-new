<?php

namespace App\Http\Controllers\Finance\JournalVoucher;

use App\Enums\JournalVoucherStatusEnum;
use App\Enums\JournalVoucherTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Finance\Account\Account;
use App\Models\Finance\JournalVoucher\JournalVoucher;
use App\Models\Finance\JournalVoucher\JournalVoucherItem;
use App\Models\Job\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class JournalVoucherController extends Controller
{
    /**
     * Display a listing of the journal vouchers.
     */
    public function index()
    {
        return view('modules.finance.journal_voucher.list');
    }

    /**
     * Show the form for creating a new journal voucher.
     */
    public function modal()
    {
        $journalVoucher = new JournalVoucher();
        $jobs = Job::select('id', 'row_no')->orderBy('row_no', 'desc')->get();
        $accounts = Account::select('id', 'name', 'code')->orderBy('name')->get();
        $voucherTypes = JournalVoucherTypeEnum::cases();

        // Get customers, suppliers for entity selection
        $customers = \App\Models\Customer\Customer::select('id', 'name_en as name')->orderBy('name_en')->get();
        $suppliers = \App\Models\Supplier\Supplier::select('id', 'name_en as name')->orderBy('name_en')->get();

        // Initialize empty journal voucher items collection for new journal voucher
        $journalVoucherItems = collect();

        return view('modules.finance.journal_voucher.journal-voucher-form', compact(
            'journalVoucher', 'jobs', 'accounts', 'voucherTypes', 'journalVoucherItems',
            'customers', 'suppliers'
        ));
    }

    /**
     * Store a newly created journal voucher in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'voucher_type' => 'required|integer|min:1|max:6',
            'job_id' => 'nullable|exists:jobs,id',
            'voucher_date' => 'required|date',
            'reference_no' => 'nullable|string',
            'currency' => 'required|string|size:3',
            'currency_rate' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'account_ids' => 'required|array',
            'account_ids.*' => 'exists:accounts,id',
            'descriptions' => 'required|array',
            'descriptions.*' => 'nullable|string',
            'debit_amounts' => 'required|array',
            'debit_amounts.*' => 'numeric|min:0',
            'credit_amounts' => 'required|array',
            'credit_amounts.*' => 'numeric|min:0',
            'entity_types' => 'nullable|array',
            'entity_types.*' => 'nullable|string|in:customer,supplier,job,tax',
            'entity_ids' => 'nullable|array',
            'entity_ids.*' => 'nullable|integer',
            'tax_ids' => 'nullable|array',
            'tax_ids.*' => 'nullable|exists:accounts,id',
            'tax_amounts' => 'nullable|array',
            'tax_amounts.*' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Create or update journal voucher
            if ($request->filled('journal_voucher_id')) {
                $journalVoucher = JournalVoucher::findOrFail($request->input('journal_voucher_id'));
                $journalVoucher->updated_by = Auth::id();
            } else {
                $journalVoucher = new JournalVoucher();
                $journalVoucher->status = JournalVoucherStatusEnum::DRAFT->value;
                $journalVoucher->company_id = companyId();
                $journalVoucher->created_by = Auth::id();

                // Generate row_no
                $year = Carbon::today()->format('Y');
                $lastRowNo = JournalVoucher::whereYear('voucher_date', $year)->max('unique_row_no') ?? 0;
                $journalVoucher->unique_row_no = $lastRowNo + 1;
                $journalVoucher->row_no = 'JV/' . date('y') . '/' . sprintf('%04d', $journalVoucher->unique_row_no);
            }

            // Set job_no if job_id is provided
            if ($validated['job_id']) {
                $job = Job::select('row_no')->find($validated['job_id']);
                $journalVoucher->job_no = $job->row_no;
            }

            // Calculate totals
            $debitTotal = 0;
            $creditTotal = 0;

            foreach ($request->input('account_ids') as $index => $accountId) {
                $debitAmount = $request->input('debit_amounts')[$index] ?? 0;
                $creditAmount = $request->input('credit_amounts')[$index] ?? 0;

                $debitTotal += $debitAmount;
                $creditTotal += $creditAmount;
            }

            // Update journal voucher fields
            $journalVoucher->voucher_type = $validated['voucher_type'];
            $journalVoucher->job_id = $validated['job_id'];
            $journalVoucher->voucher_date = $validated['voucher_date'];
            $journalVoucher->reference_no = $validated['reference_no'];
            $journalVoucher->currency = $validated['currency'];
            $journalVoucher->currency_rate = $validated['currency_rate'];
            $journalVoucher->debit_total = $debitTotal;
            $journalVoucher->credit_total = $creditTotal;
            $journalVoucher->base_debit_total = $debitTotal * $validated['currency_rate'];
            $journalVoucher->base_credit_total = $creditTotal * $validated['currency_rate'];
            $journalVoucher->notes = $validated['notes'];

            $journalVoucher->save();

            // Delete existing journal voucher items
            JournalVoucherItem::where('journal_voucher_id', $journalVoucher->id)->delete();

            // Create journal voucher items
            $journalVoucherItems = [];
            foreach ($request->input('account_ids') as $index => $accountId) {
                $debitAmount = $request->input('debit_amounts')[$index] ?? 0;
                $creditAmount = $request->input('credit_amounts')[$index] ?? 0;
                $entityType = $request->input('entity_types')[$index] ?? null;
                $entityId = $request->input('entity_ids')[$index] ?? null;
                $taxId = $request->input('tax_ids')[$index] ?? null;
                $taxAmount = $request->input('tax_amounts')[$index] ?? 0;

                if ($debitAmount > 0 || $creditAmount > 0) {
                    $journalVoucherItems[] = [
                        'journal_voucher_id' => $journalVoucher->id,
                        'account_id' => $accountId,
                        'entity_type' => $entityType,
                        'entity_id' => $entityId,
                        'tax_id' => $taxId,
                        'company_id' => companyId(),
                        'description' => $request->input('descriptions')[$index] ?? null,
                        'debit_amount' => $debitAmount,
                        'credit_amount' => $creditAmount,
                        'base_debit_amount' => $debitAmount * $validated['currency_rate'],
                        'base_credit_amount' => $creditAmount * $validated['currency_rate'],
                        'tax_amount' => $taxAmount,
                        'base_tax_amount' => $taxAmount * $validated['currency_rate'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (!empty($journalVoucherItems)) {
                DB::table('journal_voucher_items')->insert($journalVoucherItems);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Journal voucher saved successfully',
                'journal_voucher_id' => $journalVoucher->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error saving journal voucher: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified journal voucher.
     */
    public function show($id)
    {
        $journalVoucher = JournalVoucher::with(['job', 'journalVoucherItems.account', 'createdBy', 'approvedBy'])
            ->findOrFail($id);

        return view('modules.finance.journal_voucher.show', compact('journalVoucher'));
    }

    /**
     * Show the form for editing the specified journal voucher.
     */
    public function edit($id)
    {
        $journalVoucher = JournalVoucher::with('journalVoucherItems.account')->findOrFail($id);
        $jobs = Job::select('id', 'row_no')->orderBy('row_no', 'desc')->get();
        $accounts = Account::select('id', 'name', 'code')->orderBy('name')->get();
        $voucherTypes = JournalVoucherTypeEnum::cases();

        // Get customers, suppliers for entity selection
        $customers = \App\Models\Customer\Customer::select('id', 'name_en as name')->orderBy('name_en')->get();
        $suppliers = \App\Models\Supplier\Supplier::select('id', 'name_en as name')->orderBy('name_en')->get();

        return view('modules.finance.journal_voucher.journal-voucher-form', compact(
            'journalVoucher', 'jobs', 'accounts', 'voucherTypes',
            'customers', 'suppliers'
        ));
    }

    /**
     * Update the journal voucher status.
     */
    public function updateStatus($id, $status)
    {
        $journalVoucher = JournalVoucher::findOrFail($id);

        if ($status == JournalVoucherStatusEnum::APPROVED->value) {
            $journalVoucher->status = JournalVoucherStatusEnum::APPROVED->value;
            $journalVoucher->approved_by = Auth::id();
            $journalVoucher->approved_at = now();
        } elseif ($status == JournalVoucherStatusEnum::DISAPPROVED->value) {
            $journalVoucher->status = JournalVoucherStatusEnum::DISAPPROVED->value;
        } else {
            $journalVoucher->status = JournalVoucherStatusEnum::DRAFT->value;
        }

        $journalVoucher->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Journal voucher status updated successfully',
            'data' => [
                'id' => $journalVoucher->id,
                'status' => $journalVoucher->status,
            ],
        ]);
    }

    /**
     * Set disapproval reason for a journal voucher.
     */
    public function setDisapprovalReason(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $journalVoucher = JournalVoucher::findOrFail($id);
        $journalVoucher->status = JournalVoucherStatusEnum::DISAPPROVED->value;
        $journalVoucher->disapproval_reason = $validated['reason'];
        $journalVoucher->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Journal voucher disapproved successfully',
            'data' => [
                'id' => $journalVoucher->id,
                'status' => $journalVoucher->status,
            ],
        ]);
    }

    /**
     * Print the specified journal voucher.
     */
    public function print($id)
    {
        $journalVoucher = JournalVoucher::with(['job', 'journalVoucherItems.account', 'createdBy', 'approvedBy'])
            ->findOrFail($id);

        return view('modules.finance.journal_voucher.print', compact('journalVoucher'));
    }

    /**
     * Download the specified journal voucher as PDF.
     */
    public function download($id)
    {
        $journalVoucher = JournalVoucher::with(['job', 'journalVoucherItems.account', 'createdBy', 'approvedBy'])
            ->findOrFail($id);

        $html = view('modules.finance.journal_voucher.print', compact('journalVoucher'))->render();
        $fileName = "JournalVoucher_{$journalVoucher->row_no}.pdf";

        return createPDF($html, $fileName);
    }

    /**
     * Get data for DataTables.
     */
    public function fetchAllRows(Request $request)
    {
        $query = JournalVoucher::with(['job:id,row_no'])
            ->select([
                'journal_vouchers.id',
                'row_no',
                'voucher_type',
                'job_id',
                'job_no',
                'voucher_date',
                'reference_no',
                'currency',
                'debit_total',
                'credit_total',
                'status',
                'journal_vouchers.created_at',
            ])
            ->when($request->tab, function ($q) use ($request) {
                if ($request->tab !== 'all') {
                    $q->where('status', JournalVoucherStatusEnum::fromName($request->tab));
                }
            })
            ->orderBy('journal_vouchers.id', 'desc');

        // Get counts per status
        $statusCounts = JournalVoucher::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Normalize counts for all statuses
        $allCounts = [];
        foreach (JournalVoucherStatusEnum::cases() as $status) {
            $allCounts[$status->name] = $statusCounts[$status->value] ?? 0;
        }
        $allCounts['all'] = array_sum($allCounts);

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($model) => $model->id,
                'data-name' => fn($model) => 'Journal Voucher #' . htmlspecialchars($model->row_no, ENT_QUOTES, 'UTF-8'),
                'class' => 'row-item',
                'id' => fn($model) => 'journal-voucher-' . strtolower($model->row_no ?? $model->id),
            ])
            ->editColumn('voucher_date', fn($model) => \Carbon\Carbon::parse($model->voucher_date)->format('d-m-Y'))
            ->editColumn('currency', fn($model) => strtoupper($model->currency))
            ->editColumn('voucher_type', fn($model) => JournalVoucherTypeEnum::from($model->voucher_type)->label())
            ->editColumn('debit_total', fn($model) => number_format($model->debit_total, decimals()))
            ->editColumn('credit_total', fn($model) => number_format($model->credit_total, decimals()))
            ->editColumn('status', fn($model) => JournalVoucherStatusEnum::from($model->status)->label())
            ->with([
                'statusCounts' => $allCounts,
            ])
            ->toJson();
    }

    /**
     * Get context menu actions for a journal voucher.
     */
    public function actions($id)
    {
        $journalVoucher = JournalVoucher::select('id', 'row_no', 'status')->findOrFail($id);
        $contextMenu = collect([]);

        // Status actions
        if ($journalVoucher->status === JournalVoucherStatusEnum::DRAFT->value) {
            $contextMenu->push([
                'label' => __('Move to'),
                'type' => 'submenu',
                'separator' => 'after',
                'icon' => 'move_to',
                'items' => [
                    [
                        'label' => __('Approved'),
                        'id' => 'row_approved',
                        'data-id' => $journalVoucher->id,
                        'data-value' => JournalVoucherStatusEnum::APPROVED->value,
                        'icon' => 'confirmed'
                    ],
                    [
                        'label' => __('Disapproved'),
                        'id' => 'row_disapproved',
                        'class' => 'row_disapproved',
                        'data-id' => $journalVoucher->id,
                        'data-value' => JournalVoucherStatusEnum::DISAPPROVED->value,
                        'icon' => 'rejected'
                    ]
                ]
            ]);
        } elseif ($journalVoucher->status === JournalVoucherStatusEnum::APPROVED->value) {
            $contextMenu->push([
                'label' => __('Move to'),
                'type' => 'submenu',
                'separator' => 'after',
                'icon' => 'move_to',
                'items' => [
                    [
                        'label' => __('Move To Draft'),
                        'id' => 'row_draft',
                        'data-id' => $journalVoucher->id,
                        'data-value' => JournalVoucherStatusEnum::DRAFT->value,
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
            'data-id' => $journalVoucher->id,
            'type' => 'item',
            'icon' => 'view',
        ]);

        $contextMenu->push([
            'label' => __('Print'),
            'id' => 'row_print',
            'class' => 'row_print',
            'data-id' => $journalVoucher->id,
            'type' => 'item',
            'icon' => 'print',
            'onclick' => 'JOURNAL_VOUCHER.printPreview(' . $journalVoucher->id . ')',
        ]);

        $contextMenu->push([
            'label' => __('Download'),
            'id' => 'row_download',
            'class' => 'row_download',
            'data-id' => $journalVoucher->id,
            'type' => 'item',
            'icon' => 'download',
            'onclick' => 'JOURNAL_VOUCHER.downloadPDF(' . $journalVoucher->id . ')',
            'separator' => 'after',
        ]);

        // Edit and Delete actions (only for Draft status)
        if ($journalVoucher->status === JournalVoucherStatusEnum::DRAFT->value) {
            $edit = [
                'label' => __('Edit'),
                'id' => 'row_edit',
                'class' => 'row_edit',
                'data-id' => $journalVoucher->id,
                'type' => 'item',
                'icon' => 'edit'
            ];

            $delete = [
                'label' => __('Delete'),
                'id' => 'row_delete',
                'class' => 'row_delete',
                'data-id' => $journalVoucher->id,
                'type' => 'item',
                'icon' => 'delete'
            ];

            $contextMenu->push([
                'label' => __('Actions'),
                'type' => 'submenu',
                'icon' => 'action',
                'items' => [$edit, $delete]
            ]);
        }

        return response()->json($contextMenu->values());
    }

    /**
     * Remove the specified journal voucher from storage.
     */
    public function destroy($id)
    {
        $journalVoucher = JournalVoucher::findOrFail($id);

        if ($journalVoucher->status !== JournalVoucherStatusEnum::DRAFT->value) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only draft journal vouchers can be deleted',
            ], 400);
        }

        $journalVoucher->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Journal voucher deleted successfully',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Finance\JournalVoucher;

use App\Services\DocumentNumber;
use App\Enums\JournalVoucherStatusEnum;
use App\Enums\JournalVoucherTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Finance\Account\Account;
use App\Models\Finance\Finance;
use App\Models\Finance\FinanceSub;
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
        $accounts = $this->postableAccounts();
        $voucherTypes = JournalVoucherTypeEnum::cases();

        // Get customers, suppliers for entity selection
        $customers = \App\Models\Customer\Customer::select('id', 'name_en')->orderBy('name_en')->get();
        $suppliers = \App\Models\Supplier\Supplier::select('id', 'name_en')->orderBy('name_en')->get();

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

        $this->assertPeriodOpen($validated['voucher_date'], 'voucher_date');

        // A voucher is only saved when it balances: debits equal credits, there is something to post, and every amount
        // sits on an account. (A line with no amount is just an empty row and is ignored.)
        $debitTotal = 0.0;
        $creditTotal = 0.0;
        foreach ($request->input('account_ids') as $index => $accountId) {
            $debit = (float) ($request->input('debit_amounts')[$index] ?? 0);
            $credit = (float) ($request->input('credit_amounts')[$index] ?? 0);
            if ($debit <= 0 && $credit <= 0) {
                continue;
            }
            if (!$accountId) {
                return $this->unbalanced(__('Choose an account for every line that has an amount.'));
            }
            if ($debit > 0 && $credit > 0) {
                return $this->unbalanced(__('A line can have a debit or a credit, not both.'));
            }
            $debitTotal += $debit;
            $creditTotal += $credit;
        }
        if ($debitTotal <= 0 || $creditTotal <= 0) {
            return $this->unbalanced(__('Enter at least one debit and one credit amount.'));
        }
        if (abs($debitTotal - $creditTotal) > 0.005) {
            return $this->unbalanced(__('Debit and credit must be equal. Difference: :amount', ['amount' => number_format(abs($debitTotal - $creditTotal), decimals())]));
        }

        DB::beginTransaction();
        try {
            // Create or update journal voucher
            if ($request->filled('journal_voucher_id')) {
                $journalVoucher = JournalVoucher::findOrFail($request->input('journal_voucher_id'));
                // An approved voucher is already in the books: changing its lines here would leave the books on the old
                // numbers. It has to go back to Draft first (which takes its entries out), then be edited and approved again.
                if ((int) $journalVoucher->status === JournalVoucherStatusEnum::APPROVED->value) {
                    DB::rollBack();

                    return $this->unbalanced(__('An approved voucher cannot be edited. Move it to Draft first.'));
                }
                $journalVoucher->updated_by = Auth::id();
            } else {
                $journalVoucher = new JournalVoucher();
                $journalVoucher->status = JournalVoucherStatusEnum::DRAFT->value;
                $journalVoucher->company_id = companyId();
                $journalVoucher->created_by = Auth::id();

                // Generate row_no
                $prefix = 'JV/' . date('y') . '/';
                $journalVoucher->unique_row_no = DocumentNumber::next(JournalVoucher::class, $prefix);
                $journalVoucher->row_no = $prefix . sprintf('%04d', $journalVoucher->unique_row_no);
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
                'message' => __('Journal voucher saved successfully'),
                'journal_voucher_id' => $journalVoucher->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => __('Error saving journal voucher: ') . $e->getMessage(),
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

    /** The details drawer on the list (same content as the View page). */
    public function overviewDrawer($id)
    {
        $journalVoucher = JournalVoucher::with(['job', 'journalVoucherItems.account', 'createdBy', 'approvedBy'])
            ->findOrFail($id);

        return view('modules.finance.journal_voucher.view-overview-drawer', compact('journalVoucher'));
    }

    /**
     * Show the form for editing the specified journal voucher.
     */
    public function edit($id)
    {
        $journalVoucher = JournalVoucher::with('journalVoucherItems.account')->findOrFail($id);
        $jobs = Job::select('id', 'row_no')->orderBy('row_no', 'desc')->get();
        $accounts = $this->postableAccounts($journalVoucher->journalVoucherItems->pluck('account_id')->merge($journalVoucher->journalVoucherItems->pluck('tax_id'))->filter()->all());
        $voucherTypes = JournalVoucherTypeEnum::cases();

        // Get customers, suppliers for entity selection
        $customers = \App\Models\Customer\Customer::select('id', 'name_en')->orderBy('name_en')->get();
        $suppliers = \App\Models\Supplier\Supplier::select('id', 'name_en')->orderBy('name_en')->get();

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
        $previous = (int) ($journalVoucher->status instanceof \BackedEnum ? $journalVoucher->status->value : $journalVoucher->status);

        DB::beginTransaction();
        try {
            if ($status == JournalVoucherStatusEnum::APPROVED->value) {
                if (abs($journalVoucher->base_debit_total - $journalVoucher->base_credit_total) > 0.005) {
                    DB::rollBack();

                    return response()->json(['status' => 'error', 'message' => __('The voucher does not balance: debit and credit must be equal.')], 422);
                }
                $this->assertPeriodOpen($journalVoucher->getRawOriginal('voucher_date'), 'voucher_date');
                $journalVoucher->status = JournalVoucherStatusEnum::APPROVED->value;
                $journalVoucher->approved_by = Auth::id();
                $journalVoucher->approved_at = now();
                $journalVoucher->save();
                $this->postToLedger($journalVoucher);
            } else {
                $journalVoucher->status = $status == JournalVoucherStatusEnum::DISAPPROVED->value
                    ? JournalVoucherStatusEnum::DISAPPROVED->value
                    : JournalVoucherStatusEnum::DRAFT->value;
                $journalVoucher->save();
                // leaving Approved takes the entries out of the books again
                if ($previous === JournalVoucherStatusEnum::APPROVED->value) {
                    $this->removeFromLedger($journalVoucher->id);
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json([
            'status' => 'success',
            'message' => __('Journal voucher status updated successfully'),
            'data' => [
                'id' => $journalVoucher->id,
                'status' => $journalVoucher->status,
            ],
        ]);
    }

    /**
     * Approving a journal voucher posts it to the general ledger (voucher type JV): one line per item on its account,
     * with the customer / supplier / job it names. A line that carries a tax account and tax amount is split: the tax
     * part goes to the tax account (marked as a tax line, so VAT reports see it), the rest to the line's own account.
     */
    private function postToLedger(JournalVoucher $jv): void
    {
        $this->removeFromLedger($jv->id);
        $jv->loadMissing('journalVoucherItems');
        $rate = (float) ($jv->currency_rate ?: 1);

        $finance = new Finance();
        $finance->voucher_no = $jv->row_no;
        $finance->voucher_type = 'JV';
        $finance->reference_no = $jv->reference_no ?: $jv->row_no;
        $date = $jv->getRawOriginal('voucher_date');   // the models show dates as d-m-Y
        $finance->reference_date = $date;
        $finance->narration = 'Journal Voucher: ' . $jv->row_no . ($jv->notes ? ' - ' . $jv->notes : '');
        $finance->currency = $jv->currency ?: 'SAR';
        $finance->exchange_rate = $rate;
        $finance->total_debit = $jv->debit_total;
        $finance->total_credit = $jv->credit_total;
        $baseCurrency = strtoupper(optional(authUserCompany())->base_currency ?: 'SAR');
        $finance->base_currency = $baseCurrency;
        $finance->base_total_debit = $jv->base_debit_total;
        $finance->base_total_credit = $jv->base_credit_total;
        $finance->job_id = $jv->job_id;
        $finance->job_no = $jv->job_no ?? '';
        $finance->is_approved = 1;
        $finance->posted_at = now();
        $finance->linked_id = $jv->id;
        $finance->linked_type = JournalVoucher::class;
        $finance->company_id = $jv->company_id;
        $finance->user_id = Auth::id();
        $finance->save();

        $common = [
            'finance_id' => $finance->id, 'voucher_no' => $finance->voucher_no, 'voucher_type' => 'JV',
            'reference_no' => $finance->reference_no, 'reference_date' => $date,
            'currency' => $finance->currency, 'exchange_rate' => $rate, 'base_currency' => $baseCurrency,
            'job_id' => $jv->job_id, 'job_no' => $jv->job_no ?? '', 'company_id' => $jv->company_id, 'user_id' => Auth::id(),
            'is_tax_line' => 0, 'is_auto_generated' => 1, 'linked_id' => $jv->id, 'linked_type' => JournalVoucher::class,
            'created_at' => now(), 'updated_at' => now(),
        ];

        $subs = [];
        foreach ($jv->journalVoucherItems as $item) {
            $party = [
                'customer_id' => $item->entity_type === 'customer' ? $item->entity_id : null,
                'supplier_id' => $item->entity_type === 'supplier' ? $item->entity_id : null,
            ];
            $debit = (float) $item->debit_amount;
            $credit = (float) $item->credit_amount;
            $tax = min((float) $item->tax_amount, max($debit, $credit));
            $line = fn (float $d, float $c, int $account, string $desc, int $isTax) => array_merge($common, $party, [
                'account_id' => $account, 'description' => $desc, 'debit' => $d, 'credit' => $c,
                'base_debit' => round($d * $rate, 2), 'base_credit' => round($c * $rate, 2), 'is_tax_line' => $isTax,
            ]);
            $desc = $item->description ?: 'Journal Voucher ' . $jv->row_no;

            if ($tax > 0 && $item->tax_id) {
                $subs[] = $line($debit ? $debit - $tax : 0, $credit ? $credit - $tax : 0, (int) $item->account_id, $desc, 0);
                $subs[] = $line($debit ? $tax : 0, $credit ? $tax : 0, (int) $item->tax_id, 'VAT - ' . $desc, 1);
            } else {
                $subs[] = $line($debit, $credit, (int) $item->account_id, $desc, 0);
            }
        }
        // Each line is rounded on its own, so the lines can drift a cent from the voucher's base total (3 lines of 10.01 at
        // 3.755 are 37.59 each = 112.77, the total is 112.76). Put the difference on the largest line of that side, so the
        // entry balances to the cent and agrees with the voucher.
        foreach (['base_debit' => round((float) $jv->debit_total * $rate, 2), 'base_credit' => round((float) $jv->credit_total * $rate, 2)] as $col => $target) {
            $diff = round($target - array_sum(array_column($subs, $col)), 2);
            if ($diff != 0.0) {
                $biggest = array_keys(array_column($subs, $col), max(array_column($subs, $col)))[0];
                $subs[$biggest][$col] = round($subs[$biggest][$col] + $diff, 2);
            }
        }
        FinanceSub::insert($subs);
    }

    private function removeFromLedger($journalVoucherId): void
    {
        foreach (Finance::where('linked_id', $journalVoucherId)->where('linked_type', JournalVoucher::class)->get() as $finance) {
            FinanceSub::where('finance_id', $finance->id)->delete();
            $finance->delete();
        }
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
            'message' => __('Journal voucher disapproved successfully'),
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

        // ?fragment=1: just the voucher with self-contained styles, for the PDF (no <html> / <body>)
        return view('modules.finance.journal_voucher.print', ['journalVoucher' => $journalVoucher, 'fragment' => request()->boolean('fragment')]);
    }

    /**
     * Download the specified journal voucher as PDF: the print page opens with the print dialog ready, where the
     * browser's "Save as PDF" makes the file (this app has no server-side PDF library).
     */
    public function download($id)
    {
        $journalVoucher = JournalVoucher::with(['job', 'journalVoucherItems.account', 'createdBy', 'approvedBy'])
            ->findOrFail($id);

        return view('modules.finance.journal_voucher.print', ['journalVoucher' => $journalVoucher, 'autoPrint' => true]);
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
                'journal_vouchers.company_id',
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
                'message' => __('Only draft journal vouchers can be deleted'),
            ], 400);
        }

        $journalVoucher->delete();

        return response()->json([
            'status' => 'success',
            'message' => __('Journal voucher deleted successfully'),
        ]);
    }

    /** A refused save: the form shows the message. */
    private function unbalanced(string $message)
    {
        return response()->json(['status' => 'error', 'message' => $message], 422);
    }

    /**
     * Accounts a journal line can post to: active leaf accounts (no child accounts, so group headings are left out),
     * by code. $keep holds accounts already used on the voucher being edited, so an old line still shows its account.
     */
    private function postableAccounts(array $keep = [])
    {
        return Account::select('id', 'name', 'code')
            ->where(function ($q) use ($keep) {
                $q->where('is_active', 1)
                    ->whereNotExists(fn ($c) => $c->from('accounts as child')->whereColumn('child.parent_id', 'accounts.id'));
                if ($keep) {
                    $q->orWhereIn('id', $keep);
                }
            })
            ->orderBy('code')
            ->get();
    }
}

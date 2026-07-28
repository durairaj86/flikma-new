<?php

namespace App\Http\Controllers\Finance\Adjustment;

use App\Enums\CreditNoteEnum;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Zatca\ZatcaController;
use App\Models\Finance\Account\Account;
use App\Models\Finance\Adjustment\CreditNote;
use App\Models\Finance\Adjustment\CreditNoteSub;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Finance\Finance;
use App\Models\Finance\FinanceSub;
use App\Models\Job\Job;
use App\Models\Master\Description;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;
use Yajra\DataTables\Facades\DataTables;

class CreditNoteController extends Controller
{
    public function modal(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $creditNote = new CreditNote();
        $creditNote->creditNoteSubs = [new CreditNoteSub()];
        $jobs = Job::select('id', 'row_no', 'customer_id')->with('customer:id,name_en')->get();
        $parents = Account::where('type', '!=', 'Equity')->where('is_active', 1)->orderBy('name')->get();//2=>Bank & Cash sub accounts

        $subAccounts = Account::where('type', '!=', 'Equity')->where('is_grouped', 0)->orderBy('name')->get();
        $customerInvoices = CustomerInvoice::where('status', 3)->get();
        return view('modules.finance.credit-note.credit-note-form', compact('creditNote', 'jobs', 'parents', 'subAccounts','customerInvoices'));
    }

    public function edit($id)
    {
        $creditNote = CreditNote::with(['creditNoteSubs', 'documents'])->findOrFail($id);
        $jobs = Job::select('id', 'row_no', 'customer_id')->with('customer:id,name_en')->get();
        //$accounts = Account::where('type', '!=', 'Equity')->get();
        $parents = Account::where('type', '!=', 'Equity')->where('is_active', 1)->orderBy('name')->get();//2=>Bank & Cash sub accounts

        $subAccounts = Account::where('type', '!=', 'Equity')->where('is_grouped', 0)->orderBy('name')->get();
        return view('modules.finance.credit-note.credit-note-form', compact('creditNote', 'jobs', 'parents', 'subAccounts'));
    }

    public function listBasedOnJob($job_id)
    {
        $job = Job::select('row_no')->find(decodeId($job_id));
        $job_no = $job->row_no;
        return view('modules.finance.credit-note.list', compact('job_id', 'job_no'));
    }

    public function fetchAllRows(Request $request, $job_id = 'list'): \Illuminate\Http\JsonResponse
    {
        if ($job_id != 'list') {
            $job_id = decodeId($job_id);
        }
        $filter = $request->filterData ?? [];

        // Base query for Credit Notes
        $rows = CreditNote::select(
            'credit_notes.id as row_no',
            'credit_notes.id as id',
            'posted_at',
            'job_id',
            'job_no',
            'customer_id',
            'invoice_id',
            'credit_notes.currency as currency',
            'sub_total',
            'tax_total',
            'grand_total',
            'credit_notes.status as status',
            'credit_notes.created_at as created_at',
            'credit_notes.company_id as company_id',
        )
            ->with(['customer:id,name_en,name_ar,row_no'])
            ->with(['job:id,shipment_mode'])
            ->with(['invoice:id,row_no'])
            ->when($request->tab && $request->tab !== 'all', function ($q) use ($request) {
                $q->where('credit_notes.status', CreditNoteEnum::fromName($request->tab));
            })
            ->when($job_id != 'list', function ($query) use ($job_id) {
                $query->where('job_id', $job_id);
            })
            ->when(isset($filter['filter-from-date'], $filter['filter-to-date']),
                function ($query) use ($filter) {
                    $from = Carbon::parse($filter['filter-from-date'])->startOfDay();
                    $to   = Carbon::parse($filter['filter-to-date'])->addDay()->startOfDay();

                    $query->where('posted_at', '>=', $from)
                        ->where('posted_at', '<',  $to);
                }
            )
            ->when(isset($filter['customSearch']) && !empty($filter['customSearch']), function ($query) use ($filter) {
                $search = $filter['customSearch'];
                $query->where(function ($q) use ($search) {
                    $q->where('credit_notes.id', 'like', "%{$search}%")
                        ->orWhere('job_no', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($q) use ($search) {
                            $q->where('name_en', 'like', "%{$search}%")
                                ->orWhere('name_ar', 'like', "%{$search}%");
                        })
                        ->orWhereHas('invoice', function ($q) use ($search) {
                            $q->where('row_no', 'like', "%{$search}%");
                        });
                });
            })
            ->when(isset($filter['customers']) && !empty($filter['customers']), function ($query) use ($filter) {
                $query->whereIn('customer_id', decodeIds($filter['customers']));
            })
            ->when(isset($filter['invoice']) && !empty($filter['invoice']), function ($query) use ($filter) {
                $query->where('invoice_id', decodeId($filter['invoice']));
            })
            ->orderBy('credit_notes.id', 'desc');

        // ✅ Get counts per status
        $statusCounts = CreditNote::select('status', DB::raw('COUNT(*) as total'))
            ->when($job_id != 'list', function ($query) use ($job_id) {
                $query->where('job_id', $job_id);
            })
            ->when(isset($filter['filter-from-date'], $filter['filter-to-date']),
                function ($query) use ($filter) {
                    $from = Carbon::parse($filter['filter-from-date'])->startOfDay();
                    $to   = Carbon::parse($filter['filter-to-date'])->addDay()->startOfDay();

                    $query->where('posted_at', '>=', $from)
                        ->where('posted_at', '<',  $to);
                }
            )
            ->when(isset($filter['customers']) && !empty($filter['customers']), function ($query) use ($filter) {
                $query->whereIn('customer_id', decodeIds($filter['customers']));
            })
            ->when(isset($filter['invoice']) && !empty($filter['invoice']), function ($query) use ($filter) {
                $query->where('invoice_id', decodeId($filter['invoice']));
            })
            ->when(isset($filter['customSearch']) && !empty($filter['customSearch']), function ($query) use ($filter) {
                $search = $filter['customSearch'];
                $query->where(function ($q) use ($search) {
                    $q->where('credit_notes.id', 'like', "%{$search}%")
                        ->orWhere('job_no', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($q) use ($search) {
                            $q->where('name_en', 'like', "%{$search}%")
                                ->orWhere('name_ar', 'like', "%{$search}%");
                        })
                        ->orWhereHas('invoice', function ($q) use ($search) {
                            $q->where('row_no', 'like', "%{$search}%");
                        });
                });
            })
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // ✅ Calculate sales summary
        $salesSummary = CreditNote::select([
            // Overall Totals
            DB::raw('SUM(grand_total) as overall_sales'),

            // Draft Totals (status = 1)
            DB::raw('SUM(CASE WHEN status = 1 THEN grand_total ELSE 0 END) as total_draft_grand'),
            DB::raw('SUM(CASE WHEN status = 1 THEN sub_total ELSE 0 END) as total_draft_sub'),
            DB::raw('SUM(CASE WHEN status = 1 THEN tax_total ELSE 0 END) as total_draft_tax'),

            // Approved Totals (status = 3)
            DB::raw('SUM(CASE WHEN status = 3 THEN grand_total ELSE 0 END) as total_approved_grand'),
            DB::raw('SUM(CASE WHEN status = 3 THEN sub_total ELSE 0 END) as total_approved_sub'),
            DB::raw('SUM(CASE WHEN status = 3 THEN tax_total ELSE 0 END) as total_approved_tax'),
        ])
            ->when($job_id != 'list', function ($query) use ($job_id) {
                $query->where('job_id', $job_id);
            })
            ->when(isset($filter['filter-from-date'], $filter['filter-to-date']),
                function ($query) use ($filter) {
                    $from = Carbon::parse($filter['filter-from-date'])->startOfDay();
                    $to   = Carbon::parse($filter['filter-to-date'])->addDay()->startOfDay();

                    $query->where('posted_at', '>=', $from)
                        ->where('posted_at', '<',  $to);
                }
            )
            ->when(isset($filter['customers']) && !empty($filter['customers']), function ($query) use ($filter) {
                $query->whereIn('customer_id', decodeIds($filter['customers']));
            })
            ->when(isset($filter['invoice']) && !empty($filter['invoice']), function ($query) use ($filter) {
                $query->where('invoice_id', decodeId($filter['invoice']));
            })
            ->when(isset($filter['customSearch']) && !empty($filter['customSearch']), function ($query) use ($filter) {
                $search = $filter['customSearch'];
                $query->where(function ($q) use ($search) {
                    $q->where('credit_notes.id', 'like', "%{$search}%")
                        ->orWhere('job_no', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($q) use ($search) {
                            $q->where('name_en', 'like', "%{$search}%")
                                ->orWhere('name_ar', 'like', "%{$search}%");
                        })
                        ->orWhereHas('invoice', function ($q) use ($search) {
                            $q->where('row_no', 'like', "%{$search}%");
                        });
                });
            })
            ->first();

        // ✅ Normalize counts for all statuses
        $allCounts = [];
        foreach (CreditNoteEnum::cases() as $status) {
            $allCounts[$status->name] = $statusCounts[$status->value] ?? 0;
        }
        $allCounts['all'] = array_sum($allCounts);
        $decimals = decimals();

        // ✅ Return formatted DataTable
        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($model) => $model->id,
                'data-name' => fn($model) => 'Credit Note #' . htmlspecialchars($model->row_no, ENT_QUOTES, 'UTF-8'),
                'class' => 'row-item',
                'id' => fn($model) => 'credit-note-' . strtolower($model->row_no ?? $model->id),
            ])
            ->editColumn('posted_at', fn($model) => \Carbon\Carbon::parse($model->posted_at)->format('d-m-Y'))
            ->editColumn('currency', fn($model) => strtoupper($model->currency))
            ->addColumn('customer_name', fn($model) => $model->customer?->name_en ? truncateName($model->customer?->name_en, 15) : '-')
            ->addColumn('invoice_no', fn($model) => $model->invoice?->row_no ?? '-')
            ->editColumn('sub_total', fn($model) => number_format($model->sub_total, $decimals))
            ->editColumn('tax_total', fn($model) => number_format($model->tax_total, $decimals))
            ->editColumn('grand_total', fn($model) => number_format($model->grand_total, $decimals))
            ->with([
                'statusCounts' => $allCounts,
                'salesSummary' => $salesSummary,
            ])
            ->toJson();
    }

    public function store(Request $request)
    {
        // Remove commas from all unit prices
        if ($request->has('unit_price')) {
            $request->merge([
                'unit_price' => collect($request->unit_price)
                    ->map(fn($v) => str_replace(',', '', $v))
                    ->toArray()
            ]);
        }
        $request->merge(['customer' => decodeId($request->input('customer'))]);

        $validated = $request->validate([
            'credit_note_type' => 'required',
            'invoice_id' => 'required',
            'customer' => 'required|exists:customers,id',
            'credit_note_date' => 'required|date',
            'job_id' => 'required|exists:jobs,id',
            'reason' => 'required',
            //'currency_rate' => 'required',
            //'currency' => 'required|exists:currencies,code',
            'terms' => 'nullable|string|max:1000',

            'description_id.*' => 'required|string|max:255',
            'comment.*' => 'nullable|string|max:500',
            'unit_id.*' => 'required|numeric',
            'quantity.*' => 'required|numeric|min:1',
            'unit_price.*' => 'required|min:0|regex:/^\d+(\.\d{1,2})?$/',
            'tax.*' => 'nullable|string',
        ]);

        $this->assertPeriodOpen($validated['credit_note_date'], 'credit_note_date');

        //$userId = Auth::id();
        $companyId = companyId();

        // 🔹 Fetch or create new
        if ($request->filled('data-id')) {
            $creditNote = CreditNote::findOrFail($request->input('data-id'));
        } else {
            $creditNote = new CreditNote();
            /*$cNYear = Carbon::parse($request->credit_note_date)->format('Y');
            $lastNo = Job::whereYear('posted_at', $cNYear)->max('unique_row_no') ?? 0;
            $creditNote->unique_row_no = $lastNo + 1;*/
            $creditNote->row_no = 'DRC' . date('ydis') . rand(100, 999);

            $job = Job::select('row_no')->find($request->input('job_id'));
            if ($job) {
                $creditNote->job_no = $job->row_no;
            }

            $this->setBaseColumns($creditNote);
        }

        // 🔹 Calculate totals
        $subTotal = 0;
        $taxTotal = 0;

        foreach ($request->quantity as $i => $qty) {
            $price = $request->unit_price[$i] ?? 0;
            $taxRate = vatPercent($request->tax[$i] ?? 0);

            $lineTotal = $qty * $price;
            $lineTax = $lineTotal * ($taxRate / 100);

            $subTotal += $lineTotal;
            $taxTotal += $lineTax;
        }

        $grandTotal = $subTotal + $taxTotal;

        // 🔹 Assign totals
        $creditNote->invoice_id = $validated['invoice_id'] ?? null;
        $creditNote->credit_note_type = $validated['credit_note_type'];
        $creditNote->job_id = $validated['job_id'] ?? null;
        $creditNote->customer_id = $validated['customer'] ?? null;
        $creditNote->posted_at = formDate($validated['credit_note_date']);
        $creditNote->reason = $validated['reason'];
        //$customer->currency = $validated['currency'];
        //$customer->currency_rate = $validated['currency_rate'];
        $creditNote->terms = $validated['terms'] ?? null;
        $creditNote->base_sub_total = $creditNote->currency_rate * $subTotal;
        $creditNote->base_tax_total = $creditNote->currency_rate * $taxTotal;
        $creditNote->sub_total = $subTotal;
        $creditNote->tax_total = $taxTotal;
        $creditNote->grand_total = $grandTotal;
        $creditNote->status = 1;

        DB::beginTransaction();
        try {

        $creditNote->save();
        if ($request->hasFile('attachments') && count($request->file('attachments'))) {
            $userId = Auth::id();

            foreach ($request->file('attachments') as $file) {
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME); // file name without extension
                $extension = $file->getClientOriginalExtension(); // file extension
                $uniqueName = $originalName . '_' . uniqid() . '.' . $extension; // append unique ID

                // Store file using unique name
                $path = $file->storeAs(
                    'documents/' . $companyId . '/credit_note/' . $creditNote->id,
                    $uniqueName,
                    'public'
                );

                // Save record in DB
                $creditNote->documents()->create([
                    'document_type' => CreditNote::class,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(), // keep original name for display
                    'title' => 'credit_note',
                    'posted_date' => now(),
                    'user_id' => $userId,
                    'company_id' => $companyId,
                ]);
            }
        }


        // 🔹 Prepare items
        $creditNoteSub = [];
        $descriptions = Description::descriptions()->keyBy('id');
        foreach ($request->description_id as $i => $desc) {
            $qty = $request->quantity[$i] ?? 0;
            $price = $request->unit_price[$i] ?? 0;
            $taxRate = vatPercent($request->tax[$i] ?? 0);
            $lineTotal = $qty * $price;
            $lineTax = $lineTotal * ($taxRate / 100);
            $netAmount = $lineTotal + $lineTax;

            $creditNoteSub[] = [
                'credit_note_id' => $creditNote->id,
                'account_id' => $request->account[$i],
                'company_id' => $companyId,
                'description_id' => $desc,
                'description' => $descriptions[$desc]->description,
                'comment' => $request->comment[$i] ?? null,
                'unit_id' => $request->unit_id[$i],
                'quantity' => $qty,
                'unit_price' => $price,
                'tax_code' => $request->tax[$i] ?? null,
                'tax_percent' => $taxRate,
                'tax_amount' => $lineTax,
                'total' => $lineTotal,
                'total_with_tax' => $netAmount,
            ];
        }

        DB::table('credit_note_subs')
            ->where('credit_note_id', $creditNote->id)
            ->delete();

        if (!empty($creditNoteSub)) {
            DB::table('credit_note_subs')->insert($creditNoteSub);
        }

        DB::commit();

        // Finance entry
        //$this->storeCustomerInvoiceFinance($customer, $customerSub);  // For invoice

        //$this->storecustomerAdvanceFinance($advance);                 // For advance

        //$this->storecustomerAdvanceAdjustmentFinance($adjustment);    // For adjustment


        return response()->json([
            'status' => 'success',
            'message' => 'Credit note created successfully',
            'credit_note_id' => $creditNote->id,
        ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error saving credit note: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function actions($id)
    {
        $creditNote = CreditNote::select(
            'id',
            'row_no',
            'status'
        )->findOrFail($id);
        $contextMenu = collect([]);
        $edit = $delete = [];
        if ($creditNote->status === CreditNoteEnum::DRAFT->value) {
            $contextMenu->push([
                'label' => __('Move to'),
                'type' => 'submenu',
                'separator' => 'after',
                'icon' => 'move_to',
                'items' => [
                    [
                        'label' => __('Approved'),
                        'code' => '01CSBK',
                        'id' => 'row_approved',
                        'data-id' => $creditNote->id,
                        'data-value' => CreditNoteEnum::APPROVED->value,
                        'icon' => 'confirmed'
                    ],
                    [
                        'label' => __('Cancelled'),
                        'code' => '01CSRJ',
                        'id' => 'row_rejected',
                        'class' => 'row_rejected',
                        'data-id' => $creditNote->id,
                        'data-value' => CreditNoteEnum::CANCELLED->value,
                        'icon' => 'rejected'
                    ]
                ]
            ]);
        }

        $contextMenu->push([
            'label' => __('Print'),
            'code' => '01CSVW',
            'id' => 'row_print',
            'class' => 'row_print',
            'data-id' => $creditNote->id,
            'type' => 'item',
            'icon' => 'print',
            'onclick' => 'CREDIT_NOTE.printPreview(' . $creditNote->id . ')',
            //'separator' => 'before',
        ]);
        $contextMenu->push([
            'label' => __('View'),
            'code' => '01CSVW',
            'id' => 'row_view',
            'class' => 'row_view',
            'data-id' => $creditNote->id,
            'type' => 'item',
            'icon' => 'view',
            'separator' => 'after',
        ]);
        $contextMenu->push([
            'label' => __('Send Email'),
            'code' => '01CSEM',
            'id' => 'row_email',
            'data-id' => $creditNote->id,
            'type' => 'item',
            'icon' => 'email',
            //'separator' => 'after',
        ]);
        if ($creditNote->status === CreditNoteEnum::DRAFT->value) {
            $edit = [
                'label' => __('Edit'),
                'code' => '01CSED',
                'id' => 'row_edit',
                'class' => 'row_edit',
                'data-id' => $creditNote->id,
                'type' => 'item',
                'icon' => 'edit'
            ];
            $delete = [
                'label' => __('Delete'),
                'code' => '01CSED',
                'id' => 'row_delete',
                'class' => 'row_delete',
                'data-id' => $creditNote->id,
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

    public function updateStatus($id, $status): \Illuminate\Http\JsonResponse
    {
        $creditNote = CreditNote::findOrFail($id);
        $previousStatus = $creditNote->status;
        $message = 'Credit note status updated successfully!';

        DB::beginTransaction();
        try {
            if ($status == CreditNoteEnum::APPROVED->value) {

                // checking zatca registered or not
                $zatca = zatcaDateCheck($creditNote);

                if ($zatca['zatcaRegister'] && $zatca['message']) {
                    return errorResponse($zatca['message']);
                }

                $postingDate = $creditNote->posted_at;
                if ($zatca['zatcaRegister']) {
                    $postingDate = date('Y-m-d');
                }

                $creditNote->status = CreditNoteEnum::APPROVED;

                $year = Carbon::today()->format('Y');
                $lastRowNo = CreditNote::whereYear('posted_at', $year)->max('unique_row_no') ?? 0;
                $creditNote->unique_row_no = $lastRowNo + 1;
                $creditNote->draft_no = $creditNote->row_no;
                $creditNote->old_invoice_date = formDate($creditNote->posted_at);
                $creditNote->posted_at = $postingDate;
                $creditNote->row_no = 'CR-' . date('y') . '-' . sprintf('%04d', $creditNote->unique_row_no);
                $creditNote->save();

                // Post the credit note to the general ledger
                $this->createCreditNoteFinanceEntries($creditNote);

                $data = [];
                if ($zatca['zatcaRegister']) {
                    $creditNote->load(['customer']);
                    $zatca = new ZatcaController();
                    $z = $zatca->submitTax($creditNote, 'credit-note');
                    $approveMessage = __("Zatca Approved", ['module' => '', 'status' => $z['title']]);

                    if ($z['type'] == 'error') {
                        return errorResponse([$z['message'], 'ZATCA Status - ' . $z['title']]);
                    } elseif ($z['type'] == 'warning') {
                        $data['type'] = 'warning';
                        $message = [
                            $approveMessage . ' with warning:' . $z['message'],
                            __($creditNote->title) . ' ' . $creditNote->row_no
                        ];
                    } else {
                        $message = [
                            $approveMessage,
                            __($creditNote->title) . ' ' . $creditNote->row_no
                        ];
                    }

                }

            } else {
                $creditNote->status = $status;
                $creditNote->save();

                // Remove GL entries when the credit note leaves APPROVED
                if ($previousStatus == CreditNoteEnum::APPROVED->value) {
                    $this->deleteCreditNoteFinanceEntries($creditNote->id);
                }
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return errorResponse($e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => [
                'id' => $creditNote->id,
                'status' => $creditNote->status,
            ],
        ]);
    }

    /**
     * Post an approved credit note to the general ledger:
     * DR revenue (per line account) + DR Output VAT / CR Accounts Receivable.
     * Mirrors the customer-invoice posting in reverse.
     */
    private function createCreditNoteFinanceEntries(CreditNote $creditNote): void
    {
        $this->deleteCreditNoteFinanceEntries($creditNote->id);

        $currencyRate = $creditNote->currency_rate ?? 1;
        $grandTotal = $creditNote->grand_total ?? 0;
        $baseGrandTotal = $grandTotal * $currencyRate;

        $finance = new Finance();
        $finance->voucher_no = $creditNote->row_no;
        $finance->voucher_type = 'CN';
        $finance->reference_no = $creditNote->row_no;
        $finance->reference_date = formDate($creditNote->posted_at);
        $finance->customer_id = $creditNote->customer_id;
        $finance->narration = 'Credit Note: ' . $creditNote->row_no
            . ($creditNote->invoice_no ? ' against invoice ' . $creditNote->invoice_no : '');
        $finance->currency = $creditNote->currency ?? 'SAR';
        $finance->exchange_rate = $currencyRate;
        $finance->total_debit = $grandTotal;
        $finance->total_credit = $grandTotal;
        $finance->base_currency = 'SAR';
        $finance->base_total_debit = $baseGrandTotal;
        $finance->base_total_credit = $baseGrandTotal;
        $finance->job_id = $creditNote->job_id;
        $finance->job_no = $creditNote->job_no ?? '';
        $finance->is_approved = 1;
        $finance->posted_at = now();
        $finance->linked_id = $creditNote->id;
        $finance->linked_type = CreditNote::class;
        $finance->company_id = $creditNote->company_id;
        $finance->user_id = Auth::id();
        $finance->save();

        $commonData = [
            'finance_id' => $finance->id,
            'voucher_no' => $finance->voucher_no,
            'voucher_type' => $finance->voucher_type,
            'reference_no' => $finance->reference_no,
            'reference_date' => formDate($creditNote->posted_at),
            'currency' => $finance->currency,
            'exchange_rate' => $currencyRate,
            'base_currency' => 'SAR',
            'customer_id' => $creditNote->customer_id,
            'job_id' => $creditNote->job_id,
            'job_no' => $creditNote->job_no ?? '',
            'company_id' => $creditNote->company_id,
            'user_id' => Auth::id(),
            'is_tax_line' => 0,
            'is_auto_generated' => 1,
            'linked_id' => $creditNote->id,
            'linked_type' => CreditNote::class,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $financeSubs = [];

        // DR revenue reversal per line (uses each line's account)
        foreach ($creditNote->creditNoteSubs as $sub) {
            $lineTotal = $sub->total ?? $sub->line_total ?? 0;
            $financeSubs[] = array_merge($commonData, [
                'account_id' => $sub->account_id ?? 38, // fall back to 4160 Sales Revenue
                'description' => 'Credit Note - ' . ($sub->description ?? $creditNote->row_no),
                'debit' => $lineTotal,
                'credit' => 0,
                'base_debit' => $lineTotal * $currencyRate,
                'base_credit' => 0,
            ]);
        }

        // DR Output VAT reversal
        if (($creditNote->tax_total ?? 0) > 0) {
            $financeSubs[] = array_merge($commonData, [
                'account_id' => 20, // 2130 Output VAT Payable
                'description' => 'Output VAT reversal - Credit Note ' . $creditNote->row_no,
                'debit' => $creditNote->tax_total,
                'credit' => 0,
                'base_debit' => $creditNote->tax_total * $currencyRate,
                'base_credit' => 0,
                'is_tax_line' => 1,
            ]);
        }

        // CR Accounts Receivable for the full credit amount
        $financeSubs[] = array_merge($commonData, [
            'account_id' => 5, // 1130 Accounts Receivable
            'description' => 'Receivable reduction - Credit Note ' . $creditNote->row_no,
            'debit' => 0,
            'credit' => $grandTotal,
            'base_debit' => 0,
            'base_credit' => $baseGrandTotal,
        ]);

        FinanceSub::insert($financeSubs);

        // Reflect the credit against the linked invoice's settled amount so
        // the collection screen no longer offers the credited balance.
        if ($creditNote->invoice_id) {
            $invoice = CustomerInvoice::find($creditNote->invoice_id);
            if ($invoice) {
                $invoice->paid_amount = ($invoice->paid_amount ?? 0) + $grandTotal;
                $invoice->base_paid_amount = ($invoice->base_paid_amount ?? 0) + $baseGrandTotal;
                $invoice->save();
            }
        }
    }

    /**
     * Remove the GL entries of a credit note (used on re-post / un-approve)
     * and revert the settled amount on the linked invoice.
     */
    private function deleteCreditNoteFinanceEntries($creditNoteId): void
    {
        $financeEntries = Finance::where('linked_id', $creditNoteId)
            ->where('linked_type', CreditNote::class)
            ->get();

        if ($financeEntries->isEmpty()) {
            return;
        }

        $creditNote = CreditNote::find($creditNoteId);
        if ($creditNote && $creditNote->invoice_id) {
            $invoice = CustomerInvoice::find($creditNote->invoice_id);
            if ($invoice) {
                $invoice->paid_amount = max(0, ($invoice->paid_amount ?? 0) - ($creditNote->grand_total ?? 0));
                $invoice->base_paid_amount = max(0, ($invoice->base_paid_amount ?? 0) - (($creditNote->grand_total ?? 0) * ($creditNote->currency_rate ?? 1)));
                $invoice->save();
            }
        }

        foreach ($financeEntries as $finance) {
            FinanceSub::where('finance_id', $finance->id)->delete();
            $finance->delete();
        }
    }

    public function overview($id)
    {
        $creditNote = CreditNote::with(['creditNoteSubs', 'customer', 'job', 'invoice', 'documents'])->findOrFail($id);
        $descriptions = Description::descriptions()->pluck('description', 'id')->toArray();
        return view('modules.finance.credit-note.view-overview', compact('creditNote', 'descriptions'));
    }

    public function print($id)
    {
        $creditNote = $this->allPrint($id);
        $descriptions = Description::descriptions()->pluck('description', 'id')->toArray();
        $company = authUserCompany();
        return view('modules.finance.credit-note.print', compact('creditNote', 'descriptions', 'company'));
    }

    public function allPrint($id)
    {
        return CreditNote::with('creditNoteSubs', 'customer', 'job')->findOrFail($id);
    }
}

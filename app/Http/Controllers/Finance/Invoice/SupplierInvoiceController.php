<?php

namespace App\Http\Controllers\Finance\Invoice;

use App\Enums\JobEnum;
use App\Enums\SupplierInvoiceEnum;
use App\Http\Controllers\Controller;
use App\Models\Finance\Account\Account;
use App\Models\Finance\SupplierInvoice\CustomerInvoice;
use App\Models\Finance\SupplierInvoice\SupplierInvoice;
use App\Models\Finance\SupplierInvoice\SupplierInvoiceSub;
use App\Models\Job\Job;
use App\Models\Master\Description;
use App\Models\Master\LogisticActivity;
use App\Traits\Finance\SupplierFinanceOperation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class SupplierInvoiceController extends Controller
{
    use SupplierFinanceOperation;

    public function modal(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $supplier = new SupplierInvoice();
        $supplier->supplierInvoiceSubs = [new SupplierInvoiceSub()];
        $job_id = null;
        if ($request->get('jobId') == 'list') {//from job list
            $jobs = Job::select('id', 'row_no', 'customer_id')->with('customer:id,name_en')->whereIn('status', [JobEnum::COMPLETED, JobEnum::PENDING])->get();
        } else {
            $jobs = Job::select('id', 'row_no', 'customer_id')->with('customer:id,name_en')->findOrFail(decodeId($request->get('jobId')));
            $job_id = $jobs->id;
            $jobs = [$jobs];
        }

        $parents = Account::where('type', '!=', 'Equity')->where('is_active', 1)->orderBy('name')->get();//2=>Bank & Cash sub accounts

        $subAccounts = Account::where('type', '!=', 'Equity')->where('is_grouped', 0)->orderBy('name')->get();
        return view('modules.finance.supplier-invoice.supplier-invoice-form', compact('supplier', 'jobs', 'parents', 'subAccounts', 'job_id'));
    }

    public function edit($id)
    {
        $job_id = null;
        $supplier = SupplierInvoice::with(['supplierInvoiceSubs', 'documents'])->findOrFail($id);
        $jobs = Job::select('id', 'row_no', 'customer_id')->with('customer:id,name_en')->get();
        $parents = Account::where('type', '!=', 'Equity')->where('is_active', 1)->orderBy('name')->get();//2=>Bank & Cash sub accounts

        $subAccounts = Account::where('type', '!=', 'Equity')->where('is_grouped', 0)->orderBy('name')->get();

        return view('modules.finance.supplier-invoice.supplier-invoice-form', compact('supplier', 'jobs', 'parents', 'subAccounts', 'job_id'));
    }

    public function listBasedOnJob($job_id)
    {
        $job = Job::select('row_no')->find(decodeId($job_id));
        $job_no = $job->row_no;
        return view('modules.finance.supplier-invoice.list', compact('job_id', 'job_no'));
    }

    public function fetchAllRows(Request $request, $job_id): \Illuminate\Http\JsonResponse
    {
        if ($job_id != 'list') {
            $job_id = decodeId($job_id);
        }
        $filter = $request->filterData ?? [];

        // "all" is the sentinel value for the "All Payment" dropdown option,
        // meaning no filter should be applied — not a literal status to match.
        $paymentStatusFilter = array_filter((array) ($filter['filter-payment-status'] ?? []), fn($v) => $v !== 'all' && $v !== '');

        // Shared filters so the tab counts and summary cards always match
        // the visible list (company scoping is applied globally on the
        // SupplierInvoice model).
        $applyFilters = function ($query) use ($filter, $job_id, $paymentStatusFilter) {
            $query->when($job_id != 'list', function ($query) use ($job_id) {
                $query->where('job_id', $job_id);
            })
            ->when(isset($filter['filter-from-date'], $filter['filter-to-date']),
                function ($query) use ($filter) {
                    $from = Carbon::parse($filter['filter-from-date'])->startOfDay();
                    $to   = Carbon::parse($filter['filter-to-date'])->addDay()->startOfDay();

                    $query->where('invoice_date', '>=', $from)
                        ->where('invoice_date', '<',  $to);
                }
            )
            ->when(isset($filter['suppliers']) && !empty($filter['suppliers']), function ($query) use ($filter) {
                $query->whereIn('supplier_id', decodeIds($filter['suppliers']));
            })
            ->when(!empty($paymentStatusFilter), function ($query) use ($paymentStatusFilter) {
                $query->where(function ($group) use ($paymentStatusFilter) {
                    foreach ($paymentStatusFilter as $status) {
                        $group->orWhere(function ($sub) use ($status) {
                            if ($status === 'paid') {
                                $sub->where('grand_total', '>', 0)->whereColumn('paid_amount', '>=', 'grand_total');
                            } elseif ($status === 'partial') {
                                $sub->where('paid_amount', '>', 0)->whereColumn('paid_amount', '<', 'grand_total');
                            } elseif ($status === 'unpaid') {
                                $sub->where(function ($u) {
                                    $u->whereNull('paid_amount')->orWhere('paid_amount', '<=', 0);
                                });
                            }
                        });
                    }
                });
            })
            ->when(($filter['filter-overdue'] ?? 'all') === 'overdue', function ($query) {
                $query->where('due_at', '<', now())->whereColumn('paid_amount', '<', 'grand_total');
            })
            ->when(($filter['filter-overdue'] ?? 'all') === 'non_due', function ($query) {
                $query->where('due_at', '>=', now())->whereColumn('paid_amount', '<', 'grand_total');
            });
        };

        $rows = SupplierInvoice::select(
            'row_no',
            'supplier_invoices.id as id',
            'invoice_number',
            'due_at',
            'invoice_date',
            'job_id',
            'job_no',
            'supplier_id',
            'supplier_invoices.currency as currency',
            'currency_rate',
            'base_sub_total',
            'sub_total',
            'base_tax_total',
            'tax_total',
            'grand_total',
            'paid_amount',
            'supplier_invoices.status as status',
            'supplier_invoices.created_at as created_at',
            'supplier_invoices.company_id as company_id',
        )
            ->with(['supplier:id,name_en,name_ar,row_no'])
            ->with(['job:id,shipment_mode,activity_id'])
            ->when($request->tab, function ($q) use ($request) {
                $q->where('supplier_invoices.status', SupplierInvoiceEnum::fromName($request->tab));
            })
            ->tap($applyFilters)
            ->orderBy('supplier_invoices.id', 'desc');

        // Counts per status using the same filters as the list
        $statusCounts = SupplierInvoice::select('status', DB::raw('COUNT(*) as total'))
            ->tap($applyFilters)
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // ✅ Normalize counts for all statuses
        $allCounts = [];
        foreach (SupplierInvoiceEnum::cases() as $status) {
            $allCounts[$status->name] = $statusCounts[$status->value] ?? 0;
        }
        $allCounts['all'] = array_sum($allCounts);

        // Summary card totals (Draft/Approved), same shape as the customer
        // invoice list so the same card + JS pattern can be reused.
        // Use base_* (company-currency) columns, not the invoice's own
        // currency columns — mixing a USD invoice's grand_total with a SAR
        // invoice's grand_total would sum unlike currencies together.
        $salesSummary = SupplierInvoice::select([
            DB::raw('SUM(base_grand_total) as overall_sales'),
            DB::raw('SUM(CASE WHEN status = 1 THEN base_grand_total ELSE 0 END) as total_draft_grand'),
            DB::raw('SUM(CASE WHEN status = 1 THEN base_sub_total ELSE 0 END) as total_draft_sub'),
            DB::raw('SUM(CASE WHEN status = 1 THEN base_tax_total ELSE 0 END) as total_draft_tax'),
            DB::raw('SUM(CASE WHEN status = 3 THEN base_grand_total ELSE 0 END) as total_approved_grand'),
            DB::raw('SUM(CASE WHEN status = 3 THEN base_sub_total ELSE 0 END) as total_approved_sub'),
            DB::raw('SUM(CASE WHEN status = 3 THEN base_tax_total ELSE 0 END) as total_approved_tax'),
        ])
            ->tap($applyFilters)
            ->first();

        $decimals = decimals();
        $activity = LogisticActivity::activities();
        $baseCurrency = optional(authUserCompany())->base_currency ?: 'SAR';
        // ✅ Return formatted DataTable
        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->addColumn('job_activity', fn($model) => $model->job ? ($activity->where('id', $model->job->activity_id)->pluck('name')->first() ?? '-') : '-')
            // FCY Amount — only shown for invoices not already in the
            // company's own currency; SAR (or whatever the base is) rows
            // have nothing extra to show since Excl.VAT/Tax/Balance are
            // already in that currency.
            ->addColumn('fcy_amount', function ($model) use ($baseCurrency, $decimals) {
                if (!$model->currency || strtoupper($model->currency) === strtoupper($baseCurrency)) {
                    return '<span class="text-muted">—</span>';
                }

                return '<div class="cell-primary">' . strtoupper($model->currency) . ' ' . number_format($model->grand_total, $decimals) . '</div>'
                    . '<div class="cell-secondary">' . $baseCurrency . ' ' . number_format($model->currency_rate, 4) . '</div>';
            })
            ->setRowAttr([
                'data-id' => fn($model) => $model->id,
                'data-name' => fn($model) => 'Supplier #' . htmlspecialchars($model->invoice_no, ENT_QUOTES, 'UTF-8'),
                'class' => 'row-item',
                'id' => fn($model) => 'supplier-' . strtolower($model->invoice_no ?? $model->id),
            ])
            ->editColumn('invoice_date', fn($model) => Carbon::parse($model->invoice_date)->format('d-M-Y'))
            ->editColumn('currency', fn($model) => strtoupper($model->currency))
            ->editColumn('base_total', fn($model) => number_format($model->base_tax_total + $model->base_sub_total, $decimals))
            ->editColumn('grand_total', fn($model) => number_format($model->grand_total, $decimals))
            ->editColumn('due_at', fn($model) => Carbon::parse($model->due_at)->format('d-M-Y'))
            ->addColumn('due_days', function ($model) {
                $now = now()->startOfDay();
                $dueAt = Carbon::parse($model->due_at)->startOfDay();

                $diff = $now->diffInDays($dueAt, false);
                $class = 'bg-danger-subtle text-danger border border-danger';
                if ($diff < 0) {
                    $mess = 'OVERDUE: ' . abs($diff) . ' DAYS';
                } elseif ($diff == 0) {
                    $mess = 'DUE: TODAY';
                } elseif ($diff == 1) {
                    $mess = 'DUE: TOMORROW';
                } else {
                    $mess = 'OPEN: ' . $diff . ' DAYS LEFT';
                    $class = 'bg-primary-subtle text-primary border-primary';
                }
                return [
                    'label' => $mess,
                    'class' => $class
                ];
            })
            ->addColumn('balance', function($model) use ($decimals) {
                $balance = $model->grand_total - ($model->paid_amount ?? 0);
                return number_format($balance, $decimals);
            })
            /*->editColumn('created_at', fn($model) => \Carbon\Carbon::parse($model->created_at)->format('d-m-Y'))*/
            ->rawColumns(['fcy_amount'])
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
        $request->merge(['supplier' => decodeId($request->input('supplier'))]);

        $validated = $request->validate([
            'job_id' => 'required|exists:jobs,id',
            'supplier' => ['required', Rule::exists('suppliers', 'id')->where('company_id', companyId())],
            'invoice_date' => 'required|date',
            'invoice_number' => [
                'required',
                'min:3',
                Rule::unique('supplier_invoices', 'invoice_number')->where(function ($query) use ($request) {
                    return $query->where('supplier_id', $request->supplier);
                })->ignore($request['data-id']),
            ],
            //'posting_date' => 'required|date',
            'due_date' => 'required|date',
            'currency_rate' => 'required',
            'currency' => 'required|exists:currencies,code',
            'terms' => 'nullable|string|max:1000',

            'description_id.*' => 'required|string|max:255',
            'comment.*' => 'nullable|string|max:500',
            'quantity.*' => 'required|numeric|min:1',
            'unit_price.*' => 'required|min:0|regex:/^\d+(\.\d{1,2})?$/',
            'tax.*' => 'nullable|string',
            'unit_id.*' => 'required|numeric',
        ]);

        $this->assertPeriodOpen($validated['invoice_date'], 'invoice_date');

        //$userId = Auth::id();
        $companyId = companyId();

        // 🔹 Fetch or create new
        if ($request->filled('data-id')) {
            $supplier = SupplierInvoice::findOrFail($request->input('data-id'));
        } else {
            $supplier = new SupplierInvoice();

            $year = Carbon::parse($request->invoice_date)->format('Y');
            $lastRowNo = SupplierInvoice::whereYear('invoice_date', $year)->max('unique_row_no') ?? 0;

            $supplier->unique_row_no = $lastRowNo + 1;
            $supplier->row_no = 'SI' . date('y') . '-' . sprintf('%04d', $supplier->unique_row_no);

            $job = Job::select('row_no')->find($request->input('job_id'));
            if ($job) {
                $supplier->job_no = $job->row_no;
            }

            $this->setBaseColumns($supplier);
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
        $supplier->job_id = $validated['job_id'] ?? null;
        $supplier->supplier_id = $validated['supplier'] ?? null;
        $supplier->invoice_number = $validated['invoice_number'];
        //$supplier->posted_at = $validated['posting_date'];
        $supplier->invoice_date = $validated['invoice_date'];
        $supplier->due_at = $validated['due_date'];
        $supplier->currency = $validated['currency'];
        $supplier->currency_rate = $validated['currency_rate'];
        $supplier->terms = $validated['terms'] ?? null;
        $supplier->base_sub_total = $supplier->currency_rate * $subTotal;
        $supplier->base_tax_total = $supplier->currency_rate * $taxTotal;
        $supplier->sub_total = $subTotal;
        $supplier->tax_total = $taxTotal;
        $supplier->grand_total = $grandTotal;
        $supplier->base_grand_total = $supplier->currency_rate * $grandTotal;
        $supplier->status = 1;

        DB::beginTransaction();
        try {

        $supplier->save();
        if ($request->hasFile('attachments') && count($request->file('attachments'))) {
            $userId = Auth::id();

            foreach ($request->file('attachments') as $file) {
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME); // file name without extension
                $extension = $file->getClientOriginalExtension(); // file extension
                $uniqueName = $originalName . '_' . uniqid() . '.' . $extension; // append unique ID

                // Store file using unique name
                $path = $file->storeAs(
                    'documents/' . $companyId . '/supplier_invoice/' . $supplier->id,
                    $uniqueName,
                    'public'
                );

                // Save record in DB
                $supplier->documents()->create([
                    'document_type' => SupplierInvoice::class,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(), // keep original name for display
                    'title' => 'supplier_invoice',
                    'posted_date' => now(),
                    'user_id' => $userId,
                    'company_id' => $companyId,
                ]);
            }
        }


        // 🔹 Prepare items
        $supplierSub = [];
        $descriptions = Description::descriptions()->keyBy("id");
        foreach ($request->description_id as $i => $desc) {
            $qty = $request->quantity[$i] ?? 0;
            $price = $request->unit_price[$i] ?? 0;
            $taxRate = vatPercent($request->tax[$i] ?? 0);
            $lineTotal = $qty * $price;
            $lineTax = $lineTotal * ($taxRate / 100);
            $netAmount = $lineTotal + $lineTax;

            $supplierSub[] = [
                'supplier_invoice_id' => $supplier->id,
                'account_id' => $request->account[$i],
                'company_id' => $companyId,
                'description_id' => $desc,
                'description' => $descriptions[$desc]->description ?? '',
                'comment' => $request->comment[$i] ?? null,
                'unit_id' => $request->unit_id[$i],
                'quantity' => $qty,
                'unit_price' => $price,
                'base_unit_price' => $supplier->currency_rate * $price,
                'tax_code' => $request->tax[$i] ?? null,
                'tax_percent' => $taxRate,
                'tax_amount' => $lineTax,
                'base_tax_amount' => $supplier->currency_rate * $lineTax,
                'total' => $lineTotal,
                'base_total' => $supplier->currency_rate * $lineTotal,
                'total_with_tax' => $netAmount,
                'base_total_with_tax' => $supplier->currency_rate * $netAmount,
            ];
        }

        DB::table('supplier_invoice_subs')
            ->where('supplier_invoice_id', $supplier->id)
            ->delete();

        if (!empty($supplierSub)) {
            DB::table('supplier_invoice_subs')->insert($supplierSub);
        }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => __('Error saving Supplier Invoice: ') . $e->getMessage(),
            ], 500);
        }

        // Finance entries are created only on APPROVAL (in updateStatus), not on draft save

        return response()->json([
            'status' => 'success',
            'message' => __('Supplier invoice created successfully'),
            'customer_id' => $supplier->id,
        ]);
    }

    public function destroy($id)
    {
        $supplier = SupplierInvoice::findOrFail($id);
        $this->deleteSupplierFinanceByRef($supplier->invoice_number, 'SI');
        $supplier->delete();

        return response()->json(['status' => 'success', 'message' => __('Deleted successfully')]);
    }

    public function actions($id)
    {
        $supplier = SupplierInvoice::select(
            'id',
            'row_no',
            'status'
        )->findOrFail($id);
        $contextMenu = collect([]);
        $edit = $delete = [];
        if ($supplier->status === SupplierInvoiceEnum::DRAFT->value) {
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
                        'data-id' => $supplier->id,
                        'data-value' => SupplierInvoiceEnum::APPROVED->value,
                        'icon' => 'approved'
                    ],
                    [
                        'label' => __('Cancelled'),
                        'code' => '01CSRJ',
                        'id' => 'row_rejected',
                        'class' => 'row_rejected',
                        'data-id' => $supplier->id,
                        'data-value' => SupplierInvoiceEnum::CANCELLED->value,
                        'icon' => 'rejected'
                    ]
                ]
            ]);
        } elseif ($supplier->status === SupplierInvoiceEnum::fromName('approved')) {
            $contextMenu->push([
                'label' => __('Move to'),
                'type' => 'submenu',
                'separator' => 'after',
                'icon' => 'move_to',
                'items' => [
                    [
                        'label' => __('Convert To Invoice'),
                        'code' => '01CSEM',
                        'id' => 'row_converted',
                        'data-id' => $supplier->id,
                        'data-value' => SupplierInvoiceEnum::CONVERTED->value,
                        'icon' => 'converted',
                        'separator' => 'after',
                    ],
                    [
                        'label' => __('Move To Draft'),
                        'code' => '01CSBK',
                        'id' => 'row_pending',
                        'data-id' => $supplier->id,
                        'data-value' => SupplierInvoiceEnum::DRAFT->value,
                        'icon' => 'pending'
                    ]
                ]
            ]);
        }

        if ($supplier->status === SupplierInvoiceEnum::DRAFT->value) {
            $edit = [
                'label' => __('Edit'),
                'code' => '01CSED',
                'id' => 'row_edit',
                'class' => 'row_edit',
                'data-id' => $supplier->id,
                'type' => 'item',
                'icon' => 'edit'
            ];
            $delete = [
                'label' => __('Delete'),
                'code' => '01CSED',
                'id' => 'row_delete',
                'class' => 'row_delete',
                'data-id' => $supplier->id,
                'type' => 'item',
                'icon' => 'delete'
            ];
        }

        $contextMenu->push([
            'label' => __('Send Email'),
            'code' => '01CSEM',
            'id' => 'row_email',
            'data-id' => $supplier->id,
            'type' => 'item',
            'icon' => 'email',
            'separator' => 'after',
        ]);

        $contextMenu->push([
            'label' => __('Print'),
            'code' => '01CSVW',
            'id' => 'row_print',
            'class' => 'row_print',
            'data-id' => $supplier->id,
            'type' => 'item',
            'icon' => 'print',
            'onclick' => 'SUPPLIER_INVOICE.printPreview(' . $supplier->id . ')',
            //'separator' => 'before',
        ]);
        $contextMenu->push([
            'label' => __('View'),
            'code' => '01CSVW',
            'id' => 'row_view',
            'class' => 'row_view',
            'data-id' => $supplier->id,
            'type' => 'item',
            'icon' => 'view',
            //'separator' => 'before',
        ]);
        if ($supplier->status === SupplierInvoiceEnum::DRAFT->value) {
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
        $supplier = SupplierInvoice::findOrFail($id);
        $previousStatus = $supplier->status;

        DB::beginTransaction();
        try {
            $supplier->status = $status;
            $supplier->save();

            // Create finance entries when invoice is approved
            if ($status == SupplierInvoiceEnum::APPROVED->value) {
                // Get the invoice sub items for finance entries
                $supplierSubs = $supplier->supplierInvoiceSubs->map(function ($sub) {
                    return [
                        'account_id' => $sub->account_id,
                        'description' => $sub->description,
                        'total' => $sub->total,
                    ];
                })->toArray();

                $this->storeSupplierInvoiceFinance($supplier, $supplierSubs);
            }

            // Delete finance entries when invoice is moved from APPROVED to another status
            if ($previousStatus == SupplierInvoiceEnum::APPROVED->value && $status != SupplierInvoiceEnum::APPROVED->value) {
                $this->deleteSupplierFinanceByRef($supplier->invoice_number, 'SI');
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => __('Supplier invoice status updated successfully!'),
                'data' => [
                    'id' => $supplier->id,
                    'status' => $supplier->status,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => __('Error updating supplier invoice status: ') . $e->getMessage(),
            ], 500);
        }
    }

    public function overview($id)
    {
        $supplierInvoice = SupplierInvoice::with('supplierInvoiceSubs', 'supplier')->findOrFail($id);
        $descriptions = Description::descriptions()->pluck('description', 'id')->toArray();
        return view('modules.finance.supplier-invoice.view-overview', compact('supplierInvoice', 'descriptions'));
    }

    public function overviewDrawer($id)
    {
        $supplierInvoice = SupplierInvoice::with('supplierInvoiceSubs', 'supplier')->findOrFail($id);
        $descriptions = Description::descriptions()->pluck('description', 'id')->toArray();
        [$origin, $timeline] = $this->invoiceTimeline($supplierInvoice);
        return view('modules.finance.supplier-invoice.view-overview-drawer', compact('supplierInvoice', 'descriptions', 'origin', 'timeline'));
    }

    /**
     * Time frame for the supplier invoice drawer: where the job came from
     * (enquiry -> quotation -> job), the invoice's own life, the customer
     * invoice(s) raised on the same job (re-billing) and payments to the
     * supplier, oldest first.
     */
    private function invoiceTimeline(SupplierInvoice $inv): array
    {
        $events = [];
        $rank = 0; // origin chain always first, in order
        $add = function ($label, $at, $icon, $module, $by = null, $meta = null) use (&$events, &$rank) {
            if (!$at) return;
            $ts = Carbon::parse($at);
            // Date-only values carry no time: treat as end of day so they follow same-day invoice events.
            $key = $ts->format('H:i:s') === '00:00:00' ? $ts->copy()->endOfDay()->timestamp : $ts->timestamp;
            $events[] = ['label' => $label, 'at' => $at, 'icon' => $icon, 'module' => $module, 'by' => $by, 'meta' => $meta,
                'rank' => $rank, 'key' => $key, 'seq' => count($events)];
        };

        $job = $inv->job_id ? \App\Models\Job\Job::find($inv->job_id) : null;
        $quotation = $job && $job->quotation_id ? \App\Models\Quotation\Quotation::find($job->quotation_id) : null;
        $enquiry = $quotation && $quotation->enquiry_id ? \App\Models\Enquiry\Enquiry::find($quotation->enquiry_id) : null;

        if ($enquiry) $add(__('Enquiry created') . ' · ' . $enquiry->row_no, $enquiry->created_at, 'bi-chat-left-text', 'enquiry');
        if ($quotation) $add(__('Quotation posted') . ' · ' . $quotation->row_no, $quotation->created_at, 'bi-file-earmark-text', 'quotation');
        if ($job) $add(($quotation ? __('Converted to job') : __('Job created')) . ' · ' . $job->row_no, $job->created_at, 'bi-briefcase', 'job');

        $rank = 1;
        $origin = $quotation
            ? ($enquiry ? __('Enquiry') . ' → ' : '') . __('Quotation') . ' → ' . __('Job') . ' → ' . __('Supplier Invoice')
            : ($job ? __('Job') . ' → ' . __('Supplier Invoice') : __('Supplier invoice created directly'));

        $logs = \App\Models\Log\LogHistory::where('loggable_type', SupplierInvoice::class)
            ->where('loggable_id', $inv->id)->where('created_at', '>=', $inv->created_at->copy()->subSeconds(5))->orderBy('id')->get();
        $ignore = ['status', 'paid_amount', 'base_paid_amount', 'updated_at', 'posted_at', 'tax_submit_status', 'tax_submitted_at'];
        $seenCreate = false;
        foreach ($logs as $log) {
            $by = $log->user_id['name'] ?? null;
            if ($log->action === 'created') {
                $seenCreate = true;
                $add(__('Supplier invoice created') . ' · ' . $inv->row_no, $log->created_at, 'bi-receipt', 'invoice', $by);
            } elseif ($log->action === 'updated') {
                $new = $log->changes['new'] ?? [];
                if (isset($new['status'])) {
                    $st = (int) $new['status'];
                    $label = SupplierInvoiceEnum::tryFrom($st)?->label() ?? __('Status changed');
                    $icon = $st === SupplierInvoiceEnum::APPROVED->value ? 'bi-check-circle'
                        : ($st === SupplierInvoiceEnum::CANCELLED->value ? 'bi-x-circle' : 'bi-clock');
                    $add(__($label), $log->created_at, $icon, 'invoice', $by);
                } elseif (array_diff(array_keys($new), $ignore)) {
                    $add(__('Invoice updated'), $log->created_at, 'bi-pencil-square', 'invoice', $by);
                }
            }
        }
        if (!$seenCreate) $add(__('Supplier invoice created') . ' · ' . $inv->row_no, $inv->created_at, 'bi-receipt', 'invoice');

        $dec = decimals();
        // Customer invoice(s) raised on the same job = this cost re-billed to the customer.
        if ($inv->job_id) {
            foreach (\App\Models\Finance\CustomerInvoice\CustomerInvoice::where('job_id', $inv->job_id)->orderBy('id')->get() as $ci) {
                $add(__('Customer invoice') . ' · ' . $ci->row_no, $ci->created_at, 'bi-receipt-cutoff', 'customer_invoice', null,
                    number_format((float) $ci->grand_total, $dec) . ' ' . (\App\Enums\CustomerInvoiceEnum::tryFrom((int) $ci->status)?->label() ?? ''));
            }
        }

        $grand = (float) $inv->grand_total;
        $cumulative = 0.0;
        $pis = \App\Models\Finance\Payment\PaymentInvoice::where('supplier_invoice_id', $inv->id)
            ->with('payment:id,row_no,payment_date,status,created_at')->orderBy('id')->get();
        foreach ($pis as $pi) {
            if (($pi->payment->status ?? 0) == 3) continue; // cancelled
            $cumulative += (float) $pi->amount;
            $kind = $grand > 0 && $cumulative >= $grand - 0.005 ? __('Full payment') : __('Partial payment');
            $add($kind . ' · ' . ($pi->payment->row_no ?? ''), $pi->payment->created_at ?? $pi->created_at, 'bi-cash-coin', 'payment', null, number_format((float) $pi->amount, $dec));
        }

        usort($events, fn($a, $b) => [$a['rank'], $a['rank'] ? $a['key'] : $a['seq'], $a['seq']] <=> [$b['rank'], $b['rank'] ? $b['key'] : $b['seq'], $b['seq']]);

        return [$origin, $events];
    }

    public function print($id)
    {

        $descriptions = Description::descriptions()->pluck('description', 'id')->toArray();

        $supplierInvoice = $this->allPrint($id);
        /*$printData = [
            'invoice' => $invoiceData,
        ];*/

        //$html = view('print.' . $template, $printData)->render();
        /*$html = view('print.table_1', $printData)->with(['file' => 'modern_grid_invoice'])->render();

        $customerName = preg_replace('/[^a-zA-Z0-9]/', '', $invoiceData->customer->name);
        $date = date('Y-m-d');
        $fileName = "Invoice_{$customerName}_{$invoiceData->row_no}_{$date}.pdf";

        return createPDF($html, $fileName, !$invoiceData->approved_json);*/
        /*$pdf = PDF::loadView(
            'modules.finance.proforma-invoice.view-overview',
            compact('proforma', 'descriptions'),
            [],
            ['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true, 'tempDir' => storage_path('app/tmp')]
        );*/
        /*$pdf = PDF::loadView('modules.finance.proforma-invoice.view-overview', compact('proforma', 'descriptions'))
            ->setPaper('A4', 'portrait');*/
        //return $pdf->download("SupplierInvoice-{$supplier->row_no}.pdf");
        //$fileName = "SupplierInvoice-{$supplier->row_no}.pdf";

        // 👇 This sends PDF inline (not download)
        //return $pdf->stream($fileName);

        return view('modules.finance.supplier-invoice.view-overview', compact('supplierInvoice', 'descriptions'));
    }

    public function allPrint($id)
    {
        return SupplierInvoice::with('supplierInvoiceSubs', 'supplier')->findOrFail($id);
    }

    /** Delete is allowed only when nothing else points at this record (see DeletionGuard). */
    public function delete($id)
    {
        $model = SupplierInvoice::findOrFail($id);
        $guard = app(\App\Services\DeletionGuard::class);
        $why = $guard->blockers('supplier_invoice', (int) $id);
        if ($why) {
            return $guard->refusal(__('invoice'), $why);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($model, $id) {
            \Illuminate\Support\Facades\DB::table('supplier_invoice_subs')->where('supplier_invoice_id', $id)->delete();
            $model->delete();
        });

        return response()->json(['status' => 'success', 'message' => __('Invoice deleted successfully')]);
    }
}

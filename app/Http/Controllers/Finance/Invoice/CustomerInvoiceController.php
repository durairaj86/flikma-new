<?php

namespace App\Http\Controllers\Finance\Invoice;

use App\Enums\CustomerInvoiceEnum;
use App\Enums\JobEnum;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Zatca\ZatcaController;
use App\Models\Finance\Account\Account;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Finance\CustomerInvoice\CustomerInvoiceSub;
use App\Models\Finance\Finance;
use App\Models\Finance\FinanceSub;
use App\Models\Job\Job;
use App\Models\Master\Bank;
use App\Models\Master\Description;
use App\Models\Master\LogisticActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Mockery\Exception;
use Yajra\DataTables\Facades\DataTables;

class CustomerInvoiceController extends Controller
{

    public function modal(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $customer = new CustomerInvoice();
        $customer->customerInvoiceSubs = [new CustomerInvoiceSub()];
        $job_customer_id = $job_id = null;
        if ($request->get('jobId') == 'list') {
            $jobs = Job::select('id', 'row_no', 'customer_id')->with('customer:id,name_en')->whereIn('status', [JobEnum::COMPLETED, JobEnum::PENDING])->get();
        } else {
            $jobs = Job::select('id', 'row_no', 'customer_id')->with('customer:id,name_en')->findOrFail(decodeId($request->get('jobId')));
            $job_customer_id = $jobs->customer_id;
            $job_id = $jobs->id;
            $jobs = [$jobs];
        }

        //$accounts = Account::where('type', '!=', 'Equity')->where('is_active', 1)->get();
        $parents = Account::where('type', '!=', 'Equity')->where('is_active', 1)->orderBy('name')->get();//2=>Bank & Cash sub accounts

        $subAccounts = Account::where('type', '!=', 'Equity')->where('is_grouped', 0)->orderBy('name')->get();
        return view('modules.finance.customer-invoice.customer-invoice-form', compact('customer', 'jobs', 'parents', 'subAccounts', 'job_customer_id', 'job_id'));
    }

    public function edit($id)
    {
        $customer = CustomerInvoice::with(['customerInvoiceSubs', 'documents'])->findOrFail($id);
        $jobs = Job::select('id', 'row_no', 'customer_id')->with('customer:id,name_en')->get();

        $parents = Account::where('type', '!=', 'Equity')->where('is_active', 1)->orderBy('name')->get();//2=>Bank & Cash sub accounts
        $subAccounts = Account::where('type', '!=', 'Equity')->where('is_grouped', 0)->orderBy('name')->get();

        $job_customer_id = $customer->customer_id;
        $job_id = $customer->job_id;

        return view('modules.finance.customer-invoice.customer-invoice-form', compact('customer', 'jobs', 'parents', 'subAccounts', 'job_customer_id', 'job_id'));
    }

    public function listBasedOnJob($job_id)
    {
        $job = Job::select('row_no')->find(decodeId($job_id));
        $job_no = $job->row_no;
        return view('modules.finance.customer-invoice.list', compact('job_id', 'job_no'));
    }

    public function fetchAllRows(Request $request, $job_id): \Illuminate\Http\JsonResponse
    {
        if ($job_id != 'list') {
            $job_id = decodeId($job_id);
        }
        $filter = $request->filterData ?? [];
        $rows = CustomerInvoice::select(
            'row_no',
            'customer_invoices.id as id',
            'due_at',
            'invoice_date',
            'job_id',
            'job_no',
            'customer_id',
            'tax_submit_status',
            'customer_invoices.currency as currency',
            'currency_rate',
            'base_sub_total',
            'sub_total',
            'base_tax_total',
            'tax_total',
            'grand_total',
            'paid_amount',
            'customer_invoices.status as status',
            'customer_invoices.created_at as created_at',
            'customer_invoices.company_id as company_id',
        )
            ->with(['customer:id,name_en,name_ar,row_no'])
            ->with(['job:id,shipment_mode,pol,pod,activity_id,carrier,shipment_mode'])// eager load customer
            /*->when($request->tab, function ($q) use ($request) {
                $q->where('customer_invoices.status', CustomerInvoiceEnum::fromName($request->tab));
            })*/
            ->when($job_id != 'list', function ($query) use ($job_id) {
                $query->where('job_id', $job_id);
            })
            ->when(isset($filter['filter-from-date'], $filter['filter-to-date']),
                function ($query) use ($filter) {

                    $from = Carbon::parse($filter['filter-from-date'])->startOfDay();
                    $to = Carbon::parse($filter['filter-to-date'])->addDay()->startOfDay();

                    $query->where('invoice_date', '>=', $from)
                        ->where('invoice_date', '<', $to);
                }
            )
            ->when(isset($filter['customSearch']) && !empty($filter['customSearch']), function ($query) use ($filter) {
                $search = $filter['customSearch'];
                $query->where(function ($q) use ($search) {
                    $q->where('row_no', 'like', "%{$search}%")
                        ->orWhere('job_no', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($q) use ($search) {
                            $q->where('name_en', 'like', "%{$search}%")
                                ->orWhere('name_ar', 'like', "%{$search}%");
                        })
                        ->orWhereHas('job', function ($q) use ($search) {
                            $q->where('pol', 'like', "%{$search}%")
                                ->orWhere('pod', 'like', "%{$search}%");
                        });
                });
            })
            ->when(isset($filter['customers']) && !empty($filter['customers']), function ($query) use ($filter) {
                $query->whereIn('customer_id', decodeIds($filter['customers']));
            })
            ->orderBy('customer_invoices.id', 'desc');

        // ✅ Get counts per status
        $statusCounts = CustomerInvoice::select('status', DB::raw('COUNT(*) as total'))
            ->when($job_id != 'list', function ($query) use ($job_id) {
                $query->where('job_id', $job_id);
            })
            ->when(isset($filter['filter-from-date'], $filter['filter-to-date']),
                function ($query) use ($filter) {

                    $from = Carbon::parse($filter['filter-from-date'])->startOfDay();
                    $to = Carbon::parse($filter['filter-to-date'])->addDay()->startOfDay();

                    $query->where('invoice_date', '>=', $from)
                        ->where('invoice_date', '<', $to);
                }
            )
            ->when(isset($filter['customers']) && !empty($filter['customers']), function ($query) use ($filter) {
                $query->whereIn('customer_id', decodeIds($filter['customers']));
            })
            ->when(isset($request->filterData['customSearch']) && !empty($request->filterData['customSearch']), function ($query) use ($request) {
                $search = $request->filterData['customSearch'];
                $query->where(function ($q) use ($search) {
                    $q->where('row_no', 'like', "%{$search}%")
                        ->orWhere('job_no', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($q) use ($search) {
                            $q->where('name_en', 'like', "%{$search}%")
                                ->orWhere('name_ar', 'like', "%{$search}%");
                        })
                        ->orWhereHas('job', function ($q) use ($search) {
                            $q->where('pol', 'like', "%{$search}%")
                                ->orWhere('pod', 'like', "%{$search}%");
                        });
                });
            })
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $salesSummary = CustomerInvoice::select([
            // Overall Totals (Approved + Draft)
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
                    $to = Carbon::parse($filter['filter-to-date'])->addDay()->startOfDay();

                    $query->where('invoice_date', '>=', $from)
                        ->where('invoice_date', '<', $to);
                }
            )
            ->when(isset($filter['customers']) && !empty($filter['customers']), function ($query) use ($filter) {
                $query->whereIn('customer_id', decodeIds($filter['customers']));
            })
            ->when(isset($request->filterData['customSearch']) && !empty($request->filterData['customSearch']), function ($query) use ($request) {
                $search = $request->filterData['customSearch'];
                $query->where(function ($q) use ($search) {
                    $q->where('row_no', 'like', "%{$search}%")
                        ->orWhere('job_no', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($q) use ($search) {
                            $q->where('name_en', 'like', "%{$search}%")
                                ->orWhere('name_ar', 'like', "%{$search}%");
                        })
                        ->orWhereHas('job', function ($q) use ($search) {
                            $q->where('pol', 'like', "%{$search}%")
                                ->orWhere('pod', 'like', "%{$search}%");
                        });
                });
            })
            ->first();

        // ✅ Normalize counts for all statuses
        $allCounts = [];
        foreach (CustomerInvoiceEnum::cases() as $status) {
            $allCounts[$status->name] = $statusCounts[$status->value] ?? 0;
        }
        $allCounts['all'] = array_sum($allCounts);
        $decimals = decimals();
        $activity = LogisticActivity::activities();
        // ✅ Return formatted DataTable
        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($model) => $model->id,
                'data-name' => fn($model) => 'Proforma #' . htmlspecialchars($model->invoice_no, ENT_QUOTES, 'UTF-8'),
                'class' => 'row-item',
                'id' => fn($model) => 'proforma-' . strtolower($model->invoice_no ?? $model->id),
            ])
            ->editColumn('invoice_date', fn($model) => \Carbon\Carbon::parse($model->invoice_date)->format('d-m-Y'))
            ->editColumn('currency', fn($model) => strtoupper($model->currency))
            ->addColumn('customer_name', fn($model) => $model->customer?->name_en ? truncateName($model->customer?->name_en, 15) : '-')
            ->addColumn('due_status', fn($model) => 'unpaid')
            ->addColumn('job_activity', fn($model) => $activity->where('id', $model->job->activity_id)->pluck('name')->first())
            ->editColumn('grand_total', fn($model) => number_format($model->grand_total, $decimals))
            ->editColumn('invoice_date', fn($model) => Carbon::parse($model->invoice_date)->format('d-M-Y'))
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
            ->addColumn('balance', function ($model) use ($decimals) {
                $balance = $model->grand_total - ($model->paid_amount ?? 0);
                return number_format($balance, $decimals);
            })
            /*->editColumn('created_at', fn($model) => \Carbon\Carbon::parse($model->created_at)->format('d-m-Y'))*/
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
            'job_id' => 'required|exists:jobs,id',
            'customer' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date',
            'currency_rate' => 'required',
            'currency' => 'required|exists:currencies,code',
            'terms' => 'nullable|string|max:1000',

            'description_id.*' => 'required|string|max:255',
            'comment.*' => 'nullable|string|max:500',
            'unit_id.*' => 'required|numeric',
            'quantity.*' => 'required|numeric|min:1',
            'unit_price.*' => 'required|min:0|regex:/^\d+(\.\d{1,2})?$/',
            'tax.*' => 'nullable|string',
        ]);


        //$userId = Auth::id();
        $companyId = companyId();

        // 🔹 Fetch or create new
        if ($request->filled('data-id')) {
            $customer = CustomerInvoice::findOrFail($request->input('data-id'));
        } else {
            $customer = new CustomerInvoice();

            //$customer->row_no = 'DR' . date('ydis') . rand(100, 999);

            $job = Job::select('row_no')->find($request->input('job_id'));
            if ($job) {
                $customer->job_no = $job->row_no;
            }

            $this->setBaseColumns($customer);
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
        $customer->job_id = $validated['job_id'] ?? null;
        $customer->row_no = $request['row_no'] ?? null;
        $customer->customer_id = $validated['customer'] ?? null;
        $customer->invoice_date = $validated['invoice_date'];
        $customer->due_at = $validated['due_date'];
        $customer->currency = $validated['currency'];
        $customer->currency_rate = $validated['currency_rate'];
        $customer->terms = $validated['terms'] ?? null;
        $customer->base_sub_total = $customer->currency_rate * $subTotal;
        $customer->base_tax_total = $customer->currency_rate * $taxTotal;
        $customer->sub_total = $subTotal;
        $customer->tax_total = $taxTotal;
        $customer->base_grand_total = $customer->base_sub_total + $customer->base_tax_total;
        $customer->grand_total = $grandTotal;
        $customer->status = 1;

        /*DB::beginTransaction();
        try {*/

        $customer->save();
        if ($request->hasFile('attachments') && count($request->file('attachments'))) {
            $userId = Auth::id();

            foreach ($request->file('attachments') as $file) {
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME); // file name without extension
                $extension = $file->getClientOriginalExtension(); // file extension
                $uniqueName = $originalName . '_' . uniqid() . '.' . $extension; // append unique ID

                // Store file using unique name
                $path = $file->storeAs(
                    'documents/' . $companyId . '/customer_invoice/' . $customer->id,
                    $uniqueName,
                    'public'
                );

                // Save record in DB
                $customer->documents()->create([
                    'document_type' => CustomerInvoice::class,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(), // keep original name for display
                    'title' => 'customer_invoice',
                    'posted_date' => now(),
                    'user_id' => $userId,
                    'company_id' => $companyId,
                ]);
            }
        }


        // 🔹 Prepare items
        $customerSub = [];
        $descriptions = Description::descriptions()->keyBy('id');
        foreach ($request->description_id as $i => $desc) {
            $qty = $request->quantity[$i] ?? 0;
            $price = $request->unit_price[$i] ?? 0;
            $taxRate = vatPercent($request->tax[$i] ?? 0);
            $lineTotal = $qty * $price;
            $lineTax = $lineTotal * ($taxRate / 100);
            $netAmount = $lineTotal + $lineTax;

            $customerSub[] = [
                'customer_invoice_id' => $customer->id,
                'account_id' => $request->account[$i],
                'company_id' => $companyId,
                'description_id' => $desc,
                'description' => $descriptions[$desc]->description,
                'comment' => $request->comment[$i] ?? null,
                'unit_id' => $request->unit_id[$i],
                'quantity' => $qty,
                'unit_price' => $price,
                'base_unit_price' => $price * $customer->currency_rate,
                'tax_code' => $request->tax[$i] ?? null,
                'tax_percent' => $taxRate,
                'tax_amount' => $lineTax,
                'base_tax_amount' => $lineTax * $customer->currency_rate,
                'total' => $lineTotal,
                'base_total' => $lineTotal * $customer->currency_rate,
                'total_with_tax' => $netAmount,
                'base_total_with_tax' => $netAmount * $customer->currency_rate,
            ];
        }

        DB::table('customer_invoice_subs')
            ->where('customer_invoice_id', $customer->id)
            ->delete();

        if (!empty($customerSub)) {
            DB::table('customer_invoice_subs')->insert($customerSub);
        }

        DB::commit();

        // Finance entry
        //$this->storeCustomerInvoiceFinance($customer, $customerSub);  // For invoice

        //$this->storecustomerAdvanceFinance($advance);                 // For advance

        //$this->storecustomerAdvanceAdjustmentFinance($adjustment);    // For adjustment


        return response()->json([
            'status' => 'success',
            'message' => 'customer invoice created successfully',
            'customer_id' => $customer->id,
        ]);

        /*} catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error saving customer Invoice: ' . $e->getMessage());
        }*/
    }

    public function destroy($id)
    {
        $customer = CustomerInvoice::findOrFail($id);
        $this->deleteCustomerFinanceByRef($customer->invoice_number, 'SI');
        $customer->delete();

        return response()->json(['status' => 'success', 'message' => 'Deleted successfully']);
    }

    public function actions($id)
    {
        $customer = CustomerInvoice::select(
            'id',
            'row_no',
            'status'
        )->findOrFail($id);
        $contextMenu = collect([]);
        $edit = $delete = [];
        if ($customer->status === CustomerInvoiceEnum::DRAFT->value) {
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
                        'data-id' => $customer->id,
                        'data-value' => CustomerInvoiceEnum::APPROVED->value,
                        'icon' => 'confirmed'
                    ],
                    [
                        'label' => __('Cancelled'),
                        'code' => '01CSRJ',
                        'id' => 'row_rejected',
                        'class' => 'row_rejected',
                        'data-id' => $customer->id,
                        'data-value' => CustomerInvoiceEnum::CANCELLED->value,
                        'icon' => 'rejected'
                    ]
                ]
            ]);
        } elseif ($customer->status === CustomerInvoiceEnum::fromName('approved')) {
            /*$contextMenu->push([
                'label' => __('Move to'),
                'type' => 'submenu',
                'separator' => 'after',
                'icon' => 'move_to',
                'items' => [
                    [
                        'label' => __('Convert To Invoice'),
                        'code' => '01CSEM',
                        'id' => 'row_converted',
                        'data-id' => $customer->id,
                        'data-value' => CustomerInvoiceEnum::CONVERTED->value,
                        'icon' => 'converted',
                        'separator' => 'after',
                    ],
                    [
                        'label' => __('Move To Draft'),
                        'code' => '01CSBK',
                        'id' => 'row_pending',
                        'data-id' => $customer->id,
                        'data-value' => CustomerInvoiceEnum::DRAFT->value,
                        'icon' => 'pending'
                    ]
                ]
            ]);*/
        }

        if ($customer->status === CustomerInvoiceEnum::DRAFT->value) {
            $edit = [
                'label' => __('Edit'),
                'code' => '01CSED',
                'id' => 'row_edit',
                'class' => 'row_edit',
                'data-id' => $customer->id,
                'type' => 'item',
                'icon' => 'edit'
            ];
            $delete = [
                'label' => __('Delete'),
                'code' => '01CSED',
                'id' => 'row_delete',
                'class' => 'row_delete',
                'data-id' => $customer->id,
                'type' => 'item',
                'icon' => 'delete'
            ];
        }

        $contextMenu->push([
            'label' => __('Print'),
            'code' => '01CSVW',
            'id' => 'row_print',
            'class' => 'row_print',
            'data-id' => $customer->id,
            'type' => 'item',
            'icon' => 'print',
            'onclick' => 'CUSTOMER_INVOICE.printPreview(' . $customer->id . ')',
            //'separator' => 'before',
        ]);
        $contextMenu->push([
            'label' => __('View'),
            'code' => '01CSVW',
            'id' => 'row_view',
            'class' => 'row_view',
            'data-id' => $customer->id,
            'type' => 'item',
            'icon' => 'view',
            'separator' => 'after',
        ]);
        $contextMenu->push([
            'label' => __('Send Email'),
            'code' => '01CSEM',
            'id' => 'row_email',
            'data-id' => $customer->id,
            'type' => 'item',
            'icon' => 'email',
            //'separator' => 'after',
        ]);
        if ($customer->status === CustomerInvoiceEnum::DRAFT->value) {
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
        $customerInvoice = CustomerInvoice::findOrFail($id);
        $previousStatus = $customerInvoice->status;
        $message = 'Invoice status updated successfully!';

        DB::beginTransaction();
        try {
            if ($status == CustomerInvoiceEnum::APPROVED->value) {

                // checking zatca registered or not

                $zatca = zatcaDateCheck($customerInvoice);

                if ($zatca['zatcaRegister'] && $zatca['message']) {
                    return errorResponse($zatca['message']);
                }

                $postingDate = $customerInvoice->posting_date;
                if ($zatca['zatcaRegister']) {
                    $postingDate = date('Y-m-d');
                    $customerInvoice->old_invoice_date = formDate($customerInvoice->invoice_date);
                    $customerInvoice->invoice_date = $postingDate;
                }

                $customerInvoice->status = CustomerInvoiceEnum::APPROVED;

                $year = Carbon::today()->format('Y');
                $lastRowNo = CustomerInvoice::whereYear('invoice_date', $year)->max('unique_row_no') ?? 0;
                $customerInvoice->unique_row_no = $lastRowNo + 1;
                $customerInvoice->draft_no = $customerInvoice->row_no;
                //$customerInvoice->row_no = 'IN/' . date('y') . '/' . sprintf('%04d', $customerInvoice->unique_row_no);//Temporary
                $customerInvoice->save();

                // Create finance entries when invoice is approved
                $this->createCustomerInvoiceFinanceEntries($customerInvoice);

                $data = [];
                if ($zatca['zatcaRegister']) {
                    $customerInvoice->load(['customer']);
                    $zatca = new ZatcaController();
                    $z = $zatca->submitTax($customerInvoice);
                    $approveMessage = __("Zatca Approved", ['module' => '', 'status' => $z['title']]);

                    if ($z['type'] == 'error') {
                        return errorResponse([$z['message'], 'ZATCA Status - ' . $z['title']]);
                    } elseif ($z['type'] == 'warning') {
                        $data['type'] = 'warning';
                        $message = [
                            $approveMessage . ' with warning:' . $z['message'],
                            __($customerInvoice->title) . ' ' . $customerInvoice->row_no
                        ];
                    } else {
                        $message = [
                            $approveMessage,
                            __($customerInvoice->title) . ' ' . $customerInvoice->row_no
                        ];
                    }
                }
            } else {
                $customerInvoice->status = $status;
                $customerInvoice->save();

                // Delete finance entries when invoice is moved from APPROVED to another status
                if ($previousStatus == CustomerInvoiceEnum::APPROVED->value) {
                    $this->deleteCustomerInvoiceFinanceEntries($customerInvoice->id);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => [
                    'id' => $customerInvoice->id,
                    'status' => $customerInvoice->status,
                ],
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating customer invoice status: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function overview($id)
    {
        $customerInvoice = CustomerInvoice::with('customerInvoiceSubs', 'customer', 'job')->findOrFail($id);
        $descriptions = Description::descriptions()->pluck('description', 'id')->toArray();
        return view('modules.finance.customer-invoice.view-overview', compact('customerInvoice', 'descriptions'));
    }

    public function print($id)
    {

        //$descriptions = descriptions()->pluck('description', 'id')->toArray();

        $customerInvoice = $this->allPrint($id);

        /*$printData = [
            'invoice' => $invoiceData,
        ];*/

        //$html = view('print.' . $template, $printData)->render();
        /*$html = view('print.table_1', $printData)->with(['file' => 'modern_grid_invoice'])->render();

        $customerName = preg_replace('/[^a-zA-Z0-9]/', '', $invoiceData->customer->name);
        $date = date('Y-m-d');
        $fileName = "Invoice_{$customerName}_{$invoiceData->row_no}_{$date}.pdf";

        return createPDF($html, $fileName, !$invoiceData->approved_json);*/
        $descriptions = Description::descriptions()->pluck('description', 'id')->toArray();
        /*$pdf = PDF::loadView(
            'modules.finance.proforma-invoice.view-overview',
            compact('proforma', 'descriptions'),
            [],
            ['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true, 'tempDir' => storage_path('app/tmp')]
        );*/
        /*$pdf = PDF::loadView('modules.finance.proforma-invoice.view-overview', compact('proforma', 'descriptions'))
            ->setPaper('A4', 'portrait');*/
        //return $pdf->download("CustomerInvoice-{$customer->row_no}.pdf");
        //$fileName = "CustomerInvoice-{$customer->row_no}.pdf";

        // 👇 This sends PDF inline (not download)
        //return $pdf->stream($fileName);
        $bank = Bank::orderBy('sort')->first();
        $company = authUserCompany();
        $jobContainers = $customerInvoice->jobContainers->select('container_size', 'qty', 'container_number')/*->groupBy('container_size')*/ ?? [];
        $jobPackages = $customerInvoice->jobPackages/*->groupBy('container_size')*/ ?? [];

        //return view('modules.finance.customer-invoice.print', compact('customerInvoice', 'descriptions', 'bank', 'company', 'jobContainers', 'jobPackages'));
        return view('modules.print.format-2.print', compact('customerInvoice', 'descriptions', 'bank', 'company', 'jobContainers', 'jobPackages'));
    }

    public function allPrint($id)
    {
        return CustomerInvoice::with('customerInvoiceSubs', 'customer', 'job', 'jobContainers', 'jobPackages')->findOrFail($id);
    }

    /**
     * Create finance entries for a customer invoice.
     *
     * @param CustomerInvoice $customerInvoice
     * @return void
     */
    private function createCustomerInvoiceFinanceEntries(CustomerInvoice $customerInvoice)
    {
        DB::beginTransaction();

        try {
            $this->deleteCustomerInvoiceFinanceEntries($customerInvoice->id);

            $finance = new Finance();
            $finance->voucher_no = $customerInvoice->row_no;
            $finance->voucher_type = 'CI';
            $finance->reference_no = $customerInvoice->invoice_number ?? $customerInvoice->row_no;
            $finance->reference_date = formDate($customerInvoice->invoice_date);
            $finance->customer_id = $customerInvoice->customer_id;
            $finance->narration = 'Customer Invoice: ' . $customerInvoice->row_no;
            $finance->currency = $customerInvoice->currency ?? 'SAR';
            $finance->exchange_rate = $customerInvoice->currency_rate ?? 1;

            $finance->total_debit = $customerInvoice->grand_total;
            $finance->total_credit = $customerInvoice->grand_total;
            $finance->base_total_debit = $customerInvoice->base_grand_total;
            $finance->base_total_credit = $customerInvoice->base_grand_total;

            $finance->job_id = $customerInvoice->job_id ?? 0;
            $finance->job_no = $customerInvoice->job_no ?? '';
            $finance->is_approved = 1;
            $finance->posted_at = now();
            $finance->linked_id = $customerInvoice->id;
            $finance->linked_type = CustomerInvoice::class;
            $finance->company_id = $customerInvoice->company_id;
            $finance->user_id = Auth::id();
            $finance->save();

            $financeSubs = [];

            // IMPORTANT: Every key defined here MUST exist in every sub-array
            $commonData = [
                'finance_id' => $finance->id,
                'voucher_no' => $finance->voucher_no,
                'voucher_type' => $finance->voucher_type,
                'reference_no' => $finance->reference_no,
                'reference_date' => formDate($customerInvoice->invoice_date),
                'currency' => $finance->currency,
                'exchange_rate' => $finance->exchange_rate,
                'base_currency' => 'SAR',
                'customer_id' => $customerInvoice->customer_id,
                'job_id' => $customerInvoice->job_id,
                'job_no' => $customerInvoice->job_no,
                'company_id' => $customerInvoice->company_id,
                'user_id' => Auth::id(),
                'is_tax_line' => 0, // Set a default so all rows have this column
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // LINE 1: Accounts Receivable (DEBIT)
            $financeSubs[] = array_merge($commonData, [
                'account_id' => 5,//1130
                'description' => 'Receivable - Invoice ' . $customerInvoice->row_no,
                'debit' => $customerInvoice->grand_total,
                'credit' => 0,
                'base_debit' => $customerInvoice->base_grand_total,
                'base_credit' => 0,
            ]);

            // LINE 2: Sales Revenue (CREDIT)
            $financeSubs[] = array_merge($commonData, [
                'account_id' => 38,//4160
                'description' => 'Sales Revenue - Invoice ' . $customerInvoice->row_no,
                'debit' => 0,
                'credit' => $customerInvoice->sub_total,
                'base_debit' => 0,
                'base_credit' => $customerInvoice->base_sub_total,
            ]);

            // LINE 3: VAT Payable (CREDIT)
            if ($customerInvoice->tax_total > 0) {
                $financeSubs[] = array_merge($commonData, [
                    'account_id' => 20,//2130
                    'description' => 'Output VAT - Invoice ' . $customerInvoice->row_no,
                    'debit' => 0,
                    'credit' => $customerInvoice->tax_total,
                    'base_debit' => 0,
                    'base_credit' => $customerInvoice->base_tax_total,
                    'is_tax_line' => 1, // Override the default 0
                ]);
            }

            FinanceSub::insert($financeSubs);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Finance Entry Failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete finance entries for a customer invoice.
     *
     * @param int $customerInvoiceId
     * @return void
     */
    private function deleteCustomerInvoiceFinanceEntries($customerInvoiceId)
    {
        try {
            // Find all finance entries linked to this customer invoice
            $financeEntries = Finance::where('linked_id', $customerInvoiceId)
                ->where('linked_type', CustomerInvoice::class)
                ->get();

            // Delete each finance entry and its sub entries
            foreach ($financeEntries as $finance) {
                // Delete finance sub entries first
                FinanceSub::where('finance_id', $finance->id)->delete();

                // Then delete the finance entry
                $finance->delete();
            }
        } catch (\Exception $e) {
            Log::error('Error deleting finance entries for customer invoice: ' . $e->getMessage());
            throw $e;
        }
    }

}

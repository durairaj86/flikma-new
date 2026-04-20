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
            ->with(['job:id,shipment_mode'])// eager load customer
            ->when($request->tab, function ($q) use ($request) {
                $q->where('supplier_invoices.status', SupplierInvoiceEnum::fromName($request->tab));
            })
            ->when($job_id != 'list', function ($query) use ($job_id) {
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
            ->orderBy('supplier_invoices.id', 'desc');

        // ✅ Get counts per status
        $statusCounts = SupplierInvoice::select('status', DB::raw('COUNT(*) as total'))
            ->when($job_id != 'list', function ($query) use ($job_id) {
                $query->where('job_id', $job_id);
            })
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // ✅ Normalize counts for all statuses
        $allCounts = [];
        foreach (SupplierInvoiceEnum::cases() as $status) {
            $allCounts[$status->name] = $statusCounts[$status->value] ?? 0;
        }
        $decimals = decimals();
        // ✅ Return formatted DataTable
        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($model) => $model->id,
                'data-name' => fn($model) => 'Supplier #' . htmlspecialchars($model->invoice_no, ENT_QUOTES, 'UTF-8'),
                'class' => 'row-item',
                'id' => fn($model) => 'supplier-' . strtolower($model->invoice_no ?? $model->id),
            ])
            ->editColumn('invoice_date', fn($model) => Carbon::parse($model->invoice_date)->format('d-M-Y'))
            ->editColumn('currency', fn($model) => strtoupper($model->currency))
            ->addColumn('customer_name', fn($model) => $model->customer?->name_en ?? '-')
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
            ->with([
                'statusCounts' => $allCounts,
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
            'supplier' => 'required|exists:suppliers,id',
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

        /*DB::beginTransaction();
        try {*/

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
        $descriptions = Description::descriptions();
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
                'description' => $descriptions[$desc]->description,
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

        // Finance entry
        $this->storeSupplierInvoiceFinance($supplier, $supplierSub);  // For invoice

        //$this->storeSupplierAdvanceFinance($advance);                 // For advance

        //$this->storeSupplierAdvanceAdjustmentFinance($adjustment);    // For adjustment


        return response()->json([
            'status' => 'success',
            'message' => 'Supplier invoice created successfully',
            'customer_id' => $supplier->id,
        ]);

        /*} catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error saving Supplier Invoice: ' . $e->getMessage());
        }*/
    }

    public function destroy($id)
    {
        $supplier = SupplierInvoice::findOrFail($id);
        $this->deleteSupplierFinanceByRef($supplier->invoice_number, 'SI');
        $supplier->delete();

        return response()->json(['status' => 'success', 'message' => 'Deleted successfully']);
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
                'message' => 'Supplier invoice status updated successfully!',
                'data' => [
                    'id' => $supplier->id,
                    'status' => $supplier->status,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating supplier invoice status: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function overview($id)
    {
        $supplierInvoice = SupplierInvoice::with('supplierInvoiceSubs', 'supplier')->findOrFail($id);
        $descriptions = Description::descriptions()->pluck('description', 'id')->toArray();
        return view('modules.finance.supplier-invoice.view-overview', compact('supplierInvoice', 'descriptions'));
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

}

<?php

namespace App\Http\Controllers\Finance\Invoice;

use App\Enums\JobEnum;
use App\Enums\ProformaInvoiceEnum;
use App\Http\Controllers\Controller;
use App\Models\Finance\Account\Account;
use App\Models\Finance\ProformaInvoice\ProformaInvoice;
use App\Models\Finance\ProformaInvoice\ProformaInvoiceSub;
use App\Models\Job\Job;
use App\Models\Master\Description;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use niklasravnsborg\LaravelPdf\Facades\Pdf;
use Yajra\DataTables\Facades\DataTables;

class ProformaInvoiceController extends Controller
{
    public function modal(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $proforma = new ProformaInvoice();
        $proforma->proformaInvoiceSubs = [new ProformaInvoiceSub()];
        $job_customer_id = $job_id = null;
        if ($request->get('jobId') == 'list') {
            $jobs = Job::select('id', 'row_no', 'customer_id')->with('customer:id,name_en')->whereIn('status', [JobEnum::COMPLETED, JobEnum::PENDING])->get();
        } else {
            $jobs = Job::select('id', 'row_no', 'customer_id')->with('customer:id,name_en')->findOrFail(decodeId($request->get('jobId')));
            $job_customer_id = $jobs->customer_id;
            $job_id = $jobs->id;
            $jobs = [$jobs];
        }
        $accounts = Account::where('type', '!=', 'Equity')->where('is_active', 1)->get();
        return view('modules.finance.proforma-invoice.proforma-invoice-form', compact('proforma', 'jobs', 'accounts', 'job_customer_id', 'job_id'));
    }

    public function edit($id)
    {
        $proforma = ProformaInvoice::with('proformaInvoiceSubs')->findOrFail($id);
        $jobs = Job::select('id', 'row_no', 'customer_id')->with('customer:id,name_en')->get();
        return view('modules.finance.proforma-invoice.proforma-invoice-form', compact('proforma', 'jobs'));
    }

    public function listBasedOnJob($job_id)
    {
        $job = Job::select('row_no')->find(decodeId($job_id));
        $job_no = $job->row_no;
        return view('modules.finance.proforma-invoice.list', compact('job_id', 'job_no'));
    }

    public function fetchAllRows(Request $request, $job_id): \Illuminate\Http\JsonResponse
    {
        if ($job_id != 'list') {
            $job_id = decodeId($job_id);
        }
        $filter = $request->filterData ?? [];
        $rows = ProformaInvoice::select(
            'proforma_invoices.id as id',
            'row_no',
            'posted_at',
            'job_id',
            'job_no',
            'customer_id',
            'proforma_invoices.currency as currency',
            'currency_rate',
            'base_sub_total',
            'sub_total',
            'base_tax_total',
            'tax_total',
            'grand_total',
            'proforma_invoices.status as status',
            'proforma_invoices.created_at as created_at',
            'proforma_invoices.company_id as company_id',
        )
            ->with(['customer:id,name_en,name_ar,row_no'])
            ->with(['job:id,shipment_mode'])// eager load customer
            ->when($request->tab, function ($q) use ($request) {
                $q->where('proforma_invoices.status', ProformaInvoiceEnum::fromName($request->tab));
            })
            ->when($job_id != 'list', function ($query) use ($job_id) {
                $query->where('job_id', $job_id);
            })
            ->when(isset($filter['filter-from-date'], $filter['filter-to-date']),
                function ($query) use ($filter) {

                    $from = Carbon::parse($filter['filter-from-date'])->startOfDay();
                    $to = Carbon::parse($filter['filter-to-date'])->addDay()->startOfDay();

                    $query->where('posted_at', '>=', $from)
                        ->where('posted_at', '<', $to);
                }
            )
            ->when(isset($filter['customers']) && !empty($filter['customers']), function ($query) use ($filter) {
                $query->whereIn('customer_id', decodeIds($filter['customers']));
            })
            ->when(isset($filter['filter-pol']) && !empty($filter['filter-pol']), function ($query) use ($filter) {
                $query->where('pol', 'like', "%{$filter['filter-pol']}%");
            })
            ->when(isset($filter['filter-pod']) && !empty($filter['filter-pod']), function ($query) use ($filter) {
                $query->where('pod', 'like', "%{$filter['filter-pod']}%");
            })->orderBy('proforma_invoices.id', 'desc');

        // ✅ Get counts per status
        $statusCounts = ProformaInvoice::select('status', DB::raw('COUNT(*) as total'))
            ->when($job_id != 'list', function ($query) use ($job_id) {
                $query->where('job_id', $job_id);
            })
            ->when(isset($filter['filter-from-date'], $filter['filter-to-date']),
                function ($query) use ($filter) {

                    $from = Carbon::parse($filter['filter-from-date'])->startOfDay();
                    $to = Carbon::parse($filter['filter-to-date'])->addDay()->startOfDay();

                    $query->where('posted_at', '>=', $from)
                        ->where('posted_at', '<', $to);
                }
            )
            ->when(isset($filter['customers']) && !empty($filter['customers']), function ($query) use ($filter) {
                $query->whereIn('customer_id', decodeIds($filter['customers']));
            })
            ->when(isset($filter['filter-pol']) && !empty($filter['filter-pol']), function ($query) use ($filter) {
                $query->where('pol', 'like', "%{$filter['filter-pol']}%");
            })
            ->when(isset($filter['filter-pod']) && !empty($filter['filter-pod']), function ($query) use ($filter) {
                $query->where('pod', 'like', "%{$filter['filter-pod']}%");
            })
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // ✅ Normalize counts for all statuses
        $allCounts = [];
        foreach (ProformaInvoiceEnum::cases() as $status) {
            $allCounts[$status->name] = $statusCounts[$status->value] ?? 0;
        }
        $decimals = decimals();
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
            ->addColumn('customer_name', fn($model) => $model->customer?->name_en ?? '-')
            ->editColumn('amount_with_tax', fn($model) => number_format($model->grand_total, $decimals))
            ->editColumn('base_tax_total', fn($model) => number_format($model->base_tax_total, $decimals))
            ->editColumn('base_sub_total', fn($model) => number_format($model->base_sub_total, $decimals))
            ->editColumn('tax_total', fn($model) => number_format($model->tax_total, $decimals))
            ->editColumn('sub_total', fn($model) => number_format($model->sub_total, $decimals))
            ->editColumn('grand_total', fn($model) => number_format($model->grand_total, $decimals))
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

        $validated = $request->validate([
            'job_id' => 'nullable|exists:jobs,id',
            'invoice_date' => 'required|date',
            'currency_rate' => 'required',
            'currency' => 'required|exists:currencies,code',
            'terms' => 'nullable|string|max:1000',
            'reference_no' => 'nullable|string|max:128',

            'description_id.*' => 'required|string|max:255',
            'comment.*' => 'nullable|string|max:500',
            'quantity.*' => 'required|numeric|min:1',
            'unit_price.*' => 'required|min:0|regex:/^\d+(\.\d{1,2})?$/',
            'tax.*' => 'nullable|string',
            'unit_id.*' => 'required|numeric',
        ]);

        $userId = Auth::id();
        $companyId = companyId();

        // 🔹 Fetch or create new
        if ($request->filled('data-id')) {
            $proforma = ProformaInvoice::findOrFail($request->input('data-id'));
        } else {
            $proforma = new ProformaInvoice();

            $year = Carbon::parse($request->invoice_date)->format('Y');
            $lastRowNo = ProformaInvoice::whereYear('posted_at', $year)->max('unique_row_no') ?? 0;

            $proforma->unique_row_no = $lastRowNo + 1;
            $proforma->row_no = 'PFI-DXB-' . date('y') . '-' . sprintf('%04d', $proforma->unique_row_no);

            $job = Job::select('row_no', 'customer_id')->find($request->input('job_id'));
            if ($job) {
                $proforma->customer_id = $job->customer_id;
                $proforma->job_no = $job->row_no;
            }
            $this->setBaseColumns($proforma);
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
        $proforma->job_id = $validated['job_id'] ?? null;
        $proforma->posted_at = $validated['invoice_date'];
        $proforma->currency = $validated['currency'];
        $proforma->currency_rate = $validated['currency_rate'];
        $proforma->reference_no = $validated['reference_no'] ?? null;
        $proforma->terms = $validated['terms'] ?? null;
        $proforma->base_sub_total = $proforma->currency_rate * $subTotal;
        $proforma->base_tax_total = $proforma->currency_rate * $taxTotal;
        $proforma->sub_total = $subTotal;
        $proforma->tax_total = $taxTotal;
        $proforma->grand_total = $grandTotal;
        $proforma->status = 1;
        DB::beginTransaction();
        try {

            $proforma->save();

            $proformaSub = [];
            $descriptions = Description::descriptions();
            foreach ($request->description_id as $i => $desc) {
                $qty = $request->quantity[$i] ?? 0;
                $price = $request->unit_price[$i] ?? 0;
                $taxRate = vatPercent($request->tax[$i] ?? 0);
                $lineTotal = $qty * $price;
                $lineTax = $lineTotal * ($taxRate / 100);
                $netAmount = $lineTotal + $lineTax;

                $proformaSub[] = [
                    'proforma_invoice_id' => $proforma->id,
                    'company_id' => $companyId,
                    'description_id' => $desc,
                    'description' => $descriptions[$desc]->description,
                    'comment' => $request->comment[$i] ?? null,
                    'quantity' => $qty,
                    'unit_id' => $request->unit_id[$i],
                    'unit_price' => $price,
                    'tax_code' => $request->tax[$i] ?? null,
                    'tax_percent' => $taxRate,
                    'tax_amount' => $lineTax,
                    'total' => $lineTotal,
                    'total_with_tax' => $netAmount,
                ];
            }

            DB::table('proforma_invoice_subs')
                ->where('proforma_invoice_id', $proforma->id)
                ->delete();

            if (!empty($proformaSub)) {
                DB::table('proforma_invoice_subs')->insert($proformaSub);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Proforma invoice created successfully',
                'customer_id' => $proforma->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error saving Proforma Invoice: ' . $e->getMessage());
        }
    }

    public function actions($id)
    {
        $proforma = ProformaInvoice::select(
            'id',
            'row_no',
            'status'
        )->findOrFail($id);
        $contextMenu = collect([]);
        $edit = $delete = [];
        if ($proforma->status === ProformaInvoiceEnum::DRAFT->value) {
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
                        'data-id' => $proforma->id,
                        'data-value' => ProformaInvoiceEnum::APPROVED->value,
                        'icon' => 'approved'
                    ],
                    [
                        'label' => __('Cancelled'),
                        'code' => '01CSRJ',
                        'id' => 'row_rejected',
                        'class' => 'row_rejected',
                        'data-id' => $proforma->id,
                        'data-value' => ProformaInvoiceEnum::CANCELLED->value,
                        'icon' => 'rejected'
                    ]
                ]
            ]);
        } elseif ($proforma->status === ProformaInvoiceEnum::fromName('approved')) {
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
                        'data-id' => $proforma->id,
                        'data-value' => ProformaInvoiceEnum::CONVERTED->value,
                        'icon' => 'converted',
                        'separator' => 'after',
                    ],
                    [
                        'label' => __('Move To Draft'),
                        'code' => '01CSBK',
                        'id' => 'row_pending',
                        'data-id' => $proforma->id,
                        'data-value' => ProformaInvoiceEnum::DRAFT->value,
                        'icon' => 'pending'
                    ]
                ]
            ]);
        }

        if ($proforma->status === ProformaInvoiceEnum::DRAFT->value) {
            $edit = [
                'label' => __('Edit'),
                'code' => '01CSED',
                'id' => 'row_edit',
                'class' => 'row_edit',
                'data-id' => $proforma->id,
                'type' => 'item',
                'icon' => 'edit'
            ];
            $delete = [
                'label' => __('Delete'),
                'code' => '01CSED',
                'id' => 'row_delete',
                'class' => 'row_delete',
                'data-id' => $proforma->id,
                'type' => 'item',
                'icon' => 'delete'
            ];
        }

        $contextMenu->push([
            'label' => __('Send Email'),
            'code' => '01CSEM',
            'id' => 'row_email',
            'data-id' => $proforma->id,
            'type' => 'item',
            'icon' => 'email',
            'separator' => 'after',
        ]);

        $contextMenu->push([
            'label' => __('Print'),
            'code' => '01CSVW',
            'id' => 'row_print',
            'class' => 'row_print',
            'data-id' => $proforma->id,
            'type' => 'item',
            'icon' => 'print',
            'onclick' => 'PROFORMA_INVOICE.printPreview(' . $proforma->id . ')',
            //'separator' => 'before',
        ]);
        $contextMenu->push([
            'label' => __('View'),
            'code' => '01CSVW',
            'id' => 'row_view',
            'class' => 'row_view',
            'data-id' => $proforma->id,
            'type' => 'item',
            'icon' => 'view',
            //'separator' => 'before',
        ]);
        if ($proforma->status === ProformaInvoiceEnum::DRAFT->value) {
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
        $proforma = ProformaInvoice::findOrFail($id);
        $proforma->status = $status;
        $proforma->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Proforma status updated successfully!',
            'data' => [
                'id' => $proforma->id,
                'status' => $proforma->status,
            ],
        ]);
    }

    public function overview($id)
    {
        $proforma = ProformaInvoice::with('proformaInvoiceSubs', 'customer')->findOrFail($id);
        $descriptions = Description::descriptions()->pluck('description', 'id')->toArray();
        return view('modules.finance.proforma-invoice.view-overview', compact('proforma', 'descriptions'));
    }

    public function print($id)
    {

        $descriptions = Description::descriptions()->pluck('description', 'id')->toArray();

        $proforma = $this->allPrint($id);
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
        //return $pdf->download("ProformaInvoice-{$proforma->row_no}.pdf");
        //$fileName = "ProformaInvoice-{$proforma->row_no}.pdf";

        // 👇 This sends PDF inline (not download)
        //return $pdf->stream($fileName);

        return view('modules.finance.proforma-invoice.view-overview', compact('proforma', 'descriptions'));
    }

    public function allPrint($id)
    {
        return ProformaInvoice::with('proformaInvoiceSubs', 'customer')->findOrFail($id);
    }
}

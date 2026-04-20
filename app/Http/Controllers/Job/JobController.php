<?php

namespace App\Http\Controllers\Job;

use App\Enums\JobEnum;
use App\Http\Controllers\Controller;
use App\Models\Job\Job;
use App\Models\Job\JobClearance;
use App\Models\Job\JobContainer;
use App\Models\Job\JobPackage;
use App\Models\Master\LogisticActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class JobController extends Controller
{
    public function modal(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $job = new Job();
        $job->clearance = new JobClearance();
        $containers = [new JobContainer()];
        $packages = [new JobPackage()];
        $polPod = preloadPOLAndPOD();
        $carriers = defaultCarriers();
        return view('modules.job.job-form', compact('job', 'containers', 'packages', 'polPod', 'carriers'));
    }

    public function edit($id)
    {
        $job = Job::with('containers', 'packages', 'clearance')->find($id);
        $containers = $job->containers && $job->containers->count() > 0 ? $job->containers : [new JobContainer()];
        $packages = $job->packages && $job->packages->count() > 0 ? $job->packages : [new JobPackage()];
        $job->clearance = $job->clearance && $job->clearance->count() > 0 ? $job->clearance : new JobClearance();
        $polPod = preloadPOLAndPOD($job->shipment_mode);
        $carriers = defaultCarriers($job->shipment_mode);
        return view('modules.job.job-form', compact('job', 'containers', 'packages', 'polPod', 'carriers'));
    }

    public function store(Request $request)
    {

        $request->merge(['customer' => decodeId($request->input('customer'))]);
        $request->merge(['salesperson_id' => decodeId($request->input('salesperson_id'))]);

        $numericArrays = [
            'quantity',
            'length',
            'width',
            'height',
            'package_weight',
            'package_volume',
            'total_weight',
            'chargeable_weight',
            'gross', 'net', 'vol', 'container_qty'
        ];

        foreach ($numericArrays as $field) {
            if ($request->has($field)) {
                $request->merge([
                    $field => collect($request->$field)->map(fn($v) => is_string($v) ? str_replace(',', '', $v) : $v
                    )->toArray(),
                ]);
            }
        }

        $validated = $request->validate([
            // General Info
            'customer' => 'required|exists:customers,id',
            'posting_date' => 'required|date',
            'salesperson_id' => 'nullable|exists:sales_persons,id',
            'services' => 'required',
            'shipping_reference_no' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:1000',
            'requirements' => 'nullable|array',
            'requirements.*' => 'nullable|string',

            // Shipment & Cargo
            //'shipment_mode' => 'required|string',
            'activity_id' => 'required|integer',
            'cargo_type' => 'nullable|string',
            'incoterm' => 'nullable|string|max:10',
            'carrier' => 'nullable|string|max:255',
            'awb_number' => 'nullable|string|max:255',
            'hbl_number' => 'nullable|string|max:255',
            'client_ref' => 'nullable|string|max:255',
            'volume' => 'nullable|string|max:255',
            'weight' => 'nullable|string|max:255',
            'commodity' => 'nullable|string|max:255',
            'no_of_pieces' => 'nullable|numeric',

            // Parties
            'shipper' => 'nullable|string|max:255',
            'shipper_address' => 'nullable|string|max:1000',
            'consignee' => 'nullable|string|max:255',
            'consignee_address' => 'nullable|string|max:1000',
            'pickup_date' => 'nullable|string|max:255',
            'pickup_address' => 'nullable|string|max:1000',
            'delivery_date' => 'nullable|string|max:255',
            'delivery_address' => 'nullable|string|max:1000',

            // Routing & Schedule
            'place_of_receipt' => 'nullable|string|max:255',
            'pol' => 'nullable|string|max:255',
            'pod' => 'nullable|string|max:255',
            'place_of_delivery' => 'nullable|string|max:255',
            'final_destination' => 'nullable|string|max:255',
            'etd' => 'nullable|date',
            'eta' => 'nullable|date',
            'atd' => 'nullable|date',
            'ata' => 'nullable|date',
            'transshipment_port' => 'nullable|string|max:255',

            // Customs & Clearance
            'hs_code' => 'nullable|string|max:50',
            'declaration_no' => 'nullable|string|max:100',
            'customs_broker' => 'nullable|string|max:255',
            'port_clearance' => 'nullable|string|max:255',
            'type_of_clearance' => 'nullable|string|max:50',
            'doc_received' => 'nullable|date',
            'bl_receive_date' => 'nullable|date',
            'original_doc_received' => 'nullable|date',
            'saber_certificate_date' => 'nullable|date',
            'bayan_date' => 'nullable|date',
            'bayan_no' => 'nullable|string|max:100',
            'do_date' => 'nullable|date',
            'do_no' => 'nullable|string|max:100',
            'duty_amount' => 'nullable|numeric|min:0',
            'duty_amount_client' => 'nullable|numeric|min:0',
            'demurrage_date' => 'nullable|date',
            'do_remarks' => 'nullable|string|max:1000',
            'lab_clearance' => 'nullable|boolean',
            'inspection' => 'nullable|boolean',
            'clearance_status' => 'nullable|string|max:50',
            'clearance_date' => 'nullable|date',
            'clearance_remarks' => 'nullable|string|max:1000',

            // Containers (array validation)
            'container_size.*' => 'nullable|string|max:50',
            'container_type.*' => 'nullable|string',
            'container_no.*' => 'nullable|string|max:50',
            'seal_no.*' => 'nullable|string|max:50',
            'gross.*' => 'nullable|numeric|min:0',
            'net.*' => 'nullable|numeric|min:0',
            'vol.*' => 'nullable|numeric|min:0',
            'haz.*' => 'nullable|in:0,1',
            'container_qty.*' => 'nullable|numeric|min:0',
            'container_uom.*' => 'nullable|string|max:10',
            'container_remark.*' => 'nullable|string|max:255',

            // Packages (array validation)
            'package_type.*' => 'nullable|string|max:50',
            'description_goods.*' => 'nullable|string|max:255',
            'quantity.*' => 'nullable|numeric|min:0',
            'length.*' => 'nullable|numeric|min:0',
            'width.*' => 'nullable|numeric|min:0',
            'height.*' => 'nullable|numeric|min:0',
            'package_weight.*' => 'nullable|numeric|min:0',
            'package_volume.*' => 'nullable|numeric|min:0',
            'total_weight.*' => 'nullable|numeric|min:0',
            'chargeable_weight.*' => 'nullable|numeric|min:0',

            // Documents (files)
            'bl_copy' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'awb_copy' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'invoice_copy' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'packing_list' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'other_docs.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $userId = Auth::id();
        /*DB::beginTransaction();
        try {*/
        $companyId = companyId();
        if ($request->filled('data-id')) {
            // update existing job
            $job = Job::findOrFail($request->input('data-id'));
        } else {
            $job = new Job();
            $jobYear = Carbon::parse($request->posting_date)->format('Y');
            $lastJobNo = Job::whereYear('posted_at', $jobYear)->max('unique_row_no') ?? 0;
            $job->unique_row_no = $lastJobNo + 1;
            //$job->row_no = 'AL/AI/' . date('y') . '/' . sprintf('%04d', $job->unique_row_no);

            $this->setBaseColumns($job);
        }

        $shipmentMode = LogisticActivity::activities()->where('id', $validated['activity_id'])->pluck('mode')->first();

        $job->customer_id = $validated['customer'];
        $job->row_no = $request['row_no'];
        $job->posted_at = $validated['posting_date'];
        $job->salesperson_id = $validated['salesperson_id'] ?? null;
        $job->services = $validated['services'];
        $job->shipping_ref = $validated['shipping_reference_no'] ?? null;
        $job->remarks = $validated['remarks'] ?? null;
        $job->cargo_requirements = $validated['requirements'] ?? null;

        // Shipment & Cargo
        $job->shipment_mode = $shipmentMode;
        $job->activity_id = $validated['activity_id'] ?? null;
        $job->cargo_type = $validated['cargo_type'] ?? null;
        $job->incoterm = $validated['incoterm'] ?? null;
        $job->carrier = $validated['carrier'] ?? null;
        $job->voyage_flight_no = $request['voyage_flight_no'] ?? null;
        $job->awb_no = $validated['awb_number'] ?? null;
        $job->hbl_no = $validated['hbl_number'] ?? null;
        $job->client_ref = $validated['client_ref'] ?? null;
        $job->volume = $validated['volume'] ?? null;
        $job->weight = $validated['weight'] ?? null;
        $job->commodity = $validated['commodity'] ?? null;
        $job->no_of_pieces = $validated['no_of_pieces'] ?? null;

        // Parties
        $job->shipper = $validated['shipper'] ?? null;
        $job->shipper_address = $validated['shipper_address'] ?? null;
        $job->consignee = $validated['consignee'] ?? null;
        $job->consignee_address = $validated['consignee_address'] ?? null;
        $job->pickup_date = $validated['pickup_date'] ?? null;
        $job->pickup_address = $validated['pickup_address'] ?? null;
        $job->delivery_date = $validated['delivery_date'] ?? null;
        $job->delivery_address = $validated['delivery_address'] ?? null;

        // Routing & Schedule
        $job->place_of_receipt = $validated['place_of_receipt'] ?? null;
        $job->pol = $validated['pol'] ?? null;
        $job->pod = $validated['pod'] ?? null;
        $job->place_of_delivery = $validated['place_of_delivery'] ?? null;
        $job->final_destination = $validated['final_destination'] ?? null;
        $job->etd = $validated['etd'] ?? null;
        $job->eta = $validated['eta'] ?? null;
        $job->atd = $validated['atd'] ?? null;
        $job->ata = $validated['ata'] ?? null;
        $job->transshipment_port = $validated['transshipment_port'] ?? null;

        $job->save();

        // Customs & Clearance
        /*$clearance = new JobClearance();
        $clearance->job_id = $job->id;
        $clearance->hs_code = $validated['hs_code'] ?? null;
        $clearance->declaration_no = $validated['declaration_no'] ?? null;
        $clearance->customs_broker = $validated['customs_broker'] ?? null;
        $clearance->port_clearance = $validated['port_clearance'] ?? null;
        $clearance->type_of_clearance = $validated['type_of_clearance'] ?? null;
        $clearance->doc_received = $validated['doc_received'] ?? null;
        $clearance->bl_receive_date = $validated['bl_receive_date'] ?? null;
        $clearance->original_doc_received = $validated['original_doc_received'] ?? null;
        $clearance->saber_certificate_date = $validated['saber_certificate_date'] ?? null;
        $clearance->bayan_date = $validated['bayan_date'] ?? null;
        $clearance->bayan_no = $validated['bayan_no'] ?? null;
        $clearance->do_date = $validated['do_date'] ?? null;
        $clearance->do_no = $validated['do_no'] ?? null;
        $clearance->duty_amount = $validated['duty_amount'] ?? 0;
        $clearance->duty_amount_client = $validated['duty_amount_client'] ?? 0;
        $clearance->demurrage_date = $validated['demurrage_date'] ?? null;
        $clearance->do_remarks = $validated['do_remarks'] ?? null;
        $clearance->lab_clearance = $validated['lab_clearance'] ?? false;
        $clearance->inspection = $validated['inspection'] ?? false;
        $clearance->clearance_status = $validated['clearance_status'] ?? null;
        $clearance->clearance_date = $validated['clearance_date'] ?? null;
        $clearance->clearance_remarks = $validated['clearance_remarks'] ?? null;
        $this->setBaseColumns($clearance);
        $clearance->save();*/

        if ($request->filled('data-id')) {
            $clearance = JobClearance::where('job_id', $request->get('data-id'))->first();
        } else {
            $clearance = new JobClearance();
        }

        $clearance->hs_code = $validated['hs_code'] ?? null;
        $clearance->declaration_no = $validated['declaration_no'] ?? null;
        $clearance->customs_broker = $validated['customs_broker'] ?? null;
        $clearance->port_clearance = $validated['port_clearance'] ?? null;
        $clearance->type_of_clearance = $validated['type_of_clearance'] ?? null;
        $clearance->doc_received = $validated['doc_received'] ?? null;
        $clearance->bl_receive_date = $validated['bl_receive_date'] ?? null;
        $clearance->original_doc_received = $validated['original_doc_received'] ?? null;
        $clearance->saber_certificate_date = $validated['saber_certificate_date'] ?? null;
        $clearance->bayan_date = $validated['bayan_date'] ?? null;
        $clearance->bayan_no = $validated['bayan_no'] ?? null;
        $clearance->do_date = $validated['do_date'] ?? null;
        $clearance->do_no = $validated['do_no'] ?? null;
        $clearance->duty_amount = $validated['duty_amount'] ?? 0;
        $clearance->duty_amount_client = $validated['duty_amount_client'] ?? 0;
        $clearance->demurrage_date = $validated['demurrage_date'] ?? null;
        $clearance->do_remarks = $validated['do_remarks'] ?? null;
        $clearance->lab_clearance = $validated['lab_clearance'] ?? false;
        $clearance->inspection = $validated['inspection'] ?? false;
        $clearance->clearance_status = $validated['clearance_status'] ?? null;
        $clearance->clearance_date = $validated['clearance_date'] ?? null;
        $clearance->clearance_remarks = $validated['clearance_remarks'] ?? null;
        $clearance->posted_at = formDate($validated['posting_date']);
        $clearance->company_id = $companyId;

        $clearance->job_id = $job->id; // ensure job id is set

        $clearance->save();

        if ($request->has('container_size')) {
            $jobContainers = [];
            foreach ($request->container_size as $i => $size) {
                if (!$size) continue;
                $jobContainers[] = [
                    'job_id' => $job->id,
                    'container_size' => $size,
                    'container_number' => $request->container_no[$i] ?? null,
                    'seal_number' => $request->seal_no[$i] ?? null,
                    'gross_weight' => $request->gross[$i] ?? null,
                    'net_weight' => $request->net[$i] ?? null,
                    'volume' => $request->vol[$i] ?? null,
                    'hazardous' => $request->haz[$i] ?? 0,
                    // Newly Added Fields
                    'qty' => $request->container_qty[$i] ?? null,
                    'uom' => $request->container_uom[$i] ?? null,
                    // Updated Remarks Field
                    'remarks' => $request->container_remark[$i] ?? null,
                    'container_type' => $request->container_type[$i] ?? 'dry',
                    'user_id' => $userId,
                    'company_id' => $companyId,
                ];
            }
            // Delete existing containers for this job
            DB::table('job_containers')->where('job_id', $job->id)->delete();
            DB::table('job_containers')->insert($jobContainers);
        }

        if ($request->has('package_type')) {
            $jobPackages = [];
            foreach ($request->package_type as $i => $package_type) {
                if (!$package_type && !$request->description_goods[$i]) continue;
                $jobPackages[] = [
                    'job_id' => $job->id,
                    //'commodity_type' => $commodity,
                    'package_type' => $package_type,
                    'description_goods' => $request->description_goods[$i] ?? null,
                    //'hs_code' => $request->package_hs_code[$i] ?? null,
                    'quantity' => $request->quantity[$i] ?? null,
                    'length' => $request->length[$i] ?? null,
                    'width' => $request->width[$i] ?? null,
                    'height' => $request->height[$i] ?? null,
                    'package_weight' => $request->package_weight[$i] ?? null,
                    'total_weight' => $request->total_weight[$i] ?? null,
                    'chargeable_weight' => $request->chargeable_weight[$i] ?? null,
                    'volume' => $request->package_volume[$i] ?? null,
                    'user_id' => $userId,
                    'company_id' => $companyId,
                ];
            }
            // Delete existing packages for this job
            DB::table('job_packages')->where('job_id', $job->id)->delete();
            DB::table('job_packages')->insert($jobPackages);
        }

        $docFields = [
            'bl_copy' => 'Bill of Lading',
            'awb_copy' => 'Airway Bill',
            'invoice_copy' => 'Invoice',
            'packing_list' => 'Packing List',
        ];

        foreach ($docFields as $field => $docType) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);

                // Store file in public disk
                $path = $file->store('documents/' . $companyId . '/jobs/' . $job->id, 'public');

                // Save record
                $job->documents()->create([
                    'document_type' => $docType,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(), // ✅ Original file name
                    'title' => 'job',
                    'posted_date' => now(),
                    'user_id' => $userId,
                    'company_id' => $companyId,
                ]);
            }
        }


        if ($request->hasFile('other_docs')) {
            foreach ($request->file('other_docs') as $file) {
                $path = $file->store($companyId . '/jobs/documents/' . $job->id, 'public');
                $job->documents()->create([
                    'document_type' => 'Other',
                    'file_path' => $path,
                    'title' => 'Job',
                    'posted_date' => now(),
                    'user_id' => $userId,
                    'company_id' => $companyId,
                ]);
            }
        }

        DB::commit();
        return response()->json([
            'status' => 'success',
            'job_id' => $job->id,
            'message' => 'Job saved successfully'
        ]);
        /*} catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }*/
    }

    public function fetchAllRows(Request $request): \Illuminate\Http\JsonResponse
    {
        $filter = $request->filterData;
        $rows = Job::withTrashed()->select(
            'jobs.id as id',
            'jobs.row_no as row_no',
            'customer_id',
            'posted_at',
            'services',
            'shipment_mode',
            'activity_id',
            'pol',
            'pod',
            'eta',
            'etd',
            'commodity',
            'awb_no',
            'carrier',
            'shipper',
            'consignee',
            'weight',
            'volume',
            'no_of_pieces',
            'jobs.company_id as company_id',
            'jobs.status as status',
        )->with('customer:id,name_en,name_ar,email,phone', 'invoices', 'clearance:id,job_id,bayan_no')
            ->where('jobs.status', JobEnum::fromName($request->tab))
            ->when(isset($filter['filter-from-date'], $filter['filter-to-date']),
                function ($query) use ($filter) {

                    $from = Carbon::parse($filter['filter-from-date'])->startOfDay();
                    $to = Carbon::parse($filter['filter-to-date'])->addDay()->startOfDay();

                    $query->where('posted_at', '>=', $from)
                        ->where('posted_at', '<', $to);
                }
            )
            ->when($filter['customers'] ?? null, function ($query, $customers) {
                $query->whereIn('customer_id', decodeIds($customers));
            })
            ->when($filter['filter_carrier'] ?? null, function ($query, $carrier) {
                $query->where('carrier', 'like', "%{$carrier}%");
            })
            ->when($filter['filter-pol'] ?? null, function ($query, $pol) {
                $query->where('pol', 'like', "%{$pol}%");
            })
            ->when($filter['filter-pod'] ?? null, function ($query, $pod) {
                $query->where('pod', 'like', "%{$pod}%");
            })
            ->orderByDesc('id');


        // Get counts per status
        $statusCounts = Job::withTrashed()->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Normalize counts for all statuses
        $allCounts = [];
        foreach (JobEnum::cases() as $status) {
            $allCounts[$status->name] = $statusCounts[$status->value] ?? 0;
        }

        $activity = LogisticActivity::activities();
        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($model) => $model->id,
                'data-name' => fn($model) => $model->row_no,
                'data-title' => fn($model) => $model->row_no,
                'class' => 'row-item',
                'id' => fn($model) => 'job-' . strtolower($model->row_no ?? $model->id),
            ])
            ->editColumn('services', function ($model) {
                return getSelectedServices($model->services, true);
            })
            ->rawColumns(['status'])
            ->editColumn('created_at', fn($model) => Carbon::parse($model->created_at)->format('d-m-Y'))
            ->editColumn('activity_id', fn($model) => $activity->where('id', $model->activity_id)->pluck('name')->first())
            ->editColumn('eta', fn($model) => filled($model->eta) ? 'ETA: ' . Carbon::parse($model->eta)->format('d-M') : '')
            ->editColumn('etd', fn($model) => filled($model->etd) ? 'ETD: ' . Carbon::parse($model->eta)->format('d-M') : '')
            ->editColumn('polCode', fn($model) => filled($model->pol) ? $model->polCode : '')
            ->editColumn('podCode', fn($model) => filled($model->pod) ? $model->podCode : '')
            ->addColumn('invoices', function ($model) {
                return [
                    'draft' => $model->invoices->where('status', 1)->count(),
                    'approved' => $model->invoices->where('status', 3)->count(),
                ];
            })
            ->with([
                'statusCounts' => $allCounts,  // ✅ send to DataTables response
            ])
            ->toJson();
    }

    public function actions($id)
    {
        $job = Job::select(
            'id',
            'row_no',
            'status'
        )->withTrashed()->findOrFail($id);
        $contextMenu = collect([]);
        $edit = $delete = [];

        if ($job->status === JobEnum::PENDING->value || $job->status === JobEnum::COMPLETED->value) {
            $contextMenu->push([
                'label' => __('Invoice'),
                'type' => 'submenu',
                'separator' => 'after',
                'icon' => 'move_to',
                'items' => [
                    [
                        'label' => __('Customer Invoice'),
                        'code' => '01CSBK',
                        'id' => 'customer_invoice',
                        'data-id' => $job->id,
                        'data-value' => '/invoice/customer/list/' . encodeId($job->id),
                        'icon' => 'customer_invoice'
                    ],
                    [
                        'label' => __('Supplier Invoice'),
                        'code' => '01CSRJ',
                        'id' => 'supplier_invoice',
                        'class' => 'supplier_invoice',
                        'data-id' => $job->id,
                        'data-value' => '/invoice/supplier/list/' . encodeId($job->id),
                        'icon' => 'supplier_invoice'
                    ],
                    [
                        'label' => __('Proforma Invoice'),
                        'code' => '01CSRJ',
                        'id' => 'proforma_invoice',
                        'class' => 'proforma_invoice',
                        'data-id' => $job->id,
                        'data-value' => '/invoice/proforma/list/' . encodeId($job->id),
                        'icon' => 'proforma_invoice'
                    ]
                ]
            ]);
        }

        if ($job->status === JobEnum::PENDING->value) {
            $contextMenu->push([
                'label' => __('Move to'),
                'type' => 'submenu',
                'separator' => 'after',
                'icon' => 'move_to',
                'items' => [
                    [
                        'label' => __('Completed'),
                        'code' => '01CSBK',
                        'id' => 'row_completed',
                        'data-id' => $job->id,
                        'data-value' => JobEnum::COMPLETED->value,
                        'icon' => 'confirmed'
                    ],
                    [
                        'label' => __('Cancelled'),
                        'code' => '01CSRJ',
                        'id' => 'row_rejected',
                        'class' => 'row_rejected',
                        'data-id' => $job->id,
                        'data-value' => JobEnum::CANCELLED->value,
                        'icon' => 'rejected'
                    ]
                ]
            ]);
        } elseif ($job->status === JobEnum::fromName('completed')) {
            $edit = [
                'label' => __('Edit'),
                'code' => '01CSED',
                'id' => 'row_edit',
                'class' => 'row_edit',
                'data-id' => $job->id,
                'type' => 'item',
                'icon' => 'edit'
            ];
        }

        if ($job->status === JobEnum::PENDING->value) {
            $edit = [
                'label' => __('Edit'),
                'code' => '01CSED',
                'id' => 'row_edit',
                'class' => 'row_edit',
                'data-id' => $job->id,
                'type' => 'item',
                'icon' => 'edit'
            ];
            $delete = [
                'label' => __('Delete'),
                'code' => '01CSED',
                'id' => 'row_delete',
                'class' => 'row_delete',
                'data-id' => $job->id,
                'type' => 'item',
                'icon' => 'delete'
            ];
            $contextMenu->push([
                'label' => __('Send Email'),
                'code' => '01CSEM',
                'id' => 'row_email',
                'data-id' => $job->id,
                'type' => 'item',
                'icon' => 'email',
                'separator' => 'after',
            ]);
        }

        $contextMenu->push([
            'label' => __('Print'),
            'code' => '01CSVW',
            'id' => 'row_print',
            'class' => 'row_print',
            'data-id' => $job->id,
            'type' => 'item',
            'icon' => 'print',
            'onclick' => 'JOB.printPreview(' . $job->id . ')',
            //'separator' => 'before',
        ]);
        $contextMenu->push([
            'label' => __('View'),
            'code' => '01CSVW',
            'id' => 'row_view',
            'class' => 'row_view',
            'data-id' => $job->id,
            'type' => 'item',
            'icon' => 'view',
            //'separator' => 'before',
        ]);
        if ($job->status === JobEnum::PENDING->value) {
            $contextMenu->push([
                'label' => __('Actions'),
                'type' => 'submenu',
                'icon' => 'action',
                'items' => [$edit, $delete]
            ]);
        } elseif ($job->status === JobEnum::COMPLETED->value) {
            $contextMenu->push([
                'label' => __('Actions'),
                'type' => 'submenu',
                'icon' => 'action',
                'items' => [$edit]
            ]);
        }
        return response()->json($contextMenu->values());
    }

    public function updateStatus($id, $status): \Illuminate\Http\JsonResponse
    {
        $job = Job::findOrFail($id);
        $job->status = $status;
        $job->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Job status updated successfully!',
            'data' => [
                'id' => $job->id,
                'status' => $job->status,
            ],
        ]);
    }

    public function overview($id)
    {
        $job = Job::with(['customer', 'containers', 'packages', 'activity'])->withTrashed()->findOrFail($id);
        return view('modules.job.view-overview', compact('job'));
    }

    public function delete($id)
    {
        $job = Job::findOrFail($id);
        $job->update(['status' => 4]);
        $job->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Job deleted successfully!',
        ]);
    }

    public function print($id)
    {
        $job = $this->allPrint($id);
        $containers = $job->containers && $job->containers->count() > 0 ? $job->containers : [new JobContainer()];
        $packages = $job->packages && $job->packages->count() > 0 ? $job->packages : [new JobPackage()];

        return view('modules.job.view-overview', compact('job', 'containers', 'packages'));
    }

    public function allPrint($id)
    {
        return Job::with('containers', 'packages', 'customer')->withTrashed()->findOrFail($id);
    }
}

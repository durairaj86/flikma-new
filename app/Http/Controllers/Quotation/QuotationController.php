<?php

namespace App\Http\Controllers\Quotation;

use App\Enums\EnquiryEnum;
use App\Enums\QuotationEnum;
use App\Http\Controllers\Controller;
use App\Models\Enquiry\Enquiry;
use App\Mail\QuotationMail;
use App\Models\Customer\Customer;
use App\Models\Job\Job;
use App\Models\Master\Description;
use App\Models\Master\LogisticActivity;
use App\Models\Master\TransportDirectory\Airport;
use App\Models\Master\TransportDirectory\Port;
use App\Models\Prospect\Prospect;
use App\Models\Quotation\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Mockery\Exception;
use Yajra\DataTables\Facades\DataTables;

class QuotationController extends Controller
{
    public function modal(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $request = $request->all();

        $quotation = new Quotation();
        $quotation->posted_at = Carbon::today()->format('d-m-Y');
        $quotation->valid_until = Carbon::today()->addDays(29)->format('d-m-Y');
        $enquiryData = null;

        $polPod = preloadPOLAndPOD();

        if (isset($request['enquiryId'])) {
            $enquiryController = new \App\Http\Controllers\Enquiry\EnquiryController();
            $enquiry = $enquiryController->getEnquiryData($request['enquiryId'])->getData();

            //$quotation->id = $enquiry->enquiry_id;
            $quotation->customer_id = $enquiry->customer_id ?? null;
            $quotation->prospect_id = $enquiry->prospect_id ?? null;
            $quotation->salesperson_id = $enquiry->salesperson_id ?? null;
            $quotation->activity_id = $enquiry->activity_id ?? null;
            $quotation->services = $enquiry->services ?? null;
            $quotation->origin = $enquiry->origin ?? null;
            $quotation->destination = $enquiry->destination ?? null;
            $quotation->pol = $enquiry->pol ?? null;
            $quotation->pod = $enquiry->pod ?? null;
            $quotation->shipper = $enquiry->shipper ?? null;
            $quotation->place_of_receipt = $enquiry->place_of_receipt ?? null;
            $quotation->incoterm = $enquiry->incoterm ?? null;
            $quotation->volume = $enquiry->volume ?? null;
            $quotation->pickup_date = $enquiry->pickup_date ?? null;
            $quotation->enquiry_id = $enquiry->enquiry_id ?? null;

            $enquiryData = $quotation->enquiry_id;
        }
        $chargeDescriptions = Description::descriptions();
        return view('modules.quotation.quotation-form', compact('polPod', 'quotation', 'enquiryData', 'chargeDescriptions'));
    }

    public function createFromEnquiry(Request $request, $enquiry_id)
    {
        $request->merge(['enquiryId' => $enquiry_id]);

        return $this->modal($request);
    }

    public function edit($id)
    {
        $quotation = Quotation::with('containers', 'packages', 'charges')->find($id);
        $polPod = preloadPOLAndPOD($quotation->shipment_type);
        $chargeDescriptions = Description::descriptions();
        return view('modules.quotation.quotation-form', compact('polPod', 'quotation', 'chargeDescriptions'));
    }

    public function store(Request $request)
    {
        $request->merge(['customer' => decodeId($request->input('customer'))]);
        $request->merge(['salesperson_id' => decodeId($request->input('salesperson_id'))]);

        // Decode prospect_id if it exists
        if ($request->filled('prospect')) {
            $request->merge(['prospect' => decodeId($request->input('prospect'))]);
        }
        $validated = $request->validate([
            // Quotation
            'customer' => 'nullable|required_without:prospect|exists:customers,id',
            // Prospect (required when customer is NOT selected)
            'prospect' => 'nullable|required_without:customer|exists:prospects,id',
            'posted_at' => 'required|date',
            'valid_until' => 'required|date|after_or_equal:posted_at',
            'activity_id' => 'required|string|max:50',
            //'shipment_mode' => 'required|string|max:50',
            'shipment_category' => 'nullable|string|max:50',
            'incoterm' => 'nullable|string|max:20',
            'pol' => 'nullable|string|max:100',
            'pod' => 'nullable|string|max:100',
            'place_of_receipt' => 'nullable|string|max:100',
            'place_of_delivery' => 'nullable|string|max:100',
            'final_destination' => 'nullable|string|max:100',
            'carrier' => 'nullable|string|max:100',
            'prepared_by' => 'nullable|string|max:50',
            'salesperson_id' => 'nullable|integer|max:50',
            'terms' => 'nullable|string|max:500',
            'shipper' => 'nullable|string|max:100',
            //'volume' => 'nullable|numeric|min:0|max:999999.99',
            'pickup_date' => 'nullable|date',
            'enquiry_id' => 'nullable|integer|exists:enquiries,id',

            // Containers (array) — same field set as Job's Container tab
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

            // Packages (array) — same field set as Job's Package tab
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

            // Charges (array) — all nullable; charge tab is optional
            'chg_description.*' => 'nullable|string|max:255',
            'chg_unit.*' => 'nullable|string|max:100',
            'chg_qty.*' => 'nullable|integer|min:1|max:99999',
            'chg_currency.*' => 'nullable|string|max:10',
            'chg_ex_rate.*' => 'nullable|numeric|min:0|max:99999.999999',
            'chg_amt_qty.*' => 'nullable|numeric|min:0|max:999999999.99',
            'chg_tax_group.*' => 'nullable|string|max:50',
            'chg_remarks.*' => 'nullable|string|max:500',
        ]);

        if (isset($request['data-id']) and filled($request['data-id'])) {
            $quotation = Quotation::findOrFail($request->input('data-id'));
        } else {
            $quotationYear = Carbon::parse($request->posted_at)->format('Y');
            $quotation = new Quotation();
            $quotation->unique_row_no = sprintf("%03d", (Quotation::where('row_created_year', $quotationYear)->max('unique_row_no') ?? 0) + 1);
            $quotation->row_no = 'QTN/' . date('Y') . '/' . $quotation->unique_row_no;
            $quotation->row_created_year = $quotationYear;
            $quotation->status = QuotationEnum::PENDING->value;
            $quotation->user_id = Auth::id();
            $quotation->company_id = companyId();
        }

        $services = $request->services;
        $quotation->customer_id = $request->customer;
        $quotation->prospect_id = $request->prospect;
        $quotation->posted_at = $request->posted_at;
        $quotation->valid_until = $request->valid_until;
        $quotation->services = $services;
        $quotation->activity_id = $request->activity_id;
        $quotation->shipment_mode = LogisticActivity::activities($request->activity_id)->pluck('mode')->first();
        $quotation->shipment_category = $request->shipment_category;
        $quotation->incoterm = $request->incoterm;
        $quotation->pol = $request->pol;
        $quotation->pod = $request->pod;
        $quotation->place_of_receipt = $request->place_of_receipt;
        $quotation->place_of_delivery = $request->place_of_delivery;
        $quotation->final_destination = $request->final_destination;
        $quotation->carrier = $request->carrier;
        $quotation->prepared_by = $request->prepared_by;
        $quotation->salesperson_id = $request->salesperson_id;
        $quotation->terms = $request->terms;
        $quotation->shipper = $request->shipper;
        $quotation->commodity = $request->commodity;
        $quotation->pickup_date = $request->pickup_date;
        $quotation->pickup_address = $request->pickup_address;
        if ($request->filled('enquiry_id')) {
            $quotation->enquiry_id = $request->enquiry_id;
        }

        $quotation->save();

        // Mark the source enquiry as converted to quotation
        if ($quotation->enquiry_id) {
            Enquiry::where('id', $quotation->enquiry_id)
                ->where('status', '!=', EnquiryEnum::QUOTATION->value)
                ->update(['status' => EnquiryEnum::QUOTATION->value]);
        }

        // ✅ Insert containers (same field set as Job's Container tab)
        if ($request->has('container_size')) {
            $containers = [];
            foreach ($request->container_size as $index => $size) {
                if (!$size) continue;
                $containers[] = [
                    'quotation_id' => $quotation->id,
                    'container_size' => $size,
                    'container_number' => $request->container_no[$index] ?? null,
                    'seal_number' => $request->seal_no[$index] ?? null,
                    'gross_weight' => $request->gross[$index] ?? null,
                    'net_weight' => $request->net[$index] ?? null,
                    'volume' => $request->vol[$index] ?? null,
                    'hazardous' => $request->haz[$index] ?? 0,
                    'qty' => $request->container_qty[$index] ?? null,
                    'uom' => $request->container_uom[$index] ?? null,
                    'remarks' => $request->container_remark[$index] ?? null,
                    'container_type' => $request->container_type[$index] ?? 'dry',
                ];
            }
            DB::table('quotation_containers')->where('quotation_id', $quotation->id)->delete();
            if (!empty($containers)) {
                DB::table('quotation_containers')->insert($containers);
            }
        }

        // ✅ Insert packages (same field set as Job's Package tab)
        if ($request->has('package_type')) {
            $packages = [];
            foreach ($request->package_type as $index => $package_type) {
                if (!$package_type && !$request->description_goods[$index]) continue;
                $packages[] = [
                    'quotation_id' => $quotation->id,
                    'package_type' => $package_type,
                    'description_goods' => $request->description_goods[$index] ?? null,
                    'quantity' => $request->quantity[$index] ?? null,
                    'length' => $request->length[$index] ?? null,
                    'width' => $request->width[$index] ?? null,
                    'height' => $request->height[$index] ?? null,
                    'package_weight' => $request->package_weight[$index] ?? null,
                    'total_weight' => $request->total_weight[$index] ?? null,
                    'chargeable_weight' => $request->chargeable_weight[$index] ?? null,
                    'volume' => $request->package_volume[$index] ?? null,
                ];
            }
            DB::table('quotation_packages')->where('quotation_id', $quotation->id)->delete();
            if (!empty($packages)) {
                DB::table('quotation_packages')->insert($packages);
            }
        }

        // ✅ Insert charges
        $descriptions = $request->chg_description ?? [];
        if (!empty(array_filter($descriptions))) {
            $charges = [];
            foreach ($descriptions as $index => $desc) {
                $qty = (int)($request->chg_qty[$index] ?? 1);
                $exRate = (float)($request->chg_ex_rate[$index] ?? 1);
                $amtQty = (float)($request->chg_amt_qty[$index] ?? 0);
                $fcy = $amtQty ? round($qty * $amtQty, 2) : null;
                $local = $fcy ? round($fcy * $exRate, 2) : null;
                $charges[] = [
                    'quotation_id' => $quotation->id,
                    'line_no' => $index + 1,
                    'charge_description' => $desc,
                    'unit' => $request->chg_unit[$index] ?? null,
                    'qty' => $qty,
                    'currency' => $request->chg_currency[$index] ?? 'SAR',
                    'ex_rate' => $exRate,
                    'amount_per_qty' => $amtQty ?: null,
                    'fcy_amount' => $fcy,
                    'local_amount' => $local,
                    'tax_group_code' => $request->chg_tax_group[$index] ?? null,
                    'remarks' => $request->chg_remarks[$index] ?? null,
                    'sort_order' => $index,
                ];
            }
            DB::table('quotation_charges')->where('quotation_id', $quotation->id)->delete();
            DB::table('quotation_charges')->insert($charges);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Quotation created successfully',
            'quotation_id' => $quotation->id,
        ]);
    }

    public function fetchAllRows(Request $request): \Illuminate\Http\JsonResponse
    {
        $filter = $request->filterData ?? [];

        // Shared filters so the tab counts always match the visible list
        // (company scoping is applied globally on the Quotation model).
        $applyFilters = function ($query) use ($filter) {
            $query->when(!empty($filter['filter-from-date']) && !empty($filter['filter-to-date']),
                function ($query) use ($filter) {
                    // posted_at is stored as a Y-m-d string, so compare plain
                    // date strings — comparing against a datetime would drop
                    // rows that fall exactly on the from-date.
                    $from = Carbon::parse($filter['filter-from-date'])->toDateString();
                    $to = Carbon::parse($filter['filter-to-date'])->addDay()->toDateString();
                    $query->where('quotations.posted_at', '>=', $from)
                        ->where('quotations.posted_at', '<', $to);
                }
            )
                ->when(isset($filter['customers']) && !empty($filter['customers']), function ($query) use ($filter) {
                    $query->whereIn('quotations.customer_id', decodeIds($filter['customers']));
                })
                ->when(isset($filter['filter-pol']) && !empty($filter['filter-pol']), function ($query) use ($filter) {
                    $query->where('quotations.pol', 'like', "%{$filter['filter-pol']}%");
                })
                ->when(isset($filter['filter-pod']) && !empty($filter['filter-pod']), function ($query) use ($filter) {
                    $query->where('quotations.pod', 'like', "%{$filter['filter-pod']}%");
                })
                ->when(isset($filter['activity_id']) && !empty($filter['activity_id']), function ($query) use ($filter) {
                    $query->where('quotations.activity_id', $filter['activity_id']);
                });
        };

        $rows = Quotation::select(
            'quotations.id',
            'quotations.row_no',
            'quotations.posted_at',
            'quotations.valid_until',
            'quotations.services',
            'quotations.shipment_mode',
            'quotations.shipment_category',
            'quotations.incoterm',
            'quotations.pol',
            'quotations.pod',
            'quotations.place_of_receipt',
            'quotations.place_of_delivery',
            'quotations.final_destination',
            'quotations.carrier',
            'quotations.status',
            'quotations.company_id',
            'quotations.created_at',
            'quotations.shipper',
            'quotations.commodity',
            'quotations.pickup_date',
            'quotations.pickup_address',
            'quotations.notes',
            'quotations.terms',
            'quotations.prepared_by',
            'quotations.revision_count',
            DB::raw('COALESCE(customers.name_en, prospects.name) AS client_name'),
            DB::raw('logistic_activities.name AS activity_name'),
            DB::raw('sales_persons.name AS salesperson_name'),
            DB::raw('jobs.row_no AS linked_job_no'),
            DB::raw('source_enquiries.row_no AS linked_enquiry_no'),
        )
            ->leftJoin('customers', 'customers.id', '=', 'quotations.customer_id')
            ->leftJoin('prospects', 'prospects.id', '=', 'quotations.prospect_id')
            ->leftJoin('logistic_activities', 'logistic_activities.id', '=', 'quotations.activity_id')
            ->leftJoin('sales_persons', 'sales_persons.id', '=', 'quotations.salesperson_id')
            ->leftJoin('jobs', 'jobs.id', '=', 'quotations.job_id')
            ->leftJoin('enquiries as source_enquiries', 'source_enquiries.id', '=', 'quotations.enquiry_id')
            ->where('quotations.status', QuotationEnum::fromName($request->tab))
            ->tap($applyFilters)
            ->orderByDesc('quotations.id');

        // Counts per status using the same filters as the list
        $statusCounts = Quotation::select('quotations.status', DB::raw('COUNT(*) as total'))
            ->tap($applyFilters)
            ->groupBy('quotations.status')
            ->pluck('total', 'status')
            ->toArray();

        // Normalize counts for all statuses
        $allCounts = [];
        foreach (QuotationEnum::cases() as $status) {
            $allCounts[$status->name] = $statusCounts[$status->value] ?? 0;
        }

        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($model) => $model->id,
                'data-name' => fn($model) => $model->row_no,
                'class' => 'row-item',
                'id' => fn($model) => 'quotation-' . strtolower($model->row_no ?? $model->id),
            ])
            ->editColumn('services', fn($model) => getSelectedServices($model->services, true))
            ->rawColumns(['services'])
            ->with(['statusCounts' => $allCounts])
            ->toJson();
    }

    public function updateStatus($id, $status)
    {
        $quotation = Quotation::findOrFail($id);
        DB::beginTransaction();
        try {
            $quotation->status = $status;
            $quotation->save();
            if ($status == QuotationEnum::CONVERTED->value) {
                $this->convertToJob($quotation);
            }
            DB::commit();
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }


        return response()->json([
            'status' => 'success',
            'message' => 'Quotation status updated successfully!',
            'data' => [
                'id' => $quotation->id,
                'status' => $quotation->status,
            ],
        ]);
    }

    public function convertToJob(Quotation $quotation)
    {
        // Load relations
        $quotation->load(['containers', 'packages']);

        // New Job
        $job = new Job();

        // Copy only matching fields
        foreach (Job::$mapFromQuotation as $field) {
            if (isset($quotation->$field)) {
                if ($field == "customer_id" && $quotation->customer_id == null) {
                    continue;
                } elseif ($field == "prospect_id" && $quotation->prospect_id != null) {
                    $prospectData = Prospect::findOrFail($quotation->$field);
                    $customer = new Customer();
                    $customer->name_en = $prospectData->name;
                    $customer->name_ar = $prospectData->name;
                    $customer->email = $prospectData->email;
                    $customer->phone = $prospectData->phone;
                    $customer->address1_en = $prospectData->address;
                    $customer->salesperson_id = $prospectData->salesperson_id;
                    $customer->currency = 'SAR';

                    $customer->unique_row_no = sprintf("%03d", (Customer::max('unique_row_no') ?? 0) + 1);
                    $customer->row_no = 'CS' . $customer->unique_row_no;

                    $this->setBaseColumns($customer);
                    $customer->save();
                    $prospectData->customer = 1;
                    $prospectData->save();
                    $job->customer_id = $customer->id;
                } elseif (in_array($field, ['pol', 'pod'])) {
                    // Quotation (and Enquiry, which it's often copied from) stores
                    // pol/pod as a raw port id, but Job::pol_name/pol_code expect
                    // the "CODE-Name" format the job form saves. Resolve the id to
                    // that format here instead of copying the raw id across.
                    $value = $quotation->$field;
                    if ($value !== null && $value !== '' && is_numeric($value)) {
                        $portModel = strtolower($quotation->shipment_mode ?? '') === 'air' ? Airport::class : Port::class;
                        $port = $portModel::find($value);
                        $value = $port ? $port->code . '-' . $port->name : $value;
                    }
                    $job->$field = $value;
                } else {
                    $job->$field = $quotation->$field;
                }
            }
        }

        // Set extra job fields if needed
        $job->quotation_id = $quotation->id;
        $job->posted_at = Carbon::today()->format('Y-m-d');
        $job->status = 1;

        $jobYear = Carbon::parse($job->posted_at)->format('Y');
        $lastJobNo = Job::whereYear('posted_at', $jobYear)->max('unique_row_no') ?? 0;
        $job->unique_row_no = $lastJobNo + 1;
        $job->row_no = 'JOB-' . date('y') . '-' . sprintf('%04d', $job->unique_row_no);
        $this->setBaseColumns($job);
        $job->save();
        $quotation->job_id = $job->id;
        $quotation->save();

        // Attach containers
        $jobContainerColumns = \Schema::getColumnListing('job_containers');
        foreach ($quotation->containers as $c) {
            $attributes = collect($c->getAttributes())
                ->only($jobContainerColumns)
                ->except(['id', 'created_at', 'updated_at'])
                ->all();
            $job->containers()->create($attributes);
        }

        // Attach packages
        $jobPackageColumns = \Schema::getColumnListing('job_packages');
        foreach ($quotation->packages as $p) {
            $attributes = collect($p->getAttributes())
                ->only($jobPackageColumns)
                ->except(['id', 'created_at', 'updated_at'])
                ->all();
            $job->packages()->create($attributes);
        }

        return $job->load(['containers', 'packages']);
    }


    public function actions($id)
    {
        $quotation = Quotation::select(
            'id',
            'row_no',
            'customer_id',
            'posted_at',
            'valid_until',
            'shipment_mode',
            'shipment_category',
            'incoterm',
            'pol',
            'pod',
            'place_of_receipt',
            'place_of_delivery',
            'final_destination',
            'carrier',
            'salesperson_id',
            'status',
            'created_at'
        )->findOrFail($id);
        $contextMenu = collect([]);
        $edit = $delete = $cancelled = [];
        if ($quotation->status === QuotationEnum::PENDING->value) {
            $contextMenu->push([
                'label' => __('Accepted'),
                'code' => '01CSBK',
                'id' => 'row_accepted',
                'data-id' => $quotation->id,
                'data-value' => QuotationEnum::ACCEPTED->value,
                'type' => 'item',
                'icon' => 'convert',
            ]);
            $cancelled = [
                'label' => __('Cancelled'),
                'code' => '01CSRJ',
                'id' => 'row_rejected',
                'class' => 'row_rejected',
                'data-id' => $quotation->id,
                'data-value' => QuotationEnum::CANCELLED->value,
                'type' => 'item',
                'icon' => 'rejected'
            ];
            /*$contextMenu->push([
                'label' => __('Move to'),
                'type' => 'submenu',
                'separator' => 'after',
                'icon' => 'move_to',
                'items' => [
                    [
                        'label' => __('Accepted'),
                        'code' => '01CSBK',
                        'id' => 'row_accepted',
                        'data-id' => $quotation->id,
                        'data-value' => QuotationEnum::ACCEPTED->value,
                        'icon' => 'confirmed'
                    ],
                    [
                        'label' => __('Cancelled'),
                        'code' => '01CSRJ',
                        'id' => 'row_rejected',
                        'class' => 'row_rejected',
                        'data-id' => $quotation->id,
                        'data-value' => QuotationEnum::CANCELLED->value,
                        'icon' => 'rejected'
                    ]
                ]
            ]);*/
        } elseif ($quotation->status === QuotationEnum::fromName('accepted')) {
            /*$contextMenu->push([
                'label' => __('Move to'),
                'type' => 'submenu',
                'separator' => 'after',
                'icon' => 'move_to',
                'items' => [
                    [
                        'label' => __('Pending'),
                        'code' => '01CSRJ',
                        'id' => 'row_pending',
                        'class' => 'row_pending',
                        'data-id' => $quotation->id,
                        'data-value' => QuotationEnum::PENDING->value,
                        'icon' => 'pending'
                    ],
                    [
                        'label' => __('Cancelled'),
                        'code' => '01CSRJ',
                        'id' => 'row_rejected',
                        'class' => 'row_rejected',
                        'data-id' => $quotation->id,
                        'data-value' => QuotationEnum::CANCELLED->value,
                        'icon' => 'rejected'
                    ]
                ]
            ]);*/
            $contextMenu->push([
                'label' => __('Move to Pending'),
                'code' => '01CSRJ',
                'id' => 'row_pending',
                'class' => 'row_pending',
                'data-id' => $quotation->id,
                'data-value' => QuotationEnum::PENDING->value,
                'type' => 'item',
                'icon' => 'pending',
            ]);
            $contextMenu->push([
                'label' => __('Convert To Job'),
                'code' => '01INLI',
                'id' => 'row_convert_to_job',
                'data-id' => $quotation->id,
                'data-value' => QuotationEnum::CONVERTED->value,
                'type' => 'item',
                'icon' => 'convert',
            ]);
        }

        if ($quotation->status === QuotationEnum::PENDING->value) {
            $edit = [
                'label' => __('Edit'),
                'code' => '01CSED',
                'id' => 'row_edit',
                'class' => 'row_edit',
                'data-id' => $quotation->id,
                'type' => 'item',
                'icon' => 'edit'
            ];

            $contextMenu->push([
                'label' => __('Send Email'),
                'code' => '01CSEM',
                'id' => 'row_email',
                'data-id' => $quotation->id,
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
            'data-id' => $quotation->id,
            'type' => 'item',
            'icon' => 'print',
            'onclick' => 'QUOTATION.printPreview(' . $quotation->id . ')',
            //'separator' => 'before',
        ]);
        $contextMenu->push([
            'label' => __('View'),
            'code' => '01CSVW',
            'id' => 'row_view',
            'class' => 'row_view',
            'data-id' => $quotation->id,
            'type' => 'item',
            'icon' => 'view',
            //'separator' => 'before',
        ]);
        if ($quotation->status === QuotationEnum::PENDING->value) {
            $contextMenu->push([
                'label' => __('Actions'),
                'type' => 'submenu',
                'icon' => 'action',
                'items' => [$edit,$cancelled]
            ]);
        }
        return response()->json($contextMenu->values());
    }

    public function overview($id)
    {
        $quotation = Quotation::with(['containers', 'packages', 'customer', 'prospect'])->findOrFail($id);
        $quotation->party = $quotation->customer_id ? $quotation->customer : $quotation->prospect;
        return view('modules.quotation.view-overview', compact('quotation'));
    }

    public function overviewDrawer($id)
    {
        $quotation = Quotation::with(['containers', 'packages', 'charges', 'customer', 'prospect', 'activity'])->findOrFail($id);
        $quotation->party = $quotation->customer_id ? $quotation->customer : $quotation->prospect;
        return view('modules.quotation.view-overview-drawer', compact('quotation'));
    }

    public function print($id)
    {
        $quotation = Quotation::with(['containers', 'packages', 'customer', 'prospect'])->find($id);
        $quotation->party = $quotation->customer_id ? $quotation->customer : $quotation->prospect;
        return view('modules.quotation.view-overview', compact('quotation'));
    }

    /**
     * Get quotation email data
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQuotationEmailData($id)
    {
        $quotation = Quotation::with(['customer', 'prospect'])->findOrFail($id);

        $data = [
            'id' => $quotation->id,
            'to' => '',
            'cc' => Auth::user()->email
        ];

        // Set recipient email based on customer or prospect
        if ($quotation->customer_id) {
            $data['to'] = $quotation->customer->email;
        } elseif ($quotation->prospect_id) {
            $data['to'] = $quotation->prospect->email;
        }

        return response()->json($data);
    }

    /**
     * Send email
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendEmail(Request $request)
    {
        $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string',
            'body' => 'required|string',
        ]);

        $emailData = [
            'subject' => $request->subject,
            'body' => $request->body,
        ];

        // Handle attachments if any
        $attachments = [];
        if ($request->hasFile('attachment')) {
            foreach ($request->file('attachment') as $file) {
                $path = $file->store('email-attachments', 'public');
                $attachments[] = storage_path('app/public/' . $path);
            }
            $emailData['attachments'] = $attachments;
        }

        // Send email using queue
        Mail::to($request->to)
            ->cc($request->cc)
            ->queue(new QuotationMail($emailData));

        return response()->json([
            'success' => true,
            'message' => 'Email has been queued for sending.'
        ]);
    }
}

<?php

namespace App\Http\Controllers\Quotation;

use App\Enums\QuotationEnum;
use App\Http\Controllers\Controller;
use App\Mail\QuotationMail;
use App\Models\Customer\Customer;
use App\Models\Job\Job;
use App\Models\Master\LogisticActivity;
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
            $quotation->origin = $enquiry->origin ?? null;
            $quotation->destination = $enquiry->destination ?? null;
            $quotation->pol = $enquiry->pol ?? null;
            $quotation->pod = $enquiry->pod ?? null;
            $quotation->shipper = $enquiry->shipper ?? null;
            $quotation->place_of_receipt = $enquiry->place_of_receipt ?? null;
            $quotation->incoterm = $enquiry->incoterm ?? null;
            $quotation->volume = $enquiry->volume ?? null;
            $quotation->pickup_date = $enquiry->pickup_date ?? null;

            $enquiryData = $quotation->enquiry_id;
        }
        return view('modules.quotation.quotation-form', compact('polPod', 'quotation', 'enquiryData'));
    }

    public function edit($id)
    {
        $quotation = Quotation::with('containers', 'packages')->find($id);
        $polPod = preloadPOLAndPOD($quotation->shipment_type);
        return view('modules.quotation.quotation-form', compact('polPod', 'quotation'));
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

            // Containers (array)
            /*'container_number.*' => 'nullable|string|max:20',
            'seal_number.*'      => 'nullable|string|max:20',
            'container_size.*'   => 'required|string|in:20ft,40ft,40ft HC,45ft',
            'gross_weight.*'     => 'nullable|numeric|min:0|max:999999.99',
            'net_weight.*'       => 'nullable|numeric|min:0|max:999999.99',
            'volume.*'           => 'nullable|numeric|min:0|max:999999.99',
            'hazardous.*'        => 'nullable|in:Yes,No',*/

            // Packages (array)
            /*'hs_code.*'          => 'nullable|string|max:20',
            'description_goods.*'=> 'nullable|string|max:255',
            'commodity_type.*'   => 'required|string|in:General,Hazardous,Perishable,Reefer',
            'length.*'           => 'nullable|numeric|min:0|max:9999.99',
            'width.*'            => 'nullable|numeric|min:0|max:9999.99',
            'height.*'           => 'nullable|numeric|min:0|max:9999.99',
            'package_weight.*'   => 'nullable|numeric|min:0|max:999999.99',*/
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
        //$quotation->volume = $request->volume;
        $quotation->pickup_date = $request->pickup_date;
        $quotation->pickup_address = $request->pickup_address;


        $quotation->save();

        // ✅ Insert containers
        if (isset($request->container_size[0])) {
            $containers = [];
            foreach ($request->container_size as $index => $size) {
                $containers[] = [
                    'container_number' => $request->container_number[$index] ?? null,
                    'seal_number' => $request->seal_number[$index] ?? null,
                    'container_size' => $size,
                    'gross_weight' => $request->gross_weight[$index] ?? null,
                    'net_weight' => $request->net_weight[$index] ?? null,
                    'volume' => $request->volume[$index] ?? null,
                    'hazardous' => $request->hazardous[$index],
                    'quotation_id' => $quotation->id,
                ];
            }
            DB::table('quotation_containers')->where('quotation_id', $quotation->id)->delete();
            DB::table('quotation_containers')->insert($containers);
        }

        // ✅ Insert packages
        if (isset($request->commodity_type[0])) {
            $packages = [];
            foreach ($request->commodity_type as $index => $type) {
                $packages[] = [
                    'hs_code' => $request->hs_code[$index] ?? null,
                    'description_goods' => $request->description_goods[$index] ?? null,
                    'commodity_type' => $type,
                    'length' => $request->length[$index] ?? null,
                    'width' => $request->width[$index] ?? null,
                    'height' => $request->height[$index] ?? null,
                    'package_weight' => $request->package_weight[$index] ?? null,
                    'quotation_id' => $quotation->id,
                ];
            }
            DB::table('quotation_packages')->where('quotation_id', $quotation->id)->delete();
            DB::table('quotation_packages')->insert($packages);
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

        $rows = Quotation::select(
            'quotations.id as id',
            'quotations.row_no as row_no',
            'customer_id',
            'prospect_id',
            'posted_at',
            'services',
            'valid_until',
            'activity_id',
            'shipment_mode',
            'shipment_category',
            'incoterm',
            'pol',
            'pod',
            'place_of_receipt',
            'place_of_delivery',
            'final_destination',
            'carrier',
            'quotations.salesperson_id as salesperson_id',
            'quotations.company_id as company_id',
            'quotations.status as status',
            'quotations.created_at as created_at',
        )->with(['customer:id,name_en,name_ar,email,phone,row_no', 'prospect:id,name_en,email,phone,row_no', 'activity:id,name', 'salesperson:id,name'])
            ->where('quotations.status', QuotationEnum::fromName($request->tab))
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
            ->when(isset($filter['filter-pol']) && !empty($filter['filter-pol']), function ($query) use ($filter) {
                $query->where('pol', 'like', "%{$filter['filter-pol']}%");
            })
            ->when(isset($filter['filter-pod']) && !empty($filter['filter-pod']), function ($query) use ($filter) {
                $query->where('pod', 'like', "%{$filter['filter-pod']}%");
            })
            ->when(isset($filter['activity_id']) && !empty($filter['activity_id']), function ($query) use ($filter) {
                $query->where('activity_id', $filter['activity_id']);
            })
            ->orderByDesc('quotations.id');

        // Get counts per status
        $statusCounts = Quotation::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
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
            ->addColumn('name', function ($model) {
                if ($model->customer_id) {
                    return [
                        'name' => $model->customer->name_en,
                        'row_no' => $model->customer->row_no,
                    ];
                } else {
                    return [
                        'name' => $model->prospect->name_en,
                        'row_no' => $model->prospect->row_no,
                    ];
                }
            })
            ->editColumn('services', function ($model) {
                return getSelectedServices($model->services, true);
            })
            ->rawColumns(['status'])
            ->editColumn('created_at', fn($model) => Carbon::parse($model->created_at)->format('d-m-Y'))
            ->with([
                'statusCounts' => $allCounts,  // ✅ send to DataTables response
            ])
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
                    $customer->name_en = $prospectData->name_en;
                    $customer->name_ar = $prospectData->name_en;
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
        foreach ($quotation->containers as $c) {
            $job->containers()->create($c->toArray());
        }

        // Attach packages
        foreach ($quotation->packages as $p) {
            $job->packages()->create($p->toArray());
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
        $edit = $delete = [];
        if ($quotation->status === QuotationEnum::PENDING->value) {
            $contextMenu->push([
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
            ]);
        } elseif ($quotation->status === QuotationEnum::fromName('accepted')) {
            $contextMenu->push([
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
                'items' => [$edit]
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

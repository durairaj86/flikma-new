<?php

namespace App\Http\Controllers\Enquiry;

use App\Enums\EnquiryEnum;
use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use App\Models\Enquiry\Enquiry;
use App\Models\Enquiry\EnquirySub;
use App\Models\Master\LogisticActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Yajra\DataTables\Facades\DataTables;

class EnquiryController extends Controller
{
    public function modal(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $enquiry = new Enquiry();
        $polPod = preloadPOLAndPOD();
        return view('modules.enquiry.enquiry-form', compact('polPod', 'enquiry'));
    }

    public function edit($id)
    {
        $enquiry = Enquiry::find($id);
        $polPod = preloadPOLAndPOD($enquiry->shipment_type);

        return view('modules.enquiry.enquiry-form', compact('polPod', 'enquiry'));
    }

    public function store(Request $request)
    {
        $request->merge(['customer' => decodeId($request->input('customer'))]);
        $request->merge(['prospect' => decodeId($request->input('prospect'))]);
        $request->merge(['salesperson_id' => decodeId($request->input('salesperson_id'))]);
        // Validate request
        $validated = $request->validate([
            'customer' => 'nullable|required_without:prospect|exists:customers,id',

            // Prospect (required when customer is NOT selected)
            'prospect' => 'nullable|required_without:customer|exists:prospects,id',

            'company' => 'nullable|string|max:255',

            'shipment_category' => 'required|in:container,package',
            'pickup_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:pickup_date',
            'weight' => 'nullable|numeric|min:0',
            'volume' => 'nullable|numeric|min:0',
            'pol' => 'required|string|max:100',
            'pod' => 'required|string|max:100',
            'remark' => 'nullable|string',
            'activity_id' => 'required|string',

            /*'container_number.*' => 'required_if:shipment_category,container|string|max:50',
            'container_type.*' => 'required_if:shipment_category,container|string|max:50',
            'container_size.*' => 'required_if:shipment_category,container|string|max:50',
            'container_quantity.*' => 'required_if:shipment_category,container|integer|min:1',

            'package_type.*' => 'required_if:shipment_category,package|string|max:50',
            'length.*' => 'required_if:shipment_category,package|numeric|min:0',
            'width.*' => 'required_if:shipment_category,package|numeric|min:0',
            'height.*' => 'required_if:shipment_category,package|numeric|min:0',
            'weight.*' => 'required_if:shipment_category,package|numeric|min:0',*/
        ]);


        /*if ($request->filled('customer')) {
            $enquiryCustomer = Customer::findOrFail($request->customer);
        }*/

        $enquiryData = [
            'shipment_mode' => LogisticActivity::activities($request->activity_id)->pluck('mode')->first(),
            'shipment_category' => $request->shipment_category,
            'pickup_date' => $request->pickup_date,
            'expiry_date' => $request->expiry_date,
            'weight' => $request->weight,
            'volume' => $request->volume,
            'pol' => $request->pol,
            //'origin_city' => $request->origin_city,
            'pod' => $request->pod,
            //'destination_city' => $request->destination_city,
            'remark' => $request->remark,
            'salesperson_id' => $request->salesperson_id,
            'activity_id' => $request->activity_id,
            'customer_id' => $request->customer,
            'prospect_id' => $request->prospect,
            'shipper' => $request->shipper,
            'place_of_receipt' => $request->place_of_receipt,
            'incoterm' => $request->incoterm,
        ];

        /*$customerData = [];
        if ($request->has('prospect_name') && $request->prospect_name != '') {
            $customerData['customer_id'] = null;
            $customerData['prospect_name'] = $request->prospect_name;
            $customerData['prospect_phone'] = $request->prospect_phone;
        } else {
            $customerData['customer_id'] = $request->customer;
            $customerData['prospect_name'] = null;
            $customerData['prospect_phone'] = null;
        }

        $enquiryData = array_merge($enquiryData, $customerData);*/

        /*DB::beginTransaction();

        try {*/
        if (isset($request['data-id']) and filled($request['data-id'])) {
            $enquiry = Enquiry::findOrFail($request->input('data-id'));
            $enquiry->update($enquiryData);
        } else {
            $extraData['unique_row_no'] = sprintf("%03d", (Enquiry::max('unique_row_no') ?? 0) + 1);
            $extraData['row_no'] = 'ENQ' . $extraData['unique_row_no'];
            $extraData['user_id'] = Auth::id();
            $extraData['company_id'] = companyId();
            $enquiry = Enquiry::create(array_merge($enquiryData, $extraData));
        }

        DB::table('enquiry_subs')->where('enquiry_id', $enquiry->id)->delete();
        // 3️⃣ Insert Enquiry Items
        if ($request->shipment_category === 'container' && isset($request->container['type'])) {
            $containers = [];
            foreach ($request->container['type'] as $i => $type) {
                $containers[] = [
                    'enquiry_id' => $enquiry->id,
                    'type' => 'container',
                    'container_number' => $request->container['number'][$i] ?? null,
                    'container_type' => $type,
                    'container_size' => $request->container['size'][$i] ?? null,
                    'container_quantity' => $request->container['quantity'][$i] ?? null,
                    'container_hazardous' => $request->container['hazardous'][$i] ?? null,
                ];
            }
            DB::table('enquiry_subs')->insert($containers);
        } elseif ($request->shipment_category === 'package' && isset($request->package['type'])) {
            $packages = [];
            foreach ($request->package['type'] as $i => $type) {
                $packages[] = [
                    'enquiry_id' => $enquiry->id,
                    'type' => 'package',
                    'package_type' => $type,
                    'length' => $request->package['length'][$i] ?? null,
                    'width' => $request->package['width'][$i] ?? null,
                    'height' => $request->package['height'][$i] ?? null,
                    'weight' => $request->package['weight'][$i] ?? null,
                ];
            }
            DB::table('enquiry_subs')->insert($packages);
        }

        /*DB::commit();*/

        return response()->json([
            'status' => 'success',
            'message' => 'Enquiry created successfully',
            'customer_id' => $enquiry->id,
        ]);

        /*} catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save enquiry: ' . $e->getMessage()
            ]);
        }*/
    }

    public function fetchAllRows(Request $request): \Illuminate\Http\JsonResponse
    {
        $filter = $request->filterData ?? [];

        $rows = Enquiry::select(
            'id',
            'row_no',
            'unique_row_no',
            'customer_id',
            'prospect_id',
            'shipment_mode',
            'shipment_category',
            'pol',
            'pod',
            'pickup_date',
            'weight',
            'volume',
            'status',
            'activity_id',
            'expiry_date',
            'created_at',
            'company_id',
        )->with(['customer:id,name_en,name_ar,email,phone,row_no', 'prospect:id,name_en,email,phone,row_no', 'activity:id,name'])
            ->where('status', EnquiryEnum::fromName($request->tab))
            ->when(isset($filter['filter-from-date'], $filter['filter-to-date']),
                function ($query) use ($filter) {

                    $from = \Illuminate\Support\Carbon::parse($filter['filter-from-date'])->startOfDay();
                    $to = Carbon::parse($filter['filter-to-date'])->addDay()->startOfDay();

                    $query->where('created_at', '>=', $from)
                        ->where('created_at', '<', $to);
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
            ->orderbyDesc('unique_row_no');

        // Get counts per status in one query
        $statusCounts = Enquiry::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Normalize counts for all statuses (so missing ones appear as 0)
        $allCounts = [];
        foreach (EnquiryEnum::cases() as $status) {
            $allCounts[$status->name] = $statusCounts[$status->value] ?? 0;
        }

        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($model) => $model->id,
                'data-name' => fn($model) => $model->row_no,
                'class' => 'row-item',
                'id' => fn($model) => 'customer-' . strtolower($model->row_no ?? $model->id),
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
            ->addColumn('contact', function ($model) {
                if ($model->customer_id) {
                    return [
                        'email' => $model->customer->email,
                        'phone' => $model->customer->phone,
                    ];
                } else {
                    return [
                        'email' => $model->prospect->email,
                        'phone' => $model->prospect->phone,
                    ];
                }
            })
            /*->editColumn('customer_name', fn($model) => $model->customer_id ? $model->customer->name_en : $model->customer_name)
            ->editColumn('customer_email', fn($model) => $model->customer_id ? $model->customer->email : $model->customer_email)
            ->editColumn('customer_phone', fn($model) => $model->customer_id ? $model->customer->phone : $model->customer_phone)
            ->editColumn('activity_id', fn($model) => $model->activity->name)*/
            ->editColumn('status', function ($model) {
                return match ($model->status) {
                    1 => '<span class="badge bg-warning text-dark">Waiting</span>',
                    2 => '<span class="badge bg-success">Confirmed</span>',
                    3 => '<span class="badge bg-info text-dark">For Quotation</span>',
                    4 => '<span class="badge bg-danger">Cancelled</span>',
                    5 => '<span class="badge bg-primary">Quoted</span>',
                    default => '<span class="badge bg-secondary">Unknown</span>',
                };
            })->rawColumns(['status'])
            ->editColumn('created_at', fn($model) => Carbon::parse($model->created_at)->format('d-m-Y'))
            ->with([
                'statusCounts' => $allCounts,  // ✅ send to DataTables response
            ])
            ->toJson();
    }

    public function updateStatus($id, $status): \Illuminate\Http\JsonResponse
    {
        $enquiry = Enquiry::findOrFail($id);
        $enquiry->status = $status; // e.g., confirmed = 2
        if ($status == EnquiryEnum::CANCELLED->value) {
            $enquiry->remark = 'Customer Cancelled';
        }
        $enquiry->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Enquiry status updated successfully!',
            'data' => [
                'id' => $enquiry->id,
                'status' => $enquiry->status, // numeric (0,1,2..)
                'label' => EnquiryEnum::from($enquiry->status)->label(), // "Confirmed"
            ],
        ]);
    }

    public function actions($id)
    {
        $enquiry = Enquiry::select(
            'id',
            'customer_id',
            'prospect_id',
            'shipment_mode',
            'shipment_category',
            'pol',
            'pod',
            'pickup_date',
            'weight',
            'volume',
            'status',
            'remark'
        )->findOrFail($id);
        $contextMenu = collect([]);
        $edit = $delete = [];
        if ($enquiry->status === EnquiryEnum::PENDING->value) {
            $contextMenu->push([
                'label' => __('Move to'),
                'type' => 'submenu',
                'separator' => 'after',
                'icon' => 'move_to',
                'items' => [
                    [
                        'label' => __('Confirmed'),
                        'code' => '01CSBK',
                        'id' => 'row_confirmed',
                        'data-id' => $enquiry->id,
                        'data-value' => EnquiryEnum::CONFIRMED->value,
                        'icon' => 'confirmed'
                    ],
                    [
                        'label' => __('Cancelled'),
                        'code' => '01CSRJ',
                        'id' => 'row_rejected',
                        'class' => 'row_rejected',
                        'data-id' => $enquiry->id,
                        'data-value' => EnquiryEnum::CANCELLED->value,
                        'icon' => 'rejected'
                    ]
                ]
            ]);
        } elseif ($enquiry->status === EnquiryEnum::fromName('confirmed')) {
            $contextMenu->push([
                'label' => __('Convert to Quotation'),
                'code' => '01CSBK',
                'id' => 'row_quotation',
                'class' => 'row_quotation',
                'data-id' => $enquiry->id,
                'type' => 'item',
                'icon' => 'convert',
                'separator' => 'after',
                'onclick' => 'ENQUIRY.convertToQuotation(' . $enquiry->id . ')',
            ]);
        }

        if ($enquiry->status === EnquiryEnum::PENDING->value) {
            $edit = [
                'label' => __('Edit'),
                'code' => '01CSED',
                'id' => 'row_edit',
                'class' => 'row_edit',
                'data-id' => $enquiry->id,
                'type' => 'item',
                'icon' => 'edit'
            ];
            $contextMenu->push([
                'label' => __('Send Email'),
                'code' => '01CSEM',
                'id' => 'row_email',
                'data-id' => $enquiry->id,
                'type' => 'item',
                'icon' => 'email',
                'separator' => 'after',
            ]);
        }

        /*if ($enquiry->status === EnquiryEnum::QUOTATION->value) {
            $contextMenu->push([
                'label' => __('Find quotation from ' . $enquiry->customer_name),
                'code' => '01INLI',
                'id' => 'row_search',
                'data-id' => $enquiry->id,
                'type' => 'item',
                'icon' => 'search',
            ]);
        }*/


        $contextMenu->push([
            'label' => __('Print'),
            'code' => '01CSVW',
            'id' => 'row_print',
            'class' => 'row_print',
            'data-id' => $enquiry->id,
            'type' => 'item',
            'icon' => 'print',
            'onclick' => 'ENQUIRY.printPreview(' . $enquiry->id . ')',
            //'separator' => 'before',
        ]);
        $contextMenu->push([
            'label' => __('View'),
            'code' => '01CSVW',
            'id' => 'row_view',
            'class' => 'row_view',
            'data-id' => $enquiry->id,
            'type' => 'item',
            'icon' => 'view',
            //'separator' => 'before',
        ]);
        if ($enquiry->status === EnquiryEnum::PENDING->value) {
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
        $enquiry = Enquiry::with(['customer', 'enquirySubs'])->findOrFail($id);
        return view('modules.enquiry.view-overview', compact('enquiry'));
    }

    public function print($id)
    {
        $enquiry = Enquiry::find($id);
        return view('modules.enquiry.view-overview', compact('enquiry'));
    }

    /**
     * Get enquiry data for conversion to quotation
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEnquiryData($id)
    {
        $enquiry = Enquiry::with('enquirySubs', 'customer', 'prospect', 'activity')->find($id);

        if (!$enquiry) {
            return Response::json(['error' => 'Enquiry not found'], 404);
        }

        // Prepare data for quotation
        $data = [
            'customer_id' => $enquiry->customer_id,
            'prospect_id' => $enquiry->prospect_id,
            'prospect' => $enquiry->prospect ? [
                'id' => $enquiry->prospect->id,
                'name' => $enquiry->prospect->name_en,
                'row_no' => $enquiry->prospect->row_no,
            ] : null,
            'activity_id' => $enquiry->activity_id,
            'shipment_mode' => $enquiry->shipment_mode,
            'shipment_category' => $enquiry->shipment_category,
            'incoterm' => $enquiry->incoterm,
            'pol' => $enquiry->pol,
            'pod' => $enquiry->pod,
            'place_of_receipt' => $enquiry->place_of_receipt,
            'place_of_delivery' => $enquiry->place_of_delivery,
            'salesperson_id' => $enquiry->salesperson_id,
            'shipper' => $enquiry->shipper,
            'volume' => $enquiry->volume,
            'pickup_date' => $enquiry->pickup_date,
            'enquiry_id' => $enquiry->id,
            'enquiry_row_no' => $enquiry->row_no,
        ];

        return Response::json($data);
    }
}

<?php

namespace App\Http\Controllers\BL;

use App\Http\Controllers\Controller;
use App\Models\BL\SeawayBill;
use App\Models\BL\SeawayBillSub;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SeawayBillController extends Controller
{
    /**
     * Display the seaway bill form in a modal
     */
    public function modal(Request $request)
    {
        // Get any data needed for the form
        $jobs = \App\Models\Job\Job::orderBy('id', 'desc')->get();
        $descriptions = \App\Models\Master\Description::all();

        // Create an empty seaway bill object for the form
        $seawayBill = new SeawayBill();
        $seawayBill->job_id = $request->input('jobId');
        $seawayBill->seaway_bill_date = Carbon::today();
        $seawayBill->documents = collect([]);
        $seawayBill->seawayBillSubs = collect([]);
        $polPod = preloadPOLAndPOD('sea');

        return view('modules.bl.seaway.seaway-form', compact('seawayBill', 'jobs', 'descriptions', 'polPod'));
    }

    /**
     * Edit an existing seaway bill
     */
    public function edit($id)
    {
        // Fetch the seaway bill by ID with its related sub items
        $seawayBill = SeawayBill::with('seawayBillSubs', 'documents')->findOrFail($id);

        // Format dates for display
        $seawayBill->seaway_bill_date = Carbon::parse($seawayBill->seaway_bill_date)->format('Y-m-d');
        $seawayBill->delivery_date = Carbon::parse($seawayBill->delivery_date)->format('Y-m-d');

        if ($seawayBill->departure_time) {
            $seawayBill->departure_time = Carbon::parse($seawayBill->departure_time)->format('Y-m-d\TH:i');
        }

        if ($seawayBill->arrival_time) {
            $seawayBill->arrival_time = Carbon::parse($seawayBill->arrival_time)->format('Y-m-d\TH:i');
        }

        $jobs = \App\Models\Job\Job::orderBy('id', 'desc')->get();
        $descriptions = \App\Models\Master\Description::all();
        $polPod = preloadPOLAndPOD('air');

        return view('modules.bl.seaway.seaway-form', compact('seawayBill', 'jobs', 'descriptions', 'polPod'));
    }

    /**
     * Store a new seaway bill or update an existing one
     */
    public function store(Request $request)
    {
        $request->merge(['customer' => decodeId($request->input('customer'))]);
        // Validate the request data
        $validated = $request->validate([
            'job_id' => 'required|exists:jobs,id',
            'customer' => 'required|exists:customers,id',
            'seaway_bill_date' => 'required|date',
            'delivery_date' => 'required|date',
            'delivery_address' => 'required|string',
            'contact_person' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:50',
            'origin_port' => 'required|string|max:100',
            'destination_port' => 'required|string|max:100',
            'vessel_name' => 'nullable|string|max:100',
            'voyage_number' => 'nullable|string|max:50',
            'departure_time' => 'nullable|date',
            'arrival_time' => 'nullable|date',
            'shipment_type' => 'required|in:document,parcel,freight',
            'service_type' => 'required|in:standard,express,same_day',
            'payment_method' => 'required|in:prepaid,collect,third_party',
            'special_instructions' => 'nullable|string',
            'description_id' => 'required|array',
            'description_id.*' => 'required|exists:descriptions,id',
            'comment' => 'nullable|array',
            'comment.*' => 'nullable|string',
            'quantity' => 'required|array',
            'quantity.*' => 'required|numeric|min:0.01',
            'weight' => 'nullable|array',
            'weight.*' => 'nullable|numeric|min:0',
            'length' => 'nullable|array',
            'length.*' => 'nullable|numeric|min:0',
            'width' => 'nullable|array',
            'width.*' => 'nullable|numeric|min:0',
            'height' => 'nullable|array',
            'height.*' => 'nullable|numeric|min:0',
            'fragile' => 'nullable|array',
            'fragile.*' => 'nullable|boolean',
        ]);

        // Begin transaction
        DB::beginTransaction();

        try {
            // Check if we're updating an existing seaway bill or creating a new one
            $seawayBillId = $request->input('data-id');

            if ($seawayBillId) {
                // Update existing seaway bill
                $waybill = SeawayBill::findOrFail($seawayBillId);
                $waybill->update([
                    'job_id' => $request->input('job_id'),
                    'customer_id' => $request->input('customer_id'),
                    'seaway_bill_date' => $request->input('seaway_bill_date'),
                    'delivery_date' => $request->input('delivery_date'),
                    'delivery_address' => $request->input('delivery_address'),
                    'contact_person' => $request->input('contact_person'),
                    'contact_phone' => $request->input('contact_phone'),
                    'origin_port' => $request->input('origin_port'),
                    'destination_port' => $request->input('destination_port'),
                    'vessel_name' => $request->input('vessel_name'),
                    'voyage_number' => $request->input('voyage_number'),
                    'departure_time' => $request->input('departure_time'),
                    'arrival_time' => $request->input('arrival_time'),
                    'shipment_type' => $request->input('shipment_type'),
                    'service_type' => $request->input('service_type'),
                    'payment_method' => $request->input('payment_method'),
                    'special_instructions' => $request->input('special_instructions'),
                ]);

                // Delete existing seaway bill sub items to replace with new ones
                $waybill->seawayBillSubs()->delete();
            } else {
                // Create new seaway bill
                $waybill = new SeawayBill();
                $year = Carbon::parse($request->input('seaway_bill_date'))->format('y');
                $lastNo = SeawayBill::where('seaway_bill_date', $year . '%')->max('unique_row_no') ?? 0;
                $waybill->unique_row_no = $year . sprintf('%04d', $lastNo + 1);
                $waybill->row_no = 'SWB' . $waybill->unique_row_no;

                $this->setBaseColumns($waybill);

                $waybill->job_id = $request->input('job_id');
                $waybill->customer_id = $request->input('customer');
                $waybill->seaway_bill_date = $request->input('seaway_bill_date');
                $waybill->delivery_date = $request->input('delivery_date');
                $waybill->delivery_address = $request->input('delivery_address');
                $waybill->contact_person = $request->input('contact_person');
                $waybill->contact_phone = $request->input('contact_phone');
                $waybill->origin_port = $request->input('origin_port');
                $waybill->destination_port = $request->input('destination_port');
                $waybill->vessel_name = $request->input('vessel_name');
                $waybill->voyage_number = $request->input('voyage_number');
                $waybill->departure_time = $request->input('departure_time');
                $waybill->arrival_time = $request->input('arrival_time');
                $waybill->shipment_type = $request->input('shipment_type');
                $waybill->service_type = $request->input('service_type');
                $waybill->payment_method = $request->input('payment_method');
                $waybill->special_instructions = $request->input('special_instructions');
                $waybill->status = 'pending';
                $waybill->save();
            }

            // Process seaway bill sub items
            $descriptionIds = $request->input('description_id', []);
            $comments = $request->input('comment', []);
            $quantities = $request->input('quantity', []);
            $weights = $request->input('weight', []);
            $lengths = $request->input('length', []);
            $widths = $request->input('width', []);
            $heights = $request->input('height', []);
            $fragiles = $request->input('fragile', []);

            // Create seaway bill sub items
            for ($i = 0; $i < count($descriptionIds); $i++) {
                SeawayBillSub::create([
                    'seaway_bill_id' => $waybill->id,
                    'description_id' => $descriptionIds[$i],
                    'comment' => $comments[$i] ?? null,
                    'quantity' => $quantities[$i] ?? 1,
                    'weight' => $weights[$i] ?? 0,
                    'length' => $lengths[$i] ?? 0,
                    'width' => $widths[$i] ?? 0,
                    'height' => $heights[$i] ?? 0,
                    'fragile' => isset($fragiles[$i]) ? 1 : 0,
                ]);
            }

            // Handle file uploads if any
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('seaway_bill_attachments', 'public');
                    $waybill->documents()->create([
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'mime_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                        'title' => 'seawaybill',
                        'posted_at' => \Carbon\Carbon::now(),
                        'user_id' => Auth::id(),
                        'company_id' => companyId()
                    ]);
                }
            }

            // Commit transaction
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Seaway Bill saved successfully',
                'redirect' => '/bl/seaway'
            ]);
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Error saving seaway bill: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get seaway bill data for DataTables
     */
    public function fetchAllRows(Request $request)
    {
        $filterData = $request->input('filterData', []);
        $customSearch = $filterData['customSearch'] ?? '';
        $fromDate = $filterData['filter-from-date'] ?? null;
        $toDate = $filterData['filter-to-date'] ?? null;
        $customers = $filterData['customers'] ?? [];

        $query = SeawayBill::query()
            ->select([
                'seaway_bills.id',
                'seaway_bills.row_no',
                'customers.name_en as customer_name',
                'jobs.row_no as job_no',
                'jobs.pol',
                'jobs.pod',
                'seaway_bills.origin_port',
                'seaway_bills.destination_port',
                'seaway_bills.vessel_name',
                'seaway_bills.voyage_number',
                'seaway_bills.delivery_address',
                'seaway_bills.delivery_date',
                'seaway_bills.status',
                'seaway_bills.seaway_bill_date',
                'seaway_bills.company_id',
            ])
            ->leftJoin('jobs', 'seaway_bills.job_id', '=', 'jobs.id')
            ->leftJoin('customers', 'jobs.customer_id', '=', 'customers.id');

        // Apply date filters if provided
        if ($fromDate && $toDate) {
            $query->where('seaway_bills.seaway_bill_date', '>=', formDate($fromDate))
                ->where('seaway_bills.seaway_bill_date', '<=', formDate($toDate));
        }

        // Apply customer filter if provided
        if (!empty($customers)) {
            $query->whereIn('customers.name_en', (array)$customers);
        }

        // Apply search filter if provided
        if ($customSearch) {
            $query->where(function ($q) use ($customSearch) {
                $q->where('seaway_bills.row_no', 'like', '%' . $customSearch . '%')
                    ->orWhere('customers.name_en', 'like', '%' . $customSearch . '%')
                    ->orWhere('jobs.job_no', 'like', '%' . $customSearch . '%')
                    ->orWhere('jobs.pol', 'like', '%' . $customSearch . '%')
                    ->orWhere('jobs.pod', 'like', '%' . $customSearch . '%')
                    ->orWhere('seaway_bills.origin_port', 'like', '%' . $customSearch . '%')
                    ->orWhere('seaway_bills.destination_port', 'like', '%' . $customSearch . '%')
                    ->orWhere('seaway_bills.vessel_name', 'like', '%' . $customSearch . '%')
                    ->orWhere('seaway_bills.voyage_number', 'like', '%' . $customSearch . '%')
                    ->orWhere('seaway_bills.delivery_address', 'like', '%' . $customSearch . '%');
            });
        }

        $allCounts = [];

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($model) => $model->id,
                'data-name' => fn($model) => $model->row_no,
                'data-title' => fn($model) => $model->row_no,
                'class' => 'row-item',
                //'id' => fn($model) => 'airwaybill-' . strtolower($model->row_no ?? $model->id),
            ])
            ->rawColumns(['status'])
            ->editColumn('created_at', fn($model) => showDate($model->created_at))
            ->editColumn('airway_bill_date', fn($model) => showDate($model->airway_bill_date))
            ->editColumn('delivery_date', fn($model) => showDate($model->delivery_date))
            ->with([
                'statusCounts' => $allCounts,  // ✅ send to DataTables response
            ])
            ->toJson();

        // Get the data
        //$data = $query->get();

        // Get status counts
        /*$allCount = $data->count();
        $draftCount = $data->where('status', 'pending')->count();
        $approvedCount = $data->where('status', 'in_transit')->count() + $data->where('status', 'delivered')->count();
        $cancelledCount = $data->where('status', 'cancelled')->count();*/

        /*return response()->json([
            'data' => $data,
            'statusCounts' => [
                'allCount' => $allCount,
                'draftCount' => $draftCount,
                'approvedCount' => $approvedCount,
                'cancelledCount' => $cancelledCount
            ]
        ]);*/
    }

    public function actions($id)
    {
        $waybill = SeawayBill::select(
            'id',
            'row_no',
            'status'
        )->withTrashed()->findOrFail($id);
        $contextMenu = collect([]);
        $edit = $delete = [];

        if ($waybill->status === 'pending') {
            $contextMenu->push([
                'label' => __('Mark as In-Transit'),
                'code' => '01CSBK',
                'id' => 'row_in_transit',
                'data-id' => $waybill->id,
                'data-value' => 'in_transit',
                'type' => 'item',
                'icon' => 'move_to',
                'separator' => 'after',
            ]);
            /*'items' => [
                    [
                        'label' => __('In-Transit'),
                        'code' => '01CSBK',
                        'id' => 'row_in_transit',
                        'data-id' => $waybill->id,
                        'data-value' => 'in_transit',
                        'icon' => 'confirmed'
                    ],
                    [
                        'label' => __('Cancelled'),
                        'code' => '01CSRJ',
                        'id' => 'row_rejected',
                        'class' => 'row_rejected',
                        'data-id' => $waybill->id,
                        'data-value' => 'cancelled',
                        'icon' => 'rejected'
                    ]
                ]*/
        } elseif ($waybill->status === 'in_transit') {
            $contextMenu->push([
                'label' => __('Mark as Pending'),
                'code' => '01CSBK',
                'id' => 'row_pending',
                'data-id' => $waybill->id,
                'data-value' => 'pending',
                'type' => 'item',
                'icon' => 'pending',
            ], [
                'label' => __('Mark as Delivered'),
                'code' => '01CSBK',
                'id' => 'row_delivered',
                'data-id' => $waybill->id,
                'data-value' => 'delivered',
                'type' => 'item',
                'icon' => 'confirmed',
                'separator' => 'after',
            ]);
        }

        $contextMenu->push([
            'label' => __('Print'),
            'code' => '01CSVW',
            'id' => 'row_print',
            'class' => 'row_print',
            'data-id' => $waybill->id,
            'type' => 'item',
            'icon' => 'print',
            'onclick' => 'SEAWAY_BILL.printPreview(' . $waybill->id . ')',
            //'separator' => 'before',
        ]);
        $contextMenu->push([
            'label' => __('View'),
            'code' => '01CSVW',
            'id' => 'row_view',
            'class' => 'row_view',
            'data-id' => $waybill->id,
            'type' => 'item',
            'icon' => 'view',
            //'separator' => 'before',
        ]);
        if ($waybill->status === 'pending' || $waybill->status === 'in_transit') {
            $edit = [
                'label' => __('Edit'),
                'code' => '01CSED',
                'id' => 'row_edit',
                'class' => 'row_edit',
                'data-id' => $waybill->id,
                'type' => 'item',
                'icon' => 'edit'
            ];
            $delete = [
                'label' => __('Delete'),
                'code' => '01CSED',
                'id' => 'row_delete',
                'class' => 'row_delete',
                'data-id' => $waybill->id,
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
        $waybill = SeawayBill::findOrFail($id);
        $waybill->status = $status;
        $waybill->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Sea Waybill status updated successfully!',
            'data' => [
                'id' => $waybill->id,
                'status' => $waybill->status,
            ],
        ]);
    }

    /**
     * Display the seaway bill overview
     */
    public function overview($id)
    {
        $seawayBill = SeawayBill::with('seawayBillSubs', 'documents', 'job', 'customer')->findOrFail($id);
        return view('modules.bl.seaway.seaway-view', compact('seawayBill'));
    }

    /**
     * Print the seaway bill
     */
    public function print($id)
    {
        $seawayBill = SeawayBill::with('seawayBillSubs', 'documents', 'job', 'customer')->findOrFail($id);
        return view('modules.bl.seaway.print', compact('seawayBill'));
    }
}

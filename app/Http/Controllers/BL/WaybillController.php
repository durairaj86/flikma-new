<?php

namespace App\Http\Controllers\BL;

use App\Http\Controllers\Controller;
use App\Models\BL\Waybill;
use App\Models\BL\WaybillSub;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class WaybillController extends Controller
{
    /**
     * Display the waybill form in a modal
     */
    public function modal(Request $request)
    {
        // Get any data needed for the form
        $jobs = \App\Models\Job\Job::orderBy('id', 'desc')->get();
        $descriptions = \App\Models\Master\Description::all();

        // Create an empty waybill object for the form
        $waybill = new \stdClass();
        $waybill->id = null;
        $waybill->row_no = null;
        $waybill->job_id = $request->input('jobId');
        $waybill->waybill_date = Carbon::today();
        $waybill->delivery_date = '';
        $waybill->documents = collect([]);
        $waybill->waybillSubs = collect([]);

        return view('modules.bl.waybill.waybill-form', compact('waybill', 'jobs', 'descriptions'));
    }

    /**
     * Edit an existing waybill
     */
    public function edit($id)
    {
        // Fetch the waybill by ID with its related sub items
        $waybill = Waybill::with('waybillSubs', 'documents')->findOrFail($id);

        // Format dates for display
        $waybill->waybill_date = Carbon::parse($waybill->waybill_date)->format('Y-m-d');
        $waybill->delivery_date = Carbon::parse($waybill->delivery_date)->format('Y-m-d');

        $jobs = \App\Models\Job\Job::orderBy('id', 'desc')->get();
        $descriptions = \App\Models\Master\Description::all();

        return view('modules.bl.waybill.waybill-form', compact('waybill', 'jobs', 'descriptions'));
    }

    /**
     * Store a new waybill or update an existing one
     */
    public function store(Request $request)
    {
        $request->merge(['customer' => decodeId($request->input('customer'))]);
        // Validate the request data
        $validated = $request->validate([
            'job_id' => 'required|exists:jobs,id',
            'customer' => 'required|exists:customers,id',
            'waybill_date' => 'required|date',
            'delivery_date' => 'required|date',
            'delivery_address' => 'required|string',
            'contact_person' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:50',
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
            // Check if we're updating an existing waybill or creating a new one
            $waybillId = $request->input('data-id');

            if ($waybillId) {
                // Update existing waybill
                $waybill = Waybill::findOrFail($waybillId);
                $waybill->update([
                    'job_id' => $request->input('job_id'),
                    'customer_id' => $request->input('customer'),
                    'waybill_date' => formDate($request->input('waybill_date')),
                    'delivery_date' => formDate($request->input('delivery_date')),
                    'delivery_address' => $request->input('delivery_address'),
                    'contact_person' => $request->input('contact_person'),
                    'contact_phone' => $request->input('contact_phone'),
                    'shipment_type' => $request->input('shipment_type'),
                    'service_type' => $request->input('service_type'),
                    'payment_method' => $request->input('payment_method'),
                    'special_instructions' => $request->input('special_instructions'),
                ]);

                // Delete existing waybill sub items to replace with new ones
                $waybill->waybillSubs()->delete();
            } else {
                // Create new waybill
                $waybill = new Waybill();
                $year = Carbon::parse($request->input('waybill_date'))->format('y');
                $lastNo = Waybill::where('waybill_date', $year . '%')->max('unique_row_no') ?? 0;
                $waybill->unique_row_no = $year . sprintf('%04d', $lastNo + 1);
                $waybill->row_no = 'WB' . $waybill->unique_row_no;
                //$job->row_no = 'AL/AI/' . date('y') . '/' . sprintf('%04d', $job->unique_row_no);

                $this->setBaseColumns($waybill);
                $waybill->job_id = $request->input('job_id');
                $waybill->customer_id = $request->input('customer');
                $waybill->waybill_date = $request->input('waybill_date');
                $waybill->delivery_date = $request->input('delivery_date');
                $waybill->delivery_address = $request->input('delivery_address');
                $waybill->contact_person = $request->input('contact_person');
                $waybill->contact_phone = $request->input('contact_phone');
                $waybill->shipment_type = $request->input('shipment_type');
                $waybill->service_type = $request->input('service_type');
                $waybill->payment_method = $request->input('payment_method');
                $waybill->special_instructions = $request->input('special_instructions');
                $waybill->save();
            }

            // Process waybill sub items
            $descriptionIds = $request->input('description_id', []);
            $comments = $request->input('comment', []);
            $quantities = $request->input('quantity', []);
            $weights = $request->input('weight', []);
            $lengths = $request->input('length', []);
            $widths = $request->input('width', []);
            $heights = $request->input('height', []);
            $fragiles = $request->input('fragile', []);

            // Create waybill sub items
            for ($i = 0; $i < count($descriptionIds); $i++) {
                WaybillSub::create([
                    'waybill_id' => $waybill->id,
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
                    $path = $file->store('waybill_attachments', 'public');
                    $waybill->documents()->create([
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'mime_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                        'title' => 'waybill',
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
                'message' => 'Waybill saved successfully',
                //'redirect' => '/bl/waybill'
            ]);
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Error saving waybill: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get waybill data for DataTables
     */
    public function fetchAllRows(Request $request)
    {
        $filterData = $request->input('filterData', []);
        $customSearch = $filterData['customSearch'] ?? '';
        $fromDate = $filterData['filter-from-date'] ?? null;
        $toDate = $filterData['filter-to-date'] ?? null;
        $customers = $filterData['customers'] ?? [];

        $query = Waybill::select([
            'waybills.id',
            'waybills.row_no',
            'customers.name_en as customer_name',
            'jobs.row_no as job_no',
            'jobs.pol',
            'jobs.pod',
            'waybills.delivery_address',
            'waybills.delivery_date',
            'waybills.status',
            'waybills.waybill_date',
            'waybills.company_id',
        ])
            ->leftJoin('jobs', 'waybills.job_id', '=', 'jobs.id')
            ->leftJoin('customers', 'waybills.customer_id', '=', 'customers.id')
            ->where('waybills.company_id', companyId())
            ->where('waybills.deleted_at', null);

        // Apply date filters if provided
        if ($fromDate && $toDate) {
            $query->where('waybills.waybill_date', '>=', formDate($fromDate))
                ->where('waybills.waybill_date', '<=', formDate($toDate));
        }

        // Apply customer filter if provided
        if (!empty($customers)) {
            $query->whereIn('customers.name_en', (array)$customers);
        }

        // Apply search filter if provided
        if ($customSearch) {
            $query->where(function ($q) use ($customSearch) {
                $q->where('waybills.row_no', 'like', '%' . $customSearch . '%')
                    ->orWhere('customers.name_en', 'like', '%' . $customSearch . '%')
                    ->orWhere('job_no', 'like', '%' . $customSearch . '%')
                    ->orWhere('jobs.pol', 'like', '%' . $customSearch . '%')
                    ->orWhere('jobs.pod', 'like', '%' . $customSearch . '%')
                    ->orWhere('waybills.delivery_address', 'like', '%' . $customSearch . '%');
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
                //'id' => fn($model) => 'waybill-' . strtolower($model->row_no ?? $model->id),
            ])
            ->rawColumns(['status'])
            ->editColumn('created_at', fn($model) => showDate($model->created_at))
            ->editColumn('waybill_date', fn($model) => showDate($model->waybill_date))
            ->editColumn('delivery_date', fn($model) => showDate($model->delivery_date))
            ->with([
                'statusCounts' => $allCounts,  // ✅ send to DataTables response
            ])
            ->toJson();
    }

    public function actions($id)
    {
        $waybill = Waybill::select(
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
            'onclick' => 'WAYBILL.printPreview(' . $waybill->id . ')',
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
        $waybill = Waybill::findOrFail($id);
        $waybill->status = $status;
        $waybill->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Waybill status updated successfully!',
            'data' => [
                'id' => $waybill->id,
                'status' => $waybill->status,
            ],
        ]);
    }

    /**
     * Display the waybill overview
     */
    public function overview($id)
    {
        // In a real implementation, you would fetch the waybill by ID
        // For now, we'll just return a simple view
        return view('modules.bl.waybill.view-overview');
    }

    /**
     * Print the waybill
     */
    public function print($id)
    {
        // In a real implementation, you would fetch the waybill by ID
        // For now, we'll just return a simple view
        return view('modules.bl.waybill.print');
    }
}

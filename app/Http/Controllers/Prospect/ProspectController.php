<?php

namespace App\Http\Controllers\Prospect;

use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use App\Models\Enquiry\Enquiry;
use App\Models\Prospect\Prospect;
use App\Models\Quotation\Quotation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class ProspectController extends Controller
{
    public function fetchAllRows(Request $request): \Illuminate\Http\JsonResponse
    {
        $rows = Prospect::select(
            'id',
            'row_no',
            'name_en',
            'email',
            'phone',
            'salesperson_id',
            'created_at',
            'company_id'
        )->with('salesperson:id,name')->where('customer', '!=', 1);

        // Normalize counts for all statuses (so missing ones appear as 0)
        $allCounts = ['all' => $rows->count()];

        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($model) => $model->id,
                'data-name' => fn($model) => htmlspecialchars($model->name_en, ENT_QUOTES, 'UTF-8'),
                'class' => 'row-item',
                'id' => fn($model) => 'prospect-' . strtolower($model->row_no ?? $model->id),
            ])
            ->editColumn('created_at', fn($model) => Carbon::parse($model->created_at)->format('d-m-Y'))
            ->with([
                'statusCounts' => $allCounts,  // ✅ send to DataTables response
            ])
            ->toJson();
    }

    public function modal()
    {
        $prospect = new Prospect();
        return view('modules.prospect.prospect-form', compact('prospect'));
    }

    public function edit($id)
    {
        $prospect = Prospect::findOrFail($id);
        return view('modules.prospect.prospect-form', compact('prospect'));
    }

    public function quickModal(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        return view('modules.prospect.quick-prospect-form');
    }

    public function quickStore(Request $request): \Illuminate\Http\JsonResponse
    {
        if ($request->input('salesperson_id')) {
            $request->merge(['salesperson_id' => decodeId($request->input('salesperson_id'))]);
        }
        // Validation rules for quick prospect form
        $rules = [
            'quick_prospect_name' => 'required|string|max:255',
            'quick_prospect_email' => 'required|email|max:255',
            'quick_prospect_phone' => 'required|string|max:20',
            'quick_prospect_address' => 'nullable|string|max:500',
        ];

        // Validate request
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if (isset($request['data-id']) and filled($request['data-id'])) {
            $prospect = Prospect::findOrFail($request->input('data-id'));
        } else {
            // Create new customer
            $prospect = new Prospect();
            $prospect->unique_row_no = sprintf("%03d", (Prospect::max('unique_row_no') ?? 0) + 1);
            $prospect->row_no = 'PS' . $prospect->unique_row_no;

            $this->setBaseColumns($prospect);
        }

        // Set prospect data from quick form
        $prospect->name_en = $request->input('quick_prospect_name');
        $prospect->email = $request->input('quick_prospect_email');
        $prospect->phone = $request->input('quick_prospect_phone');
        $prospect->address = $request->input('quick_prospect_address');
        $prospect->salesperson_id = $request->input('salesperson_id');

        $prospect->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Prospect created successfully',
            'id' => encodeId($prospect->id),
            'name' => $prospect->name,
            'code' => $prospect->row_no,
        ]);
    }

    public function actions($id)
    {
        $prospect = Prospect::select('id')->where('customer', '!=', 1)->findOrFail($id);
        $contextMenu = collect([]);
        $edit = $delete = [];
        $contextMenu->push([
            'label' => __('Edit'),
            'code' => '01CSED',
            'id' => 'row_edit',
            'class' => 'row_edit',
            'data-id' => $prospect->id,
            'type' => 'item',
            'icon' => 'edit'
        ], [
            'label' => __('Delete'),
            'code' => '01CSED',
            'id' => 'row_delete',
            'class' => 'row_delete',
            'data-id' => $prospect->id,
            'type' => 'item',
            'icon' => 'delete'
        ]);
        return response()->json($contextMenu->values());
    }

    public function delete($id)
    {
        $prospect = Prospect::findOrFail($id);
        if (Enquiry::where('prospect_id', $id)->exists() ||
            Quotation::where('prospect_id', $id)->exists()) {

            return response()->json([
                'status' => 'warning',
                'message' => 'You cannot delete this prospect. It is linked with other modules!',
                'data' => ['id' => $prospect->id],
            ]);
        }

        $prospect->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Prospect deleted successfully.',
        ]);

    }
}

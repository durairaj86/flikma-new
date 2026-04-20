<?php

namespace App\Http\Controllers\Master\Salesperson;

use App\Http\Controllers\Controller;
use App\Models\Master\Salesperson\Salesperson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;
use Yajra\DataTables\Facades\DataTables;

class SalespersonController extends Controller
{
    private static $cache = 'activeSalesmen:';

    public function fetchAllRows()
    {
        $rows = Salesperson::select('id', 'name', 'status', 'user_id', 'company_id', 'created_at')->with('user:id,name');
        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($model) => $model->id,
                'data-name' => fn($model) => $model->name,
                'class' => 'row-item',
                'id' => fn($model) => 'salesperson-' . strtolower($model->row_no ?? $model->id),
            ])
            ->editColumn('user_id', function ($row) {
                return $row->user->name;
            })
            ->editColumn('created_at', function ($row) {
                return showDate($row->created_at);
            })
            ->editColumn('status', function ($row) {
                return $row->status == 1 ? 'Active' : 'Inactive';
            })
            ->toJson();
    }

    public function modal(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $salesperson = new Salesperson();

        return view('modules.master.salesperson.salesperson-form', [
            'salesperson' => $salesperson,
        ]);
    }

    public function edit($id)
    {
        $salesperson = Salesperson::find($id);
        return view('modules.master.salesperson.salesperson-form')->with('salesperson', $salesperson);
    }

    public function store(Request $request)
    {
        // Common validation rules
        $rules = [
            'name' => 'required|string|max:128',
        ];

        // Validate request
        $validated = $request->validate($rules);


        // Save customer
        if (isset($request['data-id']) and filled($request['data-id'])) {
            $salesperson = Salesperson::findOrFail($request->input('data-id'));
        } else {
            $salesperson = new Salesperson();
            $salesperson->status = 1;//default active
            $salesperson->unique_row_no = sprintf("%03d", (Salesperson::max('unique_row_no') ?? 0) + 1);
            $salesperson->row_no = 'SLE' . $salesperson->unique_row_no;
            $this->setBaseColumns($salesperson);
        }

        $salesperson->name = $request->input('name');
        $salesperson->save();
        Cache::forget(self::$cache . cacheName());
        return response()->json([
            'status' => 'success',
            'message' => 'Salesperson created successfully',
            'module_id' => $salesperson->id,
        ]);
    }

    public function actions($id)
    {
        $salesperson = Salesperson::select('id', 'status')->findOrFail($id);

        $contextMenu = collect([]);

        // Direct menu items

        if ($salesperson->status == 1) {
            $contextMenu->push([
                'label' => __('In-Active'),
                'code' => '01CSED',
                'id' => 'row_inactive',
                'class' => 'row_inactive',
                'data-id' => $salesperson->id,
                'data-value' => '0',
                'type' => 'item',
                'icon' => 'blocked'
            ]);
        } else {
            $contextMenu->push([
                'label' => __('Active'),
                'code' => '01CSED',
                'id' => 'row_active',
                'class' => 'row_active',
                'data-id' => $salesperson->id,
                'data-value' => '1',
                'type' => 'item',
                'icon' => 'confirmed'
            ]);
        }

        $contextMenu->push([
            'label' => __('Edit'),
            'code' => '01CSED',
            'id' => 'row_edit',
            'class' => 'row_edit',
            'data-id' => $salesperson->id,
            'type' => 'item',
            'icon' => 'edit'
        ], [
            'label' => __('Delete'),
            'code' => '01CSDL',
            'id' => 'row_delete',
            'class' => 'row_delete',
            'data-id' => $salesperson->id,
            'type' => 'item',
            'icon' => 'delete'
        ]);


        return response()->json($contextMenu->values());
    }

    public function updateStatus($id, $status): \Illuminate\Http\JsonResponse
    {
        $salesperson = Salesperson::findOrFail($id);
        $message = 'Salesperson status updated successfully!';
        if (in_array($status, [0, 1])) {
            DB::beginTransaction();
            try {
                $salesperson->update(['status' => $status]);
                Cache::forget(self::$cache . cacheName());
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                return errorResponse($e->getMessage());
            }
            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => [
                    'id' => $salesperson->id,
                    'status' => $salesperson->status,
                ],
            ]);
        }
    }
}

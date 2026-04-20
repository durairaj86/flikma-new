<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\LogisticServices;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class LogisticServiceController extends Controller
{
    public function fetchAllRows()
    {
        $rows = LogisticServices::select('id', 'category', 'service_en', 'service_ar', 'service_name_en', 'service_name_ar', 'code', 'description', 'company_id')->orderBy('service_name_en', 'asc');
        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => function ($model) {
                    return $model->id;
                    //return encryptId($model->id);
                },
                'data-name' => fn($model) => htmlspecialchars($model->service_name_en, ENT_QUOTES, 'UTF-8'),
                'class' => 'row-item',
                'id' => function ($model) {
                    return 'service-' . strtolower($model->row_no);
                }
            ])
            ->editColumn('created_at', function ($model) {
                return Carbon::parse($model->created_at)->format('d-m-Y');
            })
            ->toJson();
    }

    public function modal(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $logisticService = new LogisticServices();
        return view('modules.master.logistics-service.service-form')->with('logisticService', $logisticService);
    }

    public function edit($id)
    {
        $logisticService = LogisticServices::find($id);
        return view('modules.master.logistics-service.service-form')->with('logisticService', $logisticService);
    }

    public function store(Request $request)
    {
        // Common validation rules
        $rules = [
            'category' => 'required|string|max:255',
            'mode' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'service_en' => 'required|string|max:25',
            'service_ar' => 'required|string|max:25',
            'description' => 'nullable|string|max:128',
        ];

        $validated = $request->validate($rules);

        if (isset($request['data-id']) && filled($request['data-id'])) {
            $logisticService = LogisticServices::findOrFail($request->input('data-id'));
        } else {
            $logisticService = new LogisticServices();
            $this->setBaseColumns($logisticService);

            // Generate code: first letter of mode, category, and service_en
            $prefix = strtoupper(
                substr($request->mode, 0, 1) .
                substr($request->type, 0, 1) .
                substr($request->service_en, 0, 1)
            );

            // Ensure code is unique within company_id (or global if null)
            $companyId = auth()->user()->company_id ?? null;
            $baseCode = $prefix;
            $counter = 1;

            while (
            LogisticServices::where(function ($q) use ($companyId) {
                $q->whereNull('company_id')->orWhere('company_id', $companyId);
            })->where('code', $prefix)->exists()
            ) {
                $prefix = $baseCode . $counter++;
            }

            $logisticService->code = $prefix;
        }

// Assign fields
        $logisticService->company_id = auth()->user()->company_id ?? null;
        $logisticService->category = services($request->category,true);
        $logisticService->category_id = $request->category;
        $logisticService->mode = $request->mode;
        $logisticService->type = $request->type;
        $logisticService->service_en = $request->service_en;
        $logisticService->service_ar = $request->service_ar;
        $logisticService->service_name_en = ucfirst($request->mode) . ' ' . ucfirst($request->type) . ' ' . ucfirst($request->service_en);
        $logisticService->service_name_ar = ucfirst($request->mode) . ' ' . ucfirst($request->type) . ' ' . ucfirst($request->service_ar);
        $logisticService->description = $request->description ?? null;

        $logisticService->save();


        return response()->json([
            'status' => 'success',
            'message' => 'Customer created successfully',
            'customer_id' => $logisticService->id,
        ]);
    }

    public function actions($id)
    {
        $logisticService = LogisticServices::findOrFail($id);

        $contextMenu = collect([]);

        // Direct menu items

        $contextMenu->push([
            'label' => __('Edit'),
            'code' => '01CSED',
            'id' => 'row_edit',
            'class' => 'row_edit',
            'data-id' => $logisticService->id,
            'type' => 'item',
            'icon' => 'edit'
        ], [
            'label' => __('Delete'),
            'code' => '01CSDL',
            'id' => 'row_delete',
            'class' => 'row_delete',
            'data-id' => $logisticService->id,
            'type' => 'item',
            'icon' => 'delete'
        ]);


        return response()->json($contextMenu->values());
    }
}

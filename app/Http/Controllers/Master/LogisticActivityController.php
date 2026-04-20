<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\LogisticActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class LogisticActivityController extends Controller
{
    public function fetchAllRows()
    {
        $rows = LogisticActivity::select('id', 'name', 'code', 'type', 'service', 'company_id')->orderBy('name', 'asc')->orderBy('id', 'asc');
        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => function ($model) {
                    return $model->id;
                    //return encryptId($model->id);
                },
                'data-name' => fn($model) => htmlspecialchars($model->name, ENT_QUOTES, 'UTF-8'),
                'class' => 'row-item',
                'id' => function ($model) {
                    return 'activity-' . strtolower($model->row_no);
                }
            ])
            ->editColumn('created_at', function ($model) {
                return Carbon::parse($model->created_at)->format('d-m-Y');
            })
            ->toJson();
    }

    public function modal(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $logisticActivity = new LogisticActivity();
        return view('modules.master.logistics-activity.activity-form')->with('logisticActivity', $logisticActivity);
    }

    public function edit($id)
    {
        $logisticActivity = LogisticActivity::find($id);
        return view('modules.master.logistics-activity.activity-form')->with('logisticActivity', $logisticActivity);
    }

    public function store(Request $request)
    {
        $rules = [
            'mode' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'service_en' => 'required|string|max:25',
            'data-id' => 'nullable|exists:logistic_activities,id',
        ];

        $validated = $request->validate($rules);
        $companyId = auth()->user()->company_id ?? null;

        $logisticActivity = $request->filled('data-id')
            ? LogisticActivity::findOrFail($request->input('data-id'))
            : new LogisticActivity();

        // Only generate and check code for NEW records
        if (!$logisticActivity->exists) {
            $this->setBaseColumns($logisticActivity);
        }

        // 1. Generate the Code by taking the first letter of EVERY word in each field
        $generatedCode = strtoupper(
            $this->getFirstLetters($request->mode) .
            $this->getFirstLetters($request->type) .
            $this->getFirstLetters($request->service_en)
        );

// 2. Strict Uniqueness Check (Excluding the current record if updating)
        $exists = LogisticActivity::where('code', $generatedCode)
            ->where(function ($q) use ($companyId) {
                $q->where('company_id', $companyId)
                    ->orWhereNull('company_id');
            })
            ->when($logisticActivity->exists, function ($q) use ($logisticActivity) {
                // If updating, don't flag the record itself as a duplicate
                $q->where('id', '!=', $logisticActivity->id);
            })
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => "The code '{$generatedCode}' is already in use for this company."
            ], 422);
        }

        $logisticActivity->code = $generatedCode;

        // Assign/Update fields
        $logisticActivity->company_id = $companyId;
        $logisticActivity->mode = $request->mode;
        $logisticActivity->type = $request->type;
        $logisticActivity->service = $request->service_en;
        $logisticActivity->name = ucfirst($request->mode) . ' ' . ucfirst($request->type) . ' ' . ucfirst($request->service_en);

        $logisticActivity->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Activity processed successfully',
            'activity_id' => $logisticActivity->id,
        ]);
    }

    private function getFirstLetters($string)
    {
        if (!$string) return '';

        // Split by spaces or underscores, take the first letter of each part
        return collect(preg_split('/[\s_]+/', $string))
            ->map(fn($word) => substr($word, 0, 1))
            ->implode('');
    }

    public function actions($id)
    {
        $logisticActivity = LogisticActivity::findOrFail($id);

        $contextMenu = collect([]);

        // Direct menu items

        $contextMenu->push([
            'label' => __('Edit'),
            'code' => '01CSED',
            'id' => 'row_edit',
            'class' => 'row_edit',
            'data-id' => $logisticActivity->id,
            'type' => 'item',
            'icon' => 'edit'
        ], [
            'label' => __('Delete'),
            'code' => '01CSDL',
            'id' => 'row_delete',
            'class' => 'row_delete',
            'data-id' => $logisticActivity->id,
            'type' => 'item',
            'icon' => 'delete'
        ]);


        return response()->json($contextMenu->values());
    }
}

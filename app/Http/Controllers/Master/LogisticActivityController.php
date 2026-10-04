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
        $rows = LogisticActivity::select('id', 'name', 'code', 'mode', 'type', 'company_id')->orderBy('name', 'asc')->orderBy('id', 'asc');
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
            'name' => 'required|string|max:60',
            'mode' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'data-id' => 'nullable|exists:logistic_activities,id',
        ];

        $validated = $request->validate($rules);
        $companyId = auth()->user()->company_id ?? null;

        $logisticActivity = $request->filled('data-id')
            ? LogisticActivity::findOrFail($request->input('data-id'))
            : new LogisticActivity();

        if (!$logisticActivity->exists) {
            $this->setBaseColumns($logisticActivity);
        }

        $name = trim(preg_replace('/\s+/', ' ', $validated['name']));

        // A department name (e.g. "FCL Export") is unique within the company.
        $sameName = LogisticActivity::whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->where(function ($q) use ($companyId) {
                $q->where('company_id', $companyId)->orWhereNull('company_id');
            })
            ->when($logisticActivity->exists, fn ($q) => $q->where('id', '!=', $logisticActivity->id))
            ->exists();
        if ($sameName) {
            return response()->json([
                'status' => 'error',
                'message' => "A department named '{$name}' already exists.",
            ], 422);
        }

        // Code: initials of the name (FCL Export -> FE); a numeric suffix keeps it unique.
        if (!$logisticActivity->exists || blank($logisticActivity->code)) {
            $base = strtoupper($this->getFirstLetters($name)) ?: 'DEP';
            $code = $base;
            $i = 1;
            while (LogisticActivity::where('code', $code)
                ->where(function ($q) use ($companyId) {
                    $q->where('company_id', $companyId)->orWhereNull('company_id');
                })
                ->when($logisticActivity->exists, fn ($q) => $q->where('id', '!=', $logisticActivity->id))
                ->exists()) {
                $code = $base . (++$i);
            }
            $logisticActivity->code = $code;
        }

        $logisticActivity->company_id = $logisticActivity->exists ? $logisticActivity->company_id : $companyId;
        $logisticActivity->name = $name;
        $logisticActivity->mode = $validated['mode'];
        $logisticActivity->type = $validated['type'];
        // The old "service" column is no longer part of a department; it stays empty (NOT NULL in the table).
        $logisticActivity->service = $logisticActivity->service ?? '';
        $logisticActivity->save();

        \Illuminate\Support\Facades\Cache::forget('activities:' . cacheName());

        return response()->json([
            'status' => 'success',
            'message' => 'Department saved successfully',
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

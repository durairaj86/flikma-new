<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\PeriodClosing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class PeriodClosingController extends Controller
{
    public function fetchAllRows()
    {
        $rows = PeriodClosing::select('id', 'year', 'closing_date', 'notes', 'is_closed', 'closed_at', 'closed_by');

        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($model) => $model->id,
                'class' => 'row-item',
                'id' => fn($model) => 'period-closing-' . $model->id,
            ])
            ->editColumn('closing_date', function ($row) {
                return $row->closing_date->format('d-m-Y');
            })
            ->editColumn('is_closed', function ($row) {
                return $row->is_closed
                    ? '<span class="badge bg-danger-subtle text-danger"><i class="bi bi-lock-fill me-1"></i>Closed</span>'
                    : '<span class="badge bg-success-subtle text-success"><i class="bi bi-unlock-fill me-1"></i>Open</span>';
            })
            ->rawColumns(['is_closed'])
            ->toJson();
    }

    public function modal()
    {
        $periodClosing = new PeriodClosing();
        return view('modules.master.period-closing.period-closing-form', compact('periodClosing'));
    }

    public function edit($id)
    {
        $periodClosing = PeriodClosing::findOrFail($id);
        return view('modules.master.period-closing.period-closing-form', compact('periodClosing'));
    }

    public function store(Request $request)
    {
        $id = filled($request->input('data-id')) ? $request->input('data-id') : null;
        $companyId = companyId();

        // A closed period's cutoff date is load-bearing for every locked
        // transaction check — don't let it move under a user's feet.
        if ($id && PeriodClosing::findOrFail($id)->is_closed) {
            return response()->json([
                'status' => 'error',
                'message' => 'This period is closed. Reopen it first to make changes.',
            ], 422);
        }

        $rules = [
            'year' => [
                'required',
                'integer',
                'min:2000',
                'max:2100',
                Rule::unique('period_closings', 'year')->where('company_id', $companyId)->ignore($id),
            ],
            'closing_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        $validated = $request->validate($rules);

        if ($id) {
            $periodClosing = PeriodClosing::findOrFail($id);
        } else {
            $periodClosing = new PeriodClosing();
            $this->setBaseColumns($periodClosing);
        }

        $periodClosing->year = $validated['year'];
        $periodClosing->closing_date = $validated['closing_date'];
        $periodClosing->notes = $validated['notes'] ?? null;
        $periodClosing->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Period saved successfully',
            'module_id' => $periodClosing->id,
        ]);
    }

    public function actions($id)
    {
        $periodClosing = PeriodClosing::select('id', 'is_closed')->findOrFail($id);

        $contextMenu = collect([]);

        if (!$periodClosing->is_closed) {
            $contextMenu->push([
                'label' => __('Edit'),
                'code' => '01CSED',
                'id' => 'row_edit',
                'class' => 'row_edit',
                'data-id' => $periodClosing->id,
                'type' => 'item',
                'icon' => 'edit',
            ], [
                'label' => __('Close Period'),
                'code' => '01CSCL',
                'id' => 'row_close',
                'class' => 'row_close',
                'data-id' => $periodClosing->id,
                'type' => 'item',
                'icon' => 'lock',
            ], [
                'label' => __('Delete'),
                'code' => '01CSDL',
                'id' => 'row_delete',
                'class' => 'row_delete',
                'data-id' => $periodClosing->id,
                'type' => 'item',
                'icon' => 'delete',
            ]);
        } else {
            $contextMenu->push([
                'label' => __('Reopen Period'),
                'code' => '01CSRO',
                'id' => 'row_reopen',
                'class' => 'row_reopen',
                'data-id' => $periodClosing->id,
                'type' => 'item',
                'icon' => 'unlock',
            ]);
        }

        return response()->json($contextMenu->values());
    }

    public function close($id)
    {
        $periodClosing = PeriodClosing::findOrFail($id);
        $periodClosing->is_closed = true;
        $periodClosing->closed_by = Auth::id();
        $periodClosing->closed_at = now();
        $periodClosing->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Period ' . $periodClosing->year . ' closed. All transactions on or before ' . $periodClosing->closing_date->format('d-m-Y') . ' are now locked.',
        ]);
    }

    public function reopen($id)
    {
        $periodClosing = PeriodClosing::findOrFail($id);
        $periodClosing->is_closed = false;
        $periodClosing->closed_by = null;
        $periodClosing->closed_at = null;
        $periodClosing->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Period ' . $periodClosing->year . ' reopened.',
        ]);
    }

    public function destroy($id)
    {
        $periodClosing = PeriodClosing::findOrFail($id);

        if ($periodClosing->is_closed) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete a closed period. Reopen it first.',
            ], 422);
        }

        $periodClosing->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Period deleted successfully',
        ]);
    }
}

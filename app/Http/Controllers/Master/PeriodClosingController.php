<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Log\LogHistory;
use App\Models\Master\PeriodClosing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class PeriodClosingController extends Controller
{
    public function fetchAllRows()
    {
        // company_id: the list's ⋮ menu (Edit / Close / Reopen / Delete / History) shows only on the company's own rows
        $rows = PeriodClosing::select('id', 'company_id', 'year', 'closing_date', 'notes', 'is_closed', 'closed_at', 'closed_by');

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
                    ? '<span class="badge bg-danger-subtle text-danger"><i class="bi bi-lock-fill me-1"></i>' . __('Closed') . '</span>'
                    : '<span class="badge bg-success-subtle text-success"><i class="bi bi-unlock-fill me-1"></i>' . __('Open') . '</span>';
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
                'message' => __('This period is closed. Reopen it first to make changes.'),
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
        $periodClosing->closing_date = formDate($validated['closing_date']);
        $periodClosing->notes = $validated['notes'] ?? null;
        $isNew = !$periodClosing->exists;
        $changes = $isNew ? null : $this->changedFields($periodClosing);
        $periodClosing->save();

        if ($isNew || $changes) {
            $this->logHistory($periodClosing, $isNew ? 'created' : 'updated', $changes);
        }

        return response()->json([
            'status' => 'success',
            'message' => __('Period saved successfully'),
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

        $contextMenu->push([
            'label' => __('History'),
            'code' => '01CSHI',
            'id' => 'row_history',
            'class' => 'row_history',
            'data-id' => $periodClosing->id,
            'type' => 'item',
            'icon' => 'history',
        ]);

        return response()->json($contextMenu->values());
    }

    public function close($id)
    {
        $periodClosing = PeriodClosing::findOrFail($id);

        if ($periodClosing->is_closed) {
            return response()->json(['status' => 'error', 'message' => __('This period is already closed.')], 422);
        }

        $periodClosing->is_closed = true;
        $periodClosing->closed_by = Auth::id();
        $periodClosing->closed_at = now();
        $periodClosing->save();
        $this->logHistory($periodClosing, 'closed');

        return response()->json([
            'status' => 'success',
            'message' => __('Period :year closed. All transactions on or before :date are now locked.', [
                'year' => $periodClosing->year, 'date' => $periodClosing->closing_date->format('d-m-Y'),
            ]),
        ]);
    }

    public function reopen($id)
    {
        $periodClosing = PeriodClosing::findOrFail($id);

        if (!$periodClosing->is_closed) {
            return response()->json(['status' => 'error', 'message' => __('This period is already open.')], 422);
        }

        $periodClosing->is_closed = false;
        $periodClosing->closed_by = null;
        $periodClosing->closed_at = null;
        $periodClosing->save();
        $this->logHistory($periodClosing, 'reopened');

        return response()->json([
            'status' => 'success',
            'message' => __('Period :year reopened.', ['year' => $periodClosing->year]),
        ]);
    }

    public function destroy($id)
    {
        $periodClosing = PeriodClosing::findOrFail($id);

        if ($periodClosing->is_closed) {
            return response()->json([
                'status' => 'error',
                'message' => __('Cannot delete a closed period. Reopen it first.'),
            ], 422);
        }

        $periodClosing->delete();
        $this->logHistory($periodClosing, 'deleted');

        return response()->json([
            'status' => 'success',
            'message' => __('Period deleted successfully'),
        ]);
    }

    /** History of one period, or of every period when $id is null (deleted periods too: their history stays). */
    public function history($id = null)
    {
        $logs = LogHistory::where('loggable_type', PeriodClosing::class)
            ->when($id, fn ($query) => $query->where('loggable_id', $id))
            ->orderByDesc('created_at')->orderByDesc('id')
            ->limit(500)
            ->get();

        $title = $id ? ($logs->first()->loggable_number ?? PeriodClosing::find($id)?->year) : null;

        return view('modules.master.period-closing.history', compact('logs', 'title'));
    }

    /** Fields the user changed, with old and new values, before the save. */
    private function changedFields(PeriodClosing $periodClosing): ?array
    {
        $changes = [];
        foreach ($periodClosing->getDirty() as $field => $new) {
            if (!in_array($field, ['year', 'closing_date', 'notes'], true)) continue;
            $old = $periodClosing->getOriginal($field);
            $old = $old instanceof \DateTimeInterface ? $old->format('d-m-Y') : $old;
            $new = $periodClosing->{$field} instanceof \DateTimeInterface ? $periodClosing->{$field}->format('d-m-Y') : $new;
            if ((string) $old !== (string) $new) $changes[$field] = ['old' => $old, 'new' => $new];
        }

        return $changes ?: null;
    }

    /** One line of the period's history (created / updated / closed / reopened / deleted) with who did it and the period as it stood. */
    private function logHistory(PeriodClosing $periodClosing, string $action, ?array $changes = null): void
    {
        $user = Auth::user();

        LogHistory::create([
            'company_id' => $periodClosing->company_id ?? companyId(),
            'loggable_type' => PeriodClosing::class,
            'loggable_id' => $periodClosing->id,
            'loggable_number' => (string) $periodClosing->year,
            'loggable_name' => $periodClosing->closing_date?->format('d-m-Y'),
            'user_id' => ['id' => $user?->id, 'name' => $user?->name],
            'action' => $action,
            'changes' => $changes,
        ]);
    }
}

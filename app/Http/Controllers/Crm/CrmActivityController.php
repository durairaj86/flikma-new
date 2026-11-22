<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Crm\CrmActivity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

/** Activities (calls, meetings, notes ...) on a prospect / customer / enquiry / quotation, and the follow-ups list built from them. */
class CrmActivityController extends Controller
{
    /** Follow-ups list: Overdue / Today / Upcoming / Done tabs, mine or everyone's. */
    public function fetchAllRows(Request $request): \Illuminate\Http\JsonResponse
    {
        $tab = $request->input('tab', 'overdue');
        $today = CrmActivity::localNowNaive()->startOfDay();
        $base = CrmActivity::query()->whereNotNull('follow_up_at')
            ->when($request->input('owner', 'mine') === 'mine', fn($q) => $q->where('assigned_to', auth()->id()))
            ->when($request->filled('type'), fn($q) => $q->where('type', $request->input('type')));

        $counts = [
            'overdue' => (clone $base)->whereNull('follow_up_done_at')->where('follow_up_at', '<', $today)->count(),
            'today' => (clone $base)->whereNull('follow_up_done_at')->whereDate('follow_up_at', $today)->count(),
            'upcoming' => (clone $base)->whereNull('follow_up_done_at')->where('follow_up_at', '>=', $today->copy()->addDay())->count(),
            'done' => (clone $base)->whereNotNull('follow_up_done_at')->count(),
        ];

        $rows = (clone $base)->with('assignee:id,name')
            ->select('id', 'subject_type', 'subject_id', 'type', 'notes', 'activity_at', 'follow_up_at', 'follow_up_note', 'assigned_to', 'follow_up_done_at', 'company_id');
        match ($tab) {
            'today' => $rows->whereNull('follow_up_done_at')->whereDate('follow_up_at', $today),
            'upcoming' => $rows->whereNull('follow_up_done_at')->where('follow_up_at', '>=', $today->copy()->addDay()),
            'done' => $rows->whereNotNull('follow_up_done_at'),
            default => $rows->whereNull('follow_up_done_at')->where('follow_up_at', '<', $today),
        };

        $names = [];

        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($model) => $model->id,
                'data-subject-type' => fn($model) => $model->subject_type,
                'data-subject-id' => fn($model) => $model->subject_id,
                'class' => 'row-item',
            ])
            ->filter(function ($query) use ($request) {
                $term = trim((string) $request->input('search.value'));
                if ($term !== '') {
                    $query->where(fn($q) => $q->where('notes', 'like', "%{$term}%")->orWhere('follow_up_note', 'like', "%{$term}%"));
                }
            })
            ->addColumn('subject', function ($row) use (&$names) {
                $names[$row->subject_type] ??= [];
                if (!array_key_exists($row->subject_id, $names[$row->subject_type])) {
                    $names[$row->subject_type] += CrmActivity::describeSubjects($row->subject_type, [$row->subject_id]) + [$row->subject_id => null];
                }
                $s = $names[$row->subject_type][$row->subject_id];

                return [
                    'kind' => CrmActivity::subjectLabel($row->subject_type),
                    'no' => $s['no'] ?? null,
                    'name' => $s['name'] ?? __('(deleted)'),
                ];
            })
            ->editColumn('type', fn($row) => CrmActivity::typeLabel($row->type))
            ->addColumn('type_icon', fn($row) => CrmActivity::TYPES[$row->type]['icon'] ?? 'bi-dot')
            ->editColumn('follow_up_at', fn($row) => $row->follow_up_at?->format('d-m-Y H:i'))
            ->addColumn('overdue_days', fn($row) => !$row->follow_up_done_at && $row->follow_up_at < $today ? (int) $row->follow_up_at->copy()->startOfDay()->diffInDays($today) : 0)
            ->editColumn('follow_up_done_at', fn($row) => $row->follow_up_done_at?->format('d-m-Y H:i'))
            ->editColumn('activity_at', fn($row) => $row->activity_at?->format('d-m-Y H:i'))
            ->addColumn('assignee_name', fn($row) => $row->assignee->name ?? '')
            ->with(['statusCounts' => $counts])
            ->toJson();
    }

    public function modal(Request $request)
    {
        $activity = new CrmActivity();
        $activity->type = 'call';
        $activity->activity_at = CrmActivity::localNowNaive();
        $activity->assigned_to = auth()->id();
        $activity->subject_type = $request->input('subject_type');
        $activity->subject_id = $request->input('subject_id');
        $lockedSubject = $this->lockedSubject($activity);

        return view('modules.crm.activity-form', $this->formData($activity, $lockedSubject));
    }

    public function edit($id)
    {
        $activity = CrmActivity::findOrFail($id);

        return view('modules.crm.activity-form', $this->formData($activity, $this->lockedSubject($activity)));
    }

    public function store(Request $request)
    {
        $id = $request->input('data-id') ?: null;
        $request->validate([
            'subject_type' => ['required', Rule::in(array_keys(CrmActivity::SUBJECTS))],
            'subject_id' => ['required', 'integer'],
            'type' => ['required', Rule::in(array_keys(CrmActivity::TYPES))],
            'notes' => ['required', 'string', 'max:5000'],
            'activity_date' => ['required', 'date_format:d-m-Y'],
            'activity_time' => ['nullable', 'date_format:H:i'],
            'follow_up_date' => ['nullable', 'date_format:d-m-Y'],
            'follow_up_time' => ['nullable', 'date_format:H:i'],
            'follow_up_note' => ['nullable', 'string', 'max:255'],
            'assigned_to' => ['nullable', 'integer'],
        ], [], [
            'notes' => __('What happened'),
            'activity_date' => __('Date'),
            'follow_up_date' => __('Follow-up date'),
        ]);

        $subjectClass = CrmActivity::SUBJECTS[$request->input('subject_type')];
        if (!$subjectClass::whereKey((int) $request->input('subject_id'))->exists()) {
            throw ValidationException::withMessages(['subject_id' => __('Choose who this activity is with.')]);
        }

        $activityAt = Carbon::createFromFormat('d-m-Y H:i', $request->input('activity_date') . ' ' . ($request->input('activity_time') ?: '00:00'));
        if ($activityAt->isAfter(CrmActivity::localNowNaive()->endOfDay())) {
            throw ValidationException::withMessages(['activity_date' => __('An activity is something that already happened; use the follow-up for a future date.')]);
        }

        $followUpAt = null;
        $assignee = null;
        if ($request->filled('follow_up_date')) {
            $followUpAt = Carbon::createFromFormat('d-m-Y H:i', $request->input('follow_up_date') . ' ' . ($request->input('follow_up_time') ?: '09:00'));
            if ($followUpAt->lt($activityAt->copy()->startOfDay())) {
                throw ValidationException::withMessages(['follow_up_date' => __('The follow-up cannot be before the activity.')]);
            }
            $assignee = (int) ($request->input('assigned_to') ?: auth()->id());
            if (!User::where('company_id', companyId())->whereKey($assignee)->exists()) {
                throw ValidationException::withMessages(['assigned_to' => __('Choose a user of this company.')]);
            }
        }

        if ($id) {
            $activity = CrmActivity::findOrFail($id);
        } else {
            $activity = new CrmActivity();
            $this->setBaseColumns($activity);
        }
        $activity->subject_type = $request->input('subject_type');
        $activity->subject_id = (int) $request->input('subject_id');
        $activity->type = $request->input('type');
        $activity->notes = trim($request->input('notes'));
        $activity->activity_at = $activityAt;
        // a changed follow-up date is a new reminder
        if (!$followUpAt || !$activity->follow_up_at || !$activity->follow_up_at->equalTo($followUpAt)) {
            $activity->reminder_sent_at = null;
        }
        $activity->follow_up_at = $followUpAt;
        $activity->follow_up_note = $followUpAt ? ($request->input('follow_up_note') ?: null) : null;
        $activity->assigned_to = $assignee;
        if (!$followUpAt) {
            $activity->follow_up_done_at = null;
        }
        $activity->save();

        return response()->json([
            'status' => 'success',
            'message' => __('Activity saved successfully'),
            'module_id' => $activity->id,
            'subject_type' => $activity->subject_type,
            'subject_id' => $activity->subject_id,
        ]);
    }

    public function actions($id)
    {
        $activity = CrmActivity::select('id', 'follow_up_at', 'follow_up_done_at')->findOrFail($id);
        $menu = collect();
        if ($activity->follow_up_at && !$activity->follow_up_done_at) {
            $menu->push(['label' => __('Mark as Done'), 'code' => '01CSED', 'id' => 'row_done', 'data-id' => $activity->id, 'type' => 'item', 'icon' => 'confirmed']);
        } elseif ($activity->follow_up_done_at) {
            $menu->push(['label' => __('Reopen'), 'code' => '01CSED', 'id' => 'row_undone', 'data-id' => $activity->id, 'type' => 'item', 'icon' => 'move_to']);
        }
        $menu->push(
            ['label' => __('Log Activity'), 'code' => '01CSED', 'id' => 'row_log_activity', 'data-id' => $activity->id, 'type' => 'item', 'icon' => 'add'],
            ['label' => __('Open'), 'code' => '01CSED', 'id' => 'row_open', 'data-id' => $activity->id, 'type' => 'item', 'icon' => 'view'],
            ['label' => __('Edit'), 'code' => '01CSED', 'id' => 'row_edit', 'class' => 'row_edit', 'data-id' => $activity->id, 'type' => 'item', 'icon' => 'edit'],
            ['label' => __('Delete'), 'code' => '01CSDL', 'id' => 'row_delete', 'class' => 'row_delete', 'data-id' => $activity->id, 'type' => 'item', 'icon' => 'delete'],
        );

        return response()->json($menu->values());
    }

    /** Tick the follow-up off (or open it again). */
    public function done(Request $request, $id)
    {
        $activity = CrmActivity::findOrFail($id);
        if (!$activity->follow_up_at) {
            return response()->json(['status' => 'error', 'message' => __('This activity has no follow-up.')], 422);
        }
        $activity->follow_up_done_at = $request->boolean('undo') ? null : CrmActivity::localNowNaive();
        $activity->save();

        return response()->json([
            'status' => 'success',
            'message' => $activity->follow_up_done_at ? __('Follow-up done') : __('Follow-up reopened'),
            'subject_type' => $activity->subject_type,
            'subject_id' => $activity->subject_id,
        ]);
    }

    public function destroy($id)
    {
        CrmActivity::findOrFail($id)->delete();

        return response()->json(['status' => 'success', 'message' => __('Activity deleted successfully')]);
    }

    /** Recent records of one kind for the "With" picker: [{id, text}]. */
    public function subjects(Request $request)
    {
        $type = $request->input('type');
        abort_unless(isset(CrmActivity::SUBJECTS[$type]), 404);
        $class = CrmActivity::SUBJECTS[$type];
        $ids = $class::query()->latest('id')->limit(300)->pluck('id')->all();
        $names = CrmActivity::describeSubjects($type, $ids);

        return response()->json(collect($ids)->map(fn($id) => [
            'id' => $id,
            'text' => trim(($names[$id]['no'] ?? '') . ' - ' . ($names[$id]['name'] ?? ''), ' -'),
        ])->values());
    }

    /** Every activity on one record, newest first (drawer timeline). */
    public function timeline($type, $id)
    {
        abort_unless(isset(CrmActivity::SUBJECTS[$type]), 404);
        // a quotation's history covers all its versions (QTN/…, -R1, -R2)
        $ids = [(int) $id];
        $numbers = [];
        if ($type === 'quotation' && ($quotation = \App\Models\Quotation\Quotation::find($id))) {
            $chain = $quotation->revisionChain();
            $ids = $chain->pluck('id')->all();
            $numbers = $chain->count() > 1 ? $chain->pluck('row_no', 'id')->all() : [];
        }
        $activities = CrmActivity::with(['assignee:id,name', 'creator:id,name'])
            ->where('subject_type', $type)->whereIn('subject_id', $ids)
            ->orderByDesc('activity_at')->orderByDesc('id')->limit(100)->get();

        return view('modules.crm.timeline', compact('activities', 'numbers'));
    }

    private function formData(CrmActivity $activity, ?array $lockedSubject): array
    {
        return [
            'activity' => $activity,
            'lockedSubject' => $lockedSubject,
            'users' => User::where('company_id', companyId())->whereNull('terminated_at')
                ->where(fn($q) => $q->whereNull('status')->orWhere('status', 'active'))
                ->orderBy('name')->get(['id', 'name']),
            'subjectOptions' => $activity->subject_type && !$lockedSubject
                ? CrmActivity::describeSubjects($activity->subject_type, [(int) $activity->subject_id]) : [],
        ];
    }

    /** When the form is opened from a record (pipeline card, list menu), "With" is that record and cannot be changed. */
    private function lockedSubject(CrmActivity $activity): ?array
    {
        if (!$activity->subject_type || !isset(CrmActivity::SUBJECTS[$activity->subject_type]) || !$activity->subject_id) {
            return null;
        }
        $s = CrmActivity::describeSubjects($activity->subject_type, [(int) $activity->subject_id])[(int) $activity->subject_id] ?? null;
        if (!$s) {
            return null;
        }

        return ['kind' => CrmActivity::subjectLabel($activity->subject_type), 'no' => $s['no'], 'name' => $s['name']];
    }
}

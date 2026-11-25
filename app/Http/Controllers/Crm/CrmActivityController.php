<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Crm\CrmAction;
use App\Models\Crm\CrmActivity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

/**
 * Activities (one CRM action on one date: "Call Done", "Quote Due" ...) on a prospect / customer / enquiry / quotation, and the
 * follow-ups list built from the planned ones.
 */
class CrmActivityController extends Controller
{
    /** Follow-ups list: Overdue / Today / Upcoming / Done tabs, mine or everyone's. */
    public function fetchAllRows(Request $request): \Illuminate\Http\JsonResponse
    {
        $tab = $request->input('tab', 'overdue');
        $today = CrmActivity::localNowNaive()->startOfDay();
        $base = CrmActivity::query()->whereNotNull('follow_up_at')
            ->when($request->input('owner', 'mine') === 'mine', fn($q) => $q->where('assigned_to', auth()->id()))
            ->when($request->filled('action'), fn($q) => $q->where('action_id', (int) $request->input('action')));

        $counts = [
            'overdue' => (clone $base)->whereNull('follow_up_done_at')->where('follow_up_at', '<', $today)->count(),
            'today' => (clone $base)->whereNull('follow_up_done_at')->whereDate('follow_up_at', $today)->count(),
            'upcoming' => (clone $base)->whereNull('follow_up_done_at')->where('follow_up_at', '>=', $today->copy()->addDay())->count(),
            'done' => (clone $base)->whereNotNull('follow_up_done_at')->count(),
        ];

        $rows = (clone $base)->with(['assignee:id,name', 'action:id,name,category'])
            ->select('id', 'subject_type', 'subject_id', 'action_id', 'notes', 'activity_at', 'follow_up_at', 'assigned_to', 'follow_up_done_at', 'company_id');
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
                    $query->where('notes', 'like', "%{$term}%");
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
            ->addColumn('action_name', fn($row) => $row->actionLabel())
            ->addColumn('action_style', fn($row) => CrmAction::badgeStyle($row->action->category ?? null))
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
        $activity = $id ? CrmActivity::findOrFail($id) : null;
        $request->validate([
            'subject_type' => ['required', Rule::in(array_keys(CrmActivity::SUBJECTS))],
            'subject_id' => ['required', 'integer'],
            'action_id' => ['required', 'integer'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'activity_date' => ['required', 'date_format:d-m-Y'],
            'activity_time' => ['nullable', 'date_format:H:i'],
            'assigned_to' => ['nullable', 'integer'],
            'next_action_id' => ['nullable', 'integer'],
            'next_date' => ['nullable', 'required_with:next_action_id', 'date_format:d-m-Y'],
            'next_time' => ['nullable', 'date_format:H:i'],
            'next_assigned_to' => ['nullable', 'integer'],
        ], [], [
            'action_id' => __('Action'),
            'activity_date' => __('Date'),
            'next_action_id' => __('Next action'),
            'next_date' => __('Due date'),
        ]);

        $subjectClass = CrmActivity::SUBJECTS[$request->input('subject_type')];
        if (!$subjectClass::whereKey((int) $request->input('subject_id'))->exists()) {
            throw ValidationException::withMessages(['subject_id' => __('Choose who this activity is with.')]);
        }
        // an active action of this company (an edited activity may keep the action it has, even when that was switched off since)
        $action = CrmAction::whereKey((int) $request->input('action_id'))
            ->where(fn($q) => $q->where('is_active', true)->when($activity, fn($w) => $w->orWhere('id', $activity->action_id)))->first();
        if (!$action) {
            throw ValidationException::withMessages(['action_id' => __('Choose an action.')]);
        }

        $at = Carbon::createFromFormat('d-m-Y H:i', $request->input('activity_date') . ' ' . ($request->input('activity_time') ?: ($action->isPlanned() ? '09:00' : '00:00')));
        if (!$action->isPlanned() && $at->isAfter(CrmActivity::localNowNaive()->endOfDay())) {
            throw ValidationException::withMessages(['activity_date' => __('This action already happened, so its date cannot be in the future. For a future date choose a planned action (e.g. Call Back Due).')]);
        }
        $assignee = $action->isPlanned() ? $this->companyUser($request->input('assigned_to'), 'assigned_to') : null;

        $next = null;
        if (!$action->isPlanned() && $request->filled('next_action_id')) {
            $next = CrmAction::whereKey((int) $request->input('next_action_id'))->where('is_active', true)->where('category', CrmAction::PLANNED)->first();
            if (!$next) {
                throw ValidationException::withMessages(['next_action_id' => __('Choose a planned action.')]);
            }
            $nextAt = Carbon::createFromFormat('d-m-Y H:i', $request->input('next_date') . ' ' . ($request->input('next_time') ?: '09:00'));
            if ($nextAt->lt($at->copy()->startOfDay())) {
                throw ValidationException::withMessages(['next_date' => __('The next step cannot be before this action.')]);
            }
            $nextAssignee = $this->companyUser($request->input('next_assigned_to'), 'next_assigned_to');
        }

        if (!$activity) {
            $activity = new CrmActivity();
            $this->setBaseColumns($activity);
        }
        $activity->subject_type = $request->input('subject_type');
        $activity->subject_id = (int) $request->input('subject_id');
        $activity->action_id = $action->id;
        $activity->notes = trim((string) $request->input('notes')) ?: null;
        if ($action->isPlanned()) {
            // a changed due date is a new reminder
            if (!$activity->follow_up_at || !$activity->follow_up_at->equalTo($at)) {
                $activity->reminder_sent_at = null;
            }
            $activity->activity_at ??= CrmActivity::localNowNaive();
            $activity->follow_up_at = $at;
            $activity->assigned_to = $assignee;
        } else {
            $activity->activity_at = $at;
            $activity->follow_up_at = null;
            $activity->assigned_to = null;
            $activity->follow_up_done_at = null;
            $activity->reminder_sent_at = null;
        }

        \DB::transaction(function () use ($activity, $next, &$nextAt, &$nextAssignee) {
            $activity->save();
            if ($next) {
                $plan = new CrmActivity();
                $this->setBaseColumns($plan);
                $plan->subject_type = $activity->subject_type;
                $plan->subject_id = $activity->subject_id;
                $plan->action_id = $next->id;
                $plan->activity_at = CrmActivity::localNowNaive();
                $plan->follow_up_at = $nextAt;
                $plan->assigned_to = $nextAssignee;
                $plan->save();
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => $next ? __('Activity saved and the next step planned') : __('Activity saved successfully'),
            'module_id' => $activity->id,
            'subject_type' => $activity->subject_type,
            'subject_id' => $activity->subject_id,
        ]);
    }

    /** A user of this company (the one posted, or the signed-in user). */
    private function companyUser($posted, string $field): int
    {
        $id = (int) ($posted ?: auth()->id());
        if (!User::where('company_id', companyId())->whereKey($id)->exists()) {
            throw ValidationException::withMessages([$field => __('Choose a user of this company.')]);
        }

        return $id;
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
        $activities = CrmActivity::with(['assignee:id,name', 'creator:id,name', 'action:id,name,category'])
            ->where('subject_type', $type)->whereIn('subject_id', $ids)
            ->orderByDesc('activity_at')->orderByDesc('id')->limit(100)->get();

        return view('modules.crm.timeline', compact('activities', 'numbers'));
    }

    private function formData(CrmActivity $activity, ?array $lockedSubject): array
    {
        return [
            'activity' => $activity,
            'lockedSubject' => $lockedSubject,
            // active actions, plus the one an edited activity already has (even if switched off since)
            'actions' => CrmAction::options()->when($activity->action_id && !CrmAction::options()->contains('id', $activity->action_id),
                fn($list) => $list->push(CrmAction::find($activity->action_id))->filter()),
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

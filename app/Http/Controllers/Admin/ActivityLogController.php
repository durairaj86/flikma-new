<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['module', 'action']);
        $logs = ActivityLog::with('admin:id,name')
            ->when($filters['module'] ?? null, fn ($q, $v) => $q->where('module', $v))
            ->when($filters['action'] ?? null, fn ($q, $v) => $q->where('action', $v))
            ->latest()->paginate(50)->withQueryString();
        $modules = ActivityLog::distinct()->orderBy('module')->pluck('module');
        $actions = ActivityLog::distinct()->orderBy('action')->pluck('action');

        return view('admin.activity-logs.index', compact('logs', 'modules', 'actions', 'filters'));
    }

    public function show(ActivityLog $activityLog)
    {
        $activityLog->load('admin:id,name,email');

        return view('admin.activity-logs.show', compact('activityLog'));
    }

    public function destroy(ActivityLog $activityLog)
    {
        $activityLog->delete();

        return redirect()->route('admin.activity-logs.index')->with('success', 'Log entry deleted.');
    }
}

<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogActivityController extends Controller
{
    private const ACTIVITY_LIMIT = 10; // The default limit

    /**
     * Helper function to fetch and process logs based on offset.
     */
    protected function fetchAndProcessLogs(int $offset, int $limit)
    {
        $logs = DB::table('log_histories')
            ->where('company_id', companyId())
            ->orderBy('created_at', 'desc')
            ->offset($offset) // Skip records based on offset
            ->limit($limit)   // Limit the number of records returned
            ->get();

        if ($logs->isEmpty()) {
            return collect();
        }

        return $logs->map(function ($log) {
            // Processing logic (remains the same)
            if (empty($log->loggable_name)) {
                $log->loggable_name_display = $this->getModelName($log->loggable_type);
            } else {
                $log->loggable_name_display = $log->loggable_name;
            }

            $log->loggable_number_display = $log->loggable_number ?? 'ID ' . $log->loggable_id;
            $log->formatted_changes = $this->formatChanges($log->action, $log->changes);

            return $log;
        })->groupBy(function ($log) {
            // Group by date
            return Carbon::parse($log->created_at)->format('Y-m-d');
        });
    }

    /**
     * Loads the initial 50 activities (used for the initial page load).
     */
    public function showFeed()
    {
        $activities = $this->fetchAndProcessLogs(0, self::ACTIVITY_LIMIT);

        // Pass the initial offset (50) to the view
        return view('activity.feed', [
            'activities' => $activities,
            'nextOffset' => self::ACTIVITY_LIMIT,
            'limit' => self::ACTIVITY_LIMIT,
        ]);
    }

    /**
     * Loads subsequent batches of activities (used for AJAX requests).
     */
    public function loadMoreActivities(Request $request)
    {
        $offset = $request->input('offset', 0);
        $limit = self::ACTIVITY_LIMIT;

        $newActivities = $this->fetchAndProcessLogs($offset, $limit);

        if ($newActivities->isEmpty()) {
            return response()->json([
                'html' => '',
                'hasMore' => false
            ]);
        }

        // Render the new activities using the same partial view
        $html = view('activity.feed_load_more', ['activities' => $newActivities])->render();

        return response()->json([
            'html' => $html,
            'nextOffset' => $offset + $limit,
            'hasMore' => $newActivities->flatten()->count() === $limit,
        ]);
    }

    // getModelName() and formatChanges() methods remain unchanged
    protected function getModelName(string $type): string
    {
        // ... (unchanged)
        $parts = explode('\\', $type);
        return end($parts);
    }

    protected function formatChanges(string $action, ?string $changesJson): string
    {
        // ... (unchanged)
        if ($action === 'created') { return 'was **created**'; }
        // ...
        return 'was **updated**';
    }
}

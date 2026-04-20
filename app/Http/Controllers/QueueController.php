<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class QueueController extends Controller
{
    /**
     * Process queued emails via HTTP request.
     * This can be called by a cron job or external service in shared hosting.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processEmails(Request $request)
    {
        // Optional: Add some basic security to prevent unauthorized access
        if ($request->header('X-Queue-Secret') !== env('QUEUE_SECRET')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Process a specific number of jobs from the queue
        Artisan::call('queue:work', [
            '--once' => true,
            '--queue' => 'default',
            '--tries' => 3,
        ]);

        return response()->json(['message' => 'Queued emails processed successfully']);
    }

    /**
     * Retry failed jobs via HTTP request.
     * This can be called by a cron job or external service in shared hosting.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function retryFailedJobs(Request $request)
    {
        // Optional: Add some basic security to prevent unauthorized access
        if ($request->header('X-Queue-Secret') !== env('QUEUE_SECRET')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get all failed job IDs
        $failedJobs = DB::table('failed_jobs')->pluck('id')->toArray();

        if (empty($failedJobs)) {
            return response()->json(['message' => 'No failed jobs found']);
        }

        $count = count($failedJobs);

        // Retry each failed job
        foreach ($failedJobs as $id) {
            Artisan::call('queue:retry', ['id' => $id]);
        }

        return response()->json([
            'message' => "Retried {$count} failed jobs successfully",
            'count' => $count
        ]);
    }
}

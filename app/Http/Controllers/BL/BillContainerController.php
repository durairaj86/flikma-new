<?php

namespace App\Http\Controllers\BL;

use App\Http\Controllers\Controller;
use App\Models\Job\Job;
use Illuminate\Support\Facades\DB;

/**
 * Containers of a job, for the container picker on the waybill / airway bill / seaway bill forms.
 */
class BillContainerController extends Controller
{
    public function forJob($jobId)
    {
        $job = Job::find($jobId);
        if (!$job) {
            return response()->json([]);
        }

        $rows = DB::table('job_containers')->where('job_id', $job->id)->orderBy('id')->get()->map(function ($c) {
            $size = $c->container_size ?: null;
            return [
                'id' => $c->id,
                'no' => $c->container_number ?: ($c->container_no ?: null),
                'seal' => $c->seal_number ?: ($c->seal_no ?: null),
                'size' => $size ? containerSize($size) : null,
                'type' => $c->container_type ?: null,
                'weight' => $c->gross_weight ?: ($c->weight ?: null),
                'qty' => $c->qty ?: ($c->quantity ?: null),
            ];
        })->values();

        return response()->json($rows);
    }
}

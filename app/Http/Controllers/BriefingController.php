<?php

namespace App\Http\Controllers;

use App\Services\Briefing\OperationsBriefing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BriefingController extends Controller
{
    public function show(Request $request, OperationsBriefing $briefing): JsonResponse
    {
        $user = $request->user();
        if (!OperationsBriefing::allowed($user)) {
            return response()->json(['allowed' => false, 'items' => [], 'stats' => [], 'count' => 0, 'actionable' => 0, 'name' => '']);
        }
        if ($request->boolean('auto')) {
            session(['briefing_seen' => true]); // the automatic popup shows once per sign-in; the bell reopens it any time
        }

        return response()->json(['allowed' => true] + $briefing->build($user));
    }
}

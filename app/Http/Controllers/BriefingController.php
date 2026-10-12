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
        return response()->json(['allowed' => true] + $briefing->build($user));
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\Autocheck\AutocheckRunner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * /autocheck — local-only end-to-end checker.
 *  - headless: runs the whole flow in one DB transaction and rolls it back (no data left behind).
 *  - head (live): runs it step by step, keeps the data, and the page shows the real screens in an
 *    iframe so you can watch each document appear. "Clean up" removes what the run created.
 */
class AutocheckController extends Controller
{
    public function __construct()
    {
        abort_unless(app()->environment('local'), 404);
    }

    public function index()
    {
        return view('autocheck.index', ['steps' => AutocheckRunner::STEPS]);
    }

    public function headless(Request $request): JsonResponse
    {
        $runner = new AutocheckRunner();
        $until = $request->input('until');
        $ctx = [];
        $results = [];
        DB::beginTransaction();
        try {
            foreach (array_keys(AutocheckRunner::STEPS) as $step) {
                $res = $runner->run($step, $ctx);
                $results[] = $res;
                if (!$res['ok'] || $step === $until) {
                    break;
                }
            }
        } finally {
            DB::rollBack();
        }

        return response()->json(['results' => $results, 'rolledBack' => true]);
    }

    public function step(Request $request): JsonResponse
    {
        $step = $request->input('step');
        abort_unless(isset(AutocheckRunner::STEPS[$step]), 422);
        $ctx = session('autocheck_ctx', []);
        $res = (new AutocheckRunner())->run($step, $ctx);
        session(['autocheck_ctx' => $ctx]);
        // Deep links that need the ids created so far.
        if ($step === 'customer' && !empty($ctx['customer_id'])) {
            $res['url'] = '/customers/' . $ctx['customer_id'];
        }

        return response()->json($res);
    }

    public function cleanup(): JsonResponse
    {
        $ctx = session('autocheck_ctx', []);
        $removed = $ctx ? (new AutocheckRunner())->cleanup($ctx) : [];
        session()->forget('autocheck_ctx');

        return response()->json(['removed' => $removed, 'hadData' => (bool) $ctx]);
    }

    public function reset(): JsonResponse
    {
        session()->forget('autocheck_ctx');

        return response()->json(['ok' => true]);
    }
}

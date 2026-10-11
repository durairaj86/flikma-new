<?php

namespace App\Http\Controllers\Job;

use App\Http\Controllers\Controller;
use App\Models\Job\JobContainer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/** Demurrage & detention: how long each container has been out, and what it will cost. */
class DemurrageController extends Controller
{
    const DEFAULT_FREE_DAYS = 7;
    const DEFAULT_RATE = 100;

    /** Work out days used, days left, state and cost for one container. */
    public static function figures(JobContainer $c, float $rate): array
    {
        $free = $c->free_days ?: self::DEFAULT_FREE_DAYS;
        if (!$c->discharged_at) {
            return ['state' => 'waiting', 'used' => 0, 'left' => $free, 'over' => 0, 'cost' => 0, 'free' => $free];
        }
        $start = Carbon::parse($c->discharged_at)->startOfDay();
        $end = $c->returned_at ? Carbon::parse($c->returned_at)->startOfDay() : today();
        $used = max(1, (int) $start->diffInDays($end) + 1);
        $left = $free - $used;
        $over = max(0, -$left);
        $state = $c->returned_at ? 'returned' : ($left < 0 ? 'detention' : ($left <= 2 ? 'ending' : 'free'));

        return ['state' => $state, 'used' => $used, 'left' => $left, 'over' => $over, 'cost' => $over * $rate, 'free' => $free];
    }

    public function index(Request $request)
    {
        $rate = (float)($request->rate ?: self::DEFAULT_RATE);
        $tab = $request->tab ?: 'open';
        $items = JobContainer::with('job:id,row_no')
            ->whereHas('job')
            ->when($request->q, function ($q, $s) {
                $q->where(fn($q) => $q->where('container_no', 'like', "%{$s}%")->orWhere('container_number', 'like', "%{$s}%")
                    ->orWhereHas('job', fn($j) => $j->where('row_no', 'like', "%{$s}%")));
            })
            ->orderByDesc('id')->limit(300)->get()
            ->map(fn($c) => ['c' => $c, 'f' => self::figures($c, $rate)]);

        $counts = [
            'open' => $items->whereIn('f.state', ['free', 'ending', 'detention'])->count(),
            'detention' => $items->where('f.state', 'detention')->count(),
            'returned' => $items->where('f.state', 'returned')->count(),
            'waiting' => $items->where('f.state', 'waiting')->count(),
        ];
        $exposure = $items->where('f.state', 'detention')->sum('f.cost');
        $shown = match ($tab) {
            'detention' => $items->where('f.state', 'detention'),
            'returned' => $items->where('f.state', 'returned'),
            'waiting' => $items->where('f.state', 'waiting'),
            default => $items->whereIn('f.state', ['free', 'ending', 'detention']),
        };

        return view('modules.demurrage.index', compact('shown', 'counts', 'exposure', 'tab', 'rate') + ['q' => $request->q]);
    }

    public function edit($id)
    {
        return view('modules.demurrage.form', ['c' => JobContainer::with('job:id,row_no')->whereHas('job')->findOrFail($id)]);
    }

    public function save(Request $request, $id)
    {
        $c = JobContainer::whereHas('job')->findOrFail($id);
        $c->fill([
            'discharged_at' => $request->discharged_at ? formDate($request->discharged_at) : null,
            'gate_out_at' => $request->gate_out_at ? formDate($request->gate_out_at) : null,
            'returned_at' => $request->returned_at ? formDate($request->returned_at) : null,
            'free_days' => $request->free_days ?: null,
            'return_depot' => $request->return_depot,
        ])->save();

        return response()->json(['status' => 'success', 'message' => __('Container updated')]);
    }
}

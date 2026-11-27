<?php

namespace App\Http\Controllers\Master\Settings;

use App\Http\Controllers\Controller;
use App\Services\Crm\CrmSettings;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** Settings › CRM: week start, stale days, score points and the suggested-rank limits. */
class CrmSettingsController extends Controller
{
    public function edit()
    {
        return view('modules.settings.crm', ['settings' => CrmSettings::all()]);
    }

    public function store(Request $request)
    {
        $int = ['required', 'integer', 'min:0', 'max:9999'];
        $data = $request->validate([
            'week_start' => ['required', Rule::in(array_keys(CrmSettings::WEEK_STARTS))],
            'stale_days' => ['required', 'integer', 'min:1', 'max:365'],
            'points_touch' => $int,
            'points_rfq' => $int,
            'points_job' => $int,
            'rank_a' => $int,
            'rank_b' => $int,
            'rank_c' => $int,
        ], [], [
            'week_start' => __('Week starts on'), 'stale_days' => __('Stale customer after (days)'),
            'points_touch' => __('Points per outreach touch'), 'points_rfq' => __('Points per RFQ received'), 'points_job' => __('Points per job confirmed'),
            'rank_a' => __('Rank A from score'), 'rank_b' => __('Rank B from score'), 'rank_c' => __('Rank C from score'),
        ]);
        if (!((int) $data['rank_a'] > (int) $data['rank_b'] && (int) $data['rank_b'] > (int) $data['rank_c'])) {
            throw ValidationException::withMessages(['rank_b' => __('The limits must go down: A higher than B, B higher than C.')]);
        }
        foreach ($data as $k => $v) {
            $data[$k] = $k === 'week_start' ? $v : (int) $v;
        }
        CrmSettings::save($data);

        return response()->json(['status' => 'success', 'message' => __('CRM settings saved')]);
    }
}

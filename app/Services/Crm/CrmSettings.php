<?php

namespace App\Services\Crm;

/**
 * The CRM's lists and numbers from the sales workbook (Lists sheet, SETTINGS): customer ranks, how long before a customer is
 * "stale", and the points that make the suggested rank. A company can change the numbers in Settings › CRM.
 */
class CrmSettings
{
    /** Rank => meaning. */
    public const RANKS = [
        'A' => 'Top customer - confirmed jobs / frequent RFQs, follow up most',
        'B' => 'Good customer - RFQs received, regular follow-up',
        'C' => 'Potential - some response, occasional follow-up',
        'D' => 'Cold / low priority - light-touch follow-up',
    ];

    public const DEFAULTS = [
        'week_start' => 'sunday',
        'stale_days' => 14,       // no contact for this many days turns "Days since" red
        'points_touch' => 1,      // per outreach action (call, mail, meeting ...)
        'points_rfq' => 5,        // per enquiry received
        'points_job' => 10,       // per job confirmed
        'rank_a' => 40,           // suggested rank A from this score
        'rank_b' => 20,
        'rank_c' => 8,            // below this: D
    ];

    public const WEEK_STARTS = ['sunday' => 'Sunday', 'monday' => 'Monday', 'saturday' => 'Saturday'];

    /** The company's value (Settings › CRM), else the workbook's default. */
    public static function get(string $key): int|string
    {
        $value = self::all()[$key] ?? self::DEFAULTS[$key];

        return is_int(self::DEFAULTS[$key]) ? (int) $value : (string) $value;
    }

    /** Every setting of the current company, defaults filled in (read once per request). */
    public static function all(?int $companyId = null): array
    {
        static $cache = [];
        $companyId ??= (int) companyId();
        if (!isset($cache[$companyId])) {
            $saved = $companyId ? json_decode((string) \Illuminate\Support\Facades\DB::table('companies')->where('id', $companyId)->value('crm_settings'), true) : null;
            $cache[$companyId] = array_merge(self::DEFAULTS, array_intersect_key(is_array($saved) ? $saved : [], self::DEFAULTS));
        }

        return $cache[$companyId];
    }

    public static function save(array $values, ?int $companyId = null): void
    {
        $companyId ??= (int) companyId();
        \Illuminate\Support\Facades\DB::table('companies')->where('id', $companyId)
            ->update(['crm_settings' => json_encode(array_intersect_key($values, self::DEFAULTS))]);
    }

    /** Score = touches x points + RFQs x points + jobs x points (the workbook's formula). */
    public static function score(int $touches, int $rfqs, int $jobs): int
    {
        return $touches * self::get('points_touch') + $rfqs * self::get('points_rfq') + $jobs * self::get('points_job');
    }

    public static function suggestedRank(int $score): string
    {
        return $score >= self::get('rank_a') ? 'A' : ($score >= self::get('rank_b') ? 'B' : ($score >= self::get('rank_c') ? 'C' : 'D'));
    }

    public static function rankLabel(?string $rank): string
    {
        return $rank && isset(self::RANKS[$rank]) ? $rank . ' — ' . __(self::RANKS[$rank]) : '';
    }
}

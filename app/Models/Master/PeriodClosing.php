<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PeriodClosing extends Model
{
    protected $casts = [
        'is_closed' => 'boolean',
        'closing_date' => 'date',
        'closed_at' => 'datetime',
    ];

    protected static string $cacheKey = 'period-closing-lock:';

    protected static function booted()
    {
        static::addGlobalScope(function ($query) {
            $query->where('company_id', companyId());
        });

        static::saved(fn() => Cache::forget(self::$cacheKey . companyId()));
        static::deleted(fn() => Cache::forget(self::$cacheKey . companyId()));
    }

    /**
     * The latest closing_date among this company's *closed* periods —
     * any transaction dated on or before this date is locked. Null means
     * nothing is locked yet.
     */
    public static function lockedThroughDate($companyId = null)
    {
        $companyId = $companyId ?? companyId();

        return Cache::remember(self::$cacheKey . $companyId, now()->addHour(), function () use ($companyId) {
            $date = static::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('is_closed', true)
                ->max('closing_date');

            return $date ? \Carbon\Carbon::parse($date) : null;
        });
    }

    /**
     * True when the given date falls on/before the company's lock cutoff —
     * i.e. it belongs to a closed period and must not be saved/edited.
     */
    public static function isLocked($date, $companyId = null): bool
    {
        if (empty($date)) {
            return false;
        }

        $lockedThrough = static::lockedThroughDate($companyId);
        if (!$lockedThrough) {
            return false;
        }

        return \Carbon\Carbon::parse($date)->lessThanOrEqualTo($lockedThrough);
    }
}

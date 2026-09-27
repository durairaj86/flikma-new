<?php

namespace App\Models\Master;

use App\Enums\CustomerStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Company extends Model
{
    protected $table = 'companies';
    protected $casts = [
        'business_type' => 'array',
        'header_enabled' => 'boolean',
        'is_in_trial' => 'boolean',
        'trial_ends_at' => 'date',
        'subscription_expiry_date' => 'date',
        'ai_tokens_used' => 'integer',
        'ai_token_limit' => 'integer',
    ];
    protected static string $cache = 'company:';

    /**
     * True once the paid period has elapsed. Mirrors the reference's
     * $company->subscription_expired accessor.
     */
    public function getSubscriptionExpiredAttribute(): bool
    {
        return $this->subscription_expiry_date !== null
            && $this->subscription_expiry_date->isPast();
    }

    /** Always-current company row. Billing must never read the forever-cached copy. */
    public static function currentFresh(): ?self
    {
        $id = companyId();

        return $id ? static::find($id) : null;
    }

    public static function companies($companyId = null)
    {
        $companyId = $companyId ?? session('company_id');
        
        if (!$companyId && auth()->check()) {
            $companyId = auth()->user()->company_id ?? null;
            if ($companyId) {
                session(['company_id' => $companyId]);
            }
        }
        
        if (!$companyId) {
            return null;
        }
        
        Cache::forget(self::$cache . cacheName());
        return Cache::rememberForever(static::$cache . $companyId, function () use ($companyId) {
            return static::find($companyId);
        });
    }

    /**
     * Drop the memoized copy of a company.
     *
     * companies() caches with rememberForever, so anything that writes to the
     * companies row has to call this or every later read in the request — and
     * every subsequent request — keeps seeing the old values.
     */
    public static function forgetCached(?int $companyId = null): void
    {
        $companyId = $companyId ?? session('company_id') ?? auth()->user()?->company_id;

        if ($companyId) {
            Cache::forget(self::$cache . $companyId);
        }
    }
}

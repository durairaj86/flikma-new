<?php

namespace App\Models\Master;

use App\Traits\CompanyOrGlobalScopeTrait;
use App\Traits\CompanyScopeWithNullTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationTerm extends Model
{
    use CompanyOrGlobalScopeTrait, CompanyScopeWithNullTrait;

    protected $casts = [
        'is_general' => 'boolean',
        'is_active'  => 'boolean',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(LogisticActivity::class, 'activity_id');
    }

    /**
     * The term text to prefill a quotation's Terms & Conditions for the
     * given activity: an active term scoped to that exact activity wins,
     * otherwise fall back to an active general term (activity_id null).
     */
    public static function forActivity($activityId): ?string
    {
        $companyId = companyId();

        $specific = static::where('is_active', true)
            ->where('activity_id', $activityId)
            ->where(fn($q) => $q->where('company_id', $companyId)->orWhereNull('company_id'))
            ->orderByDesc('company_id')
            ->first();

        if ($specific) {
            return $specific->terms;
        }

        $general = static::where('is_active', true)
            ->where('is_general', true)
            ->where(fn($q) => $q->where('company_id', $companyId)->orWhereNull('company_id'))
            ->orderByDesc('company_id')
            ->first();

        return $general->terms ?? null;
    }
}

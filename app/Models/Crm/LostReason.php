<?php

namespace App\Models\Crm;

use App\Traits\CompanyScopeTrait;
use Illuminate\Database\Eloquent\Model;

/** Why a prospect, enquiry or quotation was lost (master list, one per company). */
class LostReason extends Model
{
    use CompanyScopeTrait;

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** Reasons a new company starts with; copied the first time the company needs the list. */
    public const DEFAULTS = [
        'Price too high', 'Chose a competitor', 'Transit time too long', 'No response from customer',
        'Shipment cancelled by customer', 'Service not available on this route', 'Other',
    ];

    /** Active reasons for the current company, in order; the defaults are added once when the company has none yet. */
    public static function options()
    {
        static::seedDefaults();

        return static::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name']);
    }

    public static function seedDefaults(): void
    {
        $companyId = companyId();
        if (!$companyId || static::withoutGlobalScopes()->where('company_id', $companyId)->exists()) {
            return;
        }
        foreach (self::DEFAULTS as $i => $name) {
            $reason = new static();
            $reason->company_id = $companyId;
            $reason->name = $name;
            $reason->sort_order = $i + 1;
            $reason->user_id = auth()->id();
            $reason->save();
        }
    }
}

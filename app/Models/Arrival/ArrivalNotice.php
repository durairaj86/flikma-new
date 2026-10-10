<?php

namespace App\Models\Arrival;

use App\Models\Customer\Customer;
use App\Models\Job\Job;
use App\Traits\CompanyScopeTrait;
use App\Traits\Log\LogHistoryTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Cargo arrival notice / notification sent to the consignee when the vessel is about to arrive. */
class ArrivalNotice extends Model
{
    use CompanyScopeTrait, LogHistoryTrait, SoftDeletes;

    protected $guarded = ['id', 'company_id', 'user_id', 'row_no', 'unique_row_no', 'status'];

    protected $casts = ['notice_date' => 'date', 'eta' => 'date', 'notified_at' => 'datetime', 'detention' => 'array'];

    /** Line detention as printed on the notification; used until the user edits it. */
    public const DEFAULT_DETENTION = [
        'standard' => ['free' => 8, 'tiers' => [['days' => 8, 'r20' => 30, 'r40' => 60], ['days' => 15, 'r20' => 60, 'r40' => 120], ['days' => 30, 'r20' => 100, 'r40' => 200], ['days' => null, 'r20' => 200, 'r40' => 300]]],
        'special' => ['free' => 7, 'tiers' => [['days' => 8, 'r20' => 60, 'r40' => 120], ['days' => 15, 'r20' => 100, 'r40' => 200], ['days' => 30, 'r20' => 200, 'r40' => 300], ['days' => null, 'r20' => 400, 'r40' => 550]]],
        'reefer' => ['free' => 7, 'tiers' => [['days' => 8, 'r20' => 100, 'r40' => 200], ['days' => 15, 'r20' => 200, 'r40' => 350], ['days' => 30, 'r20' => 300, 'r40' => 450], ['days' => null, 'r20' => 400, 'r40' => 550]]],
    ];

    public function detentionTable(): array
    {
        return $this->detention ?: self::DEFAULT_DETENTION;
    }

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function job(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Job::class);
    }
}

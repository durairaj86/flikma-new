<?php

namespace App\Models\Delivery;

use App\Models\Customer\Customer;
use App\Models\Job\Job;
use App\Traits\CompanyScopeTrait;
use App\Traits\Log\LogHistoryTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Delivery order: releasing cargo to a transporter and tracking it to the consignee. */
class DeliveryOrder extends Model
{
    use CompanyScopeTrait, LogHistoryTrait, SoftDeletes;

    protected $guarded = ['id', 'company_id', 'user_id', 'row_no', 'unique_row_no', 'status'];

    protected $casts = ['do_date' => 'date', 'delivery_date' => 'date', 'delivered_at' => 'datetime'];

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function job(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Job::class);
    }
}

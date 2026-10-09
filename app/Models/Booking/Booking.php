<?php

namespace App\Models\Booking;

use App\Models\Customer\Customer;
use App\Models\Job\Job;
use App\Traits\CompanyScopeTrait;
use App\Traits\Log\LogHistoryTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A carrier booking (ocean / air / road) made for a customer, optionally already tied to a job. */
class Booking extends Model
{
    use CompanyScopeTrait, LogHistoryTrait, SoftDeletes;

    protected $guarded = ['id', 'company_id', 'user_id', 'row_no', 'unique_row_no', 'status'];

    protected $casts = [
        'booking_date' => 'date',
        'etd' => 'date',
        'eta' => 'date',
        'cargo_cutoff' => 'datetime',
        'doc_cutoff' => 'datetime',
    ];

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function job(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function carrier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\CarrierLine::class, 'carrier_id');
    }
}

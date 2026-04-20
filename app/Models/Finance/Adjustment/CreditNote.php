<?php

namespace App\Models\Finance\Adjustment;

use App\Models\Customer\Customer;
use App\Models\Documents\Documents;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Job\Job;
use App\Models\Zatca\ZatcaHistory;
use App\Traits\Log\LogHistoryTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditNote extends Model
{
    use LogHistoryTrait, SoftDeletes;

    public function creditNoteSubs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CreditNoteSub::class);
    }

    public function job(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function documents(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Documents::class, 'documentable');
    }

    public function zatcaHistory()
    {
        return $this->morphOne(ZatcaHistory::class, 'invoice');
    }

    public function invoice(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CustomerInvoice::class, 'invoice_id');
    }
}

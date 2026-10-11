<?php

namespace App\Models\Job;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/** A document (invoice, credit note, expense ...) that belongs to a job. Kept in step by App\Services\Job\JobLinks. */
class JobLink extends Model
{
    use BelongsToCompany;

    protected $guarded = ['id'];

    protected $casts = ['doc_date' => 'date', 'amount' => 'float', 'paid' => 'float'];

    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }
}

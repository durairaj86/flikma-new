<?php

namespace App\Models\Job;

use Illuminate\Database\Eloquent\Model;

class JobContainer extends Model
{
    protected $guarded = ['id'];

    public function job(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Job::class, 'job_id');
    }
}

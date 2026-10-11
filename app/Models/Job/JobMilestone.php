<?php

namespace App\Models\Job;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class JobMilestone extends Model
{
    use BelongsToCompany;

    protected $guarded = ['id', 'company_id'];

    protected $casts = ['planned_date' => 'date', 'actual_at' => 'date'];
}

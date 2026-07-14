<?php

namespace App\Models\Documents;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Traits\CompanyScopeTrait;

class Documents extends Model
{
    use CompanyScopeTrait;

    protected $fillable = [
        'title', 'file_path', 'file_name', 'expiry_date', 'posted_date', 'user_id', 'company_id'
    ];

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }
}

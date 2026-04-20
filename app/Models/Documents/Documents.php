<?php

namespace App\Models\Documents;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Documents extends Model
{
    protected $fillable = [
        'title', 'file_path', 'file_name', 'expiry_date', 'posted_date', 'user_id', 'company_id'
    ];

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }
}

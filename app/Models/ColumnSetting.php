<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ColumnSetting extends Model
{
    protected $fillable = ['page_name', 'column_json', 'user_id', 'company_id'];

    protected $casts = [
        'column_json' => 'array',
    ];
}

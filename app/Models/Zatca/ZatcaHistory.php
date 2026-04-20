<?php

namespace App\Models\Zatca;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ZatcaHistory extends Model
{
    use HasFactory;
    protected $table = 'zatca_histories';

    protected $casts = [
        'submitted_json' => 'object',
    ];

    public function invoice(): MorphTo
    {
        return $this->morphTo();
    }

}

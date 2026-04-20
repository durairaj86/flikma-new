<?php

namespace App\Models\Finance\Asset;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetDepreciation extends Model
{
    protected $fillable = [
        'asset_id',
        'period_start',
        'period_end',
        'amount',
        'accumulated',
        'book_value',
        'posted',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'posted' => 'boolean',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}

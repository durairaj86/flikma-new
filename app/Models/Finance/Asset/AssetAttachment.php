<?php

namespace App\Models\Finance\Asset;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetAttachment extends Model
{
    protected $fillable = [
        'asset_id',
        'path',
        'original_name',
        'mime',
        'size',
        'uploaded_by',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}

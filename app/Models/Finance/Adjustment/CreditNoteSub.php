<?php

namespace App\Models\Finance\Adjustment;

use App\Models\Master\Description;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditNoteSub extends Model
{
    use SoftDeletes;
    public function creditNote(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CreditNote::class);
    }

    public function description()
    {
        return $this->belongsTo(Description::class);
    }
}

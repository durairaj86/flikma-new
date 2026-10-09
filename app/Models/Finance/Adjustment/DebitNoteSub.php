<?php

namespace App\Models\Finance\Adjustment;

use App\Models\Master\Description;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DebitNoteSub extends Model
{
    use SoftDeletes;

    public function debitNote(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DebitNote::class);
    }

    public function description()
    {
        return $this->belongsTo(Description::class);
    }
}

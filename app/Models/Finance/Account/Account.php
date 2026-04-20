<?php

namespace App\Models\Finance\Account;

use App\Models\BaseModel;
use App\Traits\CompanyScopeWithNullTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends BaseModel
{
    use CompanyScopeWithNullTrait;
    protected $fillable = [
        'name', 'code', 'type', 'parent_id', 'account_number',
        'is_grouped', 'is_last', 'is_level', 'is_active'
    ];

    // Parent account
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    // Child accounts
    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }
}

<?php

namespace App\Models\Finance\Account;

use App\Models\BaseModel;
use App\Traits\CompanyScopeWithNullTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\CompanyOrGlobalScopeTrait;

class Account extends BaseModel
{
    use CompanyOrGlobalScopeTrait, CompanyScopeWithNullTrait;
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

    public function scopeActive($q)
    {
        return $q->where('accounts.is_active', 1);
    }

    /** Leaf accounts only: entries are posted to these, never to a group. */
    public function scopePosting($q)
    {
        return $q->where('accounts.is_last', 1);
    }

    /**
     * Cash and bank accounts: asset accounts whose name says cash / bank, plus everything nested under them
     * (a sub-account such as "SBI" does not repeat the word "bank").
     */
    public function scopeCashOrBank($q)
    {
        $matched = static::query()->where('type', 'Asset')
            ->where(fn($w) => $w->where('name', 'like', '%cash%')->orWhere('name', 'like', '%bank%'))
            ->pluck('id')->all();
        if (!$matched) {
            return $q->whereRaw('1 = 0');
        }

        $all = $matched;
        $frontier = $matched;
        for ($depth = 0; $depth < 5 && $frontier; $depth++) {
            $children = array_values(array_diff(static::query()->whereIn('parent_id', $frontier)->pluck('id')->all(), $all));
            if (!$children) {
                break;
            }
            $all = array_merge($all, $children);
            $frontier = $children;
        }

        return $q->whereIn('accounts.id', $all);
    }
}

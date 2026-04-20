<?php

namespace App\Models\Finance\Asset;

use App\Enums\AssetStatusEnum;
use App\Models\Finance\Account\Account;
use App\Models\Supplier\Supplier;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    protected $fillable = [
        'company_id',
        'row_no',
        'name_en',
        'name_ar',
        'category_id',
        'acquisition_date',
        'cost',
        'residual_value',
        'initial_accumulated',
        'useful_life_months',
        'months_already_depreciated',
        'depreciation_method',
        'status',
        'depreciation_start_date',
        'disposed_at',
        'closed_at',
        'notes',
        'is_active',
        'created_by',
        'updated_by',
        // New fields
        'supplier_id',
        'running_asset_account_id',
        'depreciation_expense_account_id',
        'invoice_number',
        'invoice_date',
    ];

    protected $casts = [
        'acquisition_date' => 'date',
        'depreciation_start_date' => 'date',
        'disposed_at' => 'date',
        'closed_at' => 'date',
        'invoice_date' => 'date',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function depreciations(): HasMany
    {
        return $this->hasMany(AssetDepreciation::class, 'asset_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(AssetAttachment::class, 'asset_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function runningAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'running_asset_account_id');
    }

    public function depreciationExpenseAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'depreciation_expense_account_id');
    }

    public function scopeStatus($query, $statusName)
    {
        $val = AssetStatusEnum::fromName($statusName);
        if ($val) {
            $query->where('status', $val);
        }
        return $query;
    }

    protected function acquisitionDate(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : null,
            set: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    protected function depreciationStartDate(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : null,
            set: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    public function getAccumulatedDepreciationAttribute(): float
    {
        $scheduled = (float) $this->depreciations()->sum('amount');
        $opening = (float) ($this->initial_accumulated ?? 0);
        return $opening + $scheduled;
    }

    public function getBookValueAttribute(): float
    {
        $acc = (float) $this->initial_accumulated + (float) $this->depreciations()->sum('amount');
        $bv = ($this->cost - $acc);
        return $bv < 0 ? 0.0 : (float) $bv;
    }
}

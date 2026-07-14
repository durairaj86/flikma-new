<?php

namespace App\Models\Finance\Asset;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\CompanyScopeTrait;

class AssetCategory extends Model
{
    use CompanyScopeTrait;

    protected $fillable = [
        'company_id',
        'row_no',
        'name_en',
        'name_ar',
        'depreciation_method',
        'useful_life_months',
        'annual_rate_percent',
        'is_active',
    ];

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'category_id');
    }
}

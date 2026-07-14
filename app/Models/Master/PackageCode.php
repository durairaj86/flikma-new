<?php

namespace App\Models\Master;

use App\Traits\CompanyScopeWithNullTrait;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CompanyOrGlobalScopeTrait;

class PackageCode extends Model
{
    use CompanyOrGlobalScopeTrait, CompanyScopeWithNullTrait;
    //protected $connection = 'masters';
}

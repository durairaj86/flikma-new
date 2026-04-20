<?php

namespace App\Models\Master;

use App\Traits\CompanyScopeWithNullTrait;
use Illuminate\Database\Eloquent\Model;

class PackageCode extends Model
{
    use CompanyScopeWithNullTrait;
    //protected $connection = 'masters';
}

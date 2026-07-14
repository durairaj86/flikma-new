<?php

namespace App\Models\Master;

use App\Traits\CompanyScopeWithNullTrait;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CompanyScopeTrait;

class LogisticServices extends Model
{
    use CompanyScopeTrait, CompanyScopeWithNullTrait;
    //protected $connection = 'masters';
}

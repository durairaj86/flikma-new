<?php

namespace App\Models\Master\TransportDirectory;

use Illuminate\Database\Eloquent\Model;
use App\Traits\CompanyOrGlobalScopeTrait;

class Airport extends Model
{
    use CompanyOrGlobalScopeTrait;

    //protected $connection = 'masters';
    public $timestamps = false;
}

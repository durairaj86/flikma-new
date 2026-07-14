<?php

namespace App\Models\Zatca;

use Illuminate\Database\Eloquent\Model;
use App\Traits\CompanyScopeTrait;

class ZatcaRegisterDetails extends Model
{
    use CompanyScopeTrait;

    public $timestamps = false;
}

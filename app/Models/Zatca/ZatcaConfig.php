<?php

namespace App\Models\Zatca;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZatcaConfig extends Model
{
    use HasFactory;
    protected $table = 'zatca_configs';
    protected $fillable = ['company_id', 'company_id','uuid','egs_details','request_id','binary_security_token' ,'secret', 'private_key','status'];

}

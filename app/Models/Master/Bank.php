<?php

namespace App\Models\Master;

use App\Traits\CompanyScopeTrait;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use CompanyScopeTrait;

    /*
     * The invoice / quotation prints read name, account_name, account_no, iban and swift, but the columns are bank_name,
     * account_holder, account_number, iban_code and swift_code: these read-only names made the bank details print blank.
     */
    public function account(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Finance\Account\Account::class, 'account_id');
    }

    public function getNameAttribute(): ?string
    {
        return $this->attributes['bank_name'] ?? null;
    }

    public function getAccountNameAttribute(): ?string
    {
        return $this->attributes['account_holder'] ?? null;
    }

    public function getAccountNoAttribute(): ?string
    {
        return $this->attributes['account_number'] ?? null;
    }

    public function getIbanAttribute(): ?string
    {
        return $this->attributes['iban_code'] ?? null;
    }

    public function getSwiftAttribute(): ?string
    {
        return $this->attributes['swift_code'] ?? null;
    }
}

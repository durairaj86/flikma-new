<?php

namespace App\Models\Finance\Expense;

use App\Models\Finance\Account\Account;
use App\Models\Master\Description;
use App\Models\Master\Unit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseSub extends Model
{
    protected $fillable = [
        'expense_id',
        'item_id',
        'quantity',
        'unit_price',
        'tax_code',
        'tax_percent',
        'line_total',
        'total',
        'total_with_tax',
        'account_id',
        'comment',
        'employee_id'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'total_with_tax' => 'decimal:2',
        'total' => 'decimal:2'
    ];

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}

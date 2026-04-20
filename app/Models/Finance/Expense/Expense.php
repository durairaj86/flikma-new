<?php

namespace App\Models\Finance\Expense;

use App\Models\Customer\Customer;
use App\Models\Document;
use App\Models\Documents\Documents;
use App\Models\Master\Company;
use App\Models\Supplier\Supplier;
use App\Models\User;
use App\Traits\Log\LogHistoryTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Expense extends Model
{
    use LogHistoryTrait;
    protected $fillable = [
        'company_id',
        'branch_id',
        'row_no',
        'unique_number',
        'posted_at',
        'vendor_id',
        'customer_id',
        'job_id',
        'expense_date',
        'expense_category_id',
        'reference_number',
        'payment_mode',
        'is_billable',
        'status',
        'approved_at',
        'user_id',
        'company_id',
        'currency',
        'currency_rate',
        'base_sub_total',
        'base_tax_total',
        'grand_total'
    ];

    protected $casts = [
        'expense_date' => 'date',
        'due_date' => 'date',
        'approved_at' => 'datetime',
        'deleted_at' => 'datetime',
        'is_billable' => 'boolean',
        'amount_excluding_vat' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'amount_including_vat' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'vendor_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function expenseSubs(): HasMany
    {
        return $this->hasMany(ExpenseSub::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Documents::class, 'documentable');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Job\Job::class);
    }

    /*public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    public function expenseCategory(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Finance\ExpenseCategory::class);
    }*/

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    protected function postedAt(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : null,
            set: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null,
        );
    }
}

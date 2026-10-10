<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Master\Department;
use App\Traits\CompanyScopeTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, CompanyScopeTrait;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', 'email', 'phone', 'password', 'profile_photo_path',
        'company_id', 'department_id',
        'role', 'status', 'login_permission', 'last_login',
        'address_1', 'address_2', 'city', 'state', 'postal_code',
        'country', 'alternate_email', 'remark',
        // employee / payroll profile
        'is_employee', 'employee_code', 'device_user_id', 'designation', 'department', 'gender', 'joining_date', 'dob',
        'photo_path', 'login_enabled', 'terminated_at', 'shift_id', 'employment_type', 'reporting_manager_id',
        'national_id', 'bank_name', 'iban', 'gosi_number', 'emergency_contact_name', 'emergency_contact_phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        /*'profile_photo_url',*/
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'header_enabled' => 'boolean',
            'is_employee' => 'boolean',
            'login_enabled' => 'boolean',
            'gender' => 'integer',
            'joining_date' => 'date',
            'dob' => 'date',
            'terminated_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        // Every employee gets a code (EMP-0001, EMP-0002 ... per company) the moment they become one.
        static::saving(function (User $user) {
            if ($user->is_employee && empty($user->employee_code)) {
                $last = static::withoutGlobalScopes()->where('company_id', $user->company_id)->max('employee_code');
                $user->employee_code = 'EMP-' . str_pad(((int) preg_replace('/\D/', '', (string) $last)) + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    /** People who are on the payroll (employees), not just system logins. */
    public function scopeEmployees($q)
    {
        return $q->where('is_employee', true);
    }

    public function department()
    {
        return $this->belongsTo(Department::class)->select('id', 'name');
    }

    // ---- Payroll & attendance ------------------------------------------------------------------------
    public function attendances()
    {
        return $this->hasMany(\App\Models\Payroll\Attendance::class, 'employee_id');
    }

    public function employeeLoans()
    {
        return $this->hasMany(\App\Models\Payroll\EmployeeLoan::class, 'employee_id');
    }

    public function payrollRecords()
    {
        return $this->hasMany(\App\Models\Payroll\PayrollRecord::class, 'employee_id');
    }

    public function salaryStructures()
    {
        return $this->hasMany(\App\Models\Payroll\SalaryStructure::class, 'employee_id');
    }

    public function currentSalaryStructure()
    {
        return $this->salaryStructures()
            ->whereDate('effective_from', '<=', now())
            ->where(function ($q) {
                $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', now());
            })
            ->latest('effective_from')
            ->first();
    }

    public function shift()
    {
        return $this->belongsTo(\App\Models\Payroll\Shift::class, 'shift_id');
    }

    public function reportingManager()
    {
        return $this->belongsTo(\App\Models\User::class, 'reporting_manager_id');
    }

    public function directReports()
    {
        return $this->hasMany(\App\Models\User::class, 'reporting_manager_id');
    }

    public function isTerminated(): bool
    {
        return $this->terminated_at !== null;
    }

    /** Attendance, salary, loan or payroll history means the employee can only be terminated, never deleted. */
    public function hasEmployeeRecords(): bool
    {
        foreach (['attendance', 'salary_structures', 'employee_loans', 'payroll_records'] as $table) {
            if (\Illuminate\Support\Facades\DB::table($table)->where('employee_id', $this->id)->exists()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Everything that still points at this person, as human-readable reasons with counts:
     * employees reporting to them, HR history, and documents they created.
     */
    public function deletionBlockers(): array
    {
        $db = fn (string $t, string $col) => \Illuminate\Support\Facades\DB::table($t)->where($col, $this->id)->count();
        $out = [];

        if ($n = $db('users', 'reporting_manager_id')) {
            $names = static::where('reporting_manager_id', $this->id)->limit(3)->pluck('name')->implode(', ');
            $out[] = __('Assigned as reporting manager for :n employee(s) (:names)', ['n' => $n, 'names' => $names . ($n > 3 ? '…' : '')]);
        }

        $hr = ['attendance' => 'attendance records', 'salary_structures' => 'salary structures', 'employee_loans' => 'loans', 'payroll_records' => 'payroll records'];
        foreach ($hr as $table => $label) {
            if ($n = $db($table, 'employee_id')) {
                $out[] = __(':n :label exist', ['n' => $n, 'label' => __($label)]);
            }
        }

        $docs = [
            'customer_invoices' => 'customer invoices', 'supplier_invoices' => 'supplier invoices', 'expenses' => 'expenses',
            'quotations' => 'quotations', 'jobs' => 'jobs', 'collections' => 'collections', 'payments' => 'payments',
            'journal_vouchers' => 'journal vouchers', 'credit_notes' => 'credit notes', 'finance' => 'finance entries',
        ];
        foreach ($docs as $table => $label) {
            if (\Illuminate\Support\Facades\Schema::hasTable($table) && \Illuminate\Support\Facades\Schema::hasColumn($table, 'user_id') && $n = $db($table, 'user_id')) {
                $out[] = __(':n :label created by this user exist', ['n' => $n, 'label' => __($label)]);
            }
        }

        return $out;
    }
}

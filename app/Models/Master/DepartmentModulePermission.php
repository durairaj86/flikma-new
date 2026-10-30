<?php

namespace App\Models\Master;

use App\Traits\CompanyScopeTrait;
use Illuminate\Database\Eloquent\Model;

class DepartmentModulePermission extends Model
{
    use CompanyScopeTrait;

    protected $fillable = [
        'company_id', 'department_id', 'module',
        'can_view', 'can_create', 'can_edit', 'can_delete',
        'can_approve', 'can_confirm',
    ];

    protected function casts(): array
    {
        return [
            'can_view' => 'boolean',
            'can_create' => 'boolean',
            'can_edit' => 'boolean',
            'can_delete' => 'boolean',
            'can_approve' => 'boolean',
            'can_confirm' => 'boolean',
        ];
    }

    /** @var array<string,\Illuminate\Support\Collection> "company:department" => the effective rows, read once per request */
    private static array $effective = [];

    /**
     * The permission rows that count for a department, keyed by module: the company's own row where it has one, otherwise the shared
     * row (company_id empty) the default departments come with. The class is company-scoped, which hides the shared rows, so a user in
     * a shared department (Finance & Accounts ...) had no row at all and was denied everything until someone opened the department's
     * rights and saved them (that creates the company's own rows).
     */
    public static function effectiveFor(int $departmentId, ?int $companyId = null): \Illuminate\Support\Collection
    {
        $companyId ??= (int) companyId();
        $key = $companyId . ':' . $departmentId;

        return self::$effective[$key] ??= static::withoutGlobalScopes()
            ->where('department_id', $departmentId)
            ->where(fn ($q) => $q->where('company_id', $companyId)->orWhereNull('company_id'))
            ->get()
            ->sortBy(fn ($p) => $p->company_id === null ? 1 : 0)   // own rows first, so unique() keeps them
            ->unique('module')
            ->keyBy('module');
    }

    /** Forget what was read (after a department's rights are saved, and in tests). */
    public static function forgetEffective(): void
    {
        self::$effective = [];
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}

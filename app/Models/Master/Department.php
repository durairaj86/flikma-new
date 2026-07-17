<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use App\Traits\CompanyScopeTrait;

class Department extends Model
{
    use CompanyScopeTrait;

    protected $fillable = ['name', 'code', 'company_id', 'is_active'];

    protected static function booted(): void
    {
        static::created(function (Department $department) {
            $rows = array_map(fn ($module) => [
                'company_id' => $department->company_id,
                'department_id' => $department->id,
                'module' => $module,
                'can_view' => true,
                'can_create' => true,
                'can_edit' => true,
                'can_delete' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ], array_keys(config('modules')));

            DepartmentModulePermission::insert($rows);
        });
    }

    public function permissions()
    {
        return $this->hasMany(DepartmentModulePermission::class);
    }

    public function users()
    {
        return $this->hasMany(\App\Models\User::class);
    }
}

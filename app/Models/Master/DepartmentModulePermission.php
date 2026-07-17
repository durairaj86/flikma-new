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
    ];

    protected function casts(): array
    {
        return [
            'can_view' => 'boolean',
            'can_create' => 'boolean',
            'can_edit' => 'boolean',
            'can_delete' => 'boolean',
        ];
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}

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

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}

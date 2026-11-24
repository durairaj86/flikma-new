<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

/** A user's own rights (users.rights_mode = 'manual'), used instead of their department's. Same columns as DepartmentModulePermission. */
class UserModulePermission extends Model
{
    protected $fillable = [
        'company_id', 'user_id', 'module',
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

    /** @var array<int,\Illuminate\Support\Collection> user id => their rows keyed by module, read once per request */
    private static array $effective = [];

    public static function effectiveFor(int $userId): \Illuminate\Support\Collection
    {
        return self::$effective[$userId] ??= static::where('user_id', $userId)->get()->keyBy('module');
    }

    /** Forget what was read (after a user's rights are saved). */
    public static function forgetEffective(): void
    {
        self::$effective = [];
    }
}

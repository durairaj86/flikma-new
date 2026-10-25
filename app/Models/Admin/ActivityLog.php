<?php

namespace App\Models\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/** Audit trail of what admins did in the /admin panel. */
class ActivityLog extends Model
{
    protected $fillable = ['admin_id', 'action', 'module', 'module_id', 'description', 'ip_address', 'user_agent'];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public static function log(string $action, string $module, $moduleId = null, ?string $description = null): self
    {
        return static::create([
            'admin_id' => auth()->id(),
            'action' => $action,
            'module' => $module,
            'module_id' => $moduleId,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}

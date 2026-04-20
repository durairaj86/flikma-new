<?php

namespace App\Traits\Log;

use App\Models\Log\LogHistory;
use App\Scopes\CompanyScope;
use Illuminate\Support\Facades\Auth;

trait LogHistoryTrait
{
    public static function bootLogHistoryTrait()
    {
        static::addGlobalScope(new CompanyScope());
        static::created(function ($model) {
            // On create, just log the action
            if ($model instanceof LogHistory) return;
            $model->logHistory('created');
        });

        static::updated(function ($model) {
            if ($model instanceof LogHistory) return;
            $model->logHistory('updated');
        });

        static::deleted(function ($model) {
            if ($model instanceof LogHistory) return;
            $model->logHistory('deleted');
        });
    }

    public function logHistory(string $action)
    {
        if ($this instanceof LogHistory) return;

        $user = Auth::user();
        $actionBy = ['id' => $user->id, 'name' => $user->name];
        $changes = null;

        if ($action === 'updated') {
            // Only store changed fields
            $changed = $this->getChanges(); // new values
            if (!empty($changed)) {
                $changes = [
                    'old' => collect($this->getOriginal())->only(array_keys($changed))->toArray(),
                    'new' => $changed
                ];
            }
        }

        // On create or delete, we can just store action
        LogHistory::create([
            'company_id' => $this->company_id ?? null,
            'loggable_type' => get_class($this),
            'loggable_id' => $this->id,
            'loggable_number' => $this->row_no ?? null,
            'loggable_name' => $this->name ?? null,
            'user_id' => $actionBy,
            'action' => $action,
            'changes' => $changes
        ]);
    }
}

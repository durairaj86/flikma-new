<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    /*public static function companySessionCache()
    {
        $cache = cacheName();
        $cacheResponse = $cache->getData(true);
        if ($cacheResponse['status'] == 'logout') {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();
            return redirect()->route('login');
        }
        return $cache;
    }*/

    /**
     * Set common base columns for any model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param int|null $companyId
     * @return void
     */
    protected function setBaseColumns($model, $companyId = null): void
    {
        $model->user_id = Auth::id();
        $model->company_id = $companyId ?? (Auth::user()->company_id ?? null);
    }
}

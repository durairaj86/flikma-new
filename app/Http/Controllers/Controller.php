<?php

namespace App\Http\Controllers;

use App\Models\Master\PeriodClosing;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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

    /**
     * Guard a financial transaction's date against Period Closing. Call
     * this after validation, before save, in any controller that posts to
     * the ledger (invoices, payments, collections, journal vouchers, credit
     * notes) — throws the same shape of error `$request->validate()` would,
     * so existing AJAX error handling on the frontend needs no changes.
     *
     * @param string $date
     * @param string $field The form field name to attach the error to.
     * @throws ValidationException
     */
    protected function assertPeriodOpen($date, string $field = 'date'): void
    {
        if (!PeriodClosing::isLocked($date)) {
            return;
        }

        $lockedThrough = PeriodClosing::lockedThroughDate()->format('d-m-Y');

        throw ValidationException::withMessages([
            $field => "This date falls within a closed accounting period (locked through {$lockedThrough}). Contact your administrator to reopen the period before saving.",
        ]);
    }
}

<?php

use App\Http\Controllers\Portal\PortalAuthController;
use App\Http\Controllers\Portal\PortalController;
use Illuminate\Support\Facades\Route;

/*
 * Customer portal: customers of a company sign in with their own login (guard "portal", invited from the customer
 * page) and see their shipments, invoices, statement and quotations. Never a staff page, never staff rights.
 */
Route::prefix('portal')->group(function () {
    Route::middleware('guest:portal')->group(function () {
        Route::get('/login', [PortalAuthController::class, 'showLogin'])->name('portal.login');
        Route::post('/login', [PortalAuthController::class, 'login'])->middleware('throttle:20,1,portal-login');
        Route::get('/forgot', [PortalAuthController::class, 'showForgot']);
        Route::post('/forgot', [PortalAuthController::class, 'sendReset'])->middleware('throttle:10,1,portal-forgot');
    });
    // the invite / reset link works whether or not someone is signed in
    Route::get('/password/{token}', [PortalAuthController::class, 'showSetPassword'])->where('token', '[A-Za-z0-9]{48}');
    Route::post('/password/{token}', [PortalAuthController::class, 'setPassword'])->where('token', '[A-Za-z0-9]{48}')->middleware('throttle:10,1,portal-password');

    Route::get('/locale/{locale}', function (string $locale) {
        if (in_array($locale, ['en', 'ar'], true)) {
            session(['locale' => $locale]);
        }

        return back();
    })->whereIn('locale', ['en', 'ar']);

    Route::middleware(['auth:portal', 'portal.customer'])->group(function () {
        Route::get('/', [PortalController::class, 'dashboard'])->name('portal.home');
        Route::get('/shipments', [PortalController::class, 'shipments']);
        Route::get('/shipments/{id}', [PortalController::class, 'shipment'])->whereNumber('id');
        Route::get('/invoices', [PortalController::class, 'invoices']);
        Route::get('/invoices/{id}/print', [PortalController::class, 'invoicePrint'])->whereNumber('id');
        Route::get('/statement', [PortalController::class, 'statement']);
        Route::get('/quotations', [PortalController::class, 'quotations']);
        Route::get('/quotations/{id}', [PortalController::class, 'quotation'])->whereNumber('id');
        Route::post('/logout', [PortalAuthController::class, 'logout']);
    });
});

<?php

use App\Http\Controllers\Master\Settings\CompanyController;
use App\Http\Controllers\Master\Settings\InvoiceSettingsController;
use App\Http\Controllers\Master\Settings\SettingsController;
use App\Http\Controllers\Zatca\ZatcaDeviceRegister;
use App\Http\Controllers\Zatca\ZatcaController;
use App\Http\Controllers\Zatca\ZatcaEGSController;

Route::prefix('settings')->group(function () {
    Route::get('/account', [\App\Http\Controllers\Master\UserController::class, 'profile']);
    Route::post('/account', [\App\Http\Controllers\Master\UserController::class, 'profileUpdate'])->name('user.profile.update');

    //Route::view('/company', 'modules.settings.company')->name('settings.company');
    Route::get('/invoice', [InvoiceSettingsController::class, 'edit'])->name('settings.invoice');
    Route::post('/invoice', [InvoiceSettingsController::class, 'store'])->name('settings.invoice.store');
    Route::view('/tax', 'modules.settings.tax')->name('settings.tax');


    Route::post('/company', [CompanyController::class, 'store'])->name('settings.company.store');
    Route::get('/company', [CompanyController::class, 'edit'])->name('settings.company.edit');

    Route::prefix('zatca')->group(function () {
        Route::get('/register', [ZatcaDeviceRegister::class, 'zatcaRegister']);
        Route::post('/register', [ZatcaDeviceRegister::class, 'detailSave']);
        Route::post('/register/save', [ZatcaDeviceRegister::class, 'zatcasave']);
        Route::post('/test/{type}/zatca', [ZatcaController::class, 'submitTestTax']);
        Route::post('/{mode}/register/zatca', [ZatcaEGSController::class, 'zatcaDeviceRegister']);
    });
});

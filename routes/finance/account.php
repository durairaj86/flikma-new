<?php

use App\Http\Controllers\Finance\Account\AccountController;
use Illuminate\Support\Facades\Route;

Route::namespace('finance')->prefix('finance')->group(function () {
    Route::get('/accounts', [AccountController::class, 'index']);
    Route::post('/account/import/preview', [App\Http\Controllers\Finance\Account\AccountImportController::class, 'preview']);
    Route::post('/account/import', [App\Http\Controllers\Finance\Account\AccountImportController::class, 'import']);
    Route::post('/account/data', [AccountController::class, 'fetchAllRows']);
    Route::get('/account/create', [AccountController::class, 'modal']);
    Route::post('/account/create', [AccountController::class, 'store']);
    Route::get('/account/{id}/create', [AccountController::class, 'edit']);
    Route::post('/account/{id}/create', [AccountController::class, 'store']);
    Route::get('accounts/{id}/actions', [AccountController::class, 'actions']);
    Route::post('accounts/{id}/status/{status}', [AccountController::class, 'updateStatus']);
    //Route::get('/account/get/{type}', [AccountController::class, 'parentAccounts']);
});

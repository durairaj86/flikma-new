<?php

use App\Http\Controllers\Finance\OpeningBalance\OpeningBalanceController;
use App\Http\Controllers\Finance\OpeningBalance\OpeningBalanceImportController;
use Illuminate\Support\Facades\Route;

Route::namespace('finance')->prefix('masters/finance')->group(function () {
    Route::get('/opening-balance',                  [OpeningBalanceController::class, 'index'])->name('finance.opening-balance');
    // Import from a file: upload -> map columns -> check -> import
    Route::get('/opening-balance/import',           [OpeningBalanceImportController::class, 'page'])->name('finance.opening-balance.import');
    Route::get('/opening-balance/import/sample',    [OpeningBalanceImportController::class, 'sample'])->name('finance.opening-balance.import.sample');
    Route::post('/opening-balance/import/upload',   [OpeningBalanceImportController::class, 'upload'])->name('finance.opening-balance.import.upload');
    Route::post('/opening-balance/import/check',    [OpeningBalanceImportController::class, 'check'])->name('finance.opening-balance.import.check');
    Route::post('/opening-balance/import/confirm',  [OpeningBalanceImportController::class, 'import'])->name('finance.opening-balance.import.confirm');
    Route::get('/opening-balance/create',           [OpeningBalanceController::class, 'create'])->name('finance.opening-balance.create');
    Route::post('/opening-balance/create',          [OpeningBalanceController::class, 'store'])->name('finance.opening-balance.store');
    Route::get('/opening-balance/{id}/edit',        [OpeningBalanceController::class, 'edit'])->name('finance.opening-balance.edit');
    Route::put('/opening-balance/{id}/edit',        [OpeningBalanceController::class, 'update'])->name('finance.opening-balance.update');
    Route::get('/opening-balance/{id}/view',        [OpeningBalanceController::class, 'view'])->name('finance.opening-balance.view');
    Route::delete('/opening-balance/{id}/delete',   [OpeningBalanceController::class, 'delete'])->name('finance.opening-balance.delete');
});

<?php

use App\Http\Controllers\Finance\OpeningBalance\OpeningBalanceController;
use Illuminate\Support\Facades\Route;

Route::namespace('finance')->prefix('masters/finance')->group(function () {
    Route::get('/opening-balance', [OpeningBalanceController::class, 'index'])->name('finance.opening-balance');
    Route::get('/opening-balance/{id}/view', [OpeningBalanceController::class, 'view'])->name('finance.opening-balance.view');
    Route::delete('/opening-balance/{id}/delete', [OpeningBalanceController::class, 'delete'])->name('finance.opening-balance.delete');
});

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Finance\Asset\AssetController;

Route::namespace('finance')->prefix('finance/asset')->group(function () {
    Route::view('/', 'modules.finance.asset.list')->name('assets.index');
    Route::post('/data', [AssetController::class, 'fetchAllRows'])->name('assets.data');
    Route::get('/create', [AssetController::class, 'modal']);
    Route::post('/create', [AssetController::class, 'store']);
    Route::get('/{id}/create', [AssetController::class, 'edit']);
    Route::post('/{id}/create', [AssetController::class, 'store']);
    Route::get('/{id}/actions', [AssetController::class, 'actions']);
    Route::post('/{id}/status/{status}', [AssetController::class, 'updateStatus']);
    Route::get('/{id}/overview', [AssetController::class, 'overview']);
    Route::get('/{id}/print', [AssetController::class, 'print']);
    Route::post('/{id}/generate-schedule', [AssetController::class, 'generateSchedule']);
    Route::delete('/{id}', [AssetController::class, 'destroy'])->name('assets.destroy');
});

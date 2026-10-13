<?php

use App\Http\Controllers\Finance\Reconciliation\ReconciliationController;
use Illuminate\Support\Facades\Route;

// Bank reconciliation. The path words (/create, /status/) are how the module permission tells view from edit.
Route::prefix('finance/reconciliation')->group(function () {
    Route::get('/', [ReconciliationController::class, 'index'])->name('finance.reconciliation');
    Route::post('/create', [ReconciliationController::class, 'store'])->name('finance.reconciliation.store');
    Route::get('/{id}', [ReconciliationController::class, 'show'])->whereNumber('id')->name('finance.reconciliation.show');
    Route::post('/{id}/status/tick', [ReconciliationController::class, 'tick'])->whereNumber('id');
    Route::post('/{id}/status/update', [ReconciliationController::class, 'update'])->whereNumber('id');
    Route::post('/{id}/create', [ReconciliationController::class, 'addEntry'])->whereNumber('id');
    Route::post('/{id}/status/import', [ReconciliationController::class, 'import'])->whereNumber('id');
    Route::post('/{id}/status/complete', [ReconciliationController::class, 'complete'])->whereNumber('id');
    Route::delete('/{id}', [ReconciliationController::class, 'destroy'])->whereNumber('id');
});

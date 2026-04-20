<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Finance\Adjustment\CreditNoteController;

Route::namespace('finance')->prefix('adjustment')->group(function () {
    Route::prefix('credit-note')->group(function () {
        Route::view('/', 'modules.finance.credit-note.list')->name('adjustments.credit-notes');
        Route::post('/data', [CreditNoteController::class, 'fetchAllRows'])->name('adjustments.credit-notes.data');
        Route::get('/create', [CreditNoteController::class, 'modal']);
        Route::post('/create', [CreditNoteController::class, 'store']);
        Route::get('/{id}/create', [CreditNoteController::class, 'edit']);
        Route::post('/{id}/create', [CreditNoteController::class, 'store']);
        Route::get('/{id}/actions', [CreditNoteController::class, 'actions']);
        Route::post('/{id}/status/{status}', [CreditNoteController::class, 'updateStatus']);
        Route::get('/{id}/overview', [CreditNoteController::class, 'overview']);
        Route::get('/{id}/print', [CreditNoteController::class, 'print']);
    });
});

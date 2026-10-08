<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Finance\Adjustment\CreditNoteController;
use App\Http\Controllers\Finance\Adjustment\DebitNoteController;

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
        Route::delete('/{id}', [CreditNoteController::class, 'delete'])->whereNumber('id');
        Route::get('/{id}/overview', [CreditNoteController::class, 'overview']);
        Route::get('/{id}/print', [CreditNoteController::class, 'print']);
    });

    Route::prefix('debit-note')->group(function () {
        Route::view('/', 'modules.finance.debit-note.list')->name('adjustments.debit-notes');
        Route::post('/data', [DebitNoteController::class, 'fetchAllRows'])->name('adjustments.debit-notes.data');
        Route::get('/create', [DebitNoteController::class, 'modal']);
        Route::post('/create', [DebitNoteController::class, 'store']);
        Route::get('/{id}/create', [DebitNoteController::class, 'edit']);
        Route::post('/{id}/create', [DebitNoteController::class, 'store']);
        Route::get('/{id}/actions', [DebitNoteController::class, 'actions']);
        Route::post('/{id}/status/{status}', [DebitNoteController::class, 'updateStatus']);
        Route::delete('/{id}', [DebitNoteController::class, 'delete'])->whereNumber('id');
        Route::get('/{id}/overview', [DebitNoteController::class, 'overview']);
        Route::get('/{id}/print', [DebitNoteController::class, 'print']);
    });
});

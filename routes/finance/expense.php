<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Finance\Expense\ExpenseController;

Route::namespace('finance')->prefix('finance/expense')->group(function () {
    Route::view('/', 'modules.finance.expense.list')->name('expenses.index');
    Route::post('/data', [ExpenseController::class, 'fetchAllRows'])->name('expenses.data');
    Route::get('/create', [ExpenseController::class, 'modal']);
    Route::post('/create', [ExpenseController::class, 'store']);
    Route::get('/{id}/create', [ExpenseController::class, 'edit']);
    Route::post('/{id}/create', [ExpenseController::class, 'store']);
    Route::get('/{id}/actions', [ExpenseController::class, 'actions']);
    Route::post('/{id}/status/{status}', [ExpenseController::class, 'updateStatus']);
    Route::get('/{id}/overview', [ExpenseController::class, 'overview']);
    Route::get('/{id}/print', [ExpenseController::class, 'print']);
    Route::delete('/{id}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

    Route::get('/{id}/actions', [ExpenseController::class, 'actions']);
});

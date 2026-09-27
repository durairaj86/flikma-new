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
    Route::get('/{id}/overview-drawer', [ExpenseController::class, 'overviewDrawer']);
    Route::get('/{id}/print', [ExpenseController::class, 'print']);
    Route::delete('/{id}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

    Route::post('/ai/scan-receipt', [App\Http\Controllers\Finance\AiAssistController::class, 'scanReceipt'])->name('expenses.ai.scan-receipt');
    Route::post('/ai/suggest-category', [App\Http\Controllers\Finance\AiAssistController::class, 'suggestExpenseCategory'])->name('expenses.ai.suggest-category');
    Route::post('/ai/save-supplier', [App\Http\Controllers\Finance\AiAssistController::class, 'saveScannedSupplier'])->name('expenses.ai.save-supplier');
});

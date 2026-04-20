<?php

use App\Http\Controllers\Transaction\PaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('transaction/payments')->name('transaction.payments.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::post('/data', [PaymentController::class, 'fetchAllRows'])->name('data');

        Route::get('/create', [PaymentController::class, 'modal']);
        Route::post('/create', [PaymentController::class, 'store']);
        Route::get('/{id}/create', [PaymentController::class, 'edit']);
        Route::post('/{id}/create', [PaymentController::class, 'store']);

        // Get supplier invoices
        Route::get('/supplier/{supplier_id}/invoices', [PaymentController::class, 'getSupplierInvoices'])->name('supplier.invoices');

        // View payment
        Route::get('/{id}', [PaymentController::class, 'show'])->name('show');

        // Edit payment
        Route::get('/{id}/edit', [PaymentController::class, 'edit'])->name('edit');

        // Update payment status
        Route::post('/{id}/status/{status}', [PaymentController::class, 'updateStatus'])->name('status.update');

        // Disapprove payment with reason
        Route::post('/{id}/disapprove', [PaymentController::class, 'setDisapprovalReason'])->name('disapprove');

        // Print and download payment
        Route::get('/{id}/print', [PaymentController::class, 'print'])->name('print');
        Route::get('/{id}/download', [PaymentController::class, 'download'])->name('download');

        // Get context menu actions
        Route::get('/{id}/actions', [PaymentController::class, 'actions'])->name('actions');

        // Delete payment
        Route::delete('/{id}', [PaymentController::class, 'destroy'])->name('destroy');
    });
});

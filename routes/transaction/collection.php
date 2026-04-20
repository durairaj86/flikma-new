<?php

use App\Http\Controllers\Transaction\CollectionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('transaction/collections')->name('transaction.collections.')->group(function () {
        Route::get('/', [CollectionController::class, 'index'])->name('index');
        Route::post('/data', [CollectionController::class, 'fetchAllRows'])->name('data');

        Route::get('/create', [CollectionController::class, 'modal']);
        Route::post('/create', [CollectionController::class, 'store']);
        Route::get('/{id}/create', [CollectionController::class, 'edit']);
        Route::post('/{id}/create', [CollectionController::class, 'store']);

        // Get customer invoices
        Route::get('/customer/{customer_id}/invoices', [CollectionController::class, 'getCustomerInvoices'])->name('customer.invoices');

        // View collection
        Route::get('/{id}', [CollectionController::class, 'show'])->name('show');

        // Edit collection
        Route::get('/{id}/edit', [CollectionController::class, 'edit'])->name('edit');

        // Update collection status
        Route::post('/{id}/status/{status}', [CollectionController::class, 'updateStatus'])->name('status.update');

        // Disapprove collection with reason
        Route::post('/{id}/disapprove', [CollectionController::class, 'setDisapprovalReason'])->name('disapprove');

        // Print and download collection
        Route::get('/{id}/print', [CollectionController::class, 'print'])->name('print');
        Route::get('/{id}/download', [CollectionController::class, 'download'])->name('download');

        // Get context menu actions
        Route::get('/{id}/actions', [CollectionController::class, 'actions'])->name('actions');

        // Delete collection
        Route::delete('/{id}', [CollectionController::class, 'destroy'])->name('destroy');
    });
});

<?php

use App\Http\Controllers\Finance\JournalVoucher\JournalVoucherController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('finance/journal-vouchers')->name('finance.journal_vouchers.')->group(function () {
        Route::get('/', [JournalVoucherController::class, 'index'])->name('index');
        Route::post('/data', [JournalVoucherController::class, 'fetchAllRows'])->name('data');

        Route::get('/create', [JournalVoucherController::class, 'modal'])->name('create');
        Route::post('/store', [JournalVoucherController::class, 'store'])->name('store');

        // View journal voucher
        Route::get('/{id}', [JournalVoucherController::class, 'show'])->name('show');

        // Edit journal voucher
        Route::get('/{id}/edit', [JournalVoucherController::class, 'edit'])->name('edit');

        // Update journal voucher status
        Route::post('/{id}/status/{status}', [JournalVoucherController::class, 'updateStatus'])->name('status.update');

        // Disapprove journal voucher with reason
        Route::post('/{id}/disapprove', [JournalVoucherController::class, 'setDisapprovalReason'])->name('disapprove');

        // Print and download journal voucher
        Route::get('/{id}/print', [JournalVoucherController::class, 'print'])->name('print');
        Route::get('/{id}/download', [JournalVoucherController::class, 'download'])->name('download');

        // Get context menu actions
        Route::get('/{id}/actions', [JournalVoucherController::class, 'actions'])->name('actions');

        // Delete journal voucher
        Route::delete('/{id}', [JournalVoucherController::class, 'destroy'])->name('destroy');
    });
});

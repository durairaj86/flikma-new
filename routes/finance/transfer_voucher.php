<?php

use App\Http\Controllers\Finance\JournalVoucher\TransferVoucherController;
use Illuminate\Support\Facades\Route;

// Transfer Voucher: a journal voucher of type Transfer with its own screen and rights (config/modules.php finances.transfer-vouchers)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('finance/transfer-vouchers')->name('finance.transfer_vouchers.')->group(function () {
        Route::get('/', [TransferVoucherController::class, 'index'])->name('index');
        Route::post('/data', [TransferVoucherController::class, 'fetchAllRows'])->name('data');
        // Approve selected (Draft tab): each through updateStatus, needs the Approve right (path ends in /approve)
        Route::post('/bulk/approve', [TransferVoucherController::class, 'bulkApprove']);
        Route::get('/create', [TransferVoucherController::class, 'modal'])->name('create');
        Route::post('/store', [TransferVoucherController::class, 'store'])->name('store');
        Route::get('/{id}/overview-drawer', [TransferVoucherController::class, 'overviewDrawer'])->name('overview-drawer');
        Route::get('/{id}/edit', [TransferVoucherController::class, 'edit'])->name('edit');
        Route::post('/{id}/status/{status}', [TransferVoucherController::class, 'updateStatus'])->name('status.update');
        Route::post('/{id}/disapprove', [TransferVoucherController::class, 'setDisapprovalReason'])->name('disapprove');
        Route::get('/{id}/print', [TransferVoucherController::class, 'print'])->name('print');
        Route::get('/{id}/actions', [TransferVoucherController::class, 'actions'])->name('actions');
        Route::delete('/{id}', [TransferVoucherController::class, 'destroy'])->name('destroy');
    });
});

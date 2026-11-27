<?php

use App\Http\Controllers\Finance\Credit\CreditApprovalController;
use Illuminate\Support\Facades\Route;

// Credit Approval (config/modules.php finances.credit-approvals): approve / disapprove need the Approve right
Route::middleware(['auth', 'verified'])->prefix('finance/credit-approvals')->group(function () {
    Route::get('/', [CreditApprovalController::class, 'index']);
    Route::get('/create', [CreditApprovalController::class, 'modal']);
    Route::post('/create', [CreditApprovalController::class, 'store']);
    Route::get('/customer/{id}', [CreditApprovalController::class, 'customer']);
    Route::post('/{id}/approve', [CreditApprovalController::class, 'approve'])->whereNumber('id');
    Route::post('/{id}/disapprove', [CreditApprovalController::class, 'disapprove'])->whereNumber('id');
});

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Finance\Invoice\ProformaInvoiceController;
use App\Http\Controllers\Finance\Invoice\SupplierInvoiceController;
use App\Http\Controllers\Finance\Invoice\CustomerInvoiceController;

Route::namespace('finance')->prefix('invoice')->group(function () {
    Route::prefix('proforma')->group(function () {
        Route::view('/', 'modules.finance.proforma-invoice.list')->name('invoices.proforma');
        Route::get('/list/{job_id}', [ProformaInvoiceController::class, 'listBasedOnJob'])->name('invoices.proforma-with-job');
        Route::post('/data/{job_id}', [ProformaInvoiceController::class, 'fetchAllRows'])->name('invoices.proforma.data');
        Route::get('/create', [ProformaInvoiceController::class, 'modal']);
        Route::post('/create', [ProformaInvoiceController::class, 'store']);
        Route::get('/{id}/create', [ProformaInvoiceController::class, 'edit']);
        Route::post('/{id}/create', [ProformaInvoiceController::class, 'store']);
        Route::get('/{id}/actions', [ProformaInvoiceController::class, 'actions']);
        Route::post('/{id}/status/{status}', [ProformaInvoiceController::class, 'updateStatus']);
        Route::get('/{id}/overview', [ProformaInvoiceController::class, 'overview']);
        Route::get('/{id}/print', [ProformaInvoiceController::class, 'print']);
    });
    Route::prefix('supplier')->group(function () {
        Route::view('/', 'modules.finance.supplier-invoice.list')->name('invoices.supplier');
        Route::get('/list/{job_id}', [SupplierInvoiceController::class, 'listBasedOnJob'])->name('invoices.supplier-with-job');
        Route::post('/data/{job_id}', [SupplierInvoiceController::class, 'fetchAllRows'])->name('invoices.supplier.data');
        Route::get('/create', [SupplierInvoiceController::class, 'modal']);
        Route::post('/create', [SupplierInvoiceController::class, 'store']);
        Route::get('/{id}/create', [SupplierInvoiceController::class, 'edit']);
        Route::post('/{id}/create', [SupplierInvoiceController::class, 'store']);
        Route::get('/{id}/actions', [SupplierInvoiceController::class, 'actions']);
        Route::post('/{id}/status/{status}', [SupplierInvoiceController::class, 'updateStatus']);
        Route::get('/{id}/overview', [SupplierInvoiceController::class, 'overview']);
        Route::get('/{id}/print', [SupplierInvoiceController::class, 'print']);
    });
    Route::prefix('customer')->group(function () {
        Route::view('/', 'modules.finance.customer-invoice.list')->name('invoices.customer');
        Route::get('/list/{job_id}', [CustomerInvoiceController::class, 'listBasedOnJob'])->name('invoices.customer-with-job');
        Route::post('/data/{job_id}', [CustomerInvoiceController::class, 'fetchAllRows'])->name('invoices.customer.data');
        Route::get('/create', [CustomerInvoiceController::class, 'modal']);
        Route::post('/create', [CustomerInvoiceController::class, 'store']);
        Route::get('/{id}/create', [CustomerInvoiceController::class, 'edit']);
        Route::post('/{id}/create', [CustomerInvoiceController::class, 'store']);
        Route::get('/{id}/actions', [CustomerInvoiceController::class, 'actions']);
        Route::post('/{id}/status/{status}', [CustomerInvoiceController::class, 'updateStatus']);
        Route::get('/{id}/overview', [CustomerInvoiceController::class, 'overview']);
        Route::get('/{id}/print', [CustomerInvoiceController::class, 'print']);
    });
});

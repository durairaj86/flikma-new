<?php

use App\Http\Controllers\Enquiry\EnquiryController;
use App\Http\Controllers\Quotation\QuotationController;
use Illuminate\Support\Facades\Route;

Route::namespace('sales')->prefix('sales')->group(function () {
    Route::view('/enquiries', 'modules.enquiry.list')->name('enquiries');
    Route::post('/enquiry/data', [\App\Http\Controllers\Enquiry\EnquiryController::class, 'fetchAllRows'])->name('enquiries.data');
    Route::get('/enquiry/create', [\App\Http\Controllers\Enquiry\EnquiryController::class, 'modal']);
    Route::post('/enquiry/create', [\App\Http\Controllers\Enquiry\EnquiryController::class, 'store']);
    Route::get('/enquiry/{id}/create', [\App\Http\Controllers\Enquiry\EnquiryController::class, 'edit']);
    Route::post('/enquiry/{id}/create', [\App\Http\Controllers\Enquiry\EnquiryController::class, 'store']);
    Route::get('/enquiry/{id}/actions', [\App\Http\Controllers\Enquiry\EnquiryController::class, 'actions']);
    Route::post('/enquiry/{id}/status/{status}', [\App\Http\Controllers\Enquiry\EnquiryController::class, 'updateStatus']);
    Route::get('/enquiry/{id}/overview', [\App\Http\Controllers\Enquiry\EnquiryController::class, 'overview']);
    Route::get('/enquiry/{id}/print', [EnquiryController::class, 'print']);
    Route::get('/enquiry/{id}/get-data', [EnquiryController::class, 'getEnquiryData']);

    Route::view('/quotations', 'modules.quotation.list')->name('quotations');
    Route::post('/quotation/data', [\App\Http\Controllers\Quotation\QuotationController::class, 'fetchAllRows'])->name('quotations.data');
    Route::get('/quotation/create', [\App\Http\Controllers\Quotation\QuotationController::class, 'modal']);
    Route::get('/quotation/create/from-enquiry/{enquiry_id}', [\App\Http\Controllers\Quotation\QuotationController::class, 'createFromEnquiry']);
    Route::post('/quotation/create', [\App\Http\Controllers\Quotation\QuotationController::class, 'store']);
    Route::get('/quotation/{id}/create', [\App\Http\Controllers\Quotation\QuotationController::class, 'edit']);
    Route::post('/quotation/{id}/create', [\App\Http\Controllers\Quotation\QuotationController::class, 'store']);
    Route::get('/quotation/{id}/actions', [\App\Http\Controllers\Quotation\QuotationController::class, 'actions']);
    Route::post('/quotation/{id}/status/{status}', [\App\Http\Controllers\Quotation\QuotationController::class, 'updateStatus']);
    Route::get('/quotation/{id}/overview', [\App\Http\Controllers\Quotation\QuotationController::class, 'overview']);
    Route::get('/quotation/{id}/print', [QuotationController::class, 'print']);
    Route::get('/quotation/{id}/email-data', [QuotationController::class, 'getQuotationEmailData']);
    Route::post('/quotation/send-email', [QuotationController::class, 'sendEmail']);

    Route::get('/overview', [\App\Http\Controllers\Sales\SalesOverviewController::class, 'index'])->name('sales.overview');
});

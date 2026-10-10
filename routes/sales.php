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
    Route::delete('/enquiry/{id}', [\App\Http\Controllers\Enquiry\EnquiryController::class, 'delete'])->whereNumber('id');
    Route::post('/enquiry/{id}/create', [\App\Http\Controllers\Enquiry\EnquiryController::class, 'store']);
    Route::get('/enquiry/{id}/actions', [\App\Http\Controllers\Enquiry\EnquiryController::class, 'actions']);
    Route::post('/enquiry/{id}/status/{status}', [\App\Http\Controllers\Enquiry\EnquiryController::class, 'updateStatus']);
    Route::get('/enquiry/{id}/overview', [\App\Http\Controllers\Enquiry\EnquiryController::class, 'overview']);
    Route::get('/enquiry/{id}/overview-drawer', [\App\Http\Controllers\Enquiry\EnquiryController::class, 'overviewDrawer']);
    Route::get('/enquiry/{id}/print', [EnquiryController::class, 'print']);
    Route::get('/enquiry/{id}/get-data', [EnquiryController::class, 'getEnquiryData']);

    // Temporarily serving the static/basic list (dynamic column-settings
    // version kept at modules.quotation.list — swap back by changing this
    // one view name when the dynamic list is wanted again).
    Route::view('/quotations', 'modules.quotation.list-basic')->name('quotations');
    Route::post('/quotation/data', [\App\Http\Controllers\Quotation\QuotationController::class, 'fetchAllRows'])->name('quotations.data');
    Route::get('/quotation/create', [\App\Http\Controllers\Quotation\QuotationController::class, 'modal']);
    Route::get('/quotation/create/from-enquiry/{enquiry_id}', [\App\Http\Controllers\Quotation\QuotationController::class, 'createFromEnquiry']);
    Route::post('/quotation/create', [\App\Http\Controllers\Quotation\QuotationController::class, 'store']);
    Route::get('/quotation/{id}/create', [\App\Http\Controllers\Quotation\QuotationController::class, 'edit']);
    Route::delete('/quotation/{id}', [\App\Http\Controllers\Quotation\QuotationController::class, 'delete'])->whereNumber('id');
    Route::post('/quotation/{id}/create', [\App\Http\Controllers\Quotation\QuotationController::class, 'store']);
    Route::get('/quotation/{id}/actions', [\App\Http\Controllers\Quotation\QuotationController::class, 'actions']);
    Route::post('/quotation/{id}/status/{status}', [\App\Http\Controllers\Quotation\QuotationController::class, 'updateStatus']);
    Route::get('/quotation/{id}/overview', [\App\Http\Controllers\Quotation\QuotationController::class, 'overview']);
    Route::get('/quotation/{id}/overview-drawer', [\App\Http\Controllers\Quotation\QuotationController::class, 'overviewDrawer']);
    Route::get('/quotation/{id}/print', [QuotationController::class, 'print']);
    Route::get('/quotation/{id}/email-data', [QuotationController::class, 'getQuotationEmailData']);
    Route::post('/quotation/send-email', [QuotationController::class, 'sendEmail']);

    // Rate sheets
    Route::view('/rate-sheets', 'modules.rate-sheet.list')->name('rate-sheets');
    Route::post('/rate-sheet/data', [\App\Http\Controllers\Sales\RateSheetController::class, 'fetchAllRows'])->name('rate-sheets.data');
    Route::get('/rate-sheet/lookup', [\App\Http\Controllers\Sales\RateSheetController::class, 'lookup']);
    Route::get('/rate-sheet/create', [\App\Http\Controllers\Sales\RateSheetController::class, 'modal']);
    Route::post('/rate-sheet/create', [\App\Http\Controllers\Sales\RateSheetController::class, 'store']);
    Route::get('/rate-sheet/{id}/create', [\App\Http\Controllers\Sales\RateSheetController::class, 'edit']);
    Route::post('/rate-sheet/{id}/create', [\App\Http\Controllers\Sales\RateSheetController::class, 'store']);
    Route::get('/rate-sheet/{id}/actions', [\App\Http\Controllers\Sales\RateSheetController::class, 'actions']);
    Route::post('/rate-sheet/{id}/status/{status}', [\App\Http\Controllers\Sales\RateSheetController::class, 'updateStatus']);
    Route::post('/rate-sheet/{id}/duplicate', [\App\Http\Controllers\Sales\RateSheetController::class, 'duplicate']);
    Route::get('/rate-sheet/{id}/overview', [\App\Http\Controllers\Sales\RateSheetController::class, 'overview']);
    Route::delete('/rate-sheet/{id}', [\App\Http\Controllers\Sales\RateSheetController::class, 'delete'])->whereNumber('id');

});

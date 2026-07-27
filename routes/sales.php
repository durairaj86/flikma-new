<?php

use App\Http\Controllers\Enquiry\EnquiryController;
use App\Http\Controllers\Quotation\QuotationController;
use App\Http\Controllers\QuotationNew\QuotationNewController;
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
    Route::post('/quotation/{id}/create', [\App\Http\Controllers\Quotation\QuotationController::class, 'store']);
    Route::get('/quotation/{id}/actions', [\App\Http\Controllers\Quotation\QuotationController::class, 'actions']);
    Route::post('/quotation/{id}/status/{status}', [\App\Http\Controllers\Quotation\QuotationController::class, 'updateStatus']);
    Route::get('/quotation/{id}/overview', [\App\Http\Controllers\Quotation\QuotationController::class, 'overview']);
    Route::get('/quotation/{id}/overview-drawer', [\App\Http\Controllers\Quotation\QuotationController::class, 'overviewDrawer']);
    Route::get('/quotation/{id}/print', [QuotationController::class, 'print']);
    Route::get('/quotation/{id}/email-data', [QuotationController::class, 'getQuotationEmailData']);
    Route::post('/quotation/send-email', [QuotationController::class, 'sendEmail']);

    Route::get('/overview', [\App\Http\Controllers\Sales\SalesOverviewController::class, 'index'])->name('sales.overview');

    // ─── Quotation New ────────────────────────────────────────────────────
    Route::get('/quotations-new',                         [QuotationNewController::class, 'index'])->name('quotations-new');
    Route::post('/quotations-new/data',                   [QuotationNewController::class, 'fetchAllRows'])->name('quotations-new.data');

    // Wizard – step 1 (create)
    Route::get('/quotations-new/create',                  [QuotationNewController::class, 'create']);
    Route::post('/quotations-new/step1/store',            [QuotationNewController::class, 'storeStep1']);

    // Wizard – steps 2-5
    Route::get('/quotations-new/{id}/step2',              [QuotationNewController::class, 'step2']);
    Route::post('/quotations-new/{id}/step2/store',       [QuotationNewController::class, 'storeStep2']);

    Route::get('/quotations-new/{id}/step3',              [QuotationNewController::class, 'step3']);
    Route::post('/quotations-new/{id}/step3/store',       [QuotationNewController::class, 'storeStep3']);

    Route::get('/quotations-new/{id}/step4',              [QuotationNewController::class, 'step4']);
    Route::post('/quotations-new/{id}/step4/store',       [QuotationNewController::class, 'storeStep4']);

    Route::get('/quotations-new/{id}/step5',              [QuotationNewController::class, 'step5']);
    Route::post('/quotations-new/{id}/finalise',          [QuotationNewController::class, 'finalise']);

    // Edit modal
    Route::get('/quotations-new/{id}/edit',               [QuotationNewController::class, 'editModal']);
    Route::put('/quotations-new/{id}/update',             [QuotationNewController::class, 'update']);
    Route::post('/quotations-new/{id}/update',            [QuotationNewController::class, 'update']); // fallback for modal POST

    // Context menu / status
    Route::get('/quotations-new/{id}/actions',            [QuotationNewController::class, 'actions']);
    Route::post('/quotations-new/{id}/status/{status}',   [QuotationNewController::class, 'updateStatus']);

    // Costing modal
    Route::get('/quotations-new/{quotationId}/costing',           [QuotationNewController::class, 'costingModal']);
    Route::get('/quotations-new/{quotationId}/costing/{chargeId}',[QuotationNewController::class, 'costingModal']);
    Route::post('/quotations-new/{quotationId}/costing/store',    [QuotationNewController::class, 'storeCosting']);
    Route::delete('/quotations-new/charge/{chargeId}/delete',     [QuotationNewController::class, 'deleteCharge']);

    // Inline charge row template (for AJAX add row)
    Route::get('/quotations-new/charge-row-template',     function () {
        return view('modules.quotation-new._charge-row', ['charge' => null]);
    });
});

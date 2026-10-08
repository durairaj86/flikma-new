<?php

use App\Http\Controllers\BL\WaybillController;
use App\Http\Controllers\BL\AirwayBillController;
use App\Http\Controllers\BL\SeawayBillController;
use Illuminate\Support\Facades\Route;

Route::prefix('bl')->group(function () {
    // Containers of a job (container picker on the bill forms)
    Route::get('/job-containers/{job}', [\App\Http\Controllers\BL\BillContainerController::class, 'forJob'])->whereNumber('job');

    // Waybill routes
    Route::view('/waybill', 'modules.bl.waybill.list')->name('bl.waybill');
    Route::post('/waybill/data', [WaybillController::class, 'fetchAllRows'])->name('bl.waybill.data');
    Route::get('/waybill/create', [WaybillController::class, 'modal']);
    Route::post('/waybill/create', [WaybillController::class, 'store']);
    Route::get('/waybill/{id}/create', [WaybillController::class, 'edit']);
    Route::post('/waybill/{id}/create', [WaybillController::class, 'store']);
    Route::get('/waybill/{id}/overview', [WaybillController::class, 'overview']);
    Route::get('/waybill/{id}/print', [WaybillController::class, 'print']);
    Route::get('/waybill/{id}/actions', [WaybillController::class, 'actions']);
    Route::post('/waybill/{id}/status/{status}', [WaybillController::class, 'updateStatus']);

    // Airway Bill routes
    Route::view('/airway-bill', 'modules.bl.airway-bill.list')->name('bl.airway-bill');
    Route::post('/airway-bill/data', [AirwayBillController::class, 'fetchAllRows'])->name('bl.airway-bill.data');
    Route::get('/airway-bill/create', [AirwayBillController::class, 'modal']);
    Route::post('/airway-bill/create', [AirwayBillController::class, 'store']);
    Route::get('/airway-bill/{id}/create', [AirwayBillController::class, 'edit']);
    Route::post('/airway-bill/{id}/create', [AirwayBillController::class, 'store']);
    Route::get('/airway-bill/{id}/overview', [AirwayBillController::class, 'overview']);
    Route::get('/airway-bill/{id}/print', [AirwayBillController::class, 'print']);
    Route::get('/airway-bill/{id}/actions', [AirwayBillController::class, 'actions']);
    Route::post('/airway-bill/{id}/status/{status}', [AirwayBillController::class, 'updateStatus']);

    // Seaway Bill routes
    Route::view('/seaway', 'modules.bl.seaway.list')->name('bl.seaway');
    Route::post('/seaway/data', [SeawayBillController::class, 'fetchAllRows'])->name('bl.seaway.data');
    Route::get('/seaway/create', [SeawayBillController::class, 'modal']);
    Route::post('/seaway/create', [SeawayBillController::class, 'store']);
    Route::get('/seaway/{id}/create', [SeawayBillController::class, 'edit']);
    Route::post('/seaway/{id}/create', [SeawayBillController::class, 'store']);
    Route::get('/seaway/{id}/overview', [SeawayBillController::class, 'overview']);
    Route::get('/seaway/{id}/print', [SeawayBillController::class, 'print']);
    Route::get('/seaway/{id}/actions', [SeawayBillController::class, 'actions']);
    Route::post('/seaway/{id}/status/{status}', [SeawayBillController::class, 'updateStatus']);

    // Master B/L with its house bills
    Route::view('/master-bl', 'modules.master-bl.list')->name('bl.master-bl');
    Route::post('/master-bl/data', [\App\Http\Controllers\BL\MasterBlController::class, 'fetchAllRows'])->name('bl.master-bl.data');
    Route::get('/master-bl/create', [\App\Http\Controllers\BL\MasterBlController::class, 'modal']);
    Route::post('/master-bl/create', [\App\Http\Controllers\BL\MasterBlController::class, 'store']);
    Route::get('/master-bl/house-bills/{mode}/{masterId?}', [\App\Http\Controllers\BL\MasterBlController::class, 'houseBills'])->whereIn('mode', ['sea', 'air'])->whereNumber('masterId');
    Route::get('/master-bl/{id}/create', [\App\Http\Controllers\BL\MasterBlController::class, 'edit'])->whereNumber('id');
    Route::post('/master-bl/{id}/create', [\App\Http\Controllers\BL\MasterBlController::class, 'store'])->whereNumber('id');
    Route::get('/master-bl/{id}/actions', [\App\Http\Controllers\BL\MasterBlController::class, 'actions'])->whereNumber('id');
    Route::post('/master-bl/{id}/status/{status}', [\App\Http\Controllers\BL\MasterBlController::class, 'updateStatus'])->whereNumber('id');
    Route::get('/master-bl/{id}/overview', [\App\Http\Controllers\BL\MasterBlController::class, 'overview'])->whereNumber('id');
    Route::delete('/master-bl/{id}', [\App\Http\Controllers\BL\MasterBlController::class, 'delete'])->whereNumber('id');
});

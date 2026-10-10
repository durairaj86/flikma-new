<?php

use App\Http\Controllers\Job\JobController;
use Illuminate\Support\Facades\Route;

Route::namespace('operations')->prefix('operation')->group(function () {
    Route::get('/job-overview', [\App\Http\Controllers\Job\JobOverviewController::class, 'index'])->name('operation.job-overview');
    // Carrier bookings
    Route::view('/bookings', 'modules.booking.list')->name('bookings');
    Route::post('/booking/data', [\App\Http\Controllers\Booking\BookingController::class, 'fetchAllRows'])->name('bookings.data');
    Route::get('/booking/create', [\App\Http\Controllers\Booking\BookingController::class, 'modal']);
    Route::post('/booking/create', [\App\Http\Controllers\Booking\BookingController::class, 'store']);
    Route::get('/booking/{id}/create', [\App\Http\Controllers\Booking\BookingController::class, 'edit']);
    Route::post('/booking/{id}/create', [\App\Http\Controllers\Booking\BookingController::class, 'store']);
    Route::get('/booking/{id}/actions', [\App\Http\Controllers\Booking\BookingController::class, 'actions']);
    Route::post('/booking/{id}/status/{status}', [\App\Http\Controllers\Booking\BookingController::class, 'updateStatus']);
    Route::get('/booking/{id}/overview', [\App\Http\Controllers\Booking\BookingController::class, 'overview']);
    Route::delete('/booking/{id}', [\App\Http\Controllers\Booking\BookingController::class, 'delete'])->whereNumber('id');

    // Cargo arrival notice / notification
    Route::view('/arrival-notices', 'modules.arrival-notice.list')->name('arrival-notices');
    Route::post('/arrival-notice/data', [\App\Http\Controllers\Arrival\ArrivalNoticeController::class, 'fetchAllRows'])->name('arrival-notices.data');
    Route::get('/arrival-notice/create', [\App\Http\Controllers\Arrival\ArrivalNoticeController::class, 'modal']);
    Route::post('/arrival-notice/create', [\App\Http\Controllers\Arrival\ArrivalNoticeController::class, 'store']);
    Route::get('/arrival-notice/{id}/create', [\App\Http\Controllers\Arrival\ArrivalNoticeController::class, 'edit'])->whereNumber('id');
    Route::post('/arrival-notice/{id}/create', [\App\Http\Controllers\Arrival\ArrivalNoticeController::class, 'store'])->whereNumber('id');
    Route::get('/arrival-notice/{id}/actions', [\App\Http\Controllers\Arrival\ArrivalNoticeController::class, 'actions'])->whereNumber('id');
    Route::post('/arrival-notice/{id}/status/{status}', [\App\Http\Controllers\Arrival\ArrivalNoticeController::class, 'updateStatus'])->whereNumber('id');
    Route::get('/arrival-notice/{id}/overview', [\App\Http\Controllers\Arrival\ArrivalNoticeController::class, 'overview'])->whereNumber('id');
    Route::get('/arrival-notice/{id}/print', [\App\Http\Controllers\Arrival\ArrivalNoticeController::class, 'print'])->whereNumber('id');
    Route::delete('/arrival-notice/{id}', [\App\Http\Controllers\Arrival\ArrivalNoticeController::class, 'delete'])->whereNumber('id');

    // Delivery orders / dispatch
    Route::view('/delivery-orders', 'modules.delivery-order.list')->name('delivery-orders');
    Route::post('/delivery-order/data', [\App\Http\Controllers\Delivery\DeliveryOrderController::class, 'fetchAllRows'])->name('delivery-orders.data');
    Route::get('/delivery-order/create', [\App\Http\Controllers\Delivery\DeliveryOrderController::class, 'modal']);
    Route::post('/delivery-order/create', [\App\Http\Controllers\Delivery\DeliveryOrderController::class, 'store']);
    Route::get('/delivery-order/{id}/create', [\App\Http\Controllers\Delivery\DeliveryOrderController::class, 'edit']);
    Route::post('/delivery-order/{id}/create', [\App\Http\Controllers\Delivery\DeliveryOrderController::class, 'store']);
    Route::get('/delivery-order/{id}/actions', [\App\Http\Controllers\Delivery\DeliveryOrderController::class, 'actions']);
    Route::post('/delivery-order/{id}/status/{status}', [\App\Http\Controllers\Delivery\DeliveryOrderController::class, 'updateStatus']);
    Route::get('/delivery-order/{id}/overview', [\App\Http\Controllers\Delivery\DeliveryOrderController::class, 'overview']);
    Route::get('/delivery-order/{id}/print', [\App\Http\Controllers\Delivery\DeliveryOrderController::class, 'print']);
    Route::delete('/delivery-order/{id}', [\App\Http\Controllers\Delivery\DeliveryOrderController::class, 'delete'])->whereNumber('id');

    // Customs clearance (progress of each job's clearance record)
    Route::view('/customs', 'modules.customs.list')->name('customs');
    Route::post('/customs/data', [\App\Http\Controllers\Job\CustomsController::class, 'fetchAllRows'])->name('customs.data');
    Route::get('/customs/{id}/create', [\App\Http\Controllers\Job\CustomsController::class, 'edit']);
    Route::post('/customs/{id}/create', [\App\Http\Controllers\Job\CustomsController::class, 'store']);
    Route::get('/customs/{id}/actions', [\App\Http\Controllers\Job\CustomsController::class, 'actions']);
    Route::post('/customs/{id}/status/{status}', [\App\Http\Controllers\Job\CustomsController::class, 'updateStatus']);
    Route::get('/customs/{id}/overview', [\App\Http\Controllers\Job\CustomsController::class, 'overview']);

    Route::view('/jobs', 'modules.job.list', ['fixedColumns' => true])->name('jobs');
    // Same list where each user can choose and arrange the columns.
    Route::view('/jobs/new', 'modules.job.list', ['fixedColumns' => false])->name('jobs.custom');
    Route::post('/job/data', [\App\Http\Controllers\Job\JobController::class, 'fetchAllRows'])->name('jobs.data');
    Route::get('/job/insights', [\App\Http\Controllers\Job\JobController::class, 'insights']);
    Route::post('/job/ask', [\App\Http\Controllers\Job\JobController::class, 'ask']);
    Route::get('/job/create', [\App\Http\Controllers\Job\JobController::class, 'modal']);
    Route::post('/job/create', [\App\Http\Controllers\Job\JobController::class, 'store']);
    Route::get('/job/{id}/create', [\App\Http\Controllers\Job\JobController::class, 'edit']);
    Route::post('/job/{id}/create', [\App\Http\Controllers\Job\JobController::class, 'store']);
    Route::get('/job/{id}/actions', [\App\Http\Controllers\Job\JobController::class, 'actions']);
    Route::post('/job/{id}/status/{status}', [\App\Http\Controllers\Job\JobController::class, 'updateStatus']);
    Route::get('/job/{id}/overview', [\App\Http\Controllers\Job\JobController::class, 'overview']);
    Route::get('/job/{id}/overview-drawer', [\App\Http\Controllers\Job\JobController::class, 'overviewDrawer']);
    Route::get('/job/{id}/delete', [\App\Http\Controllers\Job\JobController::class, 'delete']);
    Route::get('/job/{id}/print', [JobController::class, 'print']);

    // Drivers

    // Vehicles

    // Trips
    Route::view('/trips', 'modules.fleet-trip.list')->name('trips');
    Route::post('/trip/data', [\App\Http\Controllers\Fleet\TripController::class, 'fetchAllRows'])->name('trips.data');
    Route::get('/trip/create', [\App\Http\Controllers\Fleet\TripController::class, 'modal']);
    Route::post('/trip/create', [\App\Http\Controllers\Fleet\TripController::class, 'store']);
    Route::get('/trip/{id}/create', [\App\Http\Controllers\Fleet\TripController::class, 'edit'])->whereNumber('id');
    Route::post('/trip/{id}/create', [\App\Http\Controllers\Fleet\TripController::class, 'store'])->whereNumber('id');
    Route::get('/trip/{id}/actions', [\App\Http\Controllers\Fleet\TripController::class, 'actions'])->whereNumber('id');
    Route::post('/trip/{id}/status/{status}', [\App\Http\Controllers\Fleet\TripController::class, 'updateStatus'])->whereNumber('id');
    Route::get('/trip/{id}/overview', [\App\Http\Controllers\Fleet\TripController::class, 'overview'])->whereNumber('id');
    Route::delete('/trip/{id}', [\App\Http\Controllers\Fleet\TripController::class, 'delete'])->whereNumber('id');

    // Documents center
    Route::view('/documents', 'modules.document-center.list')->name('documents');
    Route::post('/document/data', [\App\Http\Controllers\Documents\DocumentCenterController::class, 'fetchAllRows'])->name('documents.data');
    Route::get('/document/create', [\App\Http\Controllers\Documents\DocumentCenterController::class, 'modal']);
    Route::post('/document/create', [\App\Http\Controllers\Documents\DocumentCenterController::class, 'store']);
    Route::get('/document/owners/{type}', [\App\Http\Controllers\Documents\DocumentCenterController::class, 'owners']);
    Route::get('/document/{id}/create', [\App\Http\Controllers\Documents\DocumentCenterController::class, 'edit'])->whereNumber('id');
    Route::post('/document/{id}/create', [\App\Http\Controllers\Documents\DocumentCenterController::class, 'store'])->whereNumber('id');
    Route::get('/document/{id}/actions', [\App\Http\Controllers\Documents\DocumentCenterController::class, 'actions'])->whereNumber('id');
    Route::get('/document/{id}/file', [\App\Http\Controllers\Documents\DocumentCenterController::class, 'download'])->whereNumber('id');
    Route::delete('/document/{id}', [\App\Http\Controllers\Documents\DocumentCenterController::class, 'delete'])->whereNumber('id');

    // Shipment tracking
    Route::get('/tracking', [\App\Http\Controllers\Job\TrackingController::class, 'index'])->name('tracking');
    Route::get('/tracking/{id}', [\App\Http\Controllers\Job\TrackingController::class, 'edit'])->whereNumber('id');
    Route::post('/tracking/{id}', [\App\Http\Controllers\Job\TrackingController::class, 'save'])->whereNumber('id');
    Route::post('/tracking/{id}/next', [\App\Http\Controllers\Job\TrackingController::class, 'advance'])->whereNumber('id');
    Route::post('/tracking/{id}/share', [\App\Http\Controllers\Job\TrackingController::class, 'share'])->whereNumber('id');

    // Demurrage & detention
    Route::get('/demurrage', [\App\Http\Controllers\Job\DemurrageController::class, 'index'])->name('demurrage');
    Route::get('/demurrage/{id}', [\App\Http\Controllers\Job\DemurrageController::class, 'edit'])->whereNumber('id');
    Route::post('/demurrage/{id}', [\App\Http\Controllers\Job\DemurrageController::class, 'save'])->whereNumber('id');
});

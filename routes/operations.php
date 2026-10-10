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

    Route::view('/jobs', 'modules.job.list')->name('jobs');
    Route::post('/job/data', [\App\Http\Controllers\Job\JobController::class, 'fetchAllRows'])->name('jobs.data');
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
});

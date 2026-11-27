<?php

use Illuminate\Support\Facades\Route;

// Fleet (sidebar menu Fleet, user rights group Fleet): vehicles and drivers. Trips stay under /operation/trips (routes/operations.php).
Route::prefix('fleet')->group(function () {
    Route::view('/drivers', 'modules.fleet-driver.list')->name('drivers');
    Route::post('/driver/data', [\App\Http\Controllers\Fleet\DriverController::class, 'fetchAllRows'])->name('drivers.data');
    Route::get('/driver/create', [\App\Http\Controllers\Fleet\DriverController::class, 'modal']);
    Route::post('/driver/create', [\App\Http\Controllers\Fleet\DriverController::class, 'store']);
    Route::get('/driver/{id}/create', [\App\Http\Controllers\Fleet\DriverController::class, 'edit'])->whereNumber('id');
    Route::post('/driver/{id}/create', [\App\Http\Controllers\Fleet\DriverController::class, 'store'])->whereNumber('id');
    Route::get('/driver/{id}/actions', [\App\Http\Controllers\Fleet\DriverController::class, 'actions'])->whereNumber('id');
    Route::post('/driver/{id}/status/{status}', [\App\Http\Controllers\Fleet\DriverController::class, 'updateStatus'])->whereNumber('id');
    Route::get('/driver/{id}/overview', [\App\Http\Controllers\Fleet\DriverController::class, 'overview'])->whereNumber('id');
    Route::delete('/driver/{id}', [\App\Http\Controllers\Fleet\DriverController::class, 'delete'])->whereNumber('id');
    Route::view('/vehicles', 'modules.fleet-vehicle.list')->name('vehicles');
    Route::post('/vehicle/data', [\App\Http\Controllers\Fleet\VehicleController::class, 'fetchAllRows'])->name('vehicles.data');
    Route::get('/vehicle/create', [\App\Http\Controllers\Fleet\VehicleController::class, 'modal']);
    Route::post('/vehicle/create', [\App\Http\Controllers\Fleet\VehicleController::class, 'store']);
    Route::get('/vehicle/{id}/create', [\App\Http\Controllers\Fleet\VehicleController::class, 'edit'])->whereNumber('id');
    Route::post('/vehicle/{id}/create', [\App\Http\Controllers\Fleet\VehicleController::class, 'store'])->whereNumber('id');
    Route::get('/vehicle/{id}/actions', [\App\Http\Controllers\Fleet\VehicleController::class, 'actions'])->whereNumber('id');
    Route::post('/vehicle/{id}/status/{status}', [\App\Http\Controllers\Fleet\VehicleController::class, 'updateStatus'])->whereNumber('id');
    Route::get('/vehicle/{id}/overview', [\App\Http\Controllers\Fleet\VehicleController::class, 'overview'])->whereNumber('id');
    Route::delete('/vehicle/{id}', [\App\Http\Controllers\Fleet\VehicleController::class, 'delete'])->whereNumber('id');
});

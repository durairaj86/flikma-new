<?php

use App\Http\Controllers\Job\JobController;
use Illuminate\Support\Facades\Route;

Route::namespace('operations')->prefix('operation')->group(function () {
    Route::get('/job-overview', [\App\Http\Controllers\Job\JobOverviewController::class, 'index'])->name('operation.job-overview');
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

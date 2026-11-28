<?php

use App\Http\Controllers\Job\CostSheetController;
use Illuminate\Support\Facades\Route;

// Cost sheet (Settings › Finance › Cost sheet on), rights in config/modules.php finances.cost-sheets.
// One sheet per job: it is opened, saved and invoiced by the job's id; the list and delete use the sheet's own id.
Route::middleware(['auth', 'verified'])->prefix('invoice/cost-sheets')->group(function () {
    Route::get('/', [CostSheetController::class, 'index']);
    Route::get('/create', [CostSheetController::class, 'picker']);
    Route::post('/create', [CostSheetController::class, 'save']);   // a new sheet (job_id in the body): needs the Create right
    Route::get('/job/{id}', [CostSheetController::class, 'form'])->whereNumber('id');
    Route::post('/job/{id}', [CostSheetController::class, 'save'])->whereNumber('id');
    Route::post('/job/{id}/invoice', [CostSheetController::class, 'invoice'])->whereNumber('id');
    Route::delete('/{id}', [CostSheetController::class, 'destroy'])->whereNumber('id');
});

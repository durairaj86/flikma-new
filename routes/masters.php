<?php

use Illuminate\Support\Facades\Route;

Route::view('/masters/services', 'modules.master.logistics-service.list')->name('services');
Route::post('/masters/services/data', [\App\Http\Controllers\Master\LogisticServiceController::class, 'fetchAllRows'])->name('logistics-service.data');
Route::get('/masters/services/create', [\App\Http\Controllers\Master\LogisticServiceController::class, 'modal']);
Route::post('/masters/services/create', [\App\Http\Controllers\Master\LogisticServiceController::class, 'store']);
Route::get('/masters/services/{id}/create', [\App\Http\Controllers\Master\LogisticServiceController::class, 'edit']);
Route::post('/masters/services/{id}/create', [\App\Http\Controllers\Master\LogisticServiceController::class, 'store']);
Route::get('masters/services/{id}/actions', [\App\Http\Controllers\Master\LogisticServiceController::class, 'actions']);

Route::view('/masters/activities', 'modules.master.logistics-activity.list')->name('activities');
Route::post('/masters/activities/data', [\App\Http\Controllers\Master\LogisticActivityController::class, 'fetchAllRows'])->name('logistics-activity.data');
Route::get('/masters/activities/create', [\App\Http\Controllers\Master\LogisticActivityController::class, 'modal']);
Route::post('/masters/activities/create', [\App\Http\Controllers\Master\LogisticActivityController::class, 'store']);
Route::get('/masters/activities/{id}/create', [\App\Http\Controllers\Master\LogisticActivityController::class, 'edit']);
Route::post('/masters/activities/{id}/create', [\App\Http\Controllers\Master\LogisticActivityController::class, 'store']);
Route::get('masters/activities/{id}/actions', [\App\Http\Controllers\Master\LogisticActivityController::class, 'actions']);

Route::view('/masters/package/codes', 'modules.master.package-code.list')->name('package-codes');
Route::post('/masters/package/code/data', [\App\Http\Controllers\Master\PackageCodeController::class, 'fetchAllRows'])->name('package-code.data');
Route::get('/masters/package/code/create', [\App\Http\Controllers\Master\PackageCodeController::class, 'modal']);
Route::post('/masters/package/code/create', [\App\Http\Controllers\Master\PackageCodeController::class, 'store']);
Route::get('/masters/package/code/{id}/create', [\App\Http\Controllers\Master\PackageCodeController::class, 'edit']);
Route::post('/masters/package/code/{id}/create', [\App\Http\Controllers\Master\PackageCodeController::class, 'store']);
Route::get('masters/package/codes/{id}/actions', [\App\Http\Controllers\Master\PackageCodeController::class, 'actions']);

Route::view('/masters/container/types', 'modules.master.container-type.list')->name('container-types');
Route::post('/masters/container/type/data', [\App\Http\Controllers\Master\ContainerTypeController::class, 'fetchAllRows'])->name('container-type.data');

Route::view('/masters/incoterms', 'modules.master.incoterm.list')->name('incoterms');
Route::post('/masters/incoterm/data', [\App\Http\Controllers\Master\IncotermsController::class, 'fetchAllRows'])->name('incoterm.data');

Route::view('/masters/currencies', 'modules.master.currency.list');
Route::post('/masters/currency/data', [\App\Http\Controllers\Master\CurrencyController::class, 'fetchAllRows'])->name('currency.data');

Route::view('/masters/users', 'modules.master.user.list')->name('users');
Route::post('/masters/user/data', [\App\Http\Controllers\Master\UserController::class, 'fetchAllRows'])->name('user.data');
Route::get('/masters/user/create', [\App\Http\Controllers\Master\UserController::class, 'modal']);
Route::post('/masters/user/create', [\App\Http\Controllers\Master\UserController::class, 'store']);
Route::get('/masters/user/{id}/create', [\App\Http\Controllers\Master\UserController::class, 'edit']);
Route::post('/masters/user/{id}/create', [\App\Http\Controllers\Master\UserController::class, 'store']);
Route::get('masters/user/{id}/actions', [\App\Http\Controllers\Master\UserController::class, 'actions']);
Route::get('masters/user/{id}/overview', [\App\Http\Controllers\Master\UserController::class, 'overview']);

Route::view('/masters/transport/directories/seaports', 'modules.master.transport-directory.seaport.list')->name('ports');
Route::post('/masters/transport/directories/seaport/data', [\App\Http\Controllers\Master\TransportDirectory\SeaPortController::class, 'fetchAllRows'])->name('port.data');
Route::get('/masters/transport/directories/seaport/create', [\App\Http\Controllers\Master\TransportDirectory\SeaPortController::class, 'modal']);
Route::post('/masters/transport/directories/seaport/create', [\App\Http\Controllers\Master\TransportDirectory\SeaPortController::class, 'store']);
Route::get('masters/transport/directories/seaport/{id}/actions', [\App\Http\Controllers\Master\TransportDirectory\SeaPortController::class, 'actions']);
Route::get('/masters/transport/directories/seaport/{id}/create', [\App\Http\Controllers\Master\TransportDirectory\SeaPortController::class, 'edit']);
Route::post('/masters/transport/directories/seaport/{id}/create', [\App\Http\Controllers\Master\TransportDirectory\SeaPortController::class, 'store']);

Route::view('/masters/transport/directories/airports', 'modules.master.transport-directory.airport.list')->name('airports');
Route::post('/masters/transport/directories/airport/data', [\App\Http\Controllers\Master\TransportDirectory\AirportController::class, 'fetchAllRows'])->name('airport.data');
Route::get('/masters/transport/directories/airport/create', [\App\Http\Controllers\Master\TransportDirectory\AirportController::class, 'modal']);
Route::post('/masters/transport/directories/airport/create', [\App\Http\Controllers\Master\TransportDirectory\AirportController::class, 'store']);
Route::get('masters/transport/directories/airport/{id}/actions', [\App\Http\Controllers\Master\TransportDirectory\AirportController::class, 'actions']);
Route::get('/masters/transport/directories/airport/{id}/create', [\App\Http\Controllers\Master\TransportDirectory\AirportController::class, 'edit']);
Route::post('/masters/transport/directories/airport/{id}/create', [\App\Http\Controllers\Master\TransportDirectory\AirportController::class, 'store']);

Route::view('/masters/banks', 'modules.master.bank.list')->name('banks');
Route::post('/masters/bank/data', [\App\Http\Controllers\Master\BankController::class, 'fetchAllRows'])->name('bank.data');
Route::get('/masters/bank/create', [\App\Http\Controllers\Master\BankController::class, 'modal']);
Route::post('/masters/bank/create', [\App\Http\Controllers\Master\BankController::class, 'store']);
Route::get('/masters/bank/{id}/create', [\App\Http\Controllers\Master\BankController::class, 'edit']);
Route::post('/masters/bank/{id}/create', [\App\Http\Controllers\Master\BankController::class, 'store']);
Route::get('masters/bank/{id}/actions', [\App\Http\Controllers\Master\BankController::class, 'actions']);
Route::get('masters/bank/{id}/overview', [\App\Http\Controllers\Master\BankController::class, 'overview']);

Route::view('/masters/descriptions', 'modules.master.description.list')->name('descriptions');
Route::post('/masters/description/data', [\App\Http\Controllers\Master\DescriptionController::class, 'fetchAllRows'])->name('description.data');
Route::get('/masters/description/create', [\App\Http\Controllers\Master\DescriptionController::class, 'modal']);
Route::post('/masters/description/create', [\App\Http\Controllers\Master\DescriptionController::class, 'store']);
Route::get('/masters/description/{id}/create', [\App\Http\Controllers\Master\DescriptionController::class, 'edit']);
Route::post('/masters/description/{id}/create', [\App\Http\Controllers\Master\DescriptionController::class, 'store']);
Route::get('masters/description/{id}/actions', [\App\Http\Controllers\Master\DescriptionController::class, 'actions']);

Route::view('/masters/salesperson', 'modules.master.salesperson.list')->name('salespersons');
Route::post('/masters/salesperson/data', [\App\Http\Controllers\Master\Salesperson\SalespersonController::class, 'fetchAllRows'])->name('salesperson.data');
Route::get('/masters/salesperson/create', [\App\Http\Controllers\Master\Salesperson\SalespersonController::class, 'modal']);
Route::post('/masters/salesperson/create', [\App\Http\Controllers\Master\Salesperson\SalespersonController::class, 'store']);
Route::get('/masters/salesperson/{id}/create', [\App\Http\Controllers\Master\Salesperson\SalespersonController::class, 'edit']);
Route::post('/masters/salesperson/{id}/create', [\App\Http\Controllers\Master\Salesperson\SalespersonController::class, 'store']);
Route::get('masters/salesperson/{id}/actions', [\App\Http\Controllers\Master\Salesperson\SalespersonController::class, 'actions']);
Route::post('/masters/salesperson/{id}/status/{status}', [\App\Http\Controllers\Master\Salesperson\SalespersonController::class, 'updateStatus']);

Route::view('/masters/units', 'modules.master.unit.list')->name('units');
Route::post('/masters/unit/data', [\App\Http\Controllers\Master\Unit\UnitController::class, 'fetchAllRows'])->name('unit.data');
Route::get('/masters/unit/create', [\App\Http\Controllers\Master\Unit\UnitController::class, 'modal']);
Route::post('/masters/unit/create', [\App\Http\Controllers\Master\Unit\UnitController::class, 'store']);
Route::get('/masters/unit/{id}/create', [\App\Http\Controllers\Master\Unit\UnitController::class, 'edit']);
Route::post('/masters/unit/{id}/create', [\App\Http\Controllers\Master\Unit\UnitController::class, 'store']);
Route::get('masters/unit/{id}/actions', [\App\Http\Controllers\Master\Unit\UnitController::class, 'actions']);
Route::post('/masters/unit/{id}/status/{status}', [\App\Http\Controllers\Master\Unit\UnitController::class, 'updateStatus']);

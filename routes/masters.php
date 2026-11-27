<?php

use Illuminate\Support\Facades\Route;

Route::view('/masters/services', 'modules.master.logistics-activity.list')->name('services');  // Department master (FCL Import, FCL Export, ...)
Route::post('/masters/services/data', [\App\Http\Controllers\Master\LogisticServiceController::class, 'fetchAllRows'])->name('logistics-service.data');
Route::get('/masters/services/create', [\App\Http\Controllers\Master\LogisticServiceController::class, 'modal']);
Route::post('/masters/services/create', [\App\Http\Controllers\Master\LogisticServiceController::class, 'store']);
Route::get('/masters/services/{id}/create', [\App\Http\Controllers\Master\LogisticServiceController::class, 'edit']);
Route::post('/masters/services/{id}/create', [\App\Http\Controllers\Master\LogisticServiceController::class, 'store']);
Route::get('masters/services/{id}/actions', [\App\Http\Controllers\Master\LogisticServiceController::class, 'actions']);

Route::redirect('/masters/activities', '/masters/services')->name('activities');
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

// Quotation terms moved to the Sales menu (routes/sales.php): the old Masters address sends there
Route::redirect('/masters/quotation-terms', '/sales/quotation-terms', 301);

Route::view('/masters/hs-tariffs', 'modules.master.hs-tariff.list')->name('hs-tariffs');
Route::post('/masters/hs-tariff/data', [\App\Http\Controllers\Master\HsTariffController::class, 'fetchAllRows'])->name('hs-tariff.data');
Route::get('/masters/hs-tariff/create', [\App\Http\Controllers\Master\HsTariffController::class, 'modal']);
Route::post('/masters/hs-tariff/create', [\App\Http\Controllers\Master\HsTariffController::class, 'store']);
Route::get('/masters/hs-tariff/{id}/create', [\App\Http\Controllers\Master\HsTariffController::class, 'edit']);
Route::post('/masters/hs-tariff/{id}/create', [\App\Http\Controllers\Master\HsTariffController::class, 'store']);
Route::get('/masters/hs-tariff/{id}/actions', [\App\Http\Controllers\Master\HsTariffController::class, 'actions']);
Route::post('/masters/hs-tariff/{id}/status/{status}', [\App\Http\Controllers\Master\HsTariffController::class, 'updateStatus'])->whereNumber('id')->whereIn('status', ['0', '1']);
Route::delete('/masters/hs-tariff/{id}', [\App\Http\Controllers\Master\HsTariffController::class, 'destroy']);

Route::view('/masters/period-closing', 'modules.master.period-closing.list')->name('period-closing');
Route::post('/masters/period-closing/data', [\App\Http\Controllers\Master\PeriodClosingController::class, 'fetchAllRows'])->name('period-closing.data');
Route::get('/masters/period-closing/create', [\App\Http\Controllers\Master\PeriodClosingController::class, 'modal']);
Route::post('/masters/period-closing/create', [\App\Http\Controllers\Master\PeriodClosingController::class, 'store']);
Route::get('/masters/period-closing/{id}/create', [\App\Http\Controllers\Master\PeriodClosingController::class, 'edit']);
Route::post('/masters/period-closing/{id}/create', [\App\Http\Controllers\Master\PeriodClosingController::class, 'store']);
Route::get('/masters/period-closing/history', [\App\Http\Controllers\Master\PeriodClosingController::class, 'history']);
Route::get('/masters/period-closing/{id}/history', [\App\Http\Controllers\Master\PeriodClosingController::class, 'history']);
Route::get('/masters/period-closing/{id}/actions',[\App\Http\Controllers\Master\PeriodClosingController::class, 'actions']);
Route::post('/masters/period-closing/{id}/close', [\App\Http\Controllers\Master\PeriodClosingController::class, 'close']);
Route::post('/masters/period-closing/{id}/reopen', [\App\Http\Controllers\Master\PeriodClosingController::class, 'reopen']);
Route::delete('/masters/period-closing/{id}', [\App\Http\Controllers\Master\PeriodClosingController::class, 'destroy']);

Route::view('/masters/container/types', 'modules.master.container-type.list')->name('container-types');
Route::post('/masters/container/type/data', [\App\Http\Controllers\Master\ContainerTypeController::class, 'fetchAllRows'])->name('container-type.data');

Route::view('/masters/incoterms', 'modules.master.incoterm.list')->name('incoterms');
Route::post('/masters/incoterm/data', [\App\Http\Controllers\Master\IncotermsController::class, 'fetchAllRows'])->name('incoterm.data');

Route::view('/masters/currencies', 'modules.master.currency.list');
Route::post('/masters/currency/data', [\App\Http\Controllers\Master\CurrencyController::class, 'fetchAllRows'])->name('currency.data');

Route::view('/masters/lost-reasons', 'modules.master.lost-reason.list')->name('lost-reasons');
Route::post('/masters/lost-reason/data', [\App\Http\Controllers\Master\LostReasonController::class, 'fetchAllRows'])->name('lost-reason.data');
Route::get('/masters/lost-reason/create', [\App\Http\Controllers\Master\LostReasonController::class, 'modal']);
Route::post('/masters/lost-reason/create', [\App\Http\Controllers\Master\LostReasonController::class, 'store']);
Route::get('/masters/lost-reason/{id}/create', [\App\Http\Controllers\Master\LostReasonController::class, 'edit'])->whereNumber('id');
Route::post('/masters/lost-reason/{id}/create', [\App\Http\Controllers\Master\LostReasonController::class, 'store'])->whereNumber('id');
Route::get('/masters/lost-reason/{id}/actions', [\App\Http\Controllers\Master\LostReasonController::class, 'actions'])->whereNumber('id');
Route::post('/masters/lost-reason/{id}/status/{status}', [\App\Http\Controllers\Master\LostReasonController::class, 'updateStatus'])->whereNumber('id')->whereIn('status', ['0', '1']);
Route::delete('/masters/lost-reason/{id}', [\App\Http\Controllers\Master\LostReasonController::class, 'destroy'])->whereNumber('id');
// CRM actions (Call Done, RFQ Received, Quote Due ...)
Route::view('/masters/crm-actions', 'modules.master.crm-action.list')->name('crm-actions');
Route::post('/masters/crm-action/data', [\App\Http\Controllers\Master\CrmActionController::class, 'fetchAllRows'])->name('crm-action.data');
Route::get('/masters/crm-action/create', [\App\Http\Controllers\Master\CrmActionController::class, 'modal']);
Route::post('/masters/crm-action/create', [\App\Http\Controllers\Master\CrmActionController::class, 'store']);
Route::get('/masters/crm-action/{id}/create', [\App\Http\Controllers\Master\CrmActionController::class, 'edit'])->whereNumber('id');
Route::post('/masters/crm-action/{id}/create', [\App\Http\Controllers\Master\CrmActionController::class, 'store'])->whereNumber('id');
Route::get('/masters/crm-action/{id}/actions', [\App\Http\Controllers\Master\CrmActionController::class, 'actions'])->whereNumber('id');
Route::post('/masters/crm-action/{id}/status/{status}', [\App\Http\Controllers\Master\CrmActionController::class, 'updateStatus'])->whereNumber('id')->whereIn('status', ['0', '1']);
Route::delete('/masters/crm-action/{id}', [\App\Http\Controllers\Master\CrmActionController::class, 'destroy'])->whereNumber('id');

Route::view('/masters/departments', 'modules.master.department.list')->name('departments');
Route::post('/masters/department/data', [\App\Http\Controllers\Master\DepartmentController::class, 'fetchAllRows'])->name('department.data');
Route::get('/masters/department/create', [\App\Http\Controllers\Master\DepartmentController::class, 'modal']);
Route::post('/masters/department/create', [\App\Http\Controllers\Master\DepartmentController::class, 'store']);
Route::get('/masters/department/{id}/create', [\App\Http\Controllers\Master\DepartmentController::class, 'edit']);
Route::post('/masters/department/{id}/create', [\App\Http\Controllers\Master\DepartmentController::class, 'store']);
Route::get('masters/department/{id}/actions', [\App\Http\Controllers\Master\DepartmentController::class, 'actions']);
Route::post('/masters/department/{id}/status/{status}', [\App\Http\Controllers\Master\DepartmentController::class, 'updateStatus']);

Route::view('/masters/users', 'modules.master.user.list')->name('users');
Route::post('/masters/user/data', [\App\Http\Controllers\Master\UserController::class, 'fetchAllRows'])->name('user.data');
Route::get('/masters/user/create', [\App\Http\Controllers\Master\UserController::class, 'modal']);
Route::post('/masters/user/create', [\App\Http\Controllers\Master\UserController::class, 'store']);
Route::get('/masters/user/{id}/create', [\App\Http\Controllers\Master\UserController::class, 'edit']);
Route::post('/masters/user/{id}/create', [\App\Http\Controllers\Master\UserController::class, 'store']);
Route::get('masters/user/{id}/actions', [\App\Http\Controllers\Master\UserController::class, 'actions']);
Route::post('masters/user/{id}/status/{status}', [\App\Http\Controllers\Master\UserController::class, 'updateStatus']);
Route::delete('masters/user/{id}', [\App\Http\Controllers\Master\UserController::class, 'delete'])->whereNumber('id');
Route::get('masters/user/{id}/overview', [\App\Http\Controllers\Master\UserController::class, 'overview']);
Route::get('masters/user/{id}/rights', [\App\Http\Controllers\Master\UserController::class, 'rights'])->whereNumber('id');
Route::post('masters/user/{id}/rights', [\App\Http\Controllers\Master\UserController::class, 'saveRights'])->whereNumber('id');

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
Route::delete('masters/bank/{id}', [\App\Http\Controllers\Master\BankController::class, 'delete'])->whereNumber('id');
Route::get('masters/bank/{id}/overview', [\App\Http\Controllers\Master\BankController::class, 'overview']);

Route::view('/masters/descriptions', 'modules.master.description.list')->name('descriptions');
Route::post('/masters/description/data', [\App\Http\Controllers\Master\DescriptionController::class, 'fetchAllRows'])->name('description.data');
Route::get('/masters/description/create', [\App\Http\Controllers\Master\DescriptionController::class, 'modal']);
Route::post('/masters/description/create', [\App\Http\Controllers\Master\DescriptionController::class, 'store']);
Route::get('/masters/description/{id}/create', [\App\Http\Controllers\Master\DescriptionController::class, 'edit']);
Route::post('/masters/description/{id}/create', [\App\Http\Controllers\Master\DescriptionController::class, 'store']);
Route::get('masters/description/{id}/actions', [\App\Http\Controllers\Master\DescriptionController::class, 'actions']);
Route::delete('masters/description/{id}', [\App\Http\Controllers\Master\DescriptionController::class, 'delete'])->whereNumber('id');
Route::post('masters/description/{id}/status/{status}', [\App\Http\Controllers\Master\DescriptionController::class, 'updateStatus'])->whereNumber('id');

Route::view('/masters/salesperson', 'modules.master.salesperson.list')->name('salespersons');
Route::post('/masters/salesperson/data', [\App\Http\Controllers\Master\Salesperson\SalespersonController::class, 'fetchAllRows'])->name('salesperson.data');
Route::get('/masters/salesperson/create', [\App\Http\Controllers\Master\Salesperson\SalespersonController::class, 'modal']);
Route::post('/masters/salesperson/create', [\App\Http\Controllers\Master\Salesperson\SalespersonController::class, 'store']);
Route::get('/masters/salesperson/{id}/create', [\App\Http\Controllers\Master\Salesperson\SalespersonController::class, 'edit']);
Route::post('/masters/salesperson/{id}/create', [\App\Http\Controllers\Master\Salesperson\SalespersonController::class, 'store']);
Route::get('masters/salesperson/{id}/actions', [\App\Http\Controllers\Master\Salesperson\SalespersonController::class, 'actions']);
Route::delete('masters/salesperson/{id}', [\App\Http\Controllers\Master\Salesperson\SalespersonController::class, 'delete'])->whereNumber('id');
Route::post('/masters/salesperson/{id}/status/{status}', [\App\Http\Controllers\Master\Salesperson\SalespersonController::class, 'updateStatus']);

Route::view('/masters/units', 'modules.master.unit.list')->name('units');
Route::post('/masters/unit/data', [\App\Http\Controllers\Master\Unit\UnitController::class, 'fetchAllRows'])->name('unit.data');
Route::get('/masters/unit/create', [\App\Http\Controllers\Master\Unit\UnitController::class, 'modal']);
Route::post('/masters/unit/create', [\App\Http\Controllers\Master\Unit\UnitController::class, 'store']);
Route::get('/masters/unit/{id}/create', [\App\Http\Controllers\Master\Unit\UnitController::class, 'edit']);
Route::post('/masters/unit/{id}/create', [\App\Http\Controllers\Master\Unit\UnitController::class, 'store']);
Route::get('masters/unit/{id}/actions', [\App\Http\Controllers\Master\Unit\UnitController::class, 'actions']);
Route::post('/masters/unit/{id}/status/{status}', [\App\Http\Controllers\Master\Unit\UnitController::class, 'updateStatus']);

// Fleet masters
// Vehicles and drivers moved to the Fleet menu (routes/fleet.php): the old Masters addresses send there
Route::redirect('/masters/vehicles', '/fleet/vehicles', 301);
Route::redirect('/masters/drivers', '/fleet/drivers', 301);

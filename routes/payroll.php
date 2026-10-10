<?php

use App\Http\Controllers\Payroll\AttendanceController;
use App\Http\Controllers\Payroll\AttendanceDeviceController;
use App\Http\Controllers\Payroll\DashboardController;
use App\Http\Controllers\Payroll\EmployeeLoanController;
use App\Http\Controllers\Payroll\EmployeeProfileController;
use App\Http\Controllers\Payroll\PayrollRunController;
use App\Http\Controllers\Payroll\PunchEntryController;
use App\Http\Controllers\Payroll\PunchImportController;
use App\Http\Controllers\Payroll\SalaryStructureController;
use App\Http\Controllers\Payroll\ShiftController;
use Illuminate\Support\Facades\Route;

// Employee loans (the people themselves are managed in Masters > Employees)
Route::resource('employee-loans', EmployeeLoanController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
Route::post('employee-loans/{employee_loan}/pay-installment', [EmployeeLoanController::class, 'payInstallment'])->name('employee-loans.pay-installment');

// Payroll & attendance
Route::prefix('payroll')->name('payroll.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('punch-log/entries', [PunchEntryController::class, 'store'])->name('punch-entry.store');
    Route::put('punch-log/entries/{punch}', [PunchEntryController::class, 'update'])->name('punch-entry.update');
    Route::delete('punch-log/entries/{punch}', [PunchEntryController::class, 'destroy'])->name('punch-entry.destroy');
    Route::get('punch-log/import', [PunchImportController::class, 'form'])->name('punch-import');
    Route::get('punch-log/import/template', [PunchImportController::class, 'template'])->name('punch-import.template');
    Route::post('punch-log/import', [PunchImportController::class, 'upload'])->name('punch-import.upload');
    Route::get('punch-log/import/{token}', [PunchImportController::class, 'preview'])->name('punch-import.preview');
    Route::post('punch-log/import/{token}', [PunchImportController::class, 'confirm'])->name('punch-import.confirm');
    Route::get('punch-log', [AttendanceDeviceController::class, 'punchLog'])->name('punch-log');
    Route::get('devices', [AttendanceDeviceController::class, 'index'])->name('devices.index');
    Route::post('devices', [AttendanceDeviceController::class, 'store'])->name('devices.store');
    Route::post('devices/map', [AttendanceDeviceController::class, 'map'])->name('devices.map');
    Route::post('devices/reprocess', [AttendanceDeviceController::class, 'reprocess'])->name('devices.reprocess');
    Route::get('devices/{device}', [AttendanceDeviceController::class, 'configure'])->whereNumber('device')->name('devices.configure');
    Route::post('devices/{device}/connection', [AttendanceDeviceController::class, 'saveConnection'])->name('devices.connection');
    Route::post('devices/{device}/mapping', [AttendanceDeviceController::class, 'saveMapping'])->name('devices.mapping');
    Route::post('devices/{device}/preview', [AttendanceDeviceController::class, 'preview'])->name('devices.preview');
    Route::post('devices/{device}/test-pull', [AttendanceDeviceController::class, 'testPull'])->name('devices.test-pull');
    Route::post('devices/{device}/pull-now', [AttendanceDeviceController::class, 'pullNow'])->name('devices.pull-now');
    Route::put('devices/{device}', [AttendanceDeviceController::class, 'update'])->name('devices.update');
    Route::post('devices/{device}/regenerate', [AttendanceDeviceController::class, 'regenerate'])->name('devices.regenerate');
    Route::delete('devices/{device}', [AttendanceDeviceController::class, 'destroy'])->name('devices.destroy');

    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::put('attendance/{attendance}', [AttendanceController::class, 'update'])->name('attendance.update');
    Route::post('attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');
    Route::get('attendance/employee-month/{employee}', [AttendanceController::class, 'employeeMonth'])->name('attendance.employee-month');

    Route::resource('shifts', ShiftController::class)->except(['show', 'create', 'edit']);
    Route::resource('salary-structures', SalaryStructureController::class)->except(['show', 'create', 'edit']);

    Route::get('runs', [PayrollRunController::class, 'index'])->name('runs.index');
    Route::get('runs/create', [PayrollRunController::class, 'create'])->name('runs.create');
    Route::post('runs', [PayrollRunController::class, 'store'])->name('runs.store');
    Route::get('runs/{payroll_record}', [PayrollRunController::class, 'show'])->whereNumber('payroll_record')->name('runs.show');
    Route::get('runs/{payroll_record}/slip', [PayrollRunController::class, 'slip'])->whereNumber('payroll_record')->name('runs.slip');
    Route::delete('runs/{payroll_record}', [PayrollRunController::class, 'destroy'])->name('runs.destroy');
    Route::post('runs/{payroll_record}/pay', [PayrollRunController::class, 'pay'])->name('runs.pay');
    Route::post('runs/{payroll_record}/disapprove', [PayrollRunController::class, 'disapprove'])->name('runs.disapprove');

    Route::get('employees', [EmployeeProfileController::class, 'index'])->name('employees.index');
    Route::get('employees/{user}', [EmployeeProfileController::class, 'show'])->name('employees.show');
    Route::get('employees/{user}/edit', [EmployeeProfileController::class, 'edit'])->name('employees.edit');
    Route::put('employees/{user}', [EmployeeProfileController::class, 'update'])->name('employees.update');
});

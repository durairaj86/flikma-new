<?php

use App\Http\Controllers\Payroll\AttendanceController;
use App\Http\Controllers\Payroll\BasicSalaryController;
use App\Http\Controllers\Payroll\EmployeeLoanController;
use App\Http\Controllers\Payroll\MonthlySalaryController;
use Illuminate\Support\Facades\Route;

Route::prefix('payroll')->group(function () {
    // Basic Salary routes
    Route::view('/basic/salary', 'modules.payroll.basic-salary.list')->name('payroll.basic-salary');
    Route::post('/basic/salary/data', [BasicSalaryController::class, 'fetchAllRows'])->name('payroll.basic-salary.data');
    Route::get('/basic/salary/create', [BasicSalaryController::class, 'create']);
    Route::post('/basic/salary/create', [BasicSalaryController::class, 'store']);
    Route::get('/basic/salary/{id}/create', [BasicSalaryController::class, 'edit']);
    Route::post('/basic/salary/{id}/create', [BasicSalaryController::class, 'store']);
    Route::delete('/basic/salary/{id}/delete', [BasicSalaryController::class, 'destroy']);
    Route::get('/basic/salary/{id}/overview', [BasicSalaryController::class, 'overview']);
    Route::get('/basic/salary/{id}/print', [BasicSalaryController::class, 'print']);
    Route::get('/basic/salary/{id}/actions', [BasicSalaryController::class, 'actions']);
    Route::post('/basic/salary/{id}/status/{status}', [BasicSalaryController::class, 'updateStatus']);

    // Monthly Salary routes
    Route::view('/monthly/salary', 'modules.payroll.monthly-salary.list')->name('payroll.monthly-salary');
    Route::post('/monthly/salary/data', [MonthlySalaryController::class, 'fetchAllRows'])->name('payroll.monthly-salary.data');
    Route::get('/monthly/salary/create', [MonthlySalaryController::class, 'create']);
    Route::post('/monthly/salary/create', [MonthlySalaryController::class, 'store']);
    Route::get('/monthly/salary/{id}/create', [MonthlySalaryController::class, 'edit']);
    Route::post('/monthly/salary/{id}/create', [MonthlySalaryController::class, 'store']);
    Route::get('/monthly/salary/{id}/print', [MonthlySalaryController::class, 'print']);
    Route::delete('/monthly/salary/{id}/delete', [MonthlySalaryController::class, 'destroy']);
    Route::post('/monthly/salary/get-employee-basic-salary', [MonthlySalaryController::class, 'getEmployeeBasicSalary']);
    Route::get('/monthly/salary/{id}/overview', [MonthlySalaryController::class, 'overview']);
    Route::get('/monthly/salary/{id}/print', [MonthlySalaryController::class, 'print']);
    Route::get('/monthly/salary/{id}/actions', [MonthlySalaryController::class, 'actions']);
    Route::post('/monthly/salary/{id}/status/{status}', [MonthlySalaryController::class, 'updateStatus']);

    // Employee Loan routes
    Route::view('/employee/loan', 'modules.payroll.employee-loan.list')->name('payroll.employee-loan');
    Route::post('/employee/loan/data', [EmployeeLoanController::class, 'fetchAllRows'])->name('payroll.employee-loan.data');
    Route::get('/employee/loan/create', [EmployeeLoanController::class, 'create']);
    Route::post('/employee/loan/create', [EmployeeLoanController::class, 'store']);
    Route::get('/employee/loan/{id}/create', [EmployeeLoanController::class, 'edit']);
    Route::post('/employee/loan/{id}/create', [EmployeeLoanController::class, 'store']);
    Route::get('/employee/loan/{id}/view', [EmployeeLoanController::class, 'view']);
    Route::delete('/employee/loan/{id}/delete', [EmployeeLoanController::class, 'destroy']);
    Route::get('/employee/loan/{id}/overview', [EmployeeLoanController::class, 'overview']);
    Route::get('/employee/loan/{id}/print', [EmployeeLoanController::class, 'print']);
    Route::get('/employee/loan/{id}/actions', [EmployeeLoanController::class, 'actions']);
    Route::post('/employee/loan/{id}/status/{status}', [EmployeeLoanController::class, 'updateStatus']);

    // Attendance routes
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('payroll.attendance');
    Route::post('/attendance/data', [AttendanceController::class, 'fetchAllRows'])->name('payroll.attendance.data');
    Route::get('/attendance/create', [AttendanceController::class, 'create']);
    Route::post('/attendance/create', [AttendanceController::class, 'store']);
    Route::get('/attendance/{id}/create', [AttendanceController::class, 'edit']);
    Route::post('/attendance/{id}/create', [AttendanceController::class, 'store']);
    Route::delete('/attendance/{id}/delete', [AttendanceController::class, 'destroy']);
    Route::post('/attendance/calendar', [AttendanceController::class, 'getMonthlyCalendar'])->name('payroll.attendance.calendar');
});

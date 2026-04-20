<?php

use Illuminate\Support\Facades\Route;

// Finance Reports
Route::get('/reports/trial-balance', \App\Livewire\Report\Finance\TrialBalance::class);
Route::get('/reports/balance-sheet', \App\Livewire\Report\Finance\BalanceSheet::class);
Route::get('/reports/profit-and-loss', \App\Livewire\Report\Finance\ProfitAndLoss::class);
Route::get('/reports/customer-statement', \App\Livewire\Report\Finance\CustomerStatement::class);
Route::get('/reports/customer-aging', \App\Livewire\Report\Finance\CustomerAging::class);
Route::get('/reports/customer-aging-all', \App\Livewire\Report\Finance\CustomerAgingAll::class);
Route::get('/reports/supplier-statement', \App\Livewire\Report\Finance\SupplierStatement::class);
Route::get('/reports/supplier-aging', \App\Livewire\Report\Finance\SupplierAging::class);
Route::get('/reports/supplier-aging-all', \App\Livewire\Report\Finance\SupplierAgingAll::class);
Route::get('/reports/general-ledger', \App\Livewire\Report\Finance\GeneralLedger::class);

// Tax Reports
Route::get('/reports/tax-summary', \App\Livewire\Report\Finance\TaxSummary::class);
Route::get('/reports/input-tax', \App\Livewire\Report\Finance\InputTax::class);
Route::get('/reports/output-tax', \App\Livewire\Report\Finance\OutputTax::class);

// Job Reports
Route::get('/reports/job-report', \App\Livewire\Report\Job\JobReport::class);
Route::get('/reports/job-balance-report', \App\Livewire\Report\Job\JobBalanceReport::class);
Route::get('/reports/job-income-report', \App\Livewire\Report\Job\JobIncomeReport::class);

// Sale Reports
Route::get('/reports/sale-report', \App\Livewire\Report\Sale\SaleReport::class);

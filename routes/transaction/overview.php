<?php

use App\Http\Controllers\Transaction\TransactionOverviewController;
use Illuminate\Support\Facades\Route;

Route::get('/transaction/overview', [TransactionOverviewController::class, 'index'])->name('transaction.overview');

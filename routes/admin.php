<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\CacheController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmailQueueController;
use App\Http\Controllers\Admin\EnquiryController;
use App\Http\Controllers\Admin\NewsletterController;
use App\Http\Controllers\Admin\SettingController;
use Illuminate\Support\Facades\Route;

/*
| Platform admin panel (ported from fatoora-invoice). Prefix: admin.
| Guest: login. Everything else: auth + admin (users.is_admin).
*/
Route::prefix('admin')->name('admin.')->group(function () {

    Route::middleware('guest')->group(function () {
        Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('login', [LoginController::class, 'login'])->name('login.submit');
    });

    // leaving "Login as": the signed-in user is the company user then, not the admin
    Route::post('leave-impersonation', [CompanyController::class, 'leaveImpersonation'])->middleware('auth')->name('companies.leave-impersonation');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('logout', [LoginController::class, 'logout'])->name('logout');

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('companies', [CompanyController::class, 'index'])->name('companies.index');
        Route::get('companies/{company}', [CompanyController::class, 'show'])->name('companies.show');
        Route::get('companies/{company}/impersonate', [CompanyController::class, 'impersonate'])->name('companies.impersonate');

        Route::prefix('enquiries')->name('enquiries.')->group(function () {
            Route::get('/', [EnquiryController::class, 'index'])->name('index');
            Route::get('{contactMessage}', [EnquiryController::class, 'show'])->name('show');
            Route::post('{contactMessage}/mark-read', [EnquiryController::class, 'markRead'])->name('mark-read');
            Route::post('{contactMessage}/reply', [EnquiryController::class, 'reply'])->name('reply');
            Route::post('{contactMessage}/assign', [EnquiryController::class, 'assign'])->name('assign');
            Route::post('{contactMessage}/close', [EnquiryController::class, 'close'])->name('close');
            Route::post('{contactMessage}/archive', [EnquiryController::class, 'archive'])->name('archive');
            Route::delete('{contactMessage}', [EnquiryController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('newsletters')->name('newsletters.')->group(function () {
            Route::get('/', [NewsletterController::class, 'index'])->name('index');
            Route::get('create', [NewsletterController::class, 'create'])->name('create');
            Route::post('create', [NewsletterController::class, 'store'])->name('store');
            Route::get('{newsletter}', [NewsletterController::class, 'show'])->name('show');
            Route::delete('{newsletter}', [NewsletterController::class, 'destroy'])->name('destroy');
            Route::post('{newsletter}/send', [NewsletterController::class, 'send'])->name('send');
        });

        Route::prefix('email-queue')->name('email-queue.')->group(function () {
            Route::get('/', [EmailQueueController::class, 'index'])->name('index');
            Route::get('{emailQueue}', [EmailQueueController::class, 'show'])->name('show');
            Route::post('{emailQueue}/retry', [EmailQueueController::class, 'retry'])->name('retry');
            Route::post('{emailQueue}/skip', [EmailQueueController::class, 'skip'])->name('skip');
        });

        Route::prefix('activity-logs')->name('activity-logs.')->group(function () {
            Route::get('/', [ActivityLogController::class, 'index'])->name('index');
            Route::get('{activityLog}', [ActivityLogController::class, 'show'])->name('show');
            Route::delete('{activityLog}', [ActivityLogController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('index');
            Route::post('general', [SettingController::class, 'updateGeneral'])->name('general.update');
            Route::post('smtp', [SettingController::class, 'updateSmtp'])->name('smtp.update');
            Route::post('payment', [SettingController::class, 'updatePayment'])->name('payment.update');
            Route::post('social', [SettingController::class, 'updateSocial'])->name('social.update');
        });

        Route::get('cache', [CacheController::class, 'index'])->name('cache.index');
        Route::post('cache/clear', [CacheController::class, 'clear'])->name('cache.clear');
    });
});

<?php

use App\Http\Controllers\Ocr\OcrController;
use App\Http\Controllers\Common\LogActivityController;
use App\Http\Controllers\ProfileController;
use Codesmiths\LaravelOcrSpace\Facades\OcrSpace;
use Codesmiths\LaravelOcrSpace\OcrSpaceOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::domain('flikma.com')->group(base_path('routes/website.php'));
Route::domain('flikma-new.test')->group(base_path('routes/website.php'));

// Uploaded files (logos, photos, documents). Normally the web server serves public/storage (the storage:link symlink) itself and never gets here;
// on shared hosting where the link is missing or broken the request falls through to Laravel and the file is streamed from storage/app/public.
Route::get('storage/{path}', function (string $path) {
    $root = realpath(Storage::disk('public')->path(''));
    $file = $root ? realpath($root . DIRECTORY_SEPARATOR . $path) : false;
    $inside = $file && str_starts_with($file, $root . DIRECTORY_SEPARATOR);
    // never outside the uploads folder, no hidden files, no scripts
    abort_unless($inside && is_file($file) && !preg_match('#(^|/)\.#', $path) && !preg_match('/\.(php\d?|phtml|phar|htaccess)$/i', $file), 404);

    return response()->file($file, ['Cache-Control' => 'public, max-age=3600']);
})->where('path', '.*')->name('storage.fallback');

Route::get('/dashboard/widget/{key}', [\App\Http\Controllers\DashboardController::class, 'widget'])->middleware(['auth', 'verified'])->name('dashboard.widget');
Route::post('/dashboard/layout', [\App\Http\Controllers\DashboardLayoutController::class, 'save'])->middleware(['auth', 'verified'])->name('dashboard.layout.save');
Route::post('/dashboard/layout/reset', [\App\Http\Controllers\DashboardLayoutController::class, 'reset'])->middleware(['auth', 'verified'])->name('dashboard.layout.reset');
Route::get('/briefing', [\App\Http\Controllers\BriefingController::class, 'show'])->middleware(['auth', 'verified'])->name('briefing.show');
Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/welcome', function () {
    return view('auth.welcome');
})->middleware(['auth', 'verified'])->name('welcome');

// Biometric / punching-machine endpoints (token or device-serial authenticated, no session).
Route::middleware('throttle:240,1')->group(function () {
    Route::match(['get', 'post'], 'api/attendance/punch', [\App\Http\Controllers\Api\AttendancePunchController::class, 'punch'])->name('api.attendance.punch');
    Route::match(['get', 'post'], 'api/attendance/hook/{publicId}', [\App\Http\Controllers\Api\AttendancePunchController::class, 'hook'])->name('api.attendance.hook');
    Route::get('api/attendance/ping', [\App\Http\Controllers\Api\AttendancePunchController::class, 'ping'])->name('api.attendance.ping');
    Route::match(['get', 'post'], 'iclock/cdata', [\App\Http\Controllers\Api\AttendancePunchController::class, 'zkCdata']);
    Route::match(['get', 'post'], 'iclock/getrequest', [\App\Http\Controllers\Api\AttendancePunchController::class, 'zkGetRequest']);
});

Route::post('api/payroll/attendance/punch', [\App\Http\Controllers\Payroll\AttendanceController::class, 'punch'])
    ->name('payroll.attendance.punch');

// The language switch only needs a login: the email verification page shows it too.
Route::middleware('auth')->group(function () {
    /* EN/AR toggle: persists the choice in the session so SetLocale (the
       global web middleware) applies it on every subsequent request. */
    Route::get('locale/{locale}', function (string $locale) {
        if (in_array($locale, ['en', 'ar'], true)) {
            session(['locale' => $locale]);
        }

        return back();
    })->name('locale.switch');
});

Route::middleware(['auth', 'verified', 'module.permission'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // How finance entries are saved (documentation + live row counts)
    Route::get('/finance-entries/workflow', [\App\Http\Controllers\Finance\Workflow\FinanceWorkflowController::class, 'index'])->name('finance.workflow');

    Route::view('/customers', 'modules.customer.list')->name('customers');
    Route::get('/customers/search', [\App\Http\Controllers\Customer\CustomerDetailController::class, 'search']);
    Route::get('/customers/{id}', [\App\Http\Controllers\Customer\CustomerDetailController::class, 'show'])->whereNumber('id')->name('customers.show');
    Route::get('/customers/{id}/tab/{tab}', [\App\Http\Controllers\Customer\CustomerDetailController::class, 'tab'])->whereNumber('id')->name('customers.tab');
    Route::post('/customer/data', [\App\Http\Controllers\Customer\CustomerController::class, 'fetchAllRows'])->name('customers.data');
    Route::get('/customers/import', [\App\Http\Controllers\Import\PartyImportController::class, 'page'])->defaults('kind', 'customer');
    Route::get('/customer/import/sample', [\App\Http\Controllers\Import\PartyImportController::class, 'sample'])->defaults('kind', 'customer');
    Route::post('/customer/import/upload', [\App\Http\Controllers\Import\PartyImportController::class, 'upload'])->defaults('kind', 'customer');
    Route::post('/customer/import/preview', [\App\Http\Controllers\Import\PartyImportController::class, 'preview'])->defaults('kind', 'customer');
    Route::post('/customer/import', [\App\Http\Controllers\Import\PartyImportController::class, 'import'])->defaults('kind', 'customer');
    Route::get('/customer/create', [\App\Http\Controllers\Customer\CustomerController::class, 'modal']);
    Route::post('/customer/create', [\App\Http\Controllers\Customer\CustomerController::class, 'store']);
    Route::get('/customer/{id}/create', [\App\Http\Controllers\Customer\CustomerController::class, 'edit']);
    Route::post('/customer/{id}/create', [\App\Http\Controllers\Customer\CustomerController::class, 'store']);
    Route::delete('/customer/{id}', [\App\Http\Controllers\Customer\CustomerController::class, 'delete'])->whereNumber('id');
    Route::get('/customer/create/quick', [\App\Http\Controllers\Customer\CustomerController::class, 'quickModal']);
    Route::post('/customer/create/quick', [\App\Http\Controllers\Customer\CustomerController::class, 'quickStore']);
    Route::get('customer/{id}/actions', [\App\Http\Controllers\Customer\CustomerController::class, 'actions']);
    Route::post('customer/{id}/status/{status}', [\App\Http\Controllers\Customer\CustomerController::class, 'updateStatus']);
    Route::view('/customer/statement', 'modules.customer.statement')->name('customers.statement');

    Route::get('customer/{id}/overview', [\App\Http\Controllers\Customer\CustomerController::class, 'overview']);
    Route::get('customer/{id}/due-detail', [\App\Http\Controllers\Customer\CustomerController::class, 'dueDetail'])->whereNumber('id');
    Route::get('customer/{id}/unused-detail', [\App\Http\Controllers\Customer\CustomerController::class, 'unusedDetail'])->whereNumber('id');
    Route::get('customer/{id}/address', [\App\Http\Controllers\Customer\CustomerController::class, 'getAddress']);
    // customer portal logins (Customer page > General > Portal access)
    Route::post('customer/{id}/portal-users/invite', [\App\Http\Controllers\Customer\PortalUserController::class, 'invite'])->whereNumber('id');
    Route::post('customer/portal-user/{id}/resend', [\App\Http\Controllers\Customer\PortalUserController::class, 'resend'])->whereNumber('id');
    Route::post('customer/portal-user/{id}/status/{status}', [\App\Http\Controllers\Customer\PortalUserController::class, 'updateStatus'])->whereNumber('id')->whereIn('status', ['0', '1']);
    Route::post('customer/document/{id}/delete', [\App\Http\Controllers\Customer\CustomerController::class, 'deleteDocument']);
    /*Route::get('customer/{id}/invoices', [\App\Http\Controllers\Customer\CustomerController::class, 'invoices']);
    Route::get('customer/{id}/transactions', [\App\Http\Controllers\Customer\CustomerController::class, 'transactions']);*/

    Route::view('/prospects', 'modules.prospect.list')->name('prospects');
    Route::post('/prospect/data', [\App\Http\Controllers\Prospect\ProspectController::class, 'fetchAllRows'])->name('prospects.data');
    Route::get('/prospect/{id}/actions', [\App\Http\Controllers\Prospect\ProspectController::class, 'actions']);
    Route::get('/prospect/create', [\App\Http\Controllers\Prospect\ProspectController::class, 'modal']);
    Route::post('/prospect/create', [\App\Http\Controllers\Prospect\ProspectController::class, 'quickStore']);
    Route::get('/prospect/{id}/create', [\App\Http\Controllers\Prospect\ProspectController::class, 'edit']);
    Route::post('/prospect/{id}/create', [\App\Http\Controllers\Prospect\ProspectController::class, 'quickStore']);
    Route::delete('/prospect/delete/{id}', [\App\Http\Controllers\Prospect\ProspectController::class, 'delete']);
    // reasons for the "Mark as lost" dialog: asked from quotations, enquiries, prospects and the pipeline, so it is not tied to one section's rights
    Route::get('/lookup/lost-reasons', [\App\Http\Controllers\Crm\PipelineController::class, 'lostReasons']);
    Route::post('/prospect/{id}/status/{status}',[\App\Http\Controllers\Prospect\ProspectController::class, 'updateStatus'])
        ->whereNumber('id')->whereIn('status', ['new', 'contacted', 'qualified', 'lost']);

    Route::get('/prospect/create/quick', [\App\Http\Controllers\Prospect\ProspectController::class, 'quickModal']);
    Route::post('/prospect/create/quick', [\App\Http\Controllers\Prospect\ProspectController::class, 'quickStore']);

    Route::view('/suppliers', 'modules.supplier.list')->name('suppliers');
    Route::get('/suppliers/search', [\App\Http\Controllers\Supplier\SupplierDetailController::class, 'search']);
    Route::get('/suppliers/{id}', [\App\Http\Controllers\Supplier\SupplierDetailController::class, 'show'])->whereNumber('id')->name('suppliers.show');
    Route::get('/suppliers/{id}/tab/{tab}', [\App\Http\Controllers\Supplier\SupplierDetailController::class, 'tab'])->whereNumber('id')->name('suppliers.tab');
    Route::post('/supplier/data', [\App\Http\Controllers\Supplier\SupplierController::class, 'fetchAllRows'])->name('suppliers.data');
    Route::get('/suppliers/import', [\App\Http\Controllers\Import\PartyImportController::class, 'page'])->defaults('kind', 'supplier');
    Route::get('/supplier/import/sample', [\App\Http\Controllers\Import\PartyImportController::class, 'sample'])->defaults('kind', 'supplier');
    Route::post('/supplier/import/upload', [\App\Http\Controllers\Import\PartyImportController::class, 'upload'])->defaults('kind', 'supplier');
    Route::post('/supplier/import/preview', [\App\Http\Controllers\Import\PartyImportController::class, 'preview'])->defaults('kind', 'supplier');
    Route::post('/supplier/import', [\App\Http\Controllers\Import\PartyImportController::class, 'import'])->defaults('kind', 'supplier');
    Route::get('/supplier/create', [\App\Http\Controllers\Supplier\SupplierController::class, 'modal']);
    Route::post('/supplier/create', [\App\Http\Controllers\Supplier\SupplierController::class, 'store']);
    Route::get('/supplier/{id}/create', [\App\Http\Controllers\Supplier\SupplierController::class, 'edit']);
    Route::post('/supplier/{id}/create', [\App\Http\Controllers\Supplier\SupplierController::class, 'store']);
    Route::delete('/supplier/{id}', [\App\Http\Controllers\Supplier\SupplierController::class, 'delete'])->whereNumber('id');
    Route::get('supplier/{id}/actions', [\App\Http\Controllers\Supplier\SupplierController::class, 'actions']);
    Route::post('supplier/{id}/status/{status}', [\App\Http\Controllers\Supplier\SupplierController::class, 'updateStatus']);
    Route::get('supplier/{id}/overview', [\App\Http\Controllers\Supplier\SupplierController::class, 'overview']);
    //Route::view('/supplier/statement', 'modules.supplier.statement')->name('suppliers.statement');
    Route::get('/supplier/statement', [\App\Http\Controllers\Supplier\SupplierStatementController::class, 'index'])->name('suppliers.statement');


    // Document scanning (supplier invoice upload and the supplier invoice list)
    Route::post('/ocr/scan', [\App\Http\Controllers\Ocr\OcrController::class, 'googleOcrUpload'])->name('ocr.scan');
    Route::post('/ocr/read', [\App\Http\Controllers\Ocr\OcrController::class, 'upload'])->name('ocr.read');
    Route::get('/dropdown/search', [\App\Http\Controllers\Common\DropdownListSearchController::class, 'index']);
    Route::get('/load/customer', [\App\Http\Controllers\Common\DropdownListSearchController::class, 'customerList']);


    Route::get('currency/rate/{base}/{target}', [\App\Http\Controllers\CurrencyExchangeController::class, 'getExchangeRate']);

    // Column Settings (per-user, per-page column configuration)
    Route::get('column-settings/{page}', [\App\Http\Controllers\ColumnSettingsController::class, 'index']);
    Route::post('column-settings/{page}', [\App\Http\Controllers\ColumnSettingsController::class, 'save']);
    Route::delete('column-settings/{page}', [\App\Http\Controllers\ColumnSettingsController::class, 'reset']);

    //Route::view('/sales/quotations', 'modules.quotation.list')->name('quotations');
    Route::view('reports', 'modules.reports.index');
    include 'sales.php';
    include 'operations.php';
    include 'bl.php';
    include 'finance/invoice.php';
    include 'finance/adjustments.php';
    include 'finance/account.php';
    include 'finance/expense.php';
    include 'finance/asset.php';
    include 'finance/journal_voucher.php';
    include 'finance/transfer_voucher.php';
    include 'finance/credit_approval.php';
    include 'finance/cost_sheet.php';
    include 'finance/opening_balance.php';
    include 'finance/reconciliation.php';
    include 'transaction/payment.php';
    include 'transaction/collection.php';
    include 'transaction/overview.php';
    include 'settings.php';
    include 'masters.php';
    include 'fleet.php';
    include 'reports.php';
    include 'payroll.php';

    // Billing & AI usage. Deliberately absent from config/modules.php, so the
    // module.permission middleware leaves these paths unrestricted and every
    // authenticated user can reach their own subscription and usage data.
    Route::prefix('billing')->name('billing.')->middleware('owner')->group(function () {
        Route::get('', [\App\Http\Controllers\Billing\SubscriptionController::class, 'index'])->name('index');
        Route::post('contact/{package}', [\App\Http\Controllers\Billing\SubscriptionController::class, 'contact'])->name('contact');
        Route::get('ai-usage', [\App\Http\Controllers\AiUsageController::class, 'index'])->name('ai-usage');
    });

});


// Queue processing routes for shared hosting
Route::post('/queue/process-emails', [\App\Http\Controllers\QueueController::class, 'processEmails'])->name('queue.process-emails');
Route::post('/queue/retry-failed-jobs', [\App\Http\Controllers\QueueController::class, 'retryFailedJobs'])->name('queue.retry-failed-jobs');

require __DIR__ . '/auth.php';

Route::get('/log/activities/feed', [LogActivityController::class, 'showFeed'])->name('activities.feed');
Route::get('/log/activities/load-more', [LogActivityController::class, 'loadMoreActivities'])->name('activities.load_more');

Route::get('logout', function () {
    Auth::logout();
    //session()->forget('company_id');
    return redirect('/login');
});



// Local-only end-to-end checker (headless + live "head" mode). 404s outside the local environment.
Route::middleware(['auth'])->prefix('autocheck')->name('autocheck.')->group(function () {
    Route::get('/', [\App\Http\Controllers\AutocheckController::class, 'index'])->name('index');
    Route::post('/headless', [\App\Http\Controllers\AutocheckController::class, 'headless'])->name('headless');
    Route::post('/step', [\App\Http\Controllers\AutocheckController::class, 'step'])->name('step');
    Route::post('/cleanup', [\App\Http\Controllers\AutocheckController::class, 'cleanup'])->name('cleanup');
    Route::post('/reset', [\App\Http\Controllers\AutocheckController::class, 'reset'])->name('reset');
});

Route::get('/track/{token}', [\App\Http\Controllers\Job\TrackingController::class, 'publicShow'])->where('token', '[A-Za-z0-9]{32}');

// The customer's accept / reject page from the quotation email (no login: the random token is the key)
Route::middleware('throttle:30,1')->group(function () {
    Route::get('/q/{token}', [\App\Http\Controllers\Crm\QuotationResponseController::class, 'show'])->where('token', '[A-Za-z0-9]{40}');
    Route::post('/q/{token}/respond', [\App\Http\Controllers\Crm\QuotationResponseController::class, 'respond'])->where('token', '[A-Za-z0-9]{40}');
});

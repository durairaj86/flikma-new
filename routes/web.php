<?php

use App\Http\Controllers\Ocr\OcrController;
use App\Http\Controllers\Common\LogActivityController;
use App\Http\Controllers\ProfileController;
use Codesmiths\LaravelOcrSpace\Facades\OcrSpace;
use Codesmiths\LaravelOcrSpace\OcrSpaceOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

/*Route::get('/', function () {
    return view('welcome');
});*/
Route::redirect('/', '/login', 302);
Route::view('/register', 'auth.register')->name('register');

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::view('/customers', 'modules.customer.list')->name('customers');
    Route::post('/customer/data', [\App\Http\Controllers\Customer\CustomerController::class, 'fetchAllRows'])->name('customers.data');
    Route::post('/customer/import/upload', [\App\Http\Controllers\Customer\CustomerController::class, 'importUpload']);
    Route::post('/customer/import/process', [\App\Http\Controllers\Customer\CustomerController::class, 'importProcess']);
    Route::get('/customer/create', [\App\Http\Controllers\Customer\CustomerController::class, 'modal']);
    Route::post('/customer/create', [\App\Http\Controllers\Customer\CustomerController::class, 'store']);
    Route::get('/customer/{id}/create', [\App\Http\Controllers\Customer\CustomerController::class, 'edit']);
    Route::post('/customer/{id}/create', [\App\Http\Controllers\Customer\CustomerController::class, 'store']);
    Route::get('/customer/create/quick', [\App\Http\Controllers\Customer\CustomerController::class, 'quickModal']);
    Route::post('/customer/create/quick', [\App\Http\Controllers\Customer\CustomerController::class, 'quickStore']);
    Route::get('customer/{id}/actions', [\App\Http\Controllers\Customer\CustomerController::class, 'actions']);
    Route::post('customer/{id}/status/{status}', [\App\Http\Controllers\Customer\CustomerController::class, 'updateStatus']);
    Route::view('/customer/statement', 'modules.customer.statement')->name('customers.statement');

    Route::get('customer/{id}/overview', [\App\Http\Controllers\Customer\CustomerController::class, 'overview']);
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

    Route::get('/prospect/create/quick', [\App\Http\Controllers\Prospect\ProspectController::class, 'quickModal']);
    Route::post('/prospect/create/quick', [\App\Http\Controllers\Prospect\ProspectController::class, 'quickStore']);

    Route::view('/suppliers', 'modules.supplier.list')->name('suppliers');
    Route::post('/supplier/data', [\App\Http\Controllers\Supplier\SupplierController::class, 'fetchAllRows'])->name('suppliers.data');
    Route::post('/supplier/import/upload', [\App\Http\Controllers\Supplier\SupplierController::class, 'importUpload']);
    Route::post('/supplier/import/process', [\App\Http\Controllers\Supplier\SupplierController::class, 'importProcess']);
    Route::get('/supplier/create', [\App\Http\Controllers\Supplier\SupplierController::class, 'modal']);
    Route::post('/supplier/create', [\App\Http\Controllers\Supplier\SupplierController::class, 'store']);
    Route::get('/supplier/{id}/create', [\App\Http\Controllers\Supplier\SupplierController::class, 'edit']);
    Route::post('/supplier/{id}/create', [\App\Http\Controllers\Supplier\SupplierController::class, 'store']);
    Route::get('supplier/{id}/actions', [\App\Http\Controllers\Supplier\SupplierController::class, 'actions']);
    Route::post('supplier/{id}/status/{status}', [\App\Http\Controllers\Supplier\SupplierController::class, 'updateStatus']);
    Route::get('supplier/{id}/overview', [\App\Http\Controllers\Supplier\SupplierController::class, 'overview']);
    //Route::view('/supplier/statement', 'modules.supplier.statement')->name('suppliers.statement');
    Route::get('/supplier/statement', [\App\Http\Controllers\Supplier\SupplierStatementController::class, 'index'])->name('suppliers.statement');


    Route::get('/dropdown/search', [\App\Http\Controllers\Common\DropdownListSearchController::class, 'index']);
    Route::get('/load/customer', [\App\Http\Controllers\Common\DropdownListSearchController::class, 'customerList']);
    // Item routes
    Route::view('/inventory/items', 'modules.inventory.item.list')->name('items');
    Route::post('/inventory/items/data', [\App\Http\Controllers\Item\ItemController::class, 'fetchAllRows'])->name('items.data');
    Route::get('/inventory/items/create', [\App\Http\Controllers\Item\ItemController::class, 'modal']);
    Route::post('/inventory/items/create', [\App\Http\Controllers\Item\ItemController::class, 'store']);
    Route::get('/inventory/items/{id}/edit', [\App\Http\Controllers\Item\ItemController::class, 'edit']);
    Route::post('/inventory/items/{id}/create', [\App\Http\Controllers\Item\ItemController::class, 'store']);
    Route::get('/inventory/items/{id}/view', [\App\Http\Controllers\Item\ItemController::class, 'view']);
    Route::get('/inventory/items/{id}/actions', [\App\Http\Controllers\Item\ItemController::class, 'actions']);
    Route::delete('/inventory/items/{id}/delete', [\App\Http\Controllers\Item\ItemController::class, 'delete']);
    Route::post('/inventory/items/store', [\App\Http\Controllers\Item\ItemController::class, 'createItem']);


    Route::get('currency/rate/{base}/{target}', [\App\Http\Controllers\CurrencyExchangeController::class, 'getExchangeRate']);

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
    include 'finance/opening_balance.php';
    include 'transaction/payment.php';
    include 'transaction/collection.php';
    include 'settings.php';
    include 'masters.php';
    include 'reports.php';
    include 'payroll.php';

});

Route::view('/developer/quote', 'developer.quote');
Route::view('/developer/quote1', 'developer.quote1');
Route::view('/developer/quote2', 'developer.quote2');
Route::view('/developer/supplier', 'developer.supplier');
Route::view('/developer/tomselect', 'developer.tomselect');
Route::view('/developer/design', 'developer.design');
Route::view('/developer/print', 'developer.print');
Route::view('/developer/web1', 'developer.web1');
Route::view('/developer/web2', 'developer.web2');
Route::view('/developer/web3', 'developer.web3');
Route::view('/developer/job-card', 'developer.job-card');

// Queue processing routes for shared hosting
Route::post('/queue/process-emails', [\App\Http\Controllers\QueueController::class, 'processEmails'])->name('queue.process-emails');
Route::post('/queue/retry-failed-jobs', [\App\Http\Controllers\QueueController::class, 'retryFailedJobs'])->name('queue.retry-failed-jobs');

Route::get('/ocr', [OcrController::class, 'index'])->name('ocr.index');
Route::post('/ocr/upload', [OcrController::class, 'upload'])->name('ocr.upload');

require __DIR__ . '/auth.php';

Route::get('/log/activities/feed', [LogActivityController::class, 'showFeed'])->name('activities.feed');
Route::get('/log/activities/load-more', [LogActivityController::class, 'loadMoreActivities'])->name('activities.load_more');

Route::get('/dashboard-with-feed', function () {
    return view('dashboard'); // Assuming you have a basic dashboard view
})->name('dashboard.feed.test');

Route::get('logout', function () {
    Auth::logout();
    //session()->forget('company_id');
    return redirect('/login');
});


Route::get('/test-ocr', [OcrController::class, 'index']);
Route::post('/test-ocr/upload', [OcrController::class, 'upload'])->name('test-ocr.upload');

Route::get('/test-google-ocr', [OcrController::class, 'googleOcrIndex']);
Route::post('/test-google-ocr/upload', [OcrController::class, 'googleOcrUpload'])->name('test-google-ocr.upload');

Route::get('/compare-ocr-engines', [OcrController::class, 'compareEngines'])->name('ocr.compare-engines');
Route::post('/compare-ocr-engines/upload', [OcrController::class, 'compareUpload'])->name('ocr.compare-upload');

// Test route for OCR with Language enum
Route::get('/test-ocr-enum', function () {
    try {
        // Create options for OCR Space
        $options = \Codesmiths\LaravelOcrSpace\OcrSpaceOptions::make()
            ->language(\Codesmiths\LaravelOcrSpace\Enums\Language::English)
            ->detectOrientation(true)
            ->scale(true)
            ->isTable(false);

        // Test with a sample image URL
        $imageUrl = 'https://ocr.space/Content/Images/receipt.jpg';

        // Process the image URL with OCR Space
        $result = \Codesmiths\LaravelOcrSpace\Facades\OcrSpace::parseImageUrl($imageUrl, $options);

        // Get the parsed text
        $text = $result->getParsedText();

        return response()->json([
            'status' => 'success',
            'text' => $text
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});

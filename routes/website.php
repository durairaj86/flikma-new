<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Website\ContactController;

/*
|--------------------------------------------------------------------------
| Marketing Website
|--------------------------------------------------------------------------
| Registered once per marketing domain from routes/web.php. The `$page`
| variable drives SEO/schema behaviour inside website.layout.
|
*/
Route::name('website.')->group(function () {
    Route::view('/', 'website.home', ['page' => 'home'])->name('home');
    Route::view('/features', 'website.features', ['page' => 'features'])->name('features');
    Route::view('/services', 'website.services', ['page' => 'services'])->name('services');
    Route::view('/products', 'website.products', ['page' => 'products'])->name('products');
    Route::view('/why-flikma', 'website.why-flikma', ['page' => 'why-flikma'])->name('why-flikma');
    Route::view('/pricing', 'website.pricing', ['page' => 'pricing'])->name('pricing');
    Route::view('/documentation', 'website.documentation', ['page' => 'documentation'])->name('documentation');
    Route::view('/about', 'website.about', ['page' => 'about'])->name('about');
    Route::get('/contact', [ContactController::class, 'show'])->name('contact');
    Route::post('/contact', [ContactController::class, 'store'])->name('contact.submit');
});

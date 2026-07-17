<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Website\ContactController;

Route::name('website.')->group(function () {
    Route::view('/', 'website.home')->name('home');
    Route::view('/features', 'website.features')->name('features');
    Route::view('/services', 'website.services')->name('services');
    Route::view('/products', 'website.products')->name('products');
    Route::view('/why-flikma', 'website.why-flikma')->name('why-flikma');
    Route::view('/pricing', 'website.pricing')->name('pricing');
    Route::get('/contact', [ContactController::class, 'show'])->name('contact');
    Route::post('/contact', [ContactController::class, 'store'])->name('contact.submit');
});

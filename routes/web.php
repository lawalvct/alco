<?php

use App\Http\Controllers\Public\AboutController;
use App\Http\Controllers\Public\ConsultationController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\FaqController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\ServiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/about', AboutController::class)->name('about');
Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/services/{slug}', [ServiceController::class, 'show'])->name('services.show');
Route::inertia('/insights', 'Public/Section', ['kind' => 'insights'])->name('insights.index');
Route::inertia('/tools', 'Public/Section', ['kind' => 'tools'])->name('tools.index');
Route::get('/faq', FaqController::class)->name('faq');

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:leads')->name('contact.store');
Route::get('/book-consultation', [ConsultationController::class, 'show'])->name('consultation');
Route::post('/book-consultation', [ConsultationController::class, 'store'])->middleware('throttle:leads')->name('consultation.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';

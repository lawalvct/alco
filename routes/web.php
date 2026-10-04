<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::inertia('/about', 'Public/Section', ['kind' => 'about'])->name('about');
Route::inertia('/services', 'Public/Section', ['kind' => 'services'])->name('services.index');
Route::inertia('/insights', 'Public/Section', ['kind' => 'insights'])->name('insights.index');
Route::inertia('/tools', 'Public/Section', ['kind' => 'tools'])->name('tools.index');
Route::inertia('/contact', 'Public/Section', ['kind' => 'contact'])->name('contact');
Route::inertia('/book-consultation', 'Public/Section', ['kind' => 'consultation'])->name('consultation');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';

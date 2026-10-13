<?php

use App\Http\Controllers\Public\AboutController;
use App\Http\Controllers\Public\CalculatorController;
use App\Http\Controllers\Public\ConsultationController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\FaqController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\InsightController;
use App\Http\Controllers\Public\LegalController;
use App\Http\Controllers\Public\ServiceController;
use App\Http\Controllers\Public\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

Route::get('/about', AboutController::class)->name('about');
Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/services/{slug}', [ServiceController::class, 'show'])->name('services.show');
Route::get('/insights', [InsightController::class, 'index'])->name('insights.index');
Route::get('/insights/topic/{slug}', [InsightController::class, 'topic'])->name('insights.topic');
Route::get('/insights/category/{category}', [InsightController::class, 'category'])->name('insights.category');
Route::get('/insights/{slug}', [InsightController::class, 'show'])->name('insights.show');
Route::get('/tools', [CalculatorController::class, 'index'])->name('tools.index');
Route::post('/tools/{slug}/calculate', [CalculatorController::class, 'calculate'])->middleware('throttle:calculators')->name('tools.calculate');
Route::get('/tools/{slug}', [CalculatorController::class, 'show'])->name('tools.show');
Route::get('/faq', FaqController::class)->name('faq');

Route::get('/privacy', LegalController::class)->defaults('slug', 'privacy')->name('legal.privacy');
Route::get('/terms', LegalController::class)->defaults('slug', 'terms')->name('legal.terms');
Route::get('/calculator-disclaimer', LegalController::class)->defaults('slug', 'calculator-disclaimer')->name('legal.calculator-disclaimer');

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:leads')->name('contact.store');
Route::get('/book-consultation', [ConsultationController::class, 'show'])->name('consultation');
Route::post('/book-consultation', [ConsultationController::class, 'store'])->middleware('throttle:leads')->name('consultation.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';

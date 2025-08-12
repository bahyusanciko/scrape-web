<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ScrapingController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Dashboard routes
Route::prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/jobs', [DashboardController::class, 'jobs'])->name('jobs');
    Route::get('/data', [DashboardController::class, 'data'])->name('data');
    Route::get('/platform/{platform}/stats', [DashboardController::class, 'platformStats'])->name('platform.stats');
});

// Scraping routes
Route::prefix('scraping')->name('scraping.')->group(function () {
    Route::get('/', [ScrapingController::class, 'index'])->name('index');
    Route::get('/create', [ScrapingController::class, 'create'])->name('create');
    Route::post('/', [ScrapingController::class, 'store'])->name('store');
    Route::get('/status', [ScrapingController::class, 'status'])->name('status');

    // Job routes
    Route::prefix('jobs')->name('jobs.')->group(function () {
        Route::get('/{job}', [ScrapingController::class, 'show'])->name('show');
        Route::post('/{job}/execute', [ScrapingController::class, 'execute'])->name('execute');
        Route::delete('/{job}', [ScrapingController::class, 'destroy'])->name('destroy');
        Route::get('/{job}/data', [ScrapingController::class, 'data'])->name('data');
        Route::get('/{job}/export', [ScrapingController::class, 'export'])->name('export');
        Route::post('/{job}/retry', [ScrapingController::class, 'retry'])->name('retry');
    });
});

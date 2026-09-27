<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PortfolioController as AdminPortfolioController;
use App\Http\Controllers\Admin\PortfolioImageController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\PortfolioController;
use App\Http\Controllers\Frontend\ServiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home']);
Route::get('/tentang', [PageController::class, 'about']);
Route::get('/kontak', [PageController::class, 'contact']);
Route::get('/layanan', [ServiceController::class, 'index']);
Route::get('/layanan/{slug}', [ServiceController::class, 'show']);
Route::get('/portfolio', [PortfolioController::class, 'index']);
Route::get('/portfolio/{slug}', [PortfolioController::class, 'show']);

Route::get('/admin/login', [AuthController::class, 'show'])->name('login');
Route::post('/admin/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::prefix('admin')->middleware('admin.jwt')->group(function () {
    Route::get('/', DashboardController::class);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/layanan', [AdminServiceController::class, 'index']);
    Route::post('/layanan', [AdminServiceController::class, 'store']);
    Route::put('/layanan/{service}', [AdminServiceController::class, 'update']);
    Route::patch('/layanan/{service}/toggle', [AdminServiceController::class, 'toggle']);
    Route::delete('/layanan/{service}', [AdminServiceController::class, 'destroy']);

    Route::get('/portfolio', [AdminPortfolioController::class, 'index']);
    Route::post('/portfolio', [AdminPortfolioController::class, 'store']);
    Route::put('/portfolio/{portfolio}', [AdminPortfolioController::class, 'update']);
    Route::patch('/portfolio/{portfolio}/toggle', [AdminPortfolioController::class, 'toggle']);
    Route::delete('/portfolio/{portfolio}', [AdminPortfolioController::class, 'destroy']);
    Route::post('/portfolio/{portfolio}/images', [PortfolioImageController::class, 'store']);
    Route::post('/portfolio/{portfolio}/images/order', [PortfolioImageController::class, 'reorder']);
    Route::delete('/portfolio/{portfolio}/images/{image}', [PortfolioImageController::class, 'destroy']);

    Route::middleware('superadmin')->group(function () {
        Route::view('/users', 'admin.coming-soon', ['feature' => 'Users']);
        Route::get('/settings', [SettingController::class, 'edit']);
        Route::put('/settings', [SettingController::class, 'update']);
    });
});

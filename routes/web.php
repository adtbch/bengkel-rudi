<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PortfolioController as AdminPortfolioController;
use App\Http\Controllers\Admin\PortfolioImageController;
use App\Http\Controllers\Admin\PortfolioUploadController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
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

Route::get('/robots.txt', function () {
    return response("User-agent: *\nAllow: /\nDisallow: /admin\nSitemap: ".url('/sitemap.xml')."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
});

Route::get('/sitemap.xml', function () {
    $baseUrl = rtrim(config('app.url'), '/');
    $urls = collect([
        ['path' => '/', 'updated_at' => now()],
        ['path' => '/tentang', 'updated_at' => now()],
        ['path' => '/kontak', 'updated_at' => now()],
        ['path' => '/layanan', 'updated_at' => now()],
        ['path' => '/portfolio', 'updated_at' => now()],
    ])->merge(\App\Models\Service::where('is_active', true)->get(['slug', 'updated_at'])->map(fn ($service) => ['path' => "/layanan/{$service->slug}", 'updated_at' => $service->updated_at]))
        ->merge(\App\Models\Portfolio::where('is_published', true)->get(['slug', 'updated_at'])->map(fn ($portfolio) => ['path' => "/portfolio/{$portfolio->slug}", 'updated_at' => $portfolio->updated_at]));

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($urls as $url) {
        $xml .= '<url><loc>'.e($baseUrl.$url['path']).'</loc><lastmod>'.$url['updated_at']->toAtomString().'</lastmod></url>';
    }

    return response($xml.'</urlset>', 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
});

Route::get('/admin/login', [AuthController::class, 'show'])->name('login');
Route::post('/admin/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::prefix('admin')->middleware('admin.jwt')->group(function () {
    Route::get('/', DashboardController::class);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/layanan', [AdminServiceController::class, 'index']);
    Route::post('/layanan', [AdminServiceController::class, 'store']);
    Route::get('/layanan/{service}', [AdminServiceController::class, 'show']);
    Route::put('/layanan/{service}', [AdminServiceController::class, 'update']);
    Route::patch('/layanan/{service}/toggle', [AdminServiceController::class, 'toggle']);
    Route::delete('/layanan/{service}', [AdminServiceController::class, 'destroy']);

    Route::get('/portfolio', [AdminPortfolioController::class, 'index']);
    Route::post('/portfolio', [AdminPortfolioController::class, 'store']);
    Route::get('/portfolio/{portfolio}', [AdminPortfolioController::class, 'show']);
    Route::post('/portfolio/{portfolio}/save', [AdminPortfolioController::class, 'save']);
    Route::put('/portfolio/{portfolio}', [AdminPortfolioController::class, 'update']);
    Route::patch('/portfolio/{portfolio}/toggle', [AdminPortfolioController::class, 'toggle']);
    Route::delete('/portfolio/{portfolio}', [AdminPortfolioController::class, 'destroy']);
    Route::post('/portfolio/{portfolio}/uploads/sign', [PortfolioUploadController::class, 'sign'])->middleware('throttle:60,1');
    Route::delete('/portfolio/{portfolio}/uploads/{mediaUpload}', [PortfolioUploadController::class, 'destroy']);
    Route::post('/portfolio/{portfolio}/images/order', [PortfolioImageController::class, 'reorder']);
    Route::delete('/portfolio/{portfolio}/images/{image}', [PortfolioImageController::class, 'destroy']);

    Route::middleware('superadmin')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::get('/settings', [SettingController::class, 'edit']);
        Route::put('/settings', [SettingController::class, 'update']);
    });
});

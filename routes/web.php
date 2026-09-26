<?php
use App\Http\Controllers\AdminAuthController; use App\Http\Controllers\AdminDashboardController; use Illuminate\Support\Facades\Route;
Route::view('/','welcome');
Route::get('/admin/login',[AdminAuthController::class,'show'])->name('login');
Route::post('/admin/login',[AdminAuthController::class,'login'])->middleware('throttle:5,1');
Route::prefix('admin')->middleware('admin.jwt')->group(function(){ Route::get('/',AdminDashboardController::class); Route::post('/logout',[AdminAuthController::class,'logout']); Route::view('/portfolio','admin.coming-soon',['feature'=>'Portfolio']); Route::view('/layanan','admin.coming-soon',['feature'=>'Layanan']); Route::middleware('superadmin')->group(function(){ Route::view('/users','admin.coming-soon',['feature'=>'Users']); Route::view('/settings','admin.coming-soon',['feature'=>'Settings']); }); });

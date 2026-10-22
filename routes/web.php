<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FrostAlertController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RiskReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/mercado', [MarketController::class, 'index'])->name('market.index');
Route::get('/alertas', [FrostAlertController::class, 'index'])->name('alerts.index');

Route::middleware('guest')->group(function () {
    Route::get('/ingresar', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/ingresar', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('login.store');
    Route::get('/crear-cuenta', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/crear-cuenta', [AuthController::class, 'register'])->middleware('throttle:6,1')->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/salir', [AuthController::class, 'logout'])->name('logout');
    Route::get('/panel', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/pedidos', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/productos/{product}/pedir', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/productos/crear', [ProductController::class, 'create'])->name('products.create');
    Route::post('/productos', [ProductController::class, 'store'])->name('products.store');
    Route::get('/productos/{product}/editar', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/productos/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/productos/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::get('/reportes/nuevo', [RiskReportController::class, 'create'])->name('risks.create');
    Route::post('/reportes', [RiskReportController::class, 'store'])->name('risks.store');

    Route::prefix('administracion')->name('admin.')->group(function () {
        Route::get('/comunidades', [AdminController::class, 'communities'])->name('communities.index');
        Route::post('/comunidades', [AdminController::class, 'storeCommunity'])->name('communities.store');
        Route::get('/alertas/crear', [AdminController::class, 'createAlert'])->name('alerts.create');
        Route::post('/alertas', [AdminController::class, 'storeAlert'])->name('alerts.store');
        Route::get('/reportes', [AdminController::class, 'reports'])->name('reports.index');
        Route::patch('/reportes/{riskReport}', [AdminController::class, 'reviewReport'])->name('reports.review');
    });
});
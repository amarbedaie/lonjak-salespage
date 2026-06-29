<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\RecoveryController;
use App\Http\Controllers\SalespageController;
use App\Http\Controllers\SettingsController;
use App\Livewire\Builder;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'landing'])->name('landing');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/langgan', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/langgan', [AuthController::class, 'register']);
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::view('/suspended', 'suspended')->name('suspended');

// Public salespage + checkout
Route::get('/s/{slug}', [PublicController::class, 'show'])->name('salespage.public');
Route::post('/s/{slug}/order', [PublicController::class, 'order'])->name('salespage.order');

// BayarCash payment callbacks
Route::match(['get', 'post'], '/payment/return', [\App\Http\Controllers\PaymentController::class, 'return'])->name('payment.return');
Route::post('/payment/callback', [\App\Http\Controllers\PaymentController::class, 'callback'])->name('payment.callback');

// Merchant dashboard
Route::middleware(['auth', 'not.suspended'])->prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/salespages', [SalespageController::class, 'index'])->name('salespages.index');
    Route::get('/salespages/new', Builder::class)->name('salespages.create');
    Route::get('/salespages/{salespage}', [SalespageController::class, 'show'])->name('salespages.show');
    Route::post('/salespages/{salespage}/status', [SalespageController::class, 'setStatus'])->name('salespages.status');
    Route::put('/salespages/{salespage}', [SalespageController::class, 'update'])->name('salespages.update');
    Route::delete('/salespages/{salespage}', [SalespageController::class, 'destroy'])->name('salespages.destroy');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/{order}/status', [OrderController::class, 'setStatus'])->name('orders.status');

    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');

    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
    Route::view('/payments', 'dashboard.payments')->name('payments');
    Route::view('/shipping', 'dashboard.shipping')->name('shipping');
    Route::get('/recovery', [RecoveryController::class, 'index'])->name('recovery');
    Route::get('/affiliate', [AffiliateController::class, 'index'])->name('affiliate');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
});

// Admin platform
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/merchants', [AdminController::class, 'merchants'])->name('merchants');
    Route::get('/merchants/{user}', [AdminController::class, 'merchant'])->name('merchant');
    Route::post('/merchants/{user}/control', [AdminController::class, 'control'])->name('merchant.control');
    Route::get('/salespages', [AdminController::class, 'salespages'])->name('salespages');
    Route::post('/salespages/{salespage}/status', [AdminController::class, 'salespageStatus'])->name('salespage.status');
    Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
});

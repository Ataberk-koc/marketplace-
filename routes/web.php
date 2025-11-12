<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\OrderController as UserOrderController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Seller\DashboardController as SellerDashboardController;
use App\Http\Controllers\Seller\ProductController as SellerProductController;
use App\Http\Controllers\Seller\OrderController as SellerOrderController;
use App\Http\Controllers\Seller\TrendyolController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Ana sayfa ve genel sayfalar
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products', [HomeController::class, 'products'])->name('products.index');
Route::get('/products/{slug}', [HomeController::class, 'productShow'])->name('products.show');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Email Verification Routes
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [VerificationController::class, 'show'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
    Route::post('/email/resend', [VerificationController::class, 'resend'])->name('verification.resend');
});

// User Routes (Müşteri) - Email doğrulanmış ve aktif hesap gerekli
Route::middleware(['auth', 'verified.active'])->prefix('user')->name('user.')->group(function () {
    // Sepet
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::put('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

    // Siparişler
    Route::get('/orders', [UserOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [UserOrderController::class, 'show'])->name('orders.show');
    Route::get('/checkout', [UserOrderController::class, 'checkout'])->name('checkout');
    Route::post('/orders', [UserOrderController::class, 'store'])->name('orders.store');
    Route::post('/orders/{order}/cancel', [UserOrderController::class, 'cancel'])->name('orders.cancel');
});

// Admin Routes - Admin yetkisi gerekli
Route::middleware(['auth', 'verified.active', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Kullanıcı yönetimi
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/toggle-active', [AdminUserController::class, 'toggleActive'])->name('users.toggle-active');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    // Ürün yönetimi
    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::post('/products/{product}/toggle-active', [AdminProductController::class, 'toggleActive'])->name('products.toggle-active');
    Route::post('/products/{product}/toggle-featured', [AdminProductController::class, 'toggleFeatured'])->name('products.toggle-featured');
    Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');

    // Sipariş yönetimi
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::post('/orders/{order}/payment-status', [AdminOrderController::class, 'updatePaymentStatus'])->name('orders.update-payment-status');

    // Marka yönetimi
    Route::resource('brands', AdminBrandController::class);
    Route::post('/brands/sync-trendyol', [AdminBrandController::class, 'syncTrendyolBrands'])->name('brands.sync-trendyol');
    Route::get('/brands/{brand}/mapping', [AdminBrandController::class, 'mapping'])->name('brands.mapping');
    Route::post('/brands/{brand}/mapping', [AdminBrandController::class, 'saveMapping'])->name('brands.save-mapping');

    // Kategori yönetimi
    Route::resource('categories', AdminCategoryController::class);
    Route::post('/categories/sync-trendyol', [AdminCategoryController::class, 'syncTrendyolCategories'])->name('categories.sync-trendyol');
    Route::get('/categories/{category}/mapping', [AdminCategoryController::class, 'mapping'])->name('categories.mapping');
    Route::post('/categories/{category}/mapping', [AdminCategoryController::class, 'saveMapping'])->name('categories.save-mapping');
});

// Seller Routes - Satıcı yetkisi gerekli
Route::middleware(['auth', 'verified.active', 'seller'])->prefix('seller')->name('seller.')->group(function () {
    Route::get('/dashboard', [SellerDashboardController::class, 'index'])->name('dashboard');

    // Ürün yönetimi
    Route::resource('products', SellerProductController::class);

    // Sipariş yönetimi
    Route::get('/orders', [SellerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [SellerOrderController::class, 'show'])->name('orders.show');

    // Trendyol entegrasyonu
    Route::get('/trendyol', [TrendyolController::class, 'index'])->name('trendyol.index');
    Route::post('/trendyol/{product}/send', [TrendyolController::class, 'sendProduct'])->name('trendyol.send');
    Route::post('/trendyol/{product}/update', [TrendyolController::class, 'updateProduct'])->name('trendyol.update');
    Route::post('/trendyol/sync-all', [TrendyolController::class, 'syncAll'])->name('trendyol.sync-all');
});

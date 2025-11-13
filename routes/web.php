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

// Test routes - Trendyol API
Route::get('/test-trendyol-brands', function() {
    $service = new \App\Services\TrendyolService();
    $result = $service->getBrands(0);
    
    return response()->json([
        'success' => $result['success'],
        'brand_count' => count($result['data']['brands'] ?? []),
        'first_5_brands' => array_slice($result['data']['brands'] ?? [], 0, 5),
        'message' => $result['message'] ?? 'OK'
    ]);
});

Route::get('/test-trendyol-categories', function() {
    $service = new \App\Services\TrendyolService();
    $result = $service->getFlatCategories();
    
    $leafCategories = array_filter($result['data']['categories'] ?? [], function($cat) {
        return $cat['leaf'] === true;
    });
    
    return response()->json([
        'success' => $result['success'],
        'total_categories' => count($result['data']['categories'] ?? []),
        'leaf_categories' => count($leafCategories),
        'first_10_leaf_categories' => array_slice($leafCategories, 0, 10),
        'message' => $result['message'] ?? 'OK'
    ]);
});

Route::get('/test-trendyol-category-attributes/{categoryId}', function($categoryId) {
    $service = new \App\Services\TrendyolService();
    $result = $service->getCategoryAttributes($categoryId);
    
    return response()->json([
        'success' => $result['success'],
        'category_id' => $categoryId,
        'attributes_count' => count($result['data']['categoryAttributes'] ?? []),
        'first_5_attributes' => array_slice($result['data']['categoryAttributes'] ?? [], 0, 5),
        'message' => $result['message'] ?? 'OK'
    ]);
});

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
    Route::post('/email/verification-notification', [VerificationController::class, 'resend'])->name('verification.send');
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
});

// Email Verification Routes
// Email verification route'ları (isteğe bağlı - şu an kullanılmıyor)
// Route::middleware('auth')->group(function () {
//     Route::get('/email/verify', [VerificationController::class, 'show'])->name('verification.notice');
//     Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
//     Route::post('/email/resend', [VerificationController::class, 'resend'])->name('verification.resend');
// });

// User Routes (Müşteri) - Sadece auth gerekli
Route::middleware(['auth'])->prefix('user')->name('user.')->group(function () {
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

// Admin Routes - Sadece admin yetkisi gerekli
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Kullanıcı yönetimi
    Route::resource('users', AdminUserController::class)->except(['show']);
    Route::post('/users/{user}/toggle-active', [AdminUserController::class, 'toggleActive'])->name('users.toggle-active');
    Route::post('/users/{user}/change-role', [AdminUserController::class, 'changeRole'])->name('users.change-role');

    // Ürün yönetimi
    Route::resource('products', AdminProductController::class);
    Route::post('/products/{product}/toggle-active', [AdminProductController::class, 'toggleActive'])->name('products.toggle-active');
    Route::post('/products/{product}/toggle-featured', [AdminProductController::class, 'toggleFeatured'])->name('products.toggle-featured');
    Route::get('/products/{product}/attributes', [AdminProductController::class, 'attributes'])->name('products.attributes');
    Route::post('/products/{product}/attributes', [AdminProductController::class, 'saveAttributes'])->name('products.save-attributes');
    Route::post('/sync-category-attributes', [AdminProductController::class, 'syncCategoryAttributes'])->name('products.sync-category-attributes');

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
    Route::get('/categories/{category}/attributes', [AdminCategoryController::class, 'attributes'])->name('categories.attributes');
    Route::post('/categories/{category}/attributes', [AdminCategoryController::class, 'saveAttributeMapping'])->name('categories.save-attribute-mapping');

    // Beden yönetimi
    Route::resource('sizes', App\Http\Controllers\Admin\SizeController::class);
    Route::post('/sizes/sync-trendyol', [App\Http\Controllers\Admin\SizeController::class, 'syncTrendyolSizes'])->name('sizes.sync-trendyol');
    Route::get('/sizes/{size}/mapping', [App\Http\Controllers\Admin\SizeController::class, 'mapping'])->name('sizes.mapping');
    Route::post('/sizes/{size}/mapping', [App\Http\Controllers\Admin\SizeController::class, 'saveMapping'])->name('sizes.save-mapping');
    Route::get('/sizes-bulk-mapping', [App\Http\Controllers\Admin\SizeController::class, 'bulkMapping'])->name('sizes.bulk-mapping');
    Route::post('/sizes-bulk-mapping', [App\Http\Controllers\Admin\SizeController::class, 'saveBulkMapping'])->name('sizes.save-bulk-mapping');

    // Raporlar
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\ReportController::class, 'index'])->name('index');
        Route::get('/sales', [App\Http\Controllers\Admin\ReportController::class, 'sales'])->name('sales');
        Route::get('/products', [App\Http\Controllers\Admin\ReportController::class, 'products'])->name('products');
        Route::get('/categories', [App\Http\Controllers\Admin\ReportController::class, 'categories'])->name('categories');
        Route::get('/sellers', [App\Http\Controllers\Admin\ReportController::class, 'sellers'])->name('sellers');
    });

    // Ödemeler
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('index');
        Route::get('/{order}', [App\Http\Controllers\Admin\PaymentController::class, 'show'])->name('show');
        Route::patch('/{order}/status', [App\Http\Controllers\Admin\PaymentController::class, 'updateStatus'])->name('update-status');
        Route::post('/{order}/approve', [App\Http\Controllers\Admin\PaymentController::class, 'approve'])->name('approve');
        Route::post('/{order}/refund', [App\Http\Controllers\Admin\PaymentController::class, 'refund'])->name('refund');
        Route::get('/sellers/payments', [App\Http\Controllers\Admin\PaymentController::class, 'sellerPayments'])->name('sellers');
    });

    // Trendyol Yönetimi
    Route::prefix('trendyol')->name('trendyol.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\TrendyolController::class, 'index'])->name('index');
        Route::post('/sync-brands', [App\Http\Controllers\Admin\TrendyolController::class, 'syncBrands'])->name('sync-brands');
        Route::post('/sync-categories', [App\Http\Controllers\Admin\TrendyolController::class, 'syncCategories'])->name('sync-categories');
        
        // Manuel Eşleştirme Routes
        Route::get('/brand-mapping', [App\Http\Controllers\Admin\TrendyolController::class, 'brandMapping'])->name('brand-mapping');
        Route::post('/brand-mapping', [App\Http\Controllers\Admin\TrendyolController::class, 'saveBrandMapping'])->name('save-brand-mapping');
        Route::delete('/brand-mapping/{mapping}', [App\Http\Controllers\Admin\TrendyolController::class, 'deleteBrandMapping'])->name('delete-brand-mapping');
        
        Route::get('/category-mapping', [App\Http\Controllers\Admin\TrendyolController::class, 'categoryMapping'])->name('category-mapping');
        Route::post('/category-mapping', [App\Http\Controllers\Admin\TrendyolController::class, 'saveCategoryMapping'])->name('save-category-mapping');
        Route::delete('/category-mapping/{mapping}', [App\Http\Controllers\Admin\TrendyolController::class, 'deleteCategoryMapping'])->name('delete-category-mapping');
        
        // BEDEN (SIZE) EŞLEŞTİRME
        Route::get('/size-mapping', [App\Http\Controllers\Admin\TrendyolController::class, 'sizeMappingIndex'])->name('size-mapping');
        Route::get('/size-mapping/create', [App\Http\Controllers\Admin\TrendyolController::class, 'sizeMappingCreate'])->name('size-mapping-create');
        Route::post('/size-mapping', [App\Http\Controllers\Admin\TrendyolController::class, 'saveSizeMapping'])->name('save-size-mapping');
        Route::delete('/size-mapping/{mapping}', [App\Http\Controllers\Admin\TrendyolController::class, 'deleteSizeMapping'])->name('delete-size-mapping');
        Route::get('/api/trendyol-attribute-values', [App\Http\Controllers\Admin\TrendyolController::class, 'getTrendyolAttributeValues'])->name('api.trendyol-attribute-values');
        
        // ÜRÜN EŞLEŞTİRME (YENİ AKIŞ: Marka → Kategori → Ürün)
        Route::get('/product-mapping', [App\Http\Controllers\Admin\TrendyolController::class, 'productMapping'])->name('product-mapping');
        Route::get('/api/categories-by-brand/{brandId}', [App\Http\Controllers\Admin\TrendyolController::class, 'getCategoriesByBrand'])->name('api.categories-by-brand');
        Route::get('/api/products-by-brand-category', [App\Http\Controllers\Admin\TrendyolController::class, 'getProductsByBrandAndCategory'])->name('api.products-by-brand-category');
        Route::get('/category-attributes/{categoryId}', [App\Http\Controllers\Admin\TrendyolController::class, 'getCategoryAttributes'])->name('category-attributes');
        Route::post('/product-mapping', [App\Http\Controllers\Admin\TrendyolController::class, 'saveProductMapping'])->name('save-product-mapping');
        Route::delete('/product-mapping/{mapping}', [App\Http\Controllers\Admin\TrendyolController::class, 'deleteProductMapping'])->name('delete-product-mapping');
        
        // ÜRÜN GÖNDERİMİ
        Route::post('/send-single-product/{mapping}', [App\Http\Controllers\Admin\TrendyolController::class, 'sendSingleProduct'])->name('send-single-product');
        Route::post('/bulk-send', [App\Http\Controllers\Admin\TrendyolController::class, 'bulkSendProducts'])->name('bulk-send');
        Route::post('/bulk-update-inventory', [App\Http\Controllers\Admin\TrendyolController::class, 'bulkUpdateInventory'])->name('bulk-update-inventory');
        Route::post('/bulk-delete', [App\Http\Controllers\Admin\TrendyolController::class, 'bulkDeleteProducts'])->name('bulk-delete');
        Route::get('/batch-status/{batchRequestId}', [App\Http\Controllers\Admin\TrendyolController::class, 'checkBatchStatus'])->name('batch-status');
        Route::get('/products', [App\Http\Controllers\Admin\TrendyolController::class, 'filterProducts'])->name('products');
    });
});

// Seller Routes - Satıcı yetkisi gerekli
Route::middleware(['auth', 'verified.active', 'seller'])->prefix('seller')->name('seller.')->group(function () {
    Route::get('/dashboard', [SellerDashboardController::class, 'index'])->name('dashboard');

    // Ürün yönetimi
    Route::resource('products', SellerProductController::class);
    Route::get('/api/attributes-by-category', [SellerProductController::class, 'getAttributesByCategory'])->name('api.attributes-by-category');

    // Sipariş yönetimi
    Route::get('/orders', [SellerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [SellerOrderController::class, 'show'])->name('orders.show');

    // Trendyol entegrasyonu
    Route::get('/trendyol', [TrendyolController::class, 'index'])->name('trendyol.index');
    Route::post('/trendyol/{product}/send', [TrendyolController::class, 'sendProduct'])->name('trendyol.send');
    Route::post('/trendyol/{product}/update', [TrendyolController::class, 'updateProduct'])->name('trendyol.update');
    Route::post('/trendyol/sync-all', [TrendyolController::class, 'syncAll'])->name('trendyol.sync-all');
});

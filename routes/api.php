<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| RESTful API endpoints için route tanımlamaları
| Postman ile test edilebilir
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public API endpoints
Route::prefix('v1')->name('api.')->group(function () {
    // Ürünler
    Route::apiResource('products', ProductController::class);
    
    // Markalar
    Route::apiResource('brands', BrandController::class);
    
    // Kategoriler
    Route::apiResource('categories', CategoryController::class);
});

// Protected API endpoints (auth gerekli)
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Siparişler
    Route::apiResource('orders', OrderController::class);
});

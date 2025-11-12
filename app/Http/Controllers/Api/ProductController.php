<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * API Ürün Controller
 * RESTful API endpoints
 */
class ProductController extends Controller
{
    /**
     * Ürün listesini döner
     * GET /api/v1/products
     */
    public function index(Request $request)
    {
        $query = Product::with(['brand', 'category'])
            ->where('is_active', true);

        // Filtreleme
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        $products = $query->paginate($request->get('per_page', 15));

        return response()->json($products);
    }

    /**
     * Tek bir ürünün detayını döner
     * GET /api/v1/products/{id}
     */
    public function show($id)
    {
        $product = Product::with(['brand', 'category', 'sizes', 'seller'])
            ->where('is_active', true)
            ->findOrFail($id);

        return response()->json($product);
    }
}

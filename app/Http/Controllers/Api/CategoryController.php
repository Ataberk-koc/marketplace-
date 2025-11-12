<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

/**
 * API Kategori Controller
 */
class CategoryController extends Controller
{
    /**
     * Kategori listesini döner
     * GET /api/v1/categories
     */
    public function index()
    {
        $categories = Category::with('children')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->withCount('products')
            ->get();

        return response()->json($categories);
    }

    /**
     * Tek bir kategorinin detayını döner
     * GET /api/v1/categories/{id}
     */
    public function show($id)
    {
        $category = Category::with(['children', 'products'])
            ->where('is_active', true)
            ->findOrFail($id);

        return response()->json($category);
    }
}

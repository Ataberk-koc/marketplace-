<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

/**
 * API Marka Controller
 */
class BrandController extends Controller
{
    /**
     * Marka listesini döner
     * GET /api/v1/brands
     */
    public function index()
    {
        $brands = Brand::where('is_active', true)
            ->withCount('products')
            ->get();

        return response()->json($brands);
    }

    /**
     * Tek bir markanın detayını döner
     * GET /api/v1/brands/{id}
     */
    public function show($id)
    {
        $brand = Brand::with('products')
            ->where('is_active', true)
            ->findOrFail($id);

        return response()->json($brand);
    }
}

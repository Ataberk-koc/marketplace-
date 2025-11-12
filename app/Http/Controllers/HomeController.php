<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;

/**
 * Ana sayfa ve genel sayfa işlemlerini yöneten controller
 */
class HomeController extends Controller
{
    /**
     * Ana sayfayı gösterir
     */
    public function index()
    {
        // Kategoriler
        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->withCount('products')
            ->take(8)
            ->get();

        // Öne çıkan ürünler
        $products = Product::with(['brand', 'category'])
            ->where('is_active', true)
            ->latest()
            ->take(8)
            ->get();

        // İstatistikler
        $stats = [
            'products' => Product::where('is_active', true)->count(),
            'users' => \App\Models\User::where('is_active', true)->count(),
            'orders' => \App\Models\Order::count(),
            'brands' => Brand::where('is_active', true)->count(),
        ];

        return view('welcome', compact('categories', 'products', 'stats'));
    }

    /**
     * Ürün listesi sayfası
     */
    public function products(Request $request)
    {
        $query = Product::with(['brand', 'category'])
            ->where('is_active', true);

        // Kategori filtresi
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Marka filtresi
        if ($request->filled('brand')) {
            $query->where('brand_id', $request->brand);
        }

        // Fiyat filtresi
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Arama
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Sıralama
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            default:
                $query->latest();
        }

        $products = $query->paginate(12);

        $categories = Category::where('is_active', true)->get();
        $brands = Brand::where('is_active', true)->get();

        return view('products.index', compact('products', 'categories', 'brands'));
    }

    /**
     * Ürün detay sayfası
     */
    public function productShow($slug)
    {
        $product = Product::with(['brand', 'category', 'sizes', 'seller'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // İlgili ürünler
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->take(4)
            ->get();

        return view('products.show', compact('product', 'relatedProducts'));
    }
}

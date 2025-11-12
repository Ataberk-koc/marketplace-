<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;

/**
 * Admin ürün yönetimi controller
 */
class ProductController extends Controller
{
    /**
     * Ürün listesini gösterir
     */
    public function index(Request $request)
    {
        $query = Product::with(['brand', 'category', 'seller']);

        // Kategori filtresi
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Marka filtresi
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        // Durum filtresi
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Arama
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->latest()->paginate(25);

        $categories = Category::all();
        $brands = Brand::all();

        return view('admin.products.index', compact('products', 'categories', 'brands'));
    }

    /**
     * Ürün durumunu aktif/pasif yapar
     */
    public function toggleActive(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);

        $status = $product->is_active ? 'aktif' : 'pasif';
        return back()->with('success', "Ürün {$status} yapıldı!");
    }

    /**
     * Ürünü öne çıkan/çıkarmayan yapar
     */
    public function toggleFeatured(Product $product)
    {
        $product->update(['is_featured' => !$product->is_featured]);

        $status = $product->is_featured ? 'öne çıkan' : 'normal';
        return back()->with('success', "Ürün {$status} yapıldı!");
    }

    /**
     * Ürünü siler (soft delete)
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return back()->with('success', 'Ürün silindi!');
    }
}

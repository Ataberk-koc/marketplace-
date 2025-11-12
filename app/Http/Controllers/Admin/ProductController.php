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
     * Yeni ürün oluşturma formu
     */
    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        $brands = Brand::where('is_active', true)->get();

        return view('admin.products.create', compact('categories', 'brands'));
    }

    /**
     * Yeni ürün kaydeder
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'required|string|max:100|unique:products',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'stock_quantity' => 'required|integer|min:0',
            'images' => 'nullable|array',
            'images.*' => 'url',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $product = Product::create([
            'seller_id' => auth()->id(), // Admin oluşturduğu için kendi ID'si
            'name' => $request->name,
            'description' => $request->description,
            'sku' => $request->sku,
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
            'price' => $request->price,
            'discount_price' => $request->discount_price,
            'stock_quantity' => $request->stock_quantity,
            'images' => $request->images ?? [],
            'is_active' => $request->boolean('is_active', true),
            'is_featured' => $request->boolean('is_featured', false),
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Ürün başarıyla oluşturuldu!');
    }

    /**
     * Ürün düzenleme formu
     */
    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)->get();
        $brands = Brand::where('is_active', true)->get();

        return view('admin.products.edit', compact('product', 'categories', 'brands'));
    }

    /**
     * Ürünü günceller
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'required|string|max:100|unique:products,sku,' . $product->id,
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'stock_quantity' => 'required|integer|min:0',
            'images' => 'nullable|array',
            'images.*' => 'url',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'sku' => $request->sku,
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
            'price' => $request->price,
            'discount_price' => $request->discount_price,
            'stock_quantity' => $request->stock_quantity,
            'images' => $request->images ?? [],
            'is_active' => $request->boolean('is_active'),
            'is_featured' => $request->boolean('is_featured'),
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Ürün güncellendi!');
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

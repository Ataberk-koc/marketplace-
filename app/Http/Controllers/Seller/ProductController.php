<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Satıcı ürün yönetimi controller
 */
class ProductController extends Controller
{
    /**
     * Satıcının ürünlerini listeler
     */
    public function index(Request $request)
    {
        $query = Product::with(['brand', 'category'])
            ->where('user_id', auth()->id());

        // Arama
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->latest()->paginate(25);

        return view('seller.products.index', compact('products'));
    }

    /**
     * Yeni ürün oluşturma formu
     */
    public function create()
    {
        $brands = Brand::where('is_active', true)->get();
        $categories = Category::where('is_active', true)->get();
        $sizes = Size::where('is_active', true)->get();

        return view('seller.products.create', compact('brands', 'categories', 'sizes'));
    }

    /**
     * Yeni ürün kaydeder
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'brand_id' => 'required|exists:brands,id',
            'category_id' => 'required|exists:categories,id',
            'sku' => 'required|string|unique:products',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'images' => 'nullable|array',
            'images.*' => 'url',
        ]);

        $product = Product::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'brand_id' => $request->brand_id,
            'category_id' => $request->category_id,
            'sku' => $request->sku,
            'description' => $request->description,
            'price' => $request->price,
            'discount_price' => $request->discount_price,
            'stock_quantity' => $request->stock_quantity,
            'images' => $request->images,
            'is_active' => true,
        ]);

        // Bedenleri ekle
        if ($request->filled('sizes')) {
            foreach ($request->sizes as $sizeId => $sizeData) {
                if (isset($sizeData['selected'])) {
                    $product->sizes()->attach($sizeId, [
                        'stock_quantity' => $sizeData['stock'] ?? 0,
                        'additional_price' => $sizeData['price'] ?? 0,
                    ]);
                }
            }
        }

        return redirect()->route('seller.products.index')
            ->with('success', 'Ürün başarıyla oluşturuldu!');
    }

    /**
     * Ürün düzenleme formu
     */
    public function edit(Product $product)
    {
        // Satıcının kendi ürünü mü kontrol et
        if ($product->user_id !== auth()->id()) {
            abort(403);
        }

        $brands = Brand::where('is_active', true)->get();
        $categories = Category::where('is_active', true)->get();
        $sizes = Size::where('is_active', true)->get();

        return view('seller.products.edit', compact('product', 'brands', 'categories', 'sizes'));
    }

    /**
     * Ürünü günceller
     */
    public function update(Request $request, Product $product)
    {
        // Satıcının kendi ürünü mü kontrol et
        if ($product->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'brand_id' => 'required|exists:brands,id',
            'category_id' => 'required|exists:categories,id',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'images' => 'nullable|array',
            'images.*' => 'url',
        ]);

        $product->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'brand_id' => $request->brand_id,
            'category_id' => $request->category_id,
            'sku' => $request->sku,
            'description' => $request->description,
            'price' => $request->price,
            'discount_price' => $request->discount_price,
            'stock_quantity' => $request->stock_quantity,
            'images' => $request->images,
        ]);

        // Bedenleri güncelle
        $product->sizes()->detach();
        if ($request->filled('sizes')) {
            foreach ($request->sizes as $sizeId => $sizeData) {
                if (isset($sizeData['selected'])) {
                    $product->sizes()->attach($sizeId, [
                        'stock_quantity' => $sizeData['stock'] ?? 0,
                        'additional_price' => $sizeData['price'] ?? 0,
                    ]);
                }
            }
        }

        return redirect()->route('seller.products.index')
            ->with('success', 'Ürün güncellendi!');
    }

    /**
     * Ürünü siler
     */
    public function destroy(Product $product)
    {
        // Satıcının kendi ürünü mü kontrol et
        if ($product->user_id !== auth()->id()) {
            abort(403);
        }

        $product->delete();

        return back()->with('success', 'Ürün silindi!');
    }
}

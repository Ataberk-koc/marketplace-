<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductAttribute;
use App\Models\TrendyolCategoryAttribute;
use App\Services\TrendyolService;
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
            'model_code' => 'required|string|max:100',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            // Varyantlar
            'variants' => 'required|array|min:1',
            'variants.*.color' => 'required|string',
            'variants.*.size' => 'required|string',
            'variants.*.barcode' => 'required|string|max:100',
            'variants.*.sku' => 'required|string|max:100|distinct',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.discount_price' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'required|integer|min:0',
            'variants.*.vat_rate' => 'required|numeric',
            'variants.*.stock_code' => 'nullable|string|max:100',
        ]);

        \DB::beginTransaction();
        try {
            // Ana ürünü oluştur
            $product = Product::create([
                'user_id' => auth()->id(),
                'name' => $request->name,
                'sku' => $request->model_code, // Model kodu ana SKU olarak
                'description' => $request->description,
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'price' => 0, // Varyantlardan min fiyat hesaplanacak
                'discount_price' => null,
                'stock_quantity' => 0, // Varyantlardan toplam hesaplanacak
                'images' => [],
                'is_active' => $request->boolean('is_active', true),
                'is_featured' => $request->boolean('is_featured', false),
            ]);

            $totalStock = 0;
            $minPrice = PHP_INT_MAX;

            // Ürün özelliklerini hazırla
            $productAttributes = [];
            if ($request->has('attributes')) {
                foreach ($request->attributes as $key => $value) {
                    if (!empty($value)) {
                        $productAttributes[$key] = $value;
                    }
                }
            }

            // Ekstra özellikler varsa ekle
            if ($request->has('extra_attributes')) {
                foreach ($request->extra_attributes as $extraAttr) {
                    if (!empty($extraAttr['name']) && !empty($extraAttr['value'])) {
                        $productAttributes[$extraAttr['name']] = $extraAttr['value'];
                    }
                }
            }

            // Varyantları kaydet
            foreach ($request->variants as $variantData) {
                $attributes = array_merge($productAttributes, [
                    'Renk' => $variantData['color'],
                    'Beden' => $variantData['size'],
                ]);

                \App\Models\ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $variantData['sku'],
                    'barcode' => $variantData['barcode'],
                    'attributes' => $attributes,
                    'price' => $variantData['price'],
                    'discount_price' => $variantData['discount_price'] ?? null,
                    'stock_quantity' => $variantData['stock'],
                    'reserved_quantity' => 0,
                    'low_stock_threshold' => 5,
                    'is_active' => true,
                ]);

                $totalStock += $variantData['stock'];
                $minPrice = min($minPrice, $variantData['price']);
            }

            // Ana ürün bilgilerini güncelle
            $product->update([
                'stock_quantity' => $totalStock,
                'price' => $minPrice,
            ]);

            \DB::commit();

            return redirect()->route('admin.products.index')
                ->with('success', 'Ürün ve varyantları başarıyla oluşturuldu!');

        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Ürün kaydedilemedi: ' . $e->getMessage()]);
        }
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

    /**
     * Ürün detaylarını gösterir (özellikleri ile birlikte)
     */
    public function show(Product $product)
    {
        $product->load([
            'brand',
            'category',
            'seller',
            'sizes',
            'productAttributes' => function($query) {
                $query->orderBy('is_required', 'desc')->orderBy('display_order');
            },
            'trendyolMapping'
        ]);

        return view('admin.products.show', compact('product'));
    }

    /**
     * Ürün özelliklerini yönetme sayfası
     */
    public function attributes(Product $product)
    {
        $product->load([
            'brand',
            'category',
            'productAttributes' => function($query) {
                $query->orderBy('display_order');
            }
        ]);

        // Kategorinin Trendyol mapping'i varsa, o kategoriye ait attribute'ları getir
        $trendyolAttributes = [];
        if ($product->category->categoryMapping) {
            $trendyolCategoryId = $product->category->categoryMapping->trendyol_category_id;
            $trendyolAttributes = TrendyolCategoryAttribute::getCategoryAttributes($trendyolCategoryId);
        }

        return view('admin.products.attributes', compact('product', 'trendyolAttributes'));
    }

    /**
     * Ürün özelliklerini kaydet/güncelle
     */
    public function saveAttributes(Request $request, Product $product)
    {
        $validated = $request->validate([
            'attributes' => 'required|array',
            'attributes.*.attribute_name' => 'required|string',
            'attributes.*.attribute_value' => 'required|string',
            'attributes.*.attribute_type' => 'nullable|string',
            'attributes.*.trendyol_attribute_id' => 'nullable|string',
            'attributes.*.trendyol_attribute_name' => 'nullable|string',
            'attributes.*.is_required' => 'boolean',
            'attributes.*.is_variant' => 'boolean',
            'attributes.*.display_order' => 'nullable|integer',
        ]);

        // Mevcut attribute'ları sil
        $product->productAttributes()->delete();

        // Yeni attribute'ları kaydet
        foreach ($validated['attributes'] as $index => $attr) {
            $product->productAttributes()->create([
                'attribute_name' => $attr['attribute_name'],
                'attribute_value' => $attr['attribute_value'],
                'attribute_type' => $attr['attribute_type'] ?? 'text',
                'trendyol_attribute_id' => $attr['trendyol_attribute_id'] ?? null,
                'trendyol_attribute_name' => $attr['trendyol_attribute_name'] ?? null,
                'is_required' => $attr['is_required'] ?? false,
                'is_variant' => $attr['is_variant'] ?? false,
                'display_order' => $attr['display_order'] ?? $index,
            ]);
        }

        return back()->with('success', 'Ürün özellikleri kaydedildi!');
    }

    /**
     * Kategoriye ait Trendyol attribute'larını senkronize et
     */
    public function syncCategoryAttributes(Request $request)
    {
        $validated = $request->validate([
            'trendyol_category_id' => 'required|string',
        ]);

        $trendyolService = app(TrendyolService::class);
        $result = $trendyolService->getCategoryAttributes($validated['trendyol_category_id']);

        if (!$result['success']) {
            return back()->with('error', 'Trendyol category attributes alınamadı: ' . ($result['message'] ?? 'Bilinmeyen hata'));
        }

        $categoryId = $validated['trendyol_category_id'];
        $attributes = $result['data']['categoryAttributes'] ?? [];

        // Mevcut attribute'ları temizle
        TrendyolCategoryAttribute::where('trendyol_category_id', $categoryId)->delete();

        // Yeni attribute'ları kaydet
        foreach ($attributes as $index => $attr) {
            $attribute = $attr['attribute'];
            $allowedValues = [];

            if (isset($attr['attributeValues']) && is_array($attr['attributeValues'])) {
                $allowedValues = array_map(function($val) {
                    return ['id' => $val['id'], 'name' => $val['name']];
                }, $attr['attributeValues']);
            }

            TrendyolCategoryAttribute::create([
                'trendyol_category_id' => $categoryId,
                'attribute_id' => $attribute['id'],
                'attribute_name' => $attribute['name'],
                'attribute_type' => $attr['attributeType'] ?? 'text',
                'is_required' => $attr['required'] ?? false,
                'allows_custom_value' => $attr['allowCustom'] ?? false,
                'is_variant_based' => $attr['varianter'] ?? false,
                'allowed_values' => !empty($allowedValues) ? $allowedValues : null,
                'display_order' => $index,
            ]);
        }

        return back()->with('success', count($attributes) . ' adet özellik senkronize edildi!');
    }
}

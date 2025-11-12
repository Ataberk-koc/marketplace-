<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\TrendyolBrand;
use App\Models\TrendyolCategory;
use App\Services\TrendyolService;
use Illuminate\Http\Request;

class TrendyolController extends Controller
{
    protected $trendyolService;

    public function __construct(TrendyolService $trendyolService)
    {
        $this->trendyolService = $trendyolService;
    }

    /**
     * Trendyol Yönetim Paneli
     */
    public function index()
    {
        $stats = [
            'total_products' => Product::count(),
            'mapped_brands' => Brand::has('trendyolMapping')->count(),
            'total_brands' => Brand::count(),
            'mapped_categories' => Category::has('trendyolMapping')->count(),
            'total_categories' => Category::count(),
            'trendyol_brands' => TrendyolBrand::count(),
            'trendyol_categories' => TrendyolCategory::count(),
        ];

        return view('admin.trendyol.index', compact('stats'));
    }

    /**
     * Trendyol'dan markaları senkronize et
     */
    public function syncBrands()
    {
        $result = $this->trendyolService->getBrands();

        if (!$result['success']) {
            return back()->with('error', 'Markalar alınamadı: ' . ($result['message'] ?? 'Bilinmeyen hata'));
        }

        $brands = $result['data']['brands'] ?? [];
        $syncedCount = 0;

        foreach ($brands as $brandData) {
            TrendyolBrand::updateOrCreate(
                ['trendyol_brand_id' => $brandData['id']],
                ['name' => $brandData['name']]
            );
            $syncedCount++;
        }

        return back()->with('success', "{$syncedCount} marka senkronize edildi!");
    }

    /**
     * Trendyol'dan kategorileri senkronize et
     */
    public function syncCategories()
    {
        $result = $this->trendyolService->getCategories();

        if (!$result['success']) {
            return back()->with('error', 'Kategoriler alınamadı: ' . ($result['message'] ?? 'Bilinmeyen hata'));
        }

        $categories = $result['data']['categories'] ?? [];
        $syncedCount = $this->syncCategoriesRecursive($categories);

        return back()->with('success', "{$syncedCount} kategori senkronize edildi!");
    }

    /**
     * Kategorileri recursive olarak senkronize et
     */
    private function syncCategoriesRecursive($categories, $parentId = null)
    {
        $count = 0;

        foreach ($categories as $categoryData) {
            TrendyolCategory::updateOrCreate(
                ['trendyol_category_id' => $categoryData['id']],
                [
                    'name' => $categoryData['name'],
                    'parent_id' => $parentId,
                    'is_leaf' => !isset($categoryData['subCategories']) || empty($categoryData['subCategories'])
                ]
            );
            $count++;

            // Alt kategoriler varsa onları da ekle
            if (isset($categoryData['subCategories']) && !empty($categoryData['subCategories'])) {
                $count += $this->syncCategoriesRecursive($categoryData['subCategories'], $categoryData['id']);
            }
        }

        return $count;
    }

    /**
     * Toplu ürün gönderimi
     */
    public function bulkSendProducts(Request $request)
    {
        $productIds = $request->input('product_ids', []);

        if (empty($productIds)) {
            return back()->with('error', 'Lütfen en az bir ürün seçin!');
        }

        $products = Product::with(['brand.trendyolMapping', 'category.trendyolMapping', 'sizes.trendyolMapping'])
            ->whereIn('id', $productIds)
            ->get();

        $productData = [];
        $errors = [];

        foreach ($products as $product) {
            // Validasyon kontrolleri
            if (!$product->brand || !$product->brand->trendyolMapping) {
                $errors[] = "{$product->name}: Marka eşleştirilmemiş";
                continue;
            }

            if (!$product->category || !$product->category->trendyolMapping) {
                $errors[] = "{$product->name}: Kategori eşleştirilmemiş";
                continue;
            }

            $unmappedSizes = [];
            foreach ($product->sizes as $size) {
                if (!$size->trendyolMapping) {
                    $unmappedSizes[] = $size->name;
                }
            }

            if (!empty($unmappedSizes)) {
                $errors[] = "{$product->name}: Bedenler eşleştirilmemiş (" . implode(', ', $unmappedSizes) . ")";
                continue;
            }

            // Ürün datasını hazırla
            $productData[] = $this->trendyolService->formatProductForTrendyol($product);
        }

        if (!empty($errors)) {
            return back()->with('error', 'Bazı ürünler gönderilemedi:<br>' . implode('<br>', $errors));
        }

        if (empty($productData)) {
            return back()->with('error', 'Gönderilecek ürün bulunamadı!');
        }

        // Trendyol'a gönder
        $result = $this->trendyolService->createProducts($productData);

        if ($result['success']) {
            $batchRequestId = $result['data']['batchRequestId'] ?? null;
            return back()->with('success', 'Ürünler Trendyol\'a gönderildi! Batch ID: ' . $batchRequestId);
        }

        return back()->with('error', 'Ürünler gönderilemedi: ' . ($result['message'] ?? 'Bilinmeyen hata'));
    }

    /**
     * Toplu stok/fiyat güncelleme
     */
    public function bulkUpdateInventory(Request $request)
    {
        $request->validate([
            'updates' => 'required|array',
            'updates.*.barcode' => 'required|string',
            'updates.*.quantity' => 'nullable|integer|min:0|max:20000',
            'updates.*.salePrice' => 'nullable|numeric|min:0',
            'updates.*.listPrice' => 'nullable|numeric|min:0',
        ]);

        $result = $this->trendyolService->updatePriceAndInventory($request->updates);

        if ($result['success']) {
            $batchRequestId = $result['data']['batchRequestId'] ?? null;
            return back()->with('success', 'Stok/Fiyat güncellendi! Batch ID: ' . $batchRequestId);
        }

        return back()->with('error', 'Güncelleme başarısız: ' . ($result['message'] ?? 'Bilinmeyen hata'));
    }

    /**
     * Toplu ürün silme
     */
    public function bulkDeleteProducts(Request $request)
    {
        $barcodes = $request->input('barcodes', []);

        if (empty($barcodes)) {
            return back()->with('error', 'Lütfen en az bir barkod girin!');
        }

        $result = $this->trendyolService->deleteProducts($barcodes);

        if ($result['success']) {
            $batchRequestId = $result['data']['batchRequestId'] ?? null;
            return back()->with('success', 'Ürünler silindi! Batch ID: ' . $batchRequestId);
        }

        return back()->with('error', 'Silme başarısız: ' . ($result['message'] ?? 'Bilinmeyen hata'));
    }

    /**
     * Batch işlem durumu kontrolü
     */
    public function checkBatchStatus($batchRequestId)
    {
        $result = $this->trendyolService->getBatchRequestResult($batchRequestId);

        if ($result['success']) {
            return response()->json($result['data']);
        }

        return response()->json(['error' => $result['message']], 400);
    }

    /**
     * Trendyol ürünlerini filtrele/listele
     */
    public function filterProducts(Request $request)
    {
        $filters = $request->only(['approved', 'onSale', 'barcode', 'startDate', 'endDate', 'page', 'size']);

        $result = $this->trendyolService->filterProducts($filters);

        if ($result['success']) {
            return view('admin.trendyol.products', [
                'products' => $result['data']['content'] ?? [],
                'totalPages' => $result['data']['totalPages'] ?? 1,
                'currentPage' => $result['data']['page'] ?? 0,
            ]);
        }

        return back()->with('error', 'Ürünler listelenemedi: ' . ($result['message'] ?? 'Bilinmeyen hata'));
    }

    /**
     * Marka Eşleştirme Sayfası
     */
    public function brandMapping()
    {
        $localBrands = Brand::with('trendyolMapping')->orderBy('name')->get();
        $trendyolBrands = TrendyolBrand::orderBy('name')->get();
        
        return view('admin.trendyol.brand-mapping', compact('localBrands', 'trendyolBrands'));
    }

    /**
     * Marka Eşleştirme Kaydet
     */
    public function saveBrandMapping(Request $request)
    {
        $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'trendyol_brand_id' => 'required|exists:trendyol_brands,id'
        ]);

        $localBrand = Brand::findOrFail($request->brand_id);
        $trendyolBrand = TrendyolBrand::findOrFail($request->trendyol_brand_id);

        // BrandMapping tablosuna kaydet
        // NOT: trendyol_brand_id = Laravel ID (foreign key), trendyol_brand_name = bilgi amaçlı
        \App\Models\BrandMapping::updateOrCreate(
            ['brand_id' => $localBrand->id],
            [
                'trendyol_brand_id' => $trendyolBrand->id, // Laravel ID kullan (foreign key)
                'trendyol_brand_name' => $trendyolBrand->name
            ]
        );

        return back()->with('success', "\"{$localBrand->name}\" markası \"{$trendyolBrand->name}\" ile eşleştirildi!");
    }

    /**
     * Marka Eşleştirme Sil
     */
    public function deleteBrandMapping($mappingId)
    {
        $mapping = \App\Models\BrandMapping::findOrFail($mappingId);
        $brandName = $mapping->brand->name ?? 'Bilinmeyen';
        $mapping->delete();

        return back()->with('success', "\"{$brandName}\" eşleştirmesi silindi!");
    }

    /**
     * Kategori Eşleştirme Sayfası
     */
    public function categoryMapping()
    {
        $localCategories = Category::with('trendyolMapping')->orderBy('name')->get();
        $trendyolCategories = TrendyolCategory::orderBy('name')->get();
        
        return view('admin.trendyol.category-mapping', compact('localCategories', 'trendyolCategories'));
    }

    /**
     * Kategori Eşleştirme Kaydet
     */
    public function saveCategoryMapping(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'trendyol_category_id' => 'required|exists:trendyol_categories,id'
        ]);

        $localCategory = Category::findOrFail($request->category_id);
        $trendyolCategory = TrendyolCategory::findOrFail($request->trendyol_category_id);

        // CategoryMapping tablosuna kaydet
        // NOT: trendyol_category_id = Laravel ID (foreign key), trendyol_category_name = bilgi amaçlı
        \App\Models\CategoryMapping::updateOrCreate(
            ['category_id' => $localCategory->id],
            [
                'trendyol_category_id' => $trendyolCategory->id, // Laravel ID kullan (foreign key)
                'trendyol_category_name' => $trendyolCategory->name
            ]
        );

        return back()->with('success', "\"{$localCategory->name}\" kategorisi \"{$trendyolCategory->name}\" ile eşleştirildi!");
    }

    /**
     * Kategori Eşleştirme Sil
     */
    public function deleteCategoryMapping($mappingId)
    {
        $mapping = \App\Models\CategoryMapping::findOrFail($mappingId);
        $categoryName = $mapping->category->name ?? 'Bilinmeyen';
        $mapping->delete();

        return back()->with('success', "\"{$categoryName}\" eşleştirmesi silindi!");
    }

    /**
     * Ürün Eşleştirme Sayfası (TEK TABLO SİSTEMİ)
     */
    public function productMapping()
    {
        $products = Product::with(['brand', 'category', 'trendyolMapping'])->get();
        $trendyolCategories = TrendyolCategory::orderBy('name')->get();
        $trendyolBrands = TrendyolBrand::orderBy('name')->get();
        
        $existingMappings = \App\Models\ProductTrendyolMapping::with([
            'product.brand',
            'product.category',
            'trendyolCategory',
            'trendyolBrand'
        ])->get();

        $stats = [
            'total_products' => Product::count(),
            'mapped_products' => \App\Models\ProductTrendyolMapping::count(),
            'unmapped_products' => Product::count() - \App\Models\ProductTrendyolMapping::count(),
        ];

        return view('admin.trendyol.product-mapping', compact(
            'products',
            'trendyolCategories',
            'trendyolBrands',
            'existingMappings',
            'stats'
        ));
    }

    /**
     * Kategori Özelliklerini Getir (AJAX)
     */
    public function getCategoryAttributes($categoryId)
    {
        $result = $this->trendyolService->getCategoryAttributes($categoryId);
        
        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => $result['message']], 400);
        }

        return response()->json([
            'success' => true,
            'attributes' => $result['data']['categoryAttributes'] ?? []
        ]);
    }

    /**
     * Ürün Eşleştirme Kaydet
     */
    public function saveProductMapping(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'trendyol_category_id' => 'required|exists:trendyol_categories,id',
            'trendyol_brand_id' => 'required|exists:trendyol_brands,id',
            'attribute_mappings' => 'nullable|array',
        ]);

        $product = Product::findOrFail($request->product_id);
        $trendyolCategory = TrendyolCategory::findOrFail($request->trendyol_category_id);
        $trendyolBrand = TrendyolBrand::findOrFail($request->trendyol_brand_id);

        \App\Models\ProductTrendyolMapping::updateOrCreate(
            ['product_id' => $product->id],
            [
                'trendyol_category_id' => $trendyolCategory->id,
                'trendyol_category_name' => $trendyolCategory->name,
                'trendyol_brand_id' => $trendyolBrand->id,
                'trendyol_brand_name' => $trendyolBrand->name,
                'attribute_mappings' => $request->attribute_mappings ?? [],
            ]
        );

        return back()->with('success', "\"{$product->name}\" ürünü Trendyol ile eşleştirildi!");
    }

    /**
     * Ürün Eşleştirme Sil
     */
    public function deleteProductMapping($mappingId)
    {
        $mapping = \App\Models\ProductTrendyolMapping::findOrFail($mappingId);
        $productName = $mapping->product->name ?? 'Bilinmeyen';
        $mapping->delete();

        return back()->with('success', "\"{$productName}\" eşleştirmesi silindi!");
    }
}

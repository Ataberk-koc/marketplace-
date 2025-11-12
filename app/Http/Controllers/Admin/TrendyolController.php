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
            'mapped_products' => \App\Models\ProductTrendyolMapping::where('status', 'pending')->count(),
            'sent_products' => \App\Models\ProductTrendyolMapping::whereIn('status', ['sent', 'approved'])->count(),
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
     * Toplu ürün gönderimi - YENİ: ProductTrendyolMapping kullanır
     */
    public function bulkSendProducts(Request $request)
    {
        // Sadece henüz gönderilmemiş ürünleri al
        $mappings = \App\Models\ProductTrendyolMapping::with([
            'product.sizes',
            'trendyolCategory',
            'trendyolBrand'
        ])->where('is_active', true)
          ->where('status', 'pending')
          ->get();

        if ($mappings->isEmpty()) {
            return back()->with('error', 'Gönderilecek eşleştirilmiş ürün bulunamadı!');
        }

        // Ürünleri Trendyol formatına dönüştür
        $items = [];
        $mappingIds = [];
        foreach ($mappings as $mapping) {
            $formattedProduct = $this->formatProductForTrendyol($mapping);
            if ($formattedProduct) {
                $items[] = $formattedProduct;
                $mappingIds[] = $mapping->id;
            }
        }

        if (empty($items)) {
            return back()->with('error', 'Formatlanabilir ürün bulunamadı!');
        }

        // Trendyol'a gönder
        $result = $this->trendyolService->createProducts($items);

        if (!$result['success']) {
            return back()->with('error', 'Ürün gönderimi başarısız: ' . ($result['message'] ?? 'Bilinmeyen hata'));
        }

        // Başarılı gönderimde mapping'leri güncelle
        $batchRequestId = $result['batchRequestId'] ?? null;
        \App\Models\ProductTrendyolMapping::whereIn('id', $mappingIds)->update([
            'status' => 'sent',
            'batch_request_id' => $batchRequestId,
            'sent_at' => now(),
        ]);

        return back()->with('success', count($items) . ' ürün Trendyol\'a gönderildi! Batch ID: ' . $batchRequestId);
    }

    /**
     * Ürünü Trendyol formatına dönüştür
     */
    protected function formatProductForTrendyol($mapping)
    {
        $product = $mapping->product;

        // Temel alan kontrolü
        if (!$product->sku || !$product->name) {
            return null;
        }

        $item = [
            'barcode' => $product->sku,
            'title' => $product->name,
            'productMainId' => $product->sku, // Ana ürün ID (grup için)
            'brandId' => $mapping->trendyolBrand->trendyol_brand_id,
            'categoryId' => $mapping->trendyolCategory->trendyol_category_id,
            'quantity' => $product->stock_quantity ?? 0,
            'stockCode' => $product->sku,
            'dimensionalWeight' => 1, // Varsayılan
            'description' => $product->description ?? $product->name,
            'currencyType' => 'TRY',
            // Custom fiyat varsa onu kullan, yoksa ürünün kendi fiyatı
            'listPrice' => (float) ($mapping->custom_price ?? $product->price),
            'salePrice' => (float) ($mapping->custom_sale_price ?? $product->discount_price ?? $product->price),
            'cargoCompanyId' => 10, // Varsayılan kargo (Aras)
            'deliveryDuration' => 3, // 3 gün
            'images' => [],
        ];

        // Görseller
        if ($product->images && is_array($product->images)) {
            foreach ($product->images as $index => $image) {
                $item['images'][] = [
                    'url' => $image,
                    'order' => $index + 1
                ];
            }
        }

        // Özellikler (attributes from mapping)
        if ($mapping->attribute_mappings && is_array($mapping->attribute_mappings)) {
            $item['attributes'] = [];
            
            // Kategori attributelerini çek (attribute name -> attribute ID mapping için)
            $categoryAttributes = $this->trendyolService->getCategoryAttributes(
                $mapping->trendyolCategory->trendyol_category_id
            );
            
            // Attribute name -> ID mapping oluştur
            $attributeMap = [];
            if (!empty($categoryAttributes['attributes'])) {
                foreach ($categoryAttributes['attributes'] as $attr) {
                    $attributeMap[$attr['attribute']['name']] = $attr['attribute']['id'];
                }
            }
            
            // Her attribute için doğru attributeId ve attributeValueId kullan
            foreach ($mapping->attribute_mappings as $attrName => $attrValueId) {
                // Attribute ID'yi map'ten al, yoksa skip et
                if (!isset($attributeMap[$attrName])) {
                    \Log::warning("Attribute '{$attrName}' not found in category attributes", [
                        'category_id' => $mapping->trendyolCategory->trendyol_category_id,
                        'product_id' => $product->id
                    ]);
                    continue;
                }
                
                $item['attributes'][] = [
                    'attributeId' => (int) $attributeMap[$attrName],  // Doğru attribute ID
                    'attributeValueId' => (int) $attrValueId          // Selected value ID
                ];
            }
        }

        return $item;
    }

    /**
     * Tek Ürün Gönderimi
     */
    public function sendSingleProduct($mappingId)
    {
        $mapping = \App\Models\ProductTrendyolMapping::with([
            'product.sizes',
            'trendyolCategory',
            'trendyolBrand'
        ])->findOrFail($mappingId);

        // Zaten gönderilmiş mi kontrol et
        if ($mapping->status === 'sent') {
            return back()->with('warning', 'Bu ürün zaten Trendyol\'a gönderilmiş!');
        }

        // Ürünü Trendyol formatına dönüştür
        $formattedProduct = $this->formatProductForTrendyol($mapping);
        
        if (!$formattedProduct) {
            return back()->with('error', 'Ürün formatlanamadı. Lütfen ürün bilgilerini kontrol edin (SKU, isim vb.)');
        }

        // Trendyol'a gönder (tek ürün için array olarak gönder)
        $result = $this->trendyolService->createProducts([$formattedProduct]);

        if (!$result['success']) {
            return back()->with('error', 'Ürün gönderimi başarısız: ' . ($result['message'] ?? 'Bilinmeyen hata'));
        }

        // Başarılı gönderimde mapping'i güncelle
        $mapping->update([
            'status' => 'sent',
            'batch_request_id' => $result['batchRequestId'] ?? null,
            'sent_at' => now(),
        ]);

        return back()->with('success', '✓ "' . $mapping->product->name . '" Trendyol\'a gönderildi! Batch ID: ' . ($result['batchRequestId'] ?? 'N/A'));
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
        
        // Sadece henüz gönderilmemiş eşleştirmeleri göster
        $existingMappings = \App\Models\ProductTrendyolMapping::with([
            'product.brand',
            'product.category',
            'trendyolCategory',
            'trendyolBrand'
        ])->where('status', 'pending')->get();

        // Gönderilmiş ürünleri ayrı listele
        $sentProducts = \App\Models\ProductTrendyolMapping::with([
            'product.brand',
            'product.category',
            'trendyolCategory',
            'trendyolBrand'
        ])->whereIn('status', ['sent', 'approved', 'rejected'])
          ->orderBy('sent_at', 'desc')
          ->get();

        $stats = [
            'total_products' => Product::count(),
            'mapped_products' => \App\Models\ProductTrendyolMapping::where('status', 'pending')->count(),
            'unmapped_products' => Product::count() - \App\Models\ProductTrendyolMapping::count(),
            'sent_products' => \App\Models\ProductTrendyolMapping::whereIn('status', ['sent', 'approved', 'rejected'])->count(),
        ];

        return view('admin.trendyol.product-mapping', compact(
            'products',
            'trendyolCategories',
            'trendyolBrands',
            'existingMappings',
            'sentProducts',
            'stats'
        ));
    }

    /**
     * Kategori Özelliklerini Getir (AJAX)
     */
    public function getCategoryAttributes($categoryId)
    {
        // Laravel ID'den Trendyol API ID'sini al
        $trendyolCategory = TrendyolCategory::findOrFail($categoryId);
        $trendyolCategoryApiId = $trendyolCategory->trendyol_category_id;
        
        // Trendyol API ID ile attributes çek
        $result = $this->trendyolService->getCategoryAttributes($trendyolCategoryApiId);
        
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

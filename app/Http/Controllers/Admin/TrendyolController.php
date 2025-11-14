<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Size;
use App\Models\BrandMapping;
use App\Models\CategoryMapping;
use App\Models\SizeMapping;
use App\Models\TrendyolSize;
use App\Models\ProductTrendyolMapping;
use App\Services\TrendyolService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'mapped_products' => ProductTrendyolMapping::where('status', 'pending')->count(),
            'sent_products' => ProductTrendyolMapping::whereIn('status', ['sent', 'approved'])->count(),
            'mapped_brands' => BrandMapping::where('is_active', true)->count(),
            'total_brands' => Brand::count(),
            'mapped_categories' => CategoryMapping::where('is_active', true)->count(),
            'total_categories' => Category::count(),
            'trendyol_brands' => count(session('trendyol_brands', [])),
            'trendyol_categories' => count(session('trendyol_categories', [])),
        ];

        return view('admin.trendyol.index', compact('stats'));
    }

    /**
     * Trendyol'dan markaları senkronize et (Session'a kaydet)
     */
    public function syncBrands()
    {
        $result = $this->trendyolService->getBrands();

        if (!$result['success']) {
            return back()->with('error', 'Markalar alınamadı: ' . ($result['message'] ?? 'Bilinmeyen hata'));
        }

        $brands = $result['data']['brands'] ?? [];
        session(['trendyol_brands' => $brands]);

        $syncedCount = count($brands);
        return back()->with('success', "{$syncedCount} marka yüklendi!");
    }

    /**
     * Trendyol'dan kategorileri senkronize et (Session'a kaydet)
     */
    public function syncCategories()
    {
        $result = $this->trendyolService->getCategories();

        if (!$result['success']) {
            return back()->with('error', 'Kategoriler alınamadı: ' . ($result['message'] ?? 'Bilinmeyen hata'));
        }

        $categories = $result['data']['categories'] ?? [];
        session(['trendyol_categories' => $categories]);

        $syncedCount = count($categories);
        return back()->with('success', "{$syncedCount} kategori yüklendi!");
    }

    /**
     * Toplu ürün gönderme
     */
    public function bulkSend(Request $request)
    {
        $mappingIds = $request->input('mappings', []);

        if (empty($mappingIds)) {
            return back()->with('error', 'Lütfen en az bir ürün seçin!');
        }

        $mappings = ProductTrendyolMapping::whereIn('id', $mappingIds)
            ->where('status', 'pending')
            ->with('product')
            ->get();

        $successCount = 0;
        $errorCount = 0;

        foreach ($mappings as $mapping) {
            $productData = $this->formatProductForTrendyol($mapping);
            $result = $this->trendyolService->createProduct($productData);

            if ($result['success']) {
                $mapping->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'sent_by' => auth()->id(),
                    'trendyol_response' => $result['data'] ?? null
                ]);
                $successCount++;
            } else {
                $mapping->update([
                    'status' => 'error',
                    'error_message' => $result['message'] ?? 'Bilinmeyen hata'
                ]);
                $errorCount++;
            }
        }

        return back()->with('success', "{$successCount} ürün gönderildi, {$errorCount} hata oluştu!");
    }

    /**
     * Tek ürün gönderme
     */
    /**
     * Tek bir ürünü Trendyol'a gönder (YENİ MAPPING SİSTEMİ KULLANARAK)
     */
    public function sendSingleProduct(ProductTrendyolMapping $mapping)
    {
        if ($mapping->status !== 'pending') {
            return back()->with('error', 'Bu ürün zaten gönderilmiş veya işlem hatası var!');
        }

        try {
            // İlişkileri eager load et (variants ve option_values için)
            $product = $mapping->product;
            $product->load([
                'variants',
                'brand',
                'category'
            ]);

            // ⭐ YENİ: Mapping-aware payload hazırla
            $payloadResult = $this->trendyolService->prepareProductPayloadWithMappings($product);

            // Mapping hataları kontrolü
            if (!$payloadResult['success']) {
                $errorMessage = 'Mapping hataları: ' . implode(', ', $payloadResult['errors']);
                
                $mapping->update([
                    'status' => 'error',
                    'error_message' => $errorMessage
                ]);

                return back()->with('error', $errorMessage);
            }

            // Trendyol'a gönder
            $result = $this->trendyolService->createProducts($payloadResult['items']);

            if ($result['success']) {
                $mapping->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'sent_by' => auth()->id(),
                    'trendyol_response' => $result['data'] ?? null
                ]);
                
                return back()->with('success', 'Ürün başarıyla Trendyol\'a gönderildi! (Toplam variant: ' . count($payloadResult['items']) . ')');
            }

            $mapping->update([
                'status' => 'error',
                'error_message' => $result['message'] ?? 'Bilinmeyen hata'
            ]);

            return back()->with('error', 'Ürün gönderilemedi: ' . ($result['message'] ?? 'Bilinmeyen hata'));
            
        } catch (\Exception $e) {
            $mapping->update([
                'status' => 'error',
                'error_message' => $e->getMessage()
            ]);
            
            \Log::error('TrendyolController: sendSingleProduct exception', [
                'mapping_id' => $mapping->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Gönderim hatası: ' . $e->getMessage());
        }
    }

    /**
     * Ürünü Trendyol formatına çevir
     * ⭐ DEPRECATED: prepareProductPayloadWithMappings() kullanın
     * ⭐ Bu method eski size mapping sistemini kullanır
     * ⭐ Yeni attribute mapping sistemi için TrendyolService::prepareProductPayloadWithMappings() kullanılmalı
     */
    private function formatProductForTrendyol(ProductTrendyolMapping $mapping)
    {
        $product = $mapping->product;
        
        // ⭐ FAZ 3.1: Marka ID'sini mapping'den al
        $brandMapping = $product->brand->brandMapping;
        if (!$brandMapping || !$brandMapping->trendyol_brand_id) {
            throw new \Exception("Ürünün markası ({$product->brand->name}) Trendyol ile eşleştirilmemiş!");
        }
        $trendyolBrandId = $brandMapping->trendyol_brand_id;
        
        // ⭐ FAZ 3.2: Kategori ID'sini mapping'den al
        $categoryMapping = $product->category->categoryMapping;
        if (!$categoryMapping || !$categoryMapping->trendyol_category_id) {
            throw new \Exception("Ürünün kategorisi ({$product->category->name}) Trendyol ile eşleştirilmemiş!");
        }
        $trendyolCategoryId = $categoryMapping->trendyol_category_id;
        
        // ⭐ FAZ 3.3: Özellik (Beden/Renk) ID'lerini mapping'den al
        $attributes = [];
        foreach ($product->sizes as $size) {
            $sizeMapping = $size->sizeMapping;
            if (!$sizeMapping || !$sizeMapping->trendyolSize) {
                \Log::warning("Size mapping eksik", ['size_id' => $size->id, 'size_name' => $size->name]);
                continue; // Zorunlu değilse atla
            }
            
            $trendyolSize = $sizeMapping->trendyolSize;
            $attributes[] = [
                'attributeId' => (int) $trendyolSize->trendyol_attribute_id,
                'attributeValueId' => (int) $trendyolSize->trendyol_attribute_value_id
            ];
        }
        
        // Fiyatları belirle (önce custom fiyatlar, sonra ürün fiyatı)
        $listPrice = $mapping->custom_price ?? $product->price;
        $salePrice = $mapping->custom_sale_price ?? $product->discount_price ?? $product->price;

        // ⭐ TRENDYOL PAYLOAD - SADECE TRENDYOL ID'LERİ
        return [
            'barcode' => $product->sku,
            'title' => $product->name,
            'productMainId' => $product->id, // İç referans için
            'brandId' => (int) $trendyolBrandId, // ⭐ Mapping'den
            'categoryId' => (int) $trendyolCategoryId, // ⭐ Mapping'den
            'quantity' => $product->stock_quantity,
            'stockCode' => $product->sku,
            'dimensionalWeight' => 1,
            'description' => $product->description ?? $product->name,
            'currencyType' => 'TRY',
            'listPrice' => (float) $listPrice,
            'salePrice' => (float) $salePrice,
            'cargoCompanyId' => 10,
            'images' => $this->formatImages($product->images),
            'attributes' => $attributes, // ⭐ Mapping'den
        ];
    }

    /**
     * Görselleri formatla
     */
    private function formatImages($images)
    {
        if (empty($images)) {
            return [];
        }

        $formatted = [];
        foreach ($images as $index => $image) {
            $formatted[] = [
                'url' => is_array($image) ? ($image['url'] ?? $image) : $image,
                'order' => $index + 1
            ];
        }

        return $formatted;
    }

    /**
     * Özellikleri formatla
     */
    private function formatAttributes($attributes)
    {
        if (empty($attributes)) {
            return [];
        }

        $formatted = [];
        foreach ($attributes as $key => $value) {
            $formatted[] = [
                'attributeId' => $key,
                'attributeValueId' => $value
            ];
        }

        return $formatted;
    }

    /**
     * Kategori özelliklerini getir
     */
    public function getCategoryAttributes($categoryId)
    {
        $result = $this->trendyolService->getCategoryAttributes($categoryId);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori özellikleri alınamadı'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'categoryAttributes' => $result['data']['categoryAttributes'] ?? []
        ]);
    }

    /**
     * Ürün eşleştirme sayfası
     * Sıralama: 1) Marka Seç → 2) Kategori Seç → 3) Ürün Seç
     */
    public function productMapping()
    {
        // Yerel markalar (sadece aktif olanlar)
        $localBrands = Brand::where('is_active', true)
            ->withCount('products')
            ->orderBy('name')
            ->get();
        
        // Trendyol markaları (session'dan)
        $trendyolBrands = session('trendyol_brands', []);
        
        // Eğer session boşsa API'den çek
        if (empty($trendyolBrands)) {
            $result = $this->trendyolService->getBrands();
            if ($result['success']) {
                $trendyolBrands = $result['data']['brands'] ?? [];
                session(['trendyol_brands' => $trendyolBrands]);
            }
        }
        
        // Trendyol kategorileri (session'dan)
        $trendyolCategories = session('trendyol_categories', []);
        
        // Eğer session boşsa API'den çek
        if (empty($trendyolCategories)) {
            $result = $this->trendyolService->getCategories();
            if ($result['success']) {
                $trendyolCategories = $result['data']['categories'] ?? [];
                session(['trendyol_categories' => $trendyolCategories]);
            }
        }
        
        // Bekleyen eşleştirmeler
        $mappings = ProductTrendyolMapping::with('product.brand', 'product.category')
            ->latest()
            ->get();

        $stats = [
            'total_products' => Product::count(),
            'mapped_products' => ProductTrendyolMapping::whereIn('status', ['active', 'sent'])->count(),
            'pending_products' => ProductTrendyolMapping::where('status', 'pending')->count(),
            'unmapped_products' => Product::count() - ProductTrendyolMapping::count(),
        ];

        return view('admin.trendyol.product-mapping-alpine', compact(
            'localBrands',
            'trendyolCategories',
            'trendyolBrands',
            'mappings',
            'stats'
        ));
    }
    
    /**
     * API: Seçilen markaya ait yerel kategorileri getir
     */
    public function getCategoriesByBrand($brandId)
    {
        \Log::info('getCategoriesByBrand called', ['brandId' => $brandId]);
        
        $categories = Category::where('is_active', true)
            ->whereHas('products', function($query) use ($brandId) {
                $query->where('brand_id', $brandId);
            })
            ->withCount('products')
            ->orderBy('name')
            ->get();
            
        \Log::info('Categories found', ['count' => $categories->count()]);
        
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
    
    /**
     * API: Seçilen marka ve kategoriye ait ürünleri getir
     */
    public function getProductsByBrandAndCategory(Request $request)
    {
        $brandId = $request->input('brand_id');
        $categoryId = $request->input('category_id');
        
        $products = Product::with(['brand', 'category', 'sizes'])
            ->where('brand_id', $brandId)
            ->where('category_id', $categoryId)
            ->where('is_active', true)
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Ürün eşleştirmesini kaydet
     */
    public function saveProductMapping(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'trendyol_category_id' => 'required|string',
            'trendyol_category_name' => 'nullable|string',
            'trendyol_brand_id' => 'required|string',
            'trendyol_brand_name' => 'nullable|string',
            'attribute_mappings' => 'nullable|array',
            'custom_price' => 'nullable|numeric|min:0',
            'custom_sale_price' => 'nullable|numeric|min:0',
        ]);

        ProductTrendyolMapping::updateOrCreate(
            ['product_id' => $request->product_id],
            [
                'trendyol_category_id' => $request->trendyol_category_id,
                'trendyol_category_name' => $request->trendyol_category_name,
                'trendyol_brand_id' => $request->trendyol_brand_id,
                'trendyol_brand_name' => $request->trendyol_brand_name,
                'attribute_mappings' => $request->attribute_mappings,
                'custom_price' => $request->custom_price,
                'custom_sale_price' => $request->custom_sale_price,
                'status' => 'pending',
                'is_active' => true,
            ]
        );

        return redirect()->route('admin.trendyol.product-mapping')
            ->with('success', 'Ürün eşleştirmesi kaydedildi!');
    }

    /**
     * Ürün eşleştirmesini sil
     */
    public function deleteMapping(ProductTrendyolMapping $mapping)
    {
        $mapping->delete();
        return back()->with('success', 'Eşleştirme silindi!');
    }

    // ============================================
    // FAZ 1: SIZE (BEDEN) MAPPING CRUD
    // ============================================

    /**
     * Beden eşleştirme listesi
     */
    public function sizeMappingIndex()
    {
        $mappings = SizeMapping::with(['size', 'trendyolSize'])
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total_sizes' => Size::count(),
            'mapped_sizes' => SizeMapping::where('is_active', true)->count(),
            'unmapped_sizes' => Size::count() - SizeMapping::count(),
        ];

        return view('admin.trendyol.size-mapping', compact('mappings', 'stats'));
    }

    /**
     * Beden eşleştirme oluşturma formu
     */
    public function sizeMappingCreate()
    {
        // Henüz eşleştirilmemiş bedenler
        $unmappedSizes = Size::whereDoesntHave('sizeMapping')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Tüm Trendyol özellikleri (TrendyolSize'dan)
        $trendyolAttributes = TrendyolSize::select('attribute_name', 'trendyol_category_id')
            ->distinct()
            ->orderBy('attribute_name')
            ->get()
            ->groupBy('attribute_name');

        return view('admin.trendyol.size-mapping-create', compact('unmappedSizes', 'trendyolAttributes'));
    }

    /**
     * Kategoriye göre Trendyol özellik değerlerini getir (AJAX)
     */
    public function getTrendyolAttributeValues(Request $request)
    {
        $attributeName = $request->input('attribute_name');
        $categoryId = $request->input('category_id'); // Opsiyonel filtre

        $query = TrendyolSize::where('attribute_name', $attributeName);

        if ($categoryId) {
            $query->where('trendyol_category_id', $categoryId);
        }

        $values = $query->orderBy('value_name')->get();

        return response()->json([
            'success' => true,
            'data' => $values
        ]);
    }

    /**
     * Beden eşleştirmesini kaydet
     */
    public function saveSizeMapping(Request $request)
    {
        $request->validate([
            'size_id' => 'required|exists:sizes,id',
            'trendyol_size_id' => 'required|exists:trendyol_sizes,id',
        ]);

        // Aynı size_id için mevcut mapping varsa güncelle
        SizeMapping::updateOrCreate(
            ['size_id' => $request->size_id],
            [
                'trendyol_size_id' => $request->trendyol_size_id,
                'is_active' => true
            ]
        );

        return redirect()->route('admin.trendyol.size-mapping')
            ->with('success', 'Beden eşleştirmesi kaydedildi!');
    }

    /**
     * Beden eşleştirmesini sil
     */
    public function deleteSizeMapping(SizeMapping $mapping)
    {
        $mapping->delete();
        
        return redirect()->route('admin.trendyol.size-mapping')
            ->with('success', 'Beden eşleştirmesi silindi!');
    }

    /**
     * Search Trendyol brands (AJAX endpoint for autocomplete)
     */
    public function searchTrendyolBrands(Request $request)
    {
        $query = $request->query('search', '');

        // Minimum 2 characters required
        if (strlen($query) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'En az 2 karakter girin',
                'data' => []
            ]);
        }

        try {
            // Search in TrendyolBrand table
            $brands = \App\Models\TrendyolBrand::where('name', 'LIKE', '%' . $query . '%')
                ->orderBy('name')
                ->limit(50) // Limit to 50 results for performance
                ->get(['id', 'trendyol_brand_id', 'name']);

            return response()->json([
                'success' => true,
                'data' => $brands,
                'count' => $brands->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Trendyol brand search error', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Arama hatası',
                'data' => []
            ], 500);
        }
    }
}

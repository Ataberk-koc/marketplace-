<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\TrendyolProductMapping;
use App\Services\TrendyolService;
use Illuminate\Http\Request;

/**
 * Satıcı Trendyol entegrasyon controller
 */
class TrendyolController extends Controller
{
    protected $trendyolService;

    public function __construct(TrendyolService $trendyolService)
    {
        $this->trendyolService = $trendyolService;
    }

    /**
     * Trendyol'a gönderilebilecek ürünleri listeler
     */
    public function index()
    {
        $sellerId = auth()->id();
        
        $products = Product::with(['brand.trendyolMapping', 'category.trendyolMapping', 'trendyolMapping'])
            ->where('user_id', $sellerId)
            ->where('is_active', true)
            ->paginate(25);

        // İstatistikler
        $stats = [
            'total_products' => Product::where('user_id', $sellerId)->count(),
            'sent_products' => Product::where('user_id', $sellerId)
                ->whereHas('trendyolMapping', function($q) {
                    $q->where('status', 'active');
                })->count(),
            'pending_products' => Product::where('user_id', $sellerId)
                ->whereHas('trendyolMapping', function($q) {
                    $q->where('status', 'pending');
                })->count(),
            'error_products' => Product::where('user_id', $sellerId)
                ->whereHas('trendyolMapping', function($q) {
                    $q->where('status', 'error');
                })->count(),
        ];

        return view('seller.trendyol.index', compact('products', 'stats'));
    }

    /**
     * Ürünü Trendyol'a gönderir
     */
    public function sendProduct(Product $product)
    {
        // Satıcının kendi ürünü mü kontrol et
        if ($product->user_id !== auth()->id()) {
            abort(403);
        }

        // ADIM 1: Marka Eşleştirme Kontrolü
        if (!$product->brand || !$product->brand->brandMapping || !$product->brand->brandMapping->trendyol_brand_id) {
            return back()->with('error', 'Ürün gönderilemedi! Ürünün markası Trendyol markası ile eşleştirilmemiş. Lütfen admin ile iletişime geçin.');
        }

        // ADIM 2: Kategori Eşleştirme Kontrolü
        if (!$product->category || !$product->category->categoryMapping || !$product->category->categoryMapping->trendyol_category_id) {
            return back()->with('error', 'Ürün gönderilemedi! Ürünün kategorisi Trendyol kategorisi ile eşleştirilmemiş. Lütfen admin ile iletişime geçin.');
        }

        // ADIM 3: Beden Eşleştirme Kontrolü
        $unmappedSizes = [];
        foreach ($product->sizes as $size) {
            if (!$size->sizeMapping || !$size->sizeMapping->trendyolSize) {
                $unmappedSizes[] = $size->name;
            }
        }

        if (!empty($unmappedSizes)) {
            return back()->with('error', 'Ürün gönderilemedi! Şu bedenler Trendyol bedenleri ile eşleştirilmemiş: ' . implode(', ', $unmappedSizes) . '. Lütfen admin ile iletişime geçin.');
        }

        // ADIM 4: Stok Kontrolü
        if ($product->stock_quantity < 1) {
            return back()->with('error', 'Ürün gönderilemedi! Ürünün stoğu yok.');
        }

        // ADIM 5: Fiyat Kontrolü
        if ($product->price <= 0) {
            return back()->with('error', 'Ürün gönderilemedi! Ürünün fiyatı geçersiz.');
        }

        // ADIM 6: Görsel Kontrolü
        $images = is_array($product->images) ? $product->images : json_decode($product->images, true);
        if (empty($images)) {
            return back()->with('error', 'Ürün gönderilemedi! Ürünün en az 1 görseli olmalıdır.');
        }

        // Trendyol mapping oluştur veya getir
        $mapping = TrendyolProductMapping::firstOrCreate(
            ['product_id' => $product->id],
            ['status' => 'pending']
        );

        // Ürünü Trendyol formatına dönüştür
        $productData = [
            'barcode' => $product->sku ?? 'SKU' . $product->id,
            'title' => $product->name,
            'productMainId' => $product->id . time(), // Unique ID
            'brandId' => $product->brand->brandMapping->trendyol_brand_id,
            'categoryId' => $product->category->categoryMapping->trendyol_category_id,
            'quantity' => $product->stock_quantity,
            'stockCode' => $product->sku ?? 'SKU' . $product->id,
            'dimensionalWeight' => 1,
            'description' => strip_tags($product->description ?? $product->name),
            'currencyType' => 'TRY',
            'listPrice' => $product->price,
            'salePrice' => $product->final_price,
            'vatRate' => 18,
            'cargoCompanyId' => 10, // Trendyol kargo
            'images' => array_map(function($img) {
                return ['url' => $img];
            }, array_slice($images, 0, 6)), // Max 6 görsel
            'attributes' => [],
        ];

        // Beden özelliklerini ekle
        foreach ($product->sizes as $size) {
            if ($size->sizeMapping && $size->sizeMapping->trendyolSize) {
                $trendyolSize = $size->sizeMapping->trendyolSize;
                $productData['attributes'][] = [
                    'attributeId' => (int) $trendyolSize->trendyol_attribute_id,
                    'attributeValueId' => (int) $trendyolSize->trendyol_attribute_value_id,
                ];
            }
        }

        // Trendyol'a gönder
        $result = $this->trendyolService->sendProduct($productData);

        if ($result['success']) {
            $mapping->update([
                'status' => 'sent',
                'trendyol_product_id' => $result['data']['batchRequestId'] ?? null,
                'trendyol_response' => json_encode($result['data']),
                'sent_at' => now(),
            ]);

            return back()->with('success', 'Ürün Trendyol\'a başarıyla gönderildi! İşlem ID: ' . ($result['data']['batchRequestId'] ?? 'N/A'));
        }

        $mapping->update([
            'status' => 'error',
            'trendyol_response' => json_encode($result['error'] ?? $result['message']),
        ]);

        return back()->with('error', 'Ürün Trendyol\'a gönderilemedi: ' . $result['message']);
    }

    /**
     * Trendyol'dan ürün durumunu senkronize eder
     */
    public function syncProducts()
    {
        $result = $this->trendyolService->getProducts();

        if (!$result['success']) {
            return back()->with('error', 'Trendyol ürünleri alınamadı: ' . $result['message']);
        }

        // Ürün durumlarını güncelle
        $syncCount = 0;
        foreach ($result['data']['content'] ?? [] as $trendyolProduct) {
            $mapping = TrendyolProductMapping::where('trendyol_product_id', $trendyolProduct['productMainId'])->first();
            
            if ($mapping && $mapping->product->user_id === auth()->id()) {
                $mapping->update([
                    'status' => $trendyolProduct['approved'] ? 'approved' : 'sent',
                    'approved_at' => $trendyolProduct['approved'] ? now() : null,
                ]);
                $syncCount++;
            }
        }

        return back()->with('success', "{$syncCount} ürün durumu güncellendi!");
    }

    /**
     * Ürünü Trendyol'da günceller
     */
    public function updateProduct(Product $product)
    {
        // Satıcının kendi ürünü mü kontrol et
        if ($product->user_id !== auth()->id()) {
            abort(403);
        }

        $mapping = $product->trendyolMapping;
        
        if (!$mapping) {
            return back()->with('error', 'Ürün henüz Trendyol\'a gönderilmemiş!');
        }

        // Ürünü Trendyol formatına dönüştür
        $productData = $this->trendyolService->formatProductForTrendyol($product);

        // Trendyol'da güncelle
        $result = $this->trendyolService->updateProduct($mapping->trendyol_product_id, $productData);

        if ($result['success']) {
            $mapping->update([
                'trendyol_response' => json_encode($result['data']),
                'updated_at' => now(),
            ]);

            return back()->with('success', 'Ürün Trendyol\'da başarıyla güncellendi!');
        }

        return back()->with('error', 'Ürün Trendyol\'da güncellenemedi: ' . $result['message']);
    }

    /**
     * Tüm ürünleri senkronize eder
     */
    public function syncAll()
    {
        $products = Product::with('trendyolMapping')
            ->where('user_id', auth()->id())
            ->whereHas('trendyolMapping')
            ->get();

        $syncCount = 0;
        foreach ($products as $product) {
            $productData = $this->trendyolService->formatProductForTrendyol($product);
            $result = $this->trendyolService->updateProduct($product->trendyolMapping->trendyol_product_id, $productData);
            
            if ($result['success']) {
                $syncCount++;
            }
        }

        return back()->with('success', "{$syncCount} ürün başarıyla senkronize edildi!");
    }
}

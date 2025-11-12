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

        // Trendyol mapping oluştur veya getir
        $mapping = TrendyolProductMapping::firstOrCreate(
            ['product_id' => $product->id],
            ['status' => 'pending']
        );

        // Ürün eşleştirmeleri tamamlanmış mı kontrol et
        if (!$mapping->isReadyToSend()) {
            return back()->with('error', 'Ürün Trendyol\'a gönderilebilmesi için önce marka, kategori ve beden eşleştirmelerinin tamamlanması gerekiyor!');
        }

        // Ürünü Trendyol formatına dönüştür
        $productData = $this->trendyolService->formatProductForTrendyol($product);

        // Trendyol'a gönder
        $result = $this->trendyolService->sendProduct($productData);

        if ($result['success']) {
            $mapping->update([
                'status' => 'sent',
                'trendyol_product_id' => $result['data']['batchRequestId'] ?? null,
                'trendyol_response' => json_encode($result['data']),
                'sent_at' => now(),
            ]);

            return back()->with('success', 'Ürün Trendyol\'a başarıyla gönderildi!');
        }

        $mapping->update([
            'status' => 'rejected',
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

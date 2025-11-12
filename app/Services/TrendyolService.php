<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Trendyol API ile entegrasyon servisi
 * Trendyol Marketplace API dokümantasyonu: https://developers.trendyol.com
 * 
 * Base URL (Production): https://api.trendyol.com/sapigw
 * Base URL (Stage): https://stageapi.trendyol.com/stagesapigw
 */
class TrendyolService
{
    protected $apiUrl;
    protected $supplierId;
    protected $apiKey;
    protected $apiSecret;

    public function __construct()
    {
        // Production veya Stage ortamı
        $isProduction = config('services.trendyol.environment', 'production') === 'production';
        
        $this->apiUrl = $isProduction 
            ? 'https://api.trendyol.com/sapigw'
            : 'https://stageapi.trendyol.com/stagesapigw';
            
        $this->supplierId = config('services.trendyol.supplier_id');
        $this->apiKey = config('services.trendyol.api_key');
        $this->apiSecret = config('services.trendyol.api_secret');
    }

    /**
     * İade ve Sevkiyat Adres Bilgileri
     * GET /integration/sellers/{sellerId}/addresses
     */
    public function getSuppliersAddresses()
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                ->get("{$this->apiUrl}/integration/sellers/{$this->supplierId}/addresses");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            Log::error('Trendyol getSuppliersAddresses error', ['response' => $response->body()]);
            return [
                'success' => false,
                'message' => 'Adres bilgileri alınamadı',
                'error' => $response->body()
            ];
        } catch (\Exception $e) {
            Log::error('Trendyol getSuppliersAddresses exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Trendyol Kargo Şirketleri Listesi
     * Bu bilgi genellikle Trendyol dökümanından alınır (statik)
     */
    public function getProviders()
    {
        // Trendyol kargo şirketleri ID listesi (dökümanından alınmalı)
        return [
            'success' => true,
            'data' => [
                ['id' => 10, 'name' => 'Yurtiçi Kargo'],
                ['id' => 4, 'name' => 'Aras Kargo'],
                ['id' => 5, 'name' => 'Sürat Kargo'],
                ['id' => 7, 'name' => 'MNG Kargo'],
                ['id' => 12, 'name' => 'PTT Kargo'],
                ['id' => 3, 'name' => 'UPS Kargo'],
            ]
        ];
    }

    /**
     * Trendyol Marka Listesi
     * GET /integration/product/brands
     */
    public function getBrands()
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                ->get("{$this->apiUrl}/integration/product/brands");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            Log::error('Trendyol getBrands error', ['response' => $response->body()]);
            return [
                'success' => false,
                'message' => 'Markalar alınamadı',
                'error' => $response->body()
            ];
        } catch (\Exception $e) {
            Log::error('Trendyol getBrands exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Trendyol Kategori Listesi (Ağaç yapısı)
     * GET /integration/product/product-categories
     */
    public function getCategories()
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                ->get("{$this->apiUrl}/integration/product/product-categories");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            Log::error('Trendyol getCategories error', ['response' => $response->body()]);
            return [
                'success' => false,
                'message' => 'Kategoriler alınamadı',
                'error' => $response->body()
            ];
        } catch (\Exception $e) {
            Log::error('Trendyol getCategories exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Trendyol Kategori - Özellik Listesi
     * GET /integration/product/product-categories/{categoryId}/attributes
     */
    public function getCategoryAttributes($categoryId)
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                ->get("{$this->apiUrl}/integration/product/product-categories/{$categoryId}/attributes");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            Log::error('Trendyol getCategoryAttributes error', ['response' => $response->body()]);
            return [
                'success' => false,
                'message' => 'Kategori öznitelikleri alınamadı',
                'error' => $response->body()
            ];
        } catch (\Exception $e) {
            Log::error('Trendyol getCategoryAttributes exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Ürün Aktarma
     * POST /integration/product/sellers/{sellerId}/products
     */
    public function createProducts($productData)
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                ->timeout(30)
                ->post("{$this->apiUrl}/integration/product/sellers/{$this->supplierId}/products", [
                    'items' => is_array($productData[0] ?? null) ? $productData : [$productData]
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            Log::error('Trendyol createProducts error', [
                'product' => $productData,
                'response' => $response->body()
            ]);

            return [
                'success' => false,
                'message' => 'Ürün gönderilemedi',
                'error' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('Trendyol createProducts exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Ürün Bilgisi Güncelleme
     * PUT /integration/product/sellers/{sellerId}/products
     */
    public function updateProduct($productData)
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                ->timeout(30)
                ->put("{$this->apiUrl}/integration/product/sellers/{$this->supplierId}/products", [
                    'items' => is_array($productData[0] ?? null) ? $productData : [$productData]
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            Log::error('Trendyol updateProduct error', [
                'product' => $productData,
                'response' => $response->body()
            ]);

            return [
                'success' => false,
                'message' => 'Ürün güncellenemedi',
                'error' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('Trendyol updateProduct exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Stok ve Fiyat Güncelleme
     * POST /integration/inventory/sellers/{sellerId}/products/price-and-inventory
     */
    public function updatePriceAndInventory($items)
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                ->timeout(30)
                ->post("{$this->apiUrl}/integration/inventory/sellers/{$this->supplierId}/products/price-and-inventory", [
                    'items' => $items
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            Log::error('Trendyol updatePriceAndInventory error', [
                'items' => $items,
                'response' => $response->body()
            ]);

            return [
                'success' => false,
                'message' => 'Stok/Fiyat güncellenemedi',
                'error' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('Trendyol updatePriceAndInventory exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Ürün Silme
     * DELETE /integration/product/sellers/{sellerId}/products
     */
    public function deleteProducts($barcodes)
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                ->delete("{$this->apiUrl}/integration/product/sellers/{$this->supplierId}/products", [
                    'barcodes' => is_array($barcodes) ? $barcodes : [$barcodes]
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            Log::error('Trendyol deleteProducts error', [
                'barcodes' => $barcodes,
                'response' => $response->body()
            ]);

            return [
                'success' => false,
                'message' => 'Ürün silinemedi',
                'error' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('Trendyol deleteProducts exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Toplu İşlem Kontrolü
     * GET /integration/product/sellers/{sellerId}/products/batch-requests/{batchRequestId}
     */
    public function getBatchRequestResult($batchRequestId)
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                ->get("{$this->apiUrl}/integration/product/sellers/{$this->supplierId}/products/batch-requests/{$batchRequestId}");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            Log::error('Trendyol getBatchRequestResult error', ['response' => $response->body()]);
            return [
                'success' => false,
                'message' => 'Toplu işlem durumu alınamadı',
                'error' => $response->body()
            ];
        } catch (\Exception $e) {
            Log::error('Trendyol getBatchRequestResult exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Ürün Filtreleme
     * GET /integration/product/sellers/{sellerId}/products
     */
    public function filterProducts($filters = [])
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                ->get("{$this->apiUrl}/integration/product/sellers/{$this->supplierId}/products", $filters);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            Log::error('Trendyol filterProducts error', ['response' => $response->body()]);
            return [
                'success' => false,
                'message' => 'Ürünler filtrelenemedi',
                'error' => $response->body()
            ];
        } catch (\Exception $e) {
            Log::error('Trendyol filterProducts exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Trendyol kategori listesini çeker (Eski metod - geriye uyumluluk için)
     * @deprecated Use getCategories() instead
     */
    public function getCategoryTree()
    {
        return $this->getCategories();
    }

    /**
     * Ürünü Trendyol'a gönderir (Eski metod - geriye uyumluluk için)
     * @deprecated Use createProducts() instead
     */
    public function sendProduct($productData)
    {
        return $this->createProducts($productData);
    }

    /**
     * Beden/Özellik listesini çeker (Size attributes)
     * Not: Trendyol'da bedenler kategori bazlı attribute olarak gelir
     * Bu metod basitleştirilmiş bir örnektir
     */
    public function getSizeAttributes($categoryId = null)
    {
        try {
            // Eğer kategori ID verilmişse o kategorinin attribute'larını çek
            if ($categoryId) {
                return $this->getCategoryAttributes($categoryId);
            }

            // Genel size/beden listesi için mock data
            // Gerçek uygulamada kategoriye göre çekilmeli
            return [
                'success' => true,
                'data' => [
                    'attributes' => [
                        ['id' => 1, 'name' => 'S', 'attributeType' => 'size'],
                        ['id' => 2, 'name' => 'M', 'attributeType' => 'size'],
                        ['id' => 3, 'name' => 'L', 'attributeType' => 'size'],
                        ['id' => 4, 'name' => 'XL', 'attributeType' => 'size'],
                        ['id' => 5, 'name' => '36', 'attributeType' => 'shoeSize'],
                        ['id' => 6, 'name' => '38', 'attributeType' => 'shoeSize'],
                        ['id' => 7, 'name' => '40', 'attributeType' => 'shoeSize'],
                        ['id' => 8, 'name' => '42', 'attributeType' => 'shoeSize'],
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Trendyol getSizeAttributes exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Trendyol'dan ürün listesini çeker (Eski metod - geriye uyumluluk için)
     * @deprecated Use filterProducts() instead
     */
    public function getProducts($page = 0, $size = 50)
    {
        return $this->filterProducts([
            'page' => $page,
            'size' => $size
        ]);
    }

    /**
     * Ürün datasını Trendyol formatına dönüştürür
     */
    public function formatProductForTrendyol($product)
    {
        return [
            'barcode' => $product->sku,
            'title' => $product->name,
            'productMainId' => $product->sku,
            'brandId' => $product->brand->trendyolMapping->trendyol_brand_id ?? null,
            'categoryId' => $product->category->trendyolMapping->trendyol_category_id ?? null,
            'quantity' => $product->stock_quantity,
            'stockCode' => $product->sku,
            'dimensionalWeight' => 1,
            'description' => $product->description ?? '',
            'currencyType' => 'TRY',
            'listPrice' => $product->price,
            'salePrice' => $product->final_price,
            'vatRate' => 18,
            'cargoCompanyId' => 10, // Default kargo firması
            'images' => array_map(function ($image) {
                return ['url' => $image];
            }, $product->images ?? []),
            'attributes' => $this->formatProductAttributes($product),
        ];
    }

    /**
     * Ürün özniteliklerini formatlar
     */
    protected function formatProductAttributes($product)
    {
        $attributes = [];

        // Ürün özel özellikleri varsa
        if (!empty($product->attributes)) {
            foreach ($product->attributes as $key => $value) {
                $attributes[] = [
                    'attributeId' => $key,
                    'attributeValueId' => $value
                ];
            }
        }

        return $attributes;
    }
}

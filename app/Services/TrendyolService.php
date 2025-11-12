<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Trendyol API ile entegrasyon servisi
 * Trendyol Marketplace API dokümantasyonu: https://developers.trendyol.com
 */
class TrendyolService
{
    protected $apiUrl;
    protected $supplierId;
    protected $apiKey;
    protected $apiSecret;

    public function __construct()
    {
        $this->apiUrl = config('services.trendyol.api_url');
        $this->supplierId = config('services.trendyol.supplier_id');
        $this->apiKey = config('services.trendyol.api_key');
        $this->apiSecret = config('services.trendyol.api_secret');
    }

    /**
     * Trendyol marka listesini çeker
     * GET /brands
     */
    public function getBrands()
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                ->get("{$this->apiUrl}/brands");

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
     * Trendyol kategori listesini çeker
     * GET /product-categories
     */
    public function getCategories()
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                ->get("{$this->apiUrl}/product-categories");

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
     * Belirli bir kategoriye ait öznitelikleri çeker
     * GET /product-categories/{categoryId}/attributes
     */
    public function getCategoryAttributes($categoryId)
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                ->get("{$this->apiUrl}/product-categories/{$categoryId}/attributes");

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
     * Trendyol'a ürün gönderir
     * POST /suppliers/{supplierId}/products
     */
    public function sendProduct($productData)
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                ->post("{$this->apiUrl}/suppliers/{$this->supplierId}/products", [
                    'items' => [$productData]
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            Log::error('Trendyol sendProduct error', [
                'product' => $productData,
                'response' => $response->body()
            ]);

            return [
                'success' => false,
                'message' => 'Ürün gönderilemedi',
                'error' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('Trendyol sendProduct exception', [
                'product' => $productData,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Trendyol'dan ürün listesini çeker
     * GET /suppliers/{supplierId}/products
     */
    public function getProducts($page = 0, $size = 50)
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                ->get("{$this->apiUrl}/suppliers/{$this->supplierId}/products", [
                    'page' => $page,
                    'size' => $size
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            Log::error('Trendyol getProducts error', ['response' => $response->body()]);
            return [
                'success' => false,
                'message' => 'Ürünler alınamadı',
                'error' => $response->body()
            ];
        } catch (\Exception $e) {
            Log::error('Trendyol getProducts exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage()
            ];
        }
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

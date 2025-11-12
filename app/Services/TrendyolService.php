<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Trendyol API ile entegrasyon servisi (Mock Data Destekli)
 * Trendyol Marketplace API dokümantasyonu: https://developers.trendyol.com
 * 
 * Base URL (Production): https://apigw.trendyol.com
 * Base URL (Stage): https://stageapigw.trendyol.com
 * 
 * NOT: Gerçek API credentials yoksa mock veri döndürür
 */
class TrendyolService
{
    protected $apiUrl;
    protected $supplierId;
    protected $apiKey;
    protected $apiSecret;
    protected $isMockMode = false;

    public function __construct()
    {
        // Production veya Stage ortamı
        $isProduction = config('services.trendyol.environment', 'stage') === 'production';
        
        // Base URL
        $this->apiUrl = $isProduction 
            ? 'https://apigw.trendyol.com'
            : 'https://stageapigw.trendyol.com';
            
        $this->supplierId = config('services.trendyol.supplier_id');
        $this->apiKey = config('services.trendyol.api_key');
        $this->apiSecret = config('services.trendyol.api_secret');

        // Mock mode kontrolü
        $this->isMockMode = $this->checkMockMode();
        
        if ($this->isMockMode) {
            Log::info('TrendyolService: Mock mode aktif - Gerçek API çağrıları yapılmayacak');
        }
    }

    /**
     * Mock mode kontrolü
     */
    protected function checkMockMode()
    {
        // API key'ler eksik veya sahte değerler içeriyorsa mock mode
        $mockKeywords = ['mock', 'test', 'dummy', 'demo', 'your_', 'deneme'];
        
        foreach ($mockKeywords as $keyword) {
            if (stripos($this->apiKey, $keyword) !== false || 
                stripos($this->apiSecret, $keyword) !== false ||
                empty($this->apiKey) || 
                empty($this->apiSecret)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Trendyol API'ye merkezi istek metodu
     */
    protected function request(string $method, string $endpoint, array $data = [])
    {
        $userAgent = 'MarketplaceProject-Laravel-1.0.0';
        
        $fullEndpoint = str_replace('{sellerId}', $this->supplierId, $endpoint);
        $fullUrl = $this->apiUrl . $fullEndpoint;

        $request = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode("{$this->apiKey}:{$this->apiSecret}"),
            'User-Agent' => $userAgent,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])
        ->timeout(30)
        ->withOptions(['verify' => false]);

        switch (strtolower($method)) {
            case 'get':
                return $request->get($fullUrl, $data);
            case 'post':
                return $request->post($fullUrl, $data);
            case 'put':
                return $request->put($fullUrl, $data);
            case 'delete':
                return $request->delete($fullUrl, $data);
            default:
                throw new \InvalidArgumentException("Geçersiz HTTP metodu: {$method}");
        }
    }

    /**
     * Trendyol Marka Listesi (Mock Destekli)
     * GET /integration/product/brands
     */
    public function getBrands()
    {
        // Mock mode aktifse sahte veri döndür
        if ($this->isMockMode) {
            return $this->getMockBrands();
        }
        
        try {
            $response = $this->request('get', '/integration/product/brands');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            Log::error('Trendyol getBrands error', [
                'response' => $response->body(),
                'status' => $response->status()
            ]);
            
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
     * Mock Marka Verisi
     */
    protected function getMockBrands()
    {
        Log::info('TrendyolService: Mock marka verisi döndürülüyor');
        
        return [
            'success' => true,
            'data' => [
                'brands' => [
                    ['id' => 100001, 'name' => 'ZARA'],
                    ['id' => 100002, 'name' => 'MANGO'],
                    ['id' => 100003, 'name' => 'H&M'],
                    ['id' => 100004, 'name' => 'PULL & BEAR'],
                    ['id' => 100005, 'name' => 'BERSHKA'],
                    ['id' => 100006, 'name' => 'STRADIVARIUS'],
                    ['id' => 100007, 'name' => 'KOTON'],
                    ['id' => 100008, 'name' => 'DEFACTO'],
                    ['id' => 100009, 'name' => 'LC WAIKIKI'],
                    ['id' => 100010, 'name' => 'NIKE'],
                    ['id' => 100011, 'name' => 'ADIDAS'],
                    ['id' => 100012, 'name' => 'PUMA'],
                    ['id' => 100013, 'name' => 'LACOSTE'],
                    ['id' => 100014, 'name' => 'TOMMY HILFIGER'],
                    ['id' => 100015, 'name' => 'CALVIN KLEIN'],
                    ['id' => 100016, 'name' => 'LEVI\'S'],
                    ['id' => 100017, 'name' => 'MAVI'],
                    ['id' => 100018, 'name' => 'COLLEZIONE'],
                    ['id' => 100019, 'name' => 'NETWORK'],
                    ['id' => 100020, 'name' => 'DESA'],
                ]
            ]
        ];
    }

    /**
     * Trendyol Kategori Listesi (Mock Destekli)
     * GET /integration/product/product-categories
     */
    public function getCategories()
    {
        // Mock mode aktifse sahte veri döndür
        if ($this->isMockMode) {
            return $this->getMockCategories();
        }
        
        try {
            $response = $this->request('get', '/integration/product/product-categories');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            Log::error('Trendyol getCategories error', [
                'response' => $response->body(),
                'status' => $response->status()
            ]);
            
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
     * Mock Kategori Verisi
     */
    protected function getMockCategories()
    {
        Log::info('TrendyolService: Mock kategori verisi döndürülüyor');
        
        return [
            'success' => true,
            'data' => [
                'categories' => [
                    // Ana Kategoriler
                    ['id' => 1, 'name' => 'Kadın', 'parentId' => null, 'subCategories' => []],
                    ['id' => 2, 'name' => 'Erkek', 'parentId' => null, 'subCategories' => []],
                    ['id' => 3, 'name' => 'Çocuk', 'parentId' => null, 'subCategories' => []],
                    ['id' => 4, 'name' => 'Ev & Yaşam', 'parentId' => null, 'subCategories' => []],
                    ['id' => 5, 'name' => 'Süpermarket', 'parentId' => null, 'subCategories' => []],
                    
                    // Kadın Alt Kategorileri
                    ['id' => 101, 'name' => 'Giyim', 'parentId' => 1, 'subCategories' => []],
                    ['id' => 102, 'name' => 'Ayakkabı', 'parentId' => 1, 'subCategories' => []],
                    ['id' => 103, 'name' => 'Aksesuar', 'parentId' => 1, 'subCategories' => []],
                    ['id' => 104, 'name' => 'Çanta', 'parentId' => 1, 'subCategories' => []],
                    
                    // Kadın > Giyim Alt Kategorileri
                    ['id' => 1001, 'name' => 'Elbise', 'parentId' => 101, 'subCategories' => []],
                    ['id' => 1002, 'name' => 'Bluz', 'parentId' => 101, 'subCategories' => []],
                    ['id' => 1003, 'name' => 'Pantolon', 'parentId' => 101, 'subCategories' => []],
                    ['id' => 1004, 'name' => 'Etek', 'parentId' => 101, 'subCategories' => []],
                    ['id' => 1005, 'name' => 'Kazak', 'parentId' => 101, 'subCategories' => []],
                    ['id' => 1006, 'name' => 'Mont & Kaban', 'parentId' => 101, 'subCategories' => []],
                    ['id' => 1007, 'name' => 'Ceket', 'parentId' => 101, 'subCategories' => []],
                    
                    // Erkek Alt Kategorileri
                    ['id' => 201, 'name' => 'Giyim', 'parentId' => 2, 'subCategories' => []],
                    ['id' => 202, 'name' => 'Ayakkabı', 'parentId' => 2, 'subCategories' => []],
                    ['id' => 203, 'name' => 'Aksesuar', 'parentId' => 2, 'subCategories' => []],
                    
                    // Erkek > Giyim Alt Kategorileri
                    ['id' => 2001, 'name' => 'Gömlek', 'parentId' => 201, 'subCategories' => []],
                    ['id' => 2002, 'name' => 'Tişört', 'parentId' => 201, 'subCategories' => []],
                    ['id' => 2003, 'name' => 'Pantolon', 'parentId' => 201, 'subCategories' => []],
                    ['id' => 2004, 'name' => 'Kot Pantolon', 'parentId' => 201, 'subCategories' => []],
                    ['id' => 2005, 'name' => 'Kazak & Hırka', 'parentId' => 201, 'subCategories' => []],
                    ['id' => 2006, 'name' => 'Mont & Kaban', 'parentId' => 201, 'subCategories' => []],
                ]
            ]
        ];
    }

    /**
     * İade ve Sevkiyat Adres Bilgileri
     */
    public function getSuppliersAddresses()
    {
        if ($this->isMockMode) {
            return [
                'success' => true,
                'data' => [
                    'addresses' => [
                        [
                            'id' => 1,
                            'addressType' => 'RETURN',
                            'fullAddress' => 'Mock Iade Adresi, İstanbul, Türkiye',
                            'city' => 'İstanbul',
                            'district' => 'Kadıköy'
                        ]
                    ]
                ]
            ];
        }
        
        try {
            $response = $this->request('get', '/integration/sellers/{sellerId}/addresses');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Adres bilgileri alınamadı'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Kargo Şirketleri
     */
    public function getProviders()
    {
        return [
            'success' => true,
            'data' => [
                ['id' => 10, 'name' => 'Yurtiçi Kargo'],
                ['id' => 4, 'name' => 'Aras Kargo'],
                ['id' => 5, 'name' => 'Sürat Kargo'],
                ['id' => 7, 'name' => 'MNG Kargo'],
                ['id' => 12, 'name' => 'PTT Kargo'],
            ]
        ];
    }

    /**
     * Kategori Özellikleri (Beden, Renk, Kumaş, vb.)
     * GET /integration/product/product-categories/{categoryId}/attributes
     */
    public function getCategoryAttributes($categoryId)
    {
        if ($this->isMockMode) {
            return $this->getMockCategoryAttributes($categoryId);
        }
        
        try {
            $response = $this->request('get', "/integration/product/product-categories/{$categoryId}/attributes");
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }
            
            Log::error('Trendyol getCategoryAttributes error', [
                'categoryId' => $categoryId,
                'response' => $response->body(),
                'status' => $response->status()
            ]);
            
            return ['success' => false, 'message' => 'Kategori öznitelikleri alınamadı'];
        } catch (\Exception $e) {
            Log::error('Trendyol getCategoryAttributes exception', [
                'categoryId' => $categoryId,
                'error' => $e->getMessage()
            ]);
            
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Mock Kategori Özellikleri - Kategori tipine göre farklı attributes
     */
    protected function getMockCategoryAttributes($categoryId)
    {
        Log::info('TrendyolService: Mock kategori özellikleri', ['categoryId' => $categoryId]);
        
        // Giyim kategorileri için (Elbise, Bluz, Pantolon, vb.)
        if (in_array($categoryId, [1001, 1002, 1003, 1004, 1005, 1006, 1007, 2001, 2002, 2003])) {
            return [
                'success' => true,
                'data' => [
                    'categoryAttributes' => [
                        [
                            'attribute' => ['id' => 1, 'name' => 'Beden'],
                            'attributeValues' => [
                                ['id' => 101, 'name' => 'XS'],
                                ['id' => 102, 'name' => 'S'],
                                ['id' => 103, 'name' => 'M'],
                                ['id' => 104, 'name' => 'L'],
                                ['id' => 105, 'name' => 'XL'],
                                ['id' => 106, 'name' => 'XXL'],
                                ['id' => 107, 'name' => '34'],
                                ['id' => 108, 'name' => '36'],
                                ['id' => 109, 'name' => '38'],
                                ['id' => 110, 'name' => '40'],
                                ['id' => 111, 'name' => '42'],
                                ['id' => 112, 'name' => '44'],
                            ],
                            'required' => true,
                            'varianter' => true,
                        ],
                        [
                            'attribute' => ['id' => 2, 'name' => 'Renk'],
                            'attributeValues' => [
                                ['id' => 201, 'name' => 'Beyaz'],
                                ['id' => 202, 'name' => 'Siyah'],
                                ['id' => 203, 'name' => 'Kırmızı'],
                                ['id' => 204, 'name' => 'Mavi'],
                                ['id' => 205, 'name' => 'Yeşil'],
                                ['id' => 206, 'name' => 'Sarı'],
                                ['id' => 207, 'name' => 'Turuncu'],
                                ['id' => 208, 'name' => 'Mor'],
                                ['id' => 209, 'name' => 'Pembe'],
                                ['id' => 210, 'name' => 'Gri'],
                                ['id' => 211, 'name' => 'Kahverengi'],
                                ['id' => 212, 'name' => 'Lacivert'],
                            ],
                            'required' => true,
                            'varianter' => true,
                        ],
                        [
                            'attribute' => ['id' => 3, 'name' => 'Kumaş'],
                            'attributeValues' => [
                                ['id' => 301, 'name' => 'Pamuk'],
                                ['id' => 302, 'name' => 'Polyester'],
                                ['id' => 303, 'name' => 'Viskon'],
                                ['id' => 304, 'name' => 'Yün'],
                                ['id' => 305, 'name' => 'Deri'],
                                ['id' => 306, 'name' => 'Kot'],
                                ['id' => 307, 'name' => 'Kadife'],
                            ],
                            'required' => false,
                            'varianter' => false,
                        ],
                    ]
                ]
            ];
        }
        
        // Ayakkabı kategorileri için
        if (in_array($categoryId, [102, 202])) {
            return [
                'success' => true,
                'data' => [
                    'categoryAttributes' => [
                        [
                            'attribute' => ['id' => 4, 'name' => 'Numara'],
                            'attributeValues' => [
                                ['id' => 401, 'name' => '36'],
                                ['id' => 402, 'name' => '37'],
                                ['id' => 403, 'name' => '38'],
                                ['id' => 404, 'name' => '39'],
                                ['id' => 405, 'name' => '40'],
                                ['id' => 406, 'name' => '41'],
                                ['id' => 407, 'name' => '42'],
                                ['id' => 408, 'name' => '43'],
                                ['id' => 409, 'name' => '44'],
                                ['id' => 410, 'name' => '45'],
                            ],
                            'required' => true,
                            'varianter' => true,
                        ],
                        [
                            'attribute' => ['id' => 2, 'name' => 'Renk'],
                            'attributeValues' => [
                                ['id' => 201, 'name' => 'Beyaz'],
                                ['id' => 202, 'name' => 'Siyah'],
                                ['id' => 203, 'name' => 'Kırmızı'],
                                ['id' => 204, 'name' => 'Mavi'],
                                ['id' => 210, 'name' => 'Gri'],
                                ['id' => 211, 'name' => 'Kahverengi'],
                            ],
                            'required' => true,
                            'varianter' => true,
                        ],
                    ]
                ]
            ];
        }
        
        // Diğer kategoriler için genel attributes
        return [
            'success' => true,
            'data' => [
                'categoryAttributes' => [
                    [
                        'attribute' => ['id' => 1, 'name' => 'Beden'],
                        'attributeValues' => [
                            ['id' => 102, 'name' => 'S'],
                            ['id' => 103, 'name' => 'M'],
                            ['id' => 104, 'name' => 'L'],
                            ['id' => 105, 'name' => 'XL'],
                        ],
                        'required' => true,
                        'varianter' => true,
                    ]
                ]
            ]
        ];
    }

    /**
     * Diğer metodlar - Mock mode durumunda warning döndür
     */
    public function createProducts($productData)
    {
        if ($this->isMockMode) {
            Log::warning('Mock mode: Ürün gönderme işlemi gerçekleştirilemez');
            return [
                'success' => false,
                'message' => 'Mock mode aktif - Gerçek ürün gönderimi için Trendyol API credentials gerekli'
            ];
        }
        
        // Gerçek API implementasyonu...
        return ['success' => false, 'message' => 'Not implemented'];
    }

    public function updateProduct($productData)
    {
        if ($this->isMockMode) {
            return ['success' => false, 'message' => 'Mock mode aktif'];
        }
        return ['success' => false, 'message' => 'Not implemented'];
    }

    public function updatePriceAndInventory($items)
    {
        if ($this->isMockMode) {
            return ['success' => false, 'message' => 'Mock mode aktif'];
        }
        return ['success' => false, 'message' => 'Not implemented'];
    }

    public function deleteProducts($barcodes)
    {
        if ($this->isMockMode) {
            return ['success' => false, 'message' => 'Mock mode aktif'];
        }
        return ['success' => false, 'message' => 'Not implemented'];
    }

    public function getBatchRequestResult($batchRequestId)
    {
        if ($this->isMockMode) {
            return ['success' => false, 'message' => 'Mock mode aktif'];
        }
        return ['success' => false, 'message' => 'Not implemented'];
    }

    public function filterProducts($filters = [])
    {
        if ($this->isMockMode) {
            return ['success' => false, 'message' => 'Mock mode aktif'];
        }
        return ['success' => false, 'message' => 'Not implemented'];
    }

    public function getCategoryTree()
    {
        return $this->getCategories();
    }

    public function sendProduct($productData)
    {
        return $this->createProducts($productData);
    }

    public function getSizeAttributes($categoryId = null)
    {
        if ($categoryId) {
            return $this->getCategoryAttributes($categoryId);
        }
        
        return [
            'success' => true,
            'data' => [
                'attributes' => [
                    ['id' => 1, 'name' => 'S'],
                    ['id' => 2, 'name' => 'M'],
                    ['id' => 3, 'name' => 'L'],
                    ['id' => 4, 'name' => 'XL'],
                ]
            ]
        ];
    }

    public function getProducts($page = 0, $size = 50)
    {
        return $this->filterProducts(['page' => $page, 'size' => $size]);
    }

    public function formatProductForTrendyol($product)
    {
        return [
            'barcode' => $product->sku,
            'title' => $product->name,
            'brandId' => $product->brand->trendyolMapping->trendyol_brand_id ?? null,
            'categoryId' => $product->category->trendyolMapping->trendyol_category_id ?? null,
            'quantity' => $product->stock_quantity,
            'listPrice' => $product->price,
            'salePrice' => $product->final_price,
        ];
    }
}

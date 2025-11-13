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
     * Trendyol Marka Listesi (Public Endpoint - Auth Gerektirmez)
     * GET /integration/product/brands
     * Sayfalama: Her sayfada minimum 1000 marka döner
     */
    public function getBrands($page = 0)
    {
        try {
            // Public endpoint - authentication gerektirmez
            $url = $this->apiUrl . '/integration/product/brands';
            
            // Sayfa parametresi varsa ekle
            if ($page > 0) {
                $url .= '?page=' . $page;
            }

            Log::info('Trendyol getBrands request', ['url' => $url, 'page' => $page]);

            $response = Http::timeout(30)
                ->withOptions(['verify' => false])
                ->get($url);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('Trendyol getBrands success', [
                    'total_brands' => count($data['brands'] ?? []),
                    'page' => $page
                ]);
                
                return [
                    'success' => true,
                    'data' => $data
                ];
            }

            Log::error('Trendyol getBrands error', [
                'response' => $response->body(),
                'status' => $response->status(),
                'page' => $page
            ]);
            
            return [
                'success' => false,
                'message' => 'Markalar alınamadı: ' . $response->status(),
                'error' => $response->body()
            ];
        } catch (\Exception $e) {
            Log::error('Trendyol getBrands exception', [
                'error' => $e->getMessage(),
                'page' => $page
            ]);
            
            return [
                'success' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Tüm Trendyol markalarını sayfalayarak çeker
     */
    public function getAllBrands()
    {
        $allBrands = [];
        $page = 0;
        $hasMorePages = true;

        try {
            while ($hasMorePages) {
                Log::info('Fetching brands page', ['page' => $page]);
                
                $result = $this->getBrands($page);
                
                if (!$result['success']) {
                    break;
                }
                
                $brands = $result['data']['brands'] ?? [];
                
                if (empty($brands)) {
                    $hasMorePages = false;
                } else {
                    $allBrands = array_merge($allBrands, $brands);
                    $page++;
                    
                    // Güvenlik için maksimum 100 sayfa (100,000 marka)
                    if ($page >= 100) {
                        Log::warning('Max page limit reached for getBrands');
                        break;
                    }
                }
            }
            
            Log::info('Total brands fetched', ['count' => count($allBrands)]);
            
            return [
                'success' => true,
                'data' => ['brands' => $allBrands]
            ];
            
        } catch (\Exception $e) {
            Log::error('getAllBrands exception', ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'message' => 'Tüm markalar alınamadı: ' . $e->getMessage()
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
     * Trendyol Kategori Ağacı (Public Endpoint - Auth Gerektirmez)
     * GET /integration/product/product-categories
     * 
     * UYARI: Sadece en alt seviye (leaf=true) kategoriler ile ürün eklenebilir!
     * Kategori ağacı düzenli olarak güncellenir, haftalık çekmeniz önerilir.
     */
    public function getCategories()
    {
        try {
            // Public endpoint - authentication gerektirmez
            $url = $this->apiUrl . '/integration/product/product-categories';

            Log::info('Trendyol getCategories request', ['url' => $url]);

            $response = Http::timeout(60) // Kategori ağacı büyük olabilir
                ->withOptions(['verify' => false])
                ->get($url);

            if ($response->successful()) {
                $data = $response->json();
                
                // Kategori sayısını logla
                $totalCategories = $this->countCategories($data['categories'] ?? []);
                
                Log::info('Trendyol getCategories success', [
                    'total_categories' => $totalCategories
                ]);
                
                return [
                    'success' => true,
                    'data' => $data
                ];
            }

            Log::error('Trendyol getCategories error', [
                'response' => $response->body(),
                'status' => $response->status()
            ]);
            
            return [
                'success' => false,
                'message' => 'Kategoriler alınamadı: ' . $response->status(),
                'error' => $response->body()
            ];
        } catch (\Exception $e) {
            Log::error('Trendyol getCategories exception', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Kategori ağacındaki toplam kategori sayısını hesapla (recursive)
     */
    protected function countCategories($categories)
    {
        $count = count($categories);
        
        foreach ($categories as $category) {
            if (!empty($category['subCategories'])) {
                $count += $this->countCategories($category['subCategories']);
            }
        }
        
        return $count;
    }

    /**
     * Tüm kategorileri düz liste haline getirir (ağaç yapısını bozar)
     * Arama ve eşleştirme için kullanışlıdır
     */
    public function getFlatCategories()
    {
        $result = $this->getCategories();
        
        if (!$result['success']) {
            return $result;
        }
        
        $flatCategories = [];
        $this->flattenCategories($result['data']['categories'] ?? [], $flatCategories);
        
        Log::info('Flat categories created', ['count' => count($flatCategories)]);
        
        return [
            'success' => true,
            'data' => ['categories' => $flatCategories]
        ];
    }

    /**
     * Kategori ağacını düz listeye çevirir (recursive)
     */
    protected function flattenCategories($categories, &$flatList, $parentPath = '')
    {
        foreach ($categories as $category) {
            // Path oluştur
            $path = $parentPath ? $parentPath . ' > ' . $category['name'] : $category['name'];
            
            $flatCategory = [
                'id' => $category['id'],
                'name' => $category['name'],
                'path' => $path,
                'parentId' => $category['parentId'] ?? null,
                'leaf' => $category['leaf'] ?? false,
            ];
            
            $flatList[] = $flatCategory;
            
            // Alt kategorileri işle
            if (!empty($category['subCategories'])) {
                $this->flattenCategories($category['subCategories'], $flatList, $path);
            }
        }
    }

    /**
     * Trendyol Kategori Özellikleri (Public Endpoint - Auth Gerektirmez)
     * GET /integration/product/product-categories/{categoryId}/attributes
     * 
     * UYARI: Sadece LEAF (en alt seviye) kategorilerin özellikleri vardır!
     * Kategori özellikleri haftalık olarak güncellenebilir.
     * 
     * @param int $categoryId Trendyol kategori ID'si (LEAF kategori olmalı)
     * @return array
     */
    public function getCategoryAttributes($categoryId)
    {
        try {
            // Public endpoint - authentication gerektirmez
            $url = $this->apiUrl . "/integration/product/product-categories/{$categoryId}/attributes";

            Log::info('Trendyol getCategoryAttributes request', [
                'url' => $url,
                'category_id' => $categoryId
            ]);

            $response = Http::timeout(30)
                ->withOptions(['verify' => false])
                ->get($url);

            if ($response->successful()) {
                $data = $response->json();
                
                $attributeCount = count($data['categoryAttributes'] ?? []);
                
                Log::info('Trendyol getCategoryAttributes success', [
                    'category_id' => $categoryId,
                    'attributes_count' => $attributeCount
                ]);

                return [
                    'success' => true,
                    'data' => $data,
                    'message' => "{$attributeCount} özellik başarıyla alındı"
                ];
            }

            Log::error('Trendyol getCategoryAttributes failed', [
                'category_id' => $categoryId,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'success' => false,
                'data' => ['categoryAttributes' => []],
                'message' => 'Kategori özellikleri alınamadı (HTTP ' . $response->status() . ')'
            ];

        } catch (\Exception $e) {
            Log::error('Trendyol getCategoryAttributes error', [
                'category_id' => $categoryId,
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'data' => ['categoryAttributes' => []],
                'message' => 'API hatası: ' . $e->getMessage()
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
                    ['id' => 1, 'name' => 'Kadın', 'path' => 'Kadın', 'parentId' => null, 'leaf' => false, 'subCategories' => []],
                    ['id' => 2, 'name' => 'Erkek', 'path' => 'Erkek', 'parentId' => null, 'leaf' => false, 'subCategories' => []],
                    ['id' => 3, 'name' => 'Çocuk', 'path' => 'Çocuk', 'parentId' => null, 'leaf' => false, 'subCategories' => []],
                    ['id' => 4, 'name' => 'Ev & Yaşam', 'path' => 'Ev & Yaşam', 'parentId' => null, 'leaf' => false, 'subCategories' => []],
                    ['id' => 5, 'name' => 'Süpermarket', 'path' => 'Süpermarket', 'parentId' => null, 'leaf' => false, 'subCategories' => []],
                    
                    // Kadın Alt Kategorileri
                    ['id' => 101, 'name' => 'Giyim', 'path' => 'Kadın > Giyim', 'parentId' => 1, 'leaf' => false, 'subCategories' => []],
                    ['id' => 102, 'name' => 'Ayakkabı', 'path' => 'Kadın > Ayakkabı', 'parentId' => 1, 'leaf' => false, 'subCategories' => []],
                    ['id' => 103, 'name' => 'Aksesuar', 'path' => 'Kadın > Aksesuar', 'parentId' => 1, 'leaf' => false, 'subCategories' => []],
                    ['id' => 104, 'name' => 'Çanta', 'path' => 'Kadın > Çanta', 'parentId' => 1, 'leaf' => false, 'subCategories' => []],
                    
                    // Kadın > Giyim Alt Kategorileri (LEAF - Son seviye)
                    ['id' => 1001, 'name' => 'Elbise', 'path' => 'Kadın > Giyim > Elbise', 'parentId' => 101, 'leaf' => true, 'subCategories' => []],
                    ['id' => 1002, 'name' => 'Bluz', 'path' => 'Kadın > Giyim > Bluz', 'parentId' => 101, 'leaf' => true, 'subCategories' => []],
                    ['id' => 1003, 'name' => 'Pantolon', 'path' => 'Kadın > Giyim > Pantolon', 'parentId' => 101, 'leaf' => true, 'subCategories' => []],
                    ['id' => 1004, 'name' => 'Etek', 'path' => 'Kadın > Giyim > Etek', 'parentId' => 101, 'leaf' => true, 'subCategories' => []],
                    ['id' => 1005, 'name' => 'Kazak', 'path' => 'Kadın > Giyim > Kazak', 'parentId' => 101, 'leaf' => true, 'subCategories' => []],
                    ['id' => 1006, 'name' => 'Mont & Kaban', 'path' => 'Kadın > Giyim > Mont & Kaban', 'parentId' => 101, 'leaf' => true, 'subCategories' => []],
                    ['id' => 1007, 'name' => 'Ceket', 'path' => 'Kadın > Giyim > Ceket', 'parentId' => 101, 'leaf' => true, 'subCategories' => []],
                    
                    // Erkek Alt Kategorileri
                    ['id' => 201, 'name' => 'Giyim', 'path' => 'Erkek > Giyim', 'parentId' => 2, 'leaf' => false, 'subCategories' => []],
                    ['id' => 202, 'name' => 'Ayakkabı', 'path' => 'Erkek > Ayakkabı', 'parentId' => 2, 'leaf' => false, 'subCategories' => []],
                    ['id' => 203, 'name' => 'Aksesuar', 'path' => 'Erkek > Aksesuar', 'parentId' => 2, 'leaf' => false, 'subCategories' => []],
                    
                    // Erkek > Giyim Alt Kategorileri (LEAF - Son seviye)
                    ['id' => 2001, 'name' => 'Gömlek', 'path' => 'Erkek > Giyim > Gömlek', 'parentId' => 201, 'leaf' => true, 'subCategories' => []],
                    ['id' => 2002, 'name' => 'Tişört', 'path' => 'Erkek > Giyim > Tişört', 'parentId' => 201, 'leaf' => true, 'subCategories' => []],
                    ['id' => 2003, 'name' => 'Pantolon', 'path' => 'Erkek > Giyim > Pantolon', 'parentId' => 201, 'leaf' => true, 'subCategories' => []],
                    ['id' => 2004, 'name' => 'Kot Pantolon', 'path' => 'Erkek > Giyim > Kot Pantolon', 'parentId' => 201, 'leaf' => true, 'subCategories' => []],
                    ['id' => 2005, 'name' => 'Kazak & Hırka', 'path' => 'Erkek > Giyim > Kazak & Hırka', 'parentId' => 201, 'leaf' => true, 'subCategories' => []],
                    ['id' => 2006, 'name' => 'Mont & Kaban', 'path' => 'Erkek > Giyim > Mont & Kaban', 'parentId' => 201, 'leaf' => true, 'subCategories' => []],
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
        
        // Aksesuar kategorileri için (103, 203 = Kadın/Erkek Aksesuar)
        if (in_array($categoryId, [103, 203])) {
            return [
                'success' => true,
                'data' => [
                    'categoryAttributes' => [
                        [
                            'attribute' => ['id' => 5, 'name' => 'Renk'],
                            'attributeValues' => [
                                ['id' => 201, 'name' => 'Beyaz'],
                                ['id' => 202, 'name' => 'Siyah'],
                                ['id' => 203, 'name' => 'Kırmızı'],
                                ['id' => 204, 'name' => 'Mavi'],
                                ['id' => 205, 'name' => 'Yeşil'],
                                ['id' => 209, 'name' => 'Pembe'],
                                ['id' => 210, 'name' => 'Gri'],
                                ['id' => 213, 'name' => 'Altın'],
                                ['id' => 214, 'name' => 'Gümüş'],
                            ],
                            'required' => true,
                            'varianter' => true,
                        ],
                        [
                            'attribute' => ['id' => 6, 'name' => 'Materyal'],
                            'attributeValues' => [
                                ['id' => 601, 'name' => 'Metal'],
                                ['id' => 602, 'name' => 'Plastik'],
                                ['id' => 603, 'name' => 'Deri'],
                                ['id' => 604, 'name' => 'Kumaş'],
                                ['id' => 605, 'name' => 'Ahşap'],
                                ['id' => 606, 'name' => 'Cam'],
                                ['id' => 607, 'name' => 'Taş'],
                            ],
                            'required' => false,
                            'varianter' => false,
                        ],
                        [
                            'attribute' => ['id' => 7, 'name' => 'Ebat'],
                            'attributeValues' => [
                                ['id' => 701, 'name' => 'Tek Ebat'],
                                ['id' => 702, 'name' => 'Küçük'],
                                ['id' => 703, 'name' => 'Orta'],
                                ['id' => 704, 'name' => 'Büyük'],
                            ],
                            'required' => false,
                            'varianter' => true,
                        ],
                    ]
                ]
            ];
        }
        
        // Çanta kategorileri için (104, 204)
        if (in_array($categoryId, [104, 204])) {
            return [
                'success' => true,
                'data' => [
                    'categoryAttributes' => [
                        [
                            'attribute' => ['id' => 2, 'name' => 'Renk'],
                            'attributeValues' => [
                                ['id' => 201, 'name' => 'Beyaz'],
                                ['id' => 202, 'name' => 'Siyah'],
                                ['id' => 203, 'name' => 'Kırmızı'],
                                ['id' => 204, 'name' => 'Mavi'],
                                ['id' => 211, 'name' => 'Kahverengi'],
                                ['id' => 210, 'name' => 'Gri'],
                            ],
                            'required' => true,
                            'varianter' => true,
                        ],
                        [
                            'attribute' => ['id' => 8, 'name' => 'Malzeme'],
                            'attributeValues' => [
                                ['id' => 801, 'name' => 'Deri'],
                                ['id' => 802, 'name' => 'Suni Deri'],
                                ['id' => 803, 'name' => 'Kumaş'],
                                ['id' => 804, 'name' => 'Süet'],
                                ['id' => 805, 'name' => 'Kanvas'],
                            ],
                            'required' => false,
                            'varianter' => false,
                        ],
                    ]
                ]
            ];
        }
        
        // Ev & Yaşam kategorileri için (4 ve alt kategorileri)
        if ($categoryId == 4 || ($categoryId >= 400 && $categoryId < 500)) {
            return [
                'success' => true,
                'data' => [
                    'categoryAttributes' => [
                        [
                            'attribute' => ['id' => 2, 'name' => 'Renk'],
                            'attributeValues' => [
                                ['id' => 201, 'name' => 'Beyaz'],
                                ['id' => 202, 'name' => 'Siyah'],
                                ['id' => 203, 'name' => 'Kırmızı'],
                                ['id' => 204, 'name' => 'Mavi'],
                                ['id' => 205, 'name' => 'Yeşil'],
                                ['id' => 210, 'name' => 'Gri'],
                            ],
                            'required' => false,
                            'varianter' => true,
                        ],
                        [
                            'attribute' => ['id' => 9, 'name' => 'Ölçü'],
                            'attributeValues' => [
                                ['id' => 901, 'name' => 'Tek Ölçü'],
                                ['id' => 902, 'name' => '50x70 cm'],
                                ['id' => 903, 'name' => '100x150 cm'],
                                ['id' => 904, 'name' => '150x200 cm'],
                                ['id' => 905, 'name' => '200x300 cm'],
                            ],
                            'required' => false,
                            'varianter' => true,
                        ],
                        [
                            'attribute' => ['id' => 10, 'name' => 'Malzeme'],
                            'attributeValues' => [
                                ['id' => 1001, 'name' => 'Pamuk'],
                                ['id' => 1002, 'name' => 'Polyester'],
                                ['id' => 1003, 'name' => 'Ahşap'],
                                ['id' => 1004, 'name' => 'Metal'],
                                ['id' => 1005, 'name' => 'Plastik'],
                                ['id' => 1006, 'name' => 'Cam'],
                            ],
                            'required' => false,
                            'varianter' => false,
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
     * Tek Ürün Aktarma (createProduct - wrapper for createProducts)
     * POST /integration/product/sellers/{sellerId}/products
     */
    public function createProduct($productData)
    {
        // Tek ürünü array içine alıp createProducts'a gönder
        return $this->createProducts([$productData]);
    }

    /**
     * Ürün Aktarma (v2/createProducts)
     * POST /integration/product/sellers/{sellerId}/products
     * Maksimum 1000 item per request
     */
    public function createProducts($items)
    {
        if ($this->isMockMode) {
            Log::warning('Mock mode: Ürün gönderme işlemi gerçekleştirilemez');
            return [
                'success' => true, // Mock mode'da başarılı göster (test için)
                'message' => 'Mock mode: Ürünler gönderildi (simülasyon)',
                'batchRequestId' => 'MOCK-' . uniqid(),
                'itemCount' => count($items)
            ];
        }
        
        try {
            // Maksimum 1000 item kontrolü
            if (count($items) > 1000) {
                return [
                    'success' => false,
                    'message' => 'Maksimum 1000 ürün gönderilebilir. Şu an: ' . count($items)
                ];
            }

            $response = $this->request('post', "/integration/product/sellers/{$this->supplierId}/products", [
                'items' => $items
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('Trendyol ürün gönderimi başarılı', [
                    'batchRequestId' => $data['batchRequestId'] ?? null,
                    'itemCount' => count($items)
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Ürünler başarıyla gönderildi',
                    'batchRequestId' => $data['batchRequestId'] ?? null,
                    'data' => $data
                ];
            }

            Log::error('Trendyol ürün gönderimi hatası', [
                'response' => $response->body(),
                'status' => $response->status()
            ]);
            
            return [
                'success' => false,
                'message' => 'Ürün gönderimi başarısız',
                'error' => $response->body()
            ];
        } catch (\Exception $e) {
            Log::error('Trendyol createProducts exception', ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage()
            ];
        }
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

    /**
     * Batch İşlem Durumu Kontrolü
     * GET /integration/product/sellers/{sellerId}/products/batch-requests/{batchRequestId}
     */
    public function getBatchRequestResult($batchRequestId)
    {
        if ($this->isMockMode) {
            // Mock mode'da başarılı sonuç döndür
            return [
                'success' => true,
                'message' => 'Mock mode: Batch işlemi tamamlandı (simülasyon)',
                'data' => [
                    'batchRequestId' => $batchRequestId,
                    'status' => 'COMPLETED',
                    'createdItemCount' => rand(1, 10),
                    'failedItemCount' => 0,
                    'items' => []
                ]
            ];
        }
        
        try {
            $response = $this->request('get', "/integration/product/sellers/{$this->supplierId}/products/batch-requests/{$batchRequestId}");

            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'success' => true,
                    'data' => $data
                ];
            }

            return [
                'success' => false,
                'message' => 'Batch sonucu alınamadı',
                'error' => $response->body()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage()
            ];
        }
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

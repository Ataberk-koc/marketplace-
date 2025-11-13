<?php

/**
 * TRENDYOL MAPPING SYSTEM - QUICK REFERENCE
 * 
 * Bu dosya sistemi nasıl kullanacağınızı gösteren örnekler içerir.
 * Bu dosya çalıştırılabilir değildir, sadece referans amaçlıdır.
 */

// ============================================
// ÖRNEK 1: Controller'da Tek Ürün Gönderme
// ============================================

namespace App\Http\Controllers\Admin;

use App\Services\TrendyolService;
use App\Models\Product;

class TrendyolProductController extends Controller
{
    protected $trendyolService;

    public function __construct(TrendyolService $trendyolService)
    {
        $this->trendyolService = $trendyolService;
    }

    public function sendToTrendyol($productId)
    {
        // 1. Product yükle (relationships ile)
        $product = Product::with(['variants', 'brand', 'category'])
            ->findOrFail($productId);

        // 2. Mapping kullanarak payload hazırla
        $payloadResult = $this->trendyolService->prepareProductPayloadWithMappings($product);

        // 3. Mapping hataları kontrolü
        if (!$payloadResult['success']) {
            return back()->withErrors([
                'mapping' => 'Mapping hataları mevcut:',
                'details' => $payloadResult['errors']
            ]);
        }

        // 4. Trendyol'a gönder
        $result = $this->trendyolService->createProducts($payloadResult['items']);

        if ($result['success']) {
            return back()->with('success', 
                "Ürün başarıyla gönderildi! Toplam variant: " . count($payloadResult['items']));
        }

        return back()->withErrors(['api' => $result['message']]);
    }
}

// ============================================
// ÖRNEK 2: Toplu Ürün Gönderme
// ============================================

class BulkTrendyolController extends Controller
{
    public function sendMultipleProducts(Request $request)
    {
        $productIds = $request->input('product_ids', []);
        $results = ['success' => [], 'failed' => []];

        foreach ($productIds as $productId) {
            $product = Product::with(['variants', 'brand', 'category'])->find($productId);
            
            if (!$product) {
                $results['failed'][$productId] = 'Product bulunamadı';
                continue;
            }

            $payloadResult = app(TrendyolService::class)
                ->prepareProductPayloadWithMappings($product);

            if (!$payloadResult['success']) {
                $results['failed'][$productId] = $payloadResult['errors'];
                continue;
            }

            $apiResult = app(TrendyolService::class)
                ->createProducts($payloadResult['items']);

            if ($apiResult['success']) {
                $results['success'][$productId] = $apiResult['batchRequestId'];
            } else {
                $results['failed'][$productId] = $apiResult['message'];
            }
        }

        return response()->json($results);
    }
}

// ============================================
// ÖRNEK 3: Mapping Kontrol Fonksiyonu
// ============================================

class MappingCheckController extends Controller
{
    public function checkProductMappings($productId)
    {
        $product = Product::with(['variants', 'brand', 'category'])->findOrFail($productId);
        
        $checks = [
            'brand_mapped' => false,
            'category_mapped' => false,
            'all_variants_mapped' => true,
            'unmapped_attributes' => []
        ];

        // Brand kontrolü
        $brandMapping = \App\Models\BrandMapping::where('brand_id', $product->brand_id)
            ->where('is_active', true)
            ->exists();
        $checks['brand_mapped'] = $brandMapping;

        // Category kontrolü
        $categoryMapping = \App\Models\CategoryMapping::where('category_id', $product->category_id)
            ->where('is_active', true)
            ->exists();
        $checks['category_mapped'] = $categoryMapping;

        // Attribute kontrolü (her variant için)
        foreach ($product->variants as $variant) {
            $optionValues = is_array($variant->option_values) 
                ? $variant->option_values 
                : json_decode($variant->option_values, true);

            foreach ($optionValues as $optionValue) {
                $mapping = \App\Models\TrendyolAttributeMapping::where('option_id', $optionValue['option_id'])
                    ->where('option_value_id', $optionValue['value_id'])
                    ->where('is_active', true)
                    ->exists();

                if (!$mapping) {
                    $checks['all_variants_mapped'] = false;
                    $checks['unmapped_attributes'][] = [
                        'variant_id' => $variant->id,
                        'option' => $optionValue['option_name'],
                        'value' => $optionValue['value']
                    ];
                }
            }
        }

        return response()->json($checks);
    }
}

// ============================================
// ÖRNEK 4: Manuel Mapping Oluşturma
// ============================================

class MappingSetupController extends Controller
{
    public function createMapping(Request $request)
    {
        $mapping = \App\Models\TrendyolAttributeMapping::create([
            'option_id' => $request->option_id,
            'option_value_id' => $request->option_value_id,
            'trendyol_attribute_id' => $request->trendyol_attribute_id,
            'trendyol_value_id' => $request->trendyol_value_id,
            'trendyol_category_id' => $request->trendyol_category_id ?? null, // Null = global
            'is_active' => true
        ]);

        return response()->json([
            'message' => 'Mapping oluşturuldu',
            'mapping' => $mapping
        ]);
    }

    public function createBrandMapping(Request $request)
    {
        $mapping = \App\Models\BrandMapping::create([
            'brand_id' => $request->brand_id,
            'trendyol_brand_id' => $request->trendyol_brand_id,
            'is_active' => true
        ]);

        return response()->json([
            'message' => 'Brand mapping oluşturuldu',
            'mapping' => $mapping
        ]);
    }

    public function createCategoryMapping(Request $request)
    {
        $mapping = \App\Models\CategoryMapping::create([
            'category_id' => $request->category_id,
            'trendyol_category_id' => $request->trendyol_category_id,
            'is_active' => true
        ]);

        return response()->json([
            'message' => 'Category mapping oluşturuldu',
            'mapping' => $mapping
        ]);
    }
}

// ============================================
// ÖRNEK 5: Artisan Command (Toplu İşlem)
// ============================================

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Services\TrendyolService;

class SendProductsToTrendyol extends Command
{
    protected $signature = 'trendyol:send-products {--category_id=} {--brand_id=}';
    protected $description = 'Send products to Trendyol with mappings';

    public function handle()
    {
        $query = Product::with(['variants', 'brand', 'category']);

        if ($this->option('category_id')) {
            $query->where('category_id', $this->option('category_id'));
        }

        if ($this->option('brand_id')) {
            $query->where('brand_id', $this->option('brand_id'));
        }

        $products = $query->get();
        $this->info("Toplam {$products->count()} ürün bulundu.");

        $trendyolService = app(TrendyolService::class);
        $progressBar = $this->output->createProgressBar($products->count());

        $success = 0;
        $failed = 0;

        foreach ($products as $product) {
            $payloadResult = $trendyolService->prepareProductPayloadWithMappings($product);

            if (!$payloadResult['success']) {
                $this->error("\nÜrün #{$product->id}: Mapping hataları - " . implode(', ', $payloadResult['errors']));
                $failed++;
                $progressBar->advance();
                continue;
            }

            $apiResult = $trendyolService->createProducts($payloadResult['items']);

            if ($apiResult['success']) {
                $success++;
            } else {
                $this->error("\nÜrün #{$product->id}: API hatası - {$apiResult['message']}");
                $failed++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
        $this->info("Başarılı: {$success}, Hatalı: {$failed}");
    }
}

// ============================================
// ÖRNEK 6: API Endpoint (RESTful)
// ============================================

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TrendyolService;
use App\Models\Product;

class TrendyolApiController extends Controller
{
    /**
     * POST /api/trendyol/products/{product}/send
     */
    public function sendProduct(Product $product)
    {
        $product->load(['variants', 'brand', 'category']);

        $payloadResult = app(TrendyolService::class)
            ->prepareProductPayloadWithMappings($product);

        if (!$payloadResult['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Mapping errors detected',
                'errors' => $payloadResult['errors']
            ], 400);
        }

        $apiResult = app(TrendyolService::class)
            ->createProducts($payloadResult['items']);

        if ($apiResult['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Product sent successfully',
                'batch_request_id' => $apiResult['batchRequestId'],
                'variant_count' => count($payloadResult['items'])
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to send product to Trendyol',
            'error' => $apiResult['message']
        ], 500);
    }

    /**
     * GET /api/trendyol/products/{product}/check-mappings
     */
    public function checkMappings(Product $product)
    {
        $product->load(['variants', 'brand', 'category']);

        $payloadResult = app(TrendyolService::class)
            ->prepareProductPayloadWithMappings($product);

        return response()->json([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'is_ready' => $payloadResult['success'],
            'errors' => $payloadResult['errors'],
            'variant_count' => count($payloadResult['items'])
        ]);
    }
}

// ============================================
// ÖRNEK 7: Database Seed (Test Data)
// ============================================

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TrendyolAttributeMapping;
use App\Models\Option;
use App\Models\OptionValue;

class TrendyolMappingSeeder extends Seeder
{
    public function run()
    {
        // Örnek: Renk mappings
        $colorOption = Option::where('name', 'Renk')->first();
        if ($colorOption) {
            $colors = [
                'Kırmızı' => ['attribute_id' => 203, 'value_id' => 456],
                'Mavi' => ['attribute_id' => 203, 'value_id' => 457],
                'Yeşil' => ['attribute_id' => 203, 'value_id' => 458],
            ];

            foreach ($colors as $colorName => $trendyolIds) {
                $value = OptionValue::where('option_id', $colorOption->id)
                    ->where('value', $colorName)
                    ->first();

                if ($value) {
                    TrendyolAttributeMapping::updateOrCreate(
                        [
                            'option_id' => $colorOption->id,
                            'option_value_id' => $value->id,
                        ],
                        [
                            'trendyol_attribute_id' => $trendyolIds['attribute_id'],
                            'trendyol_value_id' => $trendyolIds['value_id'],
                            'is_active' => true
                        ]
                    );
                }
            }
        }

        // Örnek: Beden mappings
        $sizeOption = Option::where('name', 'Beden')->first();
        if ($sizeOption) {
            $sizes = [
                'S' => ['attribute_id' => 204, 'value_id' => 789],
                'M' => ['attribute_id' => 204, 'value_id' => 790],
                'L' => ['attribute_id' => 204, 'value_id' => 791],
            ];

            foreach ($sizes as $sizeName => $trendyolIds) {
                $value = OptionValue::where('option_id', $sizeOption->id)
                    ->where('value', $sizeName)
                    ->first();

                if ($value) {
                    TrendyolAttributeMapping::updateOrCreate(
                        [
                            'option_id' => $sizeOption->id,
                            'option_value_id' => $value->id,
                        ],
                        [
                            'trendyol_attribute_id' => $trendyolIds['attribute_id'],
                            'trendyol_value_id' => $trendyolIds['value_id'],
                            'is_active' => true
                        ]
                    );
                }
            }
        }
    }
}

// ============================================
// ÖRNEK 8: Livewire Component (Real-time Check)
// ============================================

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Services\TrendyolService;

class ProductMappingCheck extends Component
{
    public $productId;
    public $mappingStatus = [];

    public function mount($productId)
    {
        $this->productId = $productId;
        $this->checkMappings();
    }

    public function checkMappings()
    {
        $product = Product::with(['variants', 'brand', 'category'])->find($this->productId);

        if (!$product) {
            $this->mappingStatus = ['error' => 'Product bulunamadı'];
            return;
        }

        $payloadResult = app(TrendyolService::class)
            ->prepareProductPayloadWithMappings($product);

        $this->mappingStatus = [
            'is_ready' => $payloadResult['success'],
            'errors' => $payloadResult['errors'],
            'variant_count' => count($payloadResult['items'])
        ];
    }

    public function render()
    {
        return view('livewire.product-mapping-check');
    }
}

// ============================================
// ÖRNEK 9: Test Case
// ============================================

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Option;
use App\Models\OptionValue;
use App\Models\TrendyolAttributeMapping;
use App\Services\TrendyolService;

class TrendyolMappingTest extends TestCase
{
    public function test_product_payload_with_complete_mappings()
    {
        // Setup product with variants
        $product = Product::factory()->create();
        
        $colorOption = Option::factory()->create(['name' => 'Renk']);
        $redValue = OptionValue::factory()->create([
            'option_id' => $colorOption->id,
            'value' => 'Kırmızı'
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'option_values' => [
                [
                    'option_id' => $colorOption->id,
                    'option_name' => 'Renk',
                    'value_id' => $redValue->id,
                    'value' => 'Kırmızı'
                ]
            ]
        ]);

        // Create mapping
        TrendyolAttributeMapping::create([
            'option_id' => $colorOption->id,
            'option_value_id' => $redValue->id,
            'trendyol_attribute_id' => 203,
            'trendyol_value_id' => 456,
            'is_active' => true
        ]);

        // Test service
        $service = app(TrendyolService::class);
        $result = $service->prepareProductPayloadWithMappings($product);

        $this->assertTrue($result['success']);
        $this->assertCount(1, $result['items']);
        $this->assertEquals(203, $result['items'][0]['attributes'][0]['attributeId']);
        $this->assertEquals(456, $result['items'][0]['attributes'][0]['attributeValueId']);
    }

    public function test_product_payload_with_missing_mappings()
    {
        $product = Product::factory()->create();
        
        $colorOption = Option::factory()->create(['name' => 'Renk']);
        $redValue = OptionValue::factory()->create([
            'option_id' => $colorOption->id,
            'value' => 'Kırmızı'
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'option_values' => [
                [
                    'option_id' => $colorOption->id,
                    'option_name' => 'Renk',
                    'value_id' => $redValue->id,
                    'value' => 'Kırmızı'
                ]
            ]
        ]);

        // No mapping created!

        $service = app(TrendyolService::class);
        $result = $service->prepareProductPayloadWithMappings($product);

        $this->assertFalse($result['success']);
        $this->assertNotEmpty($result['errors']);
    }
}

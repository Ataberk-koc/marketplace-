<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductAttribute;
use App\Models\TrendyolCategoryAttribute;
use App\Services\TrendyolService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Admin ürün yönetimi controller
 */
class ProductController extends Controller
{
    /**
     * Ürün listesini gösterir - ENHANCED with debugging
     */
    public function index(Request $request)
    {
        // Better eager loading with variants
        $query = Product::with([
            'brand', 
            'category', 
            'seller',
            'variants' => function($q) {
                $q->select('product_id', 'stock_quantity', 'price', 'discount_price');
            }
        ]);

        // Kategori filtresi
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Marka filtresi
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        // Durum filtresi
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Arama
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Order by latest and paginate
        $products = $query->latest()->paginate(25);

        // Debug: Log product count
        \Log::info('Products loaded', [
            'total' => $products->total(),
            'count' => $products->count(),
            'current_page' => $products->currentPage()
        ]);

        $categories = Category::all();
        $brands = Brand::all();

        return view('admin.products.index', compact('products', 'categories', 'brands'));
    }

    /**
     * Ürün durumunu aktif/pasif yapar
     */
    public function toggleActive(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);

        $status = $product->is_active ? 'aktif' : 'pasif';
        return back()->with('success', "Ürün {$status} yapıldı!");
    }

    /**
     * Ürünü öne çıkan/çıkarmayan yapar
     */
    public function toggleFeatured(Product $product)
    {
        $product->update(['is_featured' => !$product->is_featured]);

        $status = $product->is_featured ? 'öne çıkan' : 'normal';
        return back()->with('success', "Ürün {$status} yapıldı!");
    }

    /**
     * Yeni ürün oluşturma formu
     */
    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        $brands = Brand::where('is_active', true)->get();
        $definedOptions = \App\Models\Option::with('values')->where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.products.create', compact('categories', 'brands', 'definedOptions'));
    }

    /**
     * Yeni ürün kaydeder - WITH COMPREHENSIVE ERROR HANDLING
     */
    public function store(Request $request)
    {
        // ⭐ STEP 1: Log all incoming request data
        Log::info('🆕 NEW PRODUCT SAVE ATTEMPT', [
            'request_all' => $request->all(),
            'has_variants_json' => $request->has('variants_json'),
            'has_attributes_json' => $request->has('attributes_json'),
            'user_id' => auth()->id()
        ]);

        try {
            // ⭐ STEP 2: Validation
            Log::info('📋 Starting validation...');
            
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'model_code' => 'required|string|max:100',
                'description' => 'nullable|string',
                'category_id' => 'required|exists:categories,id',
                'brand_id' => 'nullable|exists:brands,id',
                'variants_json' => 'required|string',
                'attributes_json' => 'nullable|string',
            ]);

            Log::info('✅ Validation passed');

            // ⭐ STEP 3: Parse JSON data
            Log::info('🔍 Parsing variants_json...');
            $variantsData = json_decode($request->variants_json, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid variants_json format: ' . json_last_error_msg());
            }

            if (!$variantsData || count($variantsData) === 0) {
                throw new \Exception('No variants found in variants_json!');
            }

            Log::info('✅ Parsed variants', ['count' => count($variantsData)]);

            // Parse attributes JSON (optional)
            $attributesData = [];
            if ($request->filled('attributes_json')) {
                Log::info('🔍 Parsing attributes_json...');
                $attributesData = json_decode($request->attributes_json, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::warning('⚠️ Invalid attributes_json, skipping', ['error' => json_last_error_msg()]);
                    $attributesData = [];
                }
            }

            // ⭐ STEP 4: Start database transaction
            Log::info('💾 Starting database transaction...');
            
            DB::beginTransaction();

            // ⭐ STEP 5: Generate unique slug (inside transaction to avoid race conditions)
            Log::info('🔑 Generating unique slug...');
            $slug = $this->generateUniqueSlug($request->name);
            Log::info('✅ Unique slug generated', ['slug' => $slug]);

            // ⭐ STEP 6: Create main product
            Log::info('📦 Creating main product...');
            
            $product = Product::create([
                'user_id' => auth()->id(),
                'name' => $request->name,
                'slug' => $slug, // ⭐ Unique slug
                'sku' => $request->model_code . '-' . time(), // Unique SKU
                'model_code' => $request->model_code,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'price' => 0, // Will be updated after variants
                'discount_price' => null,
                'stock_quantity' => 0, // Will be updated after variants
                'images' => [],
                'is_active' => true,
                'is_featured' => false,
            ]);

            Log::info('✅ Main product created', ['product_id' => $product->id]);

            // ⭐ STEP 7: Create variants
            Log::info('🎨 Creating variants...');
            
            $totalStock = 0;
            $minPrice = PHP_INT_MAX;
            $variantCount = 0;

            foreach ($variantsData as $index => $variantData) {
                Log::info("📝 Creating variant #{$index}", ['data' => $variantData]);

                // Build option_values array
                $optionValues = [];
                if (isset($variantData['option_mapping']) && is_array($variantData['option_mapping'])) {
                    foreach ($variantData['option_mapping'] as $mapping) {
                        $optionValues[] = [
                            'option_id' => $mapping['option_id'] ?? null,
                            'option_name' => $mapping['option_name'] ?? 'Unknown',
                            'value_id' => $mapping['value_id'] ?? null,
                            'value' => $mapping['value'] ?? 'Unknown',
                        ];
                    }
                }

                // ⭐ Generate unique barcode if empty or duplicate
                $barcode = $variantData['barcode'] ?? null;
                if (empty($barcode) || $this->barcodeExists($barcode)) {
                    $barcode = $this->generateUniqueBarcode();
                    Log::info("🔢 Generated unique barcode", ['barcode' => $barcode]);
                }

                // Create variant
                $variant = \App\Models\ProductVariant::create([
                    'product_id' => $product->id,
                    'variant_name' => $variantData['name'] ?? 'Variant ' . ($index + 1),
                    'sku' => $variantData['sku'] ?? ($request->model_code . '-V' . ($index + 1)),
                    'barcode' => $barcode,
                    'option_values' => $optionValues,
                    'attributes' => $variantData['attributes'] ?? [],
                    'price' => floatval($variantData['price'] ?? 0),
                    'discount_price' => !empty($variantData['discount_price']) ? floatval($variantData['discount_price']) : null,
                    'stock_quantity' => intval($variantData['stock'] ?? 0),
                    'reserved_quantity' => 0,
                    'low_stock_threshold' => 5,
                    'is_active' => true,
                    'sort_order' => $index,
                ]);

                Log::info("✅ Variant created", ['variant_id' => $variant->id, 'sku' => $variant->sku]);

                // Update totals
                $totalStock += $variant->stock_quantity;
                $minPrice = min($minPrice, $variant->price);
                $variantCount++;
            }

            Log::info("✅ All variants created", ['total_count' => $variantCount]);

            // ⭐ STEP 8: Update main product with calculated values
            Log::info('🔄 Updating main product with totals...');
            
            $product->update([
                'stock_quantity' => $totalStock,
                'price' => $minPrice !== PHP_INT_MAX ? $minPrice : 0,
            ]);

            Log::info('✅ Main product updated', [
                'total_stock' => $totalStock,
                'min_price' => $minPrice
            ]);

            // ⭐ STEP 9: Create static attributes (if any)
            if (!empty($attributesData) && is_array($attributesData)) {
                Log::info('🏷️ Creating product attributes...', ['count' => count($attributesData)]);
                
                foreach ($attributesData as $attrIndex => $attribute) {
                    if (!empty($attribute['name']) && !empty($attribute['value'])) {
                        ProductAttribute::create([
                            'product_id' => $product->id,
                            'attribute_name' => $attribute['name'],
                            'attribute_value' => $attribute['value'],
                            'attribute_type' => 'text',
                            'display_order' => $attrIndex,
                            'is_required' => false,
                            'is_variant' => false,
                        ]);
                        
                        Log::info('✅ Attribute created', ['name' => $attribute['name'], 'value' => $attribute['value']]);
                    }
                }
            }

            // ⭐ STEP 10: Commit transaction
            DB::commit();
            
            Log::info('🎉 PRODUCT SAVE SUCCESSFUL', [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'variant_count' => $variantCount,
                'total_stock' => $totalStock,
                'attributes_count' => count($attributesData)
            ]);

            return redirect()->route('admin.products.index')
                ->with('success', "Ürün '{$product->name}' ve {$variantCount} varyantı başarıyla oluşturuldu!");

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors
            Log::error('❌ VALIDATION FAILED', [
                'errors' => $e->errors(),
                'message' => $e->getMessage()
            ]);
            
            return back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Doğrulama hatası! Lütfen formu kontrol edin.');

        } catch (\Exception $e) {
            // Any other error
            DB::rollBack();
            
            Log::error('❌ PRODUCT SAVE FAILED', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Ürün kaydedilemedi: ' . $e->getMessage() . ' (Satır: ' . $e->getLine() . ')');
        }
    }

    /**
     * Ürün düzenleme formu
     */
    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)->get();
        $brands = Brand::where('is_active', true)->get();

        return view('admin.products.edit', compact('product', 'categories', 'brands'));
    }

    /**
     * Ürünü günceller
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'required|string|max:100|unique:products,sku,' . $product->id,
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'stock_quantity' => 'required|integer|min:0',
            'images' => 'nullable|array',
            'images.*' => 'url',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'sku' => $request->sku,
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
            'price' => $request->price,
            'discount_price' => $request->discount_price,
            'stock_quantity' => $request->stock_quantity,
            'images' => $request->images ?? [],
            'is_active' => $request->boolean('is_active'),
            'is_featured' => $request->boolean('is_featured'),
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Ürün güncellendi!');
    }

    /**
     * Ürünü siler (soft delete)
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return back()->with('success', 'Ürün silindi!');
    }

    /**
     * Ürün detaylarını gösterir (özellikleri ile birlikte)
     */
    public function show(Product $product)
    {
        $product->load([
            'brand',
            'category',
            'seller',
            'sizes',
            'productAttributes' => function($query) {
                $query->orderBy('is_required', 'desc')->orderBy('display_order');
            },
            'trendyolMapping'
        ]);

        return view('admin.products.show', compact('product'));
    }

    /**
     * Ürün özelliklerini yönetme sayfası
     */
    public function attributes(Product $product)
    {
        $product->load([
            'brand',
            'category',
            'productAttributes' => function($query) {
                $query->orderBy('display_order');
            }
        ]);

        // Kategorinin Trendyol mapping'i varsa, o kategoriye ait attribute'ları getir
        $trendyolAttributes = [];
        if ($product->category->categoryMapping) {
            $trendyolCategoryId = $product->category->categoryMapping->trendyol_category_id;
            $trendyolAttributes = TrendyolCategoryAttribute::getCategoryAttributes($trendyolCategoryId);
        }

        return view('admin.products.attributes', compact('product', 'trendyolAttributes'));
    }

    /**
     * Ürün özelliklerini kaydet/güncelle
     */
    public function saveAttributes(Request $request, Product $product)
    {
        $validated = $request->validate([
            'attributes' => 'required|array',
            'attributes.*.attribute_name' => 'required|string',
            'attributes.*.attribute_value' => 'required|string',
            'attributes.*.attribute_type' => 'nullable|string',
            'attributes.*.trendyol_attribute_id' => 'nullable|string',
            'attributes.*.trendyol_attribute_name' => 'nullable|string',
            'attributes.*.is_required' => 'boolean',
            'attributes.*.is_variant' => 'boolean',
            'attributes.*.display_order' => 'nullable|integer',
        ]);

        // Mevcut attribute'ları sil
        $product->productAttributes()->delete();

        // Yeni attribute'ları kaydet
        foreach ($validated['attributes'] as $index => $attr) {
            $product->productAttributes()->create([
                'attribute_name' => $attr['attribute_name'],
                'attribute_value' => $attr['attribute_value'],
                'attribute_type' => $attr['attribute_type'] ?? 'text',
                'trendyol_attribute_id' => $attr['trendyol_attribute_id'] ?? null,
                'trendyol_attribute_name' => $attr['trendyol_attribute_name'] ?? null,
                'is_required' => $attr['is_required'] ?? false,
                'is_variant' => $attr['is_variant'] ?? false,
                'display_order' => $attr['display_order'] ?? $index,
            ]);
        }

        return back()->with('success', 'Ürün özellikleri kaydedildi!');
    }

    /**
     * Kategoriye ait Trendyol attribute'larını senkronize et
     */
    public function syncCategoryAttributes(Request $request)
    {
        $validated = $request->validate([
            'trendyol_category_id' => 'required|string',
        ]);

        $trendyolService = app(TrendyolService::class);
        $result = $trendyolService->getCategoryAttributes($validated['trendyol_category_id']);

        if (!$result['success']) {
            return back()->with('error', 'Trendyol category attributes alınamadı: ' . ($result['message'] ?? 'Bilinmeyen hata'));
        }

        $categoryId = $validated['trendyol_category_id'];
        $attributes = $result['data']['categoryAttributes'] ?? [];

        // Mevcut attribute'ları temizle
        TrendyolCategoryAttribute::where('trendyol_category_id', $categoryId)->delete();

        // Yeni attribute'ları kaydet
        foreach ($attributes as $index => $attr) {
            $attribute = $attr['attribute'];
            $allowedValues = [];

            if (isset($attr['attributeValues']) && is_array($attr['attributeValues'])) {
                $allowedValues = array_map(function($val) {
                    return ['id' => $val['id'], 'name' => $val['name']];
                }, $attr['attributeValues']);
            }

            TrendyolCategoryAttribute::create([
                'trendyol_category_id' => $categoryId,
                'attribute_id' => $attribute['id'],
                'attribute_name' => $attribute['name'],
                'attribute_type' => $attr['attributeType'] ?? 'text',
                'is_required' => $attr['required'] ?? false,
                'allows_custom_value' => $attr['allowCustom'] ?? false,
                'is_variant_based' => $attr['varianter'] ?? false,
                'allowed_values' => !empty($allowedValues) ? $allowedValues : null,
                'display_order' => $index,
            ]);
        }

        return back()->with('success', count($attributes) . ' adet özellik senkronize edildi!');
    }

    /**
     * Generate a unique slug for the product
     * 
     * @param string $name Product name
     * @return string Unique slug
     */
    private function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        // Check if slug exists (including soft-deleted records)
        while (Product::withTrashed()->where('slug', $slug)->exists()) {
            // Append random string to make it unique
            $randomString = Str::lower(Str::random(4));
            $slug = $originalSlug . '-' . $randomString;
            
            Log::info('🔄 Slug collision detected, trying new slug', [
                'original' => $originalSlug,
                'attempt' => $counter,
                'new_slug' => $slug
            ]);
            
            $counter++;
            
            // Safety check: prevent infinite loop
            if ($counter > 10) {
                // Fallback: append timestamp
                $slug = $originalSlug . '-' . time();
                Log::warning('⚠️ Max slug attempts reached, using timestamp', ['slug' => $slug]);
                break;
            }
        }

        return $slug;
    }

    /**
     * Check if barcode already exists
     * 
     * @param string $barcode
     * @return bool
     */
    private function barcodeExists(string $barcode): bool
    {
        return \App\Models\ProductVariant::where('barcode', $barcode)->exists();
    }

    /**
     * Generate a unique barcode for the product variant
     * 
     * @return string Unique barcode (13 digits EAN-13 format)
     */
    private function generateUniqueBarcode(): string
    {
        $maxAttempts = 10;
        $attempt = 0;

        do {
            // Generate 13-digit barcode (EAN-13 format)
            // First 12 digits are random, last digit is check digit
            $first12Digits = str_pad(mt_rand(0, 999999999999), 12, '0', STR_PAD_LEFT);
            
            // Calculate EAN-13 check digit
            $sum = 0;
            for ($i = 0; $i < 12; $i++) {
                $digit = (int)$first12Digits[$i];
                $sum += ($i % 2 === 0) ? $digit : $digit * 3;
            }
            $checkDigit = (10 - ($sum % 10)) % 10;
            
            $barcode = $first12Digits . $checkDigit;
            $attempt++;

            if (!$this->barcodeExists($barcode)) {
                return $barcode;
            }

            Log::info('🔄 Barcode collision, generating new one', ['attempt' => $attempt]);

        } while ($attempt < $maxAttempts);

        // Fallback: use timestamp-based barcode
        $fallbackBarcode = '99' . str_pad(time() % 10000000000, 11, '0', STR_PAD_LEFT);
        Log::warning('⚠️ Max barcode attempts reached, using timestamp-based', ['barcode' => $fallbackBarcode]);
        
        return $fallbackBarcode;
    }
}

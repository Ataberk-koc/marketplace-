# Trendyol Attribute Mapping System - Usage Guide

## Overview
This system maps local product attributes (options/values) to Trendyol marketplace attribute IDs when sending products to Trendyol API.

**Key Principle:** Send Trendyol IDs (e.g., `203` for "Red"), NOT text values (e.g., "Kırmızı")

---

## System Architecture

### Database Tables
1. **`options`** - Local attribute definitions (e.g., "Renk", "Beden")
2. **`option_values`** - Local attribute values (e.g., "Kırmızı", "Mavi", "S", "M")
3. **`trendyol_attribute_mappings`** - Maps local values to Trendyol IDs
   ```sql
   - option_id → trendyol_attribute_id
   - option_value_id → trendyol_value_id
   - trendyol_category_id (for category-specific mappings)
   ```
4. **`brand_mappings`** - Maps local brands to Trendyol brand IDs
5. **`category_mappings`** - Maps local categories to Trendyol category IDs
6. **`product_variants`** - Stores `option_values` as JSON array

### Models
- `TrendyolAttributeMapping` - Attribute mapping model
- `BrandMapping` - Brand mapping model
- `CategoryMapping` - Category mapping model
- `Product` - Product model (with variants)
- `ProductVariant` - Product variant model (with option_values)

### Service Layer
- `TrendyolService::prepareProductPayloadWithMappings()` - Main method
- `TrendyolService::resolveBrandMapping()` - Brand resolution
- `TrendyolService::resolveCategoryMapping()` - Category resolution
- `TrendyolService::resolveAttributeMappings()` - Attribute resolution (CORE)

---

## Product Variant Structure

### ProductVariant.option_values JSON Format
```json
[
  {
    "option_id": 1,
    "option_name": "Renk",
    "value_id": 5,
    "value": "Kırmızı"
  },
  {
    "option_id": 2,
    "option_name": "Beden",
    "value_id": 8,
    "value": "M"
  }
]
```

**Note:** This structure is automatically created when using the modern product creation page (Alpine.js + Cartesian product).

---

## Usage Flow

### Step 1: Create Product with Variants
```php
// In product create form (already implemented in create.blade.php)
// System automatically generates variants with option_values JSON
```

### Step 2: Map Attributes to Trendyol
```
Visit: /admin/trendyol/mapping
- Select Trendyol category
- Map local options to Trendyol attributes
- Map local values to Trendyol values
- Save mappings
```

### Step 3: Send Product to Trendyol
```php
// In controller
use App\Services\TrendyolService;
use App\Models\Product;

public function sendProduct(Product $product)
{
    $trendyolService = app(TrendyolService::class);
    
    // Load relationships
    $product->load(['variants', 'brand', 'category']);
    
    // Prepare payload with mappings
    $payloadResult = $trendyolService->prepareProductPayloadWithMappings($product);
    
    // Check for mapping errors
    if (!$payloadResult['success']) {
        return response()->json([
            'error' => 'Mapping errors',
            'details' => $payloadResult['errors']
        ], 400);
    }
    
    // Send to Trendyol
    $result = $trendyolService->createProducts($payloadResult['items']);
    
    if ($result['success']) {
        return response()->json([
            'message' => 'Product sent successfully',
            'batchRequestId' => $result['batchRequestId'],
            'itemCount' => count($payloadResult['items'])
        ]);
    }
    
    return response()->json([
        'error' => 'Failed to send product',
        'details' => $result['message']
    ], 500);
}
```

---

## Mapping Resolution Logic

### Brand Mapping
```php
// Finds: brand_mappings WHERE brand_id = X AND is_active = true
// Returns: trendyol_brand_id
```

### Category Mapping
```php
// Finds: category_mappings WHERE category_id = X AND is_active = true
// Returns: trendyol_category_id
```

### Attribute Mapping (CORE)
```php
// For each variant.option_values:
//   Finds: trendyol_attribute_mappings WHERE
//          option_id = X AND
//          option_value_id = Y AND
//          is_active = true AND
//          (trendyol_category_id = Z OR trendyol_category_id IS NULL)
//   ORDER BY: trendyol_category_id IS NULL (category-specific first)
//   Returns: trendyol_attribute_id, trendyol_value_id
```

### Error Handling
- **Unmapped Brand:** Returns error, stops processing
- **Unmapped Category:** Returns error, stops processing
- **Unmapped Attribute:** Logs warning, adds to errors array, skips variant
- **No Valid Variants:** Returns error with details

---

## Trendyol API Payload Format

### Example Payload (After Mapping)
```json
{
  "items": [
    {
      "barcode": "PROD-001-VAR-1",
      "title": "Premium T-Shirt",
      "productMainId": "PROD-001",
      "brandId": 12345,           // ← From brand_mappings
      "categoryId": 67890,        // ← From category_mappings
      "quantity": 100,
      "stockCode": "PROD-001-VAR-1",
      "dimensionalWeight": 0,
      "description": "High quality cotton t-shirt",
      "currencyType": "TRY",
      "listPrice": 299.90,
      "salePrice": 249.90,
      "vatRate": 10,
      "cargoCompanyId": 10,
      "images": [
        {"url": "https://example.com/images/product.jpg"}
      ],
      "attributes": [
        {
          "attributeId": 203,     // ← From trendyol_attribute_mappings (Renk)
          "attributeValueId": 456 // ← From trendyol_attribute_mappings (Kırmızı)
        },
        {
          "attributeId": 204,     // ← From trendyol_attribute_mappings (Beden)
          "attributeValueId": 789 // ← From trendyol_attribute_mappings (M)
        }
      ]
    }
  ]
}
```

**CRITICAL:** Only Trendyol IDs are sent, never text values!

---

## Logging

All mapping resolutions are logged for debugging:

```php
// Success logs
Log::info('TrendyolService: Brand mapping çözümlendi', [
    'brand_id' => 1,
    'trendyol_brand_id' => 12345
]);

Log::info('TrendyolService: Attribute mapping çözümlendi', [
    'option_id' => 1,
    'option_name' => 'Renk',
    'value_id' => 5,
    'value' => 'Kırmızı',
    'trendyol_attribute_id' => 203,
    'trendyol_value_id' => 456
]);

// Warning logs (unmapped)
Log::warning('TrendyolService: Attribute mapping bulunamadı', [
    'option_id' => 1,
    'option_name' => 'Renk',
    'value_id' => 5,
    'value' => 'Kırmızı',
    'trendyol_category_id' => 67890
]);
```

---

## Testing

### Manual Test
1. Create a product with variants (use `/admin/products/create`)
2. Map all attributes at `/admin/trendyol/mapping`
3. Send product via `/admin/trendyol/products/{mapping}/send`
4. Check logs at `storage/logs/laravel.log`

### Check Mapping Coverage
```sql
-- Find unmapped attributes
SELECT 
    ov.id AS value_id,
    o.name AS option_name,
    ov.value AS value_name,
    COUNT(tam.id) AS mapping_count
FROM option_values ov
JOIN options o ON ov.option_id = o.id
LEFT JOIN trendyol_attribute_mappings tam ON tam.option_value_id = ov.id
GROUP BY ov.id, o.name, ov.value
HAVING mapping_count = 0;
```

---

## Migration from Old System

### Old Approach (DEPRECATED)
```php
// Used size_mappings table
// Required manual size mapping
// Only supported size attributes
// Text-based attribute names
```

### New Approach (CURRENT)
```php
// Uses trendyol_attribute_mappings table
// Auto-match feature available
// Supports ALL attribute types (color, size, material, etc.)
// ID-based mapping (Trendyol API standard)
// Category-specific mapping support
// Comprehensive logging
```

### Migration Steps
1. Keep old `size_mappings` for backward compatibility
2. Create mappings in `trendyol_attribute_mappings` table
3. Use `prepareProductPayloadWithMappings()` for new products
4. Old `formatProductForTrendyol()` marked as deprecated

---

## Troubleshooting

### Issue: "Brand mapping bulunamadı"
**Solution:** Map brand at `/admin/trendyol/brands` (not yet implemented) or manually insert:
```sql
INSERT INTO brand_mappings (brand_id, trendyol_brand_id, is_active) 
VALUES (1, 12345, 1);
```

### Issue: "Category mapping bulunamadı"
**Solution:** Map category at `/admin/trendyol/categories` (not yet implemented) or manually insert:
```sql
INSERT INTO category_mappings (category_id, trendyol_category_id, is_active) 
VALUES (1, 67890, 1);
```

### Issue: "Attribute mapping eksik"
**Solution:** Visit `/admin/trendyol/mapping` and map the attribute/value.

### Issue: "Hiçbir variant için payload oluşturulamadı"
**Solution:** 
1. Check if product has variants
2. Check if variants have `option_values` JSON
3. Ensure all option_values are mapped

---

## API Reference

### TrendyolService::prepareProductPayloadWithMappings($product)
**Parameters:**
- `$product` (Product) - Product model instance with loaded relationships

**Returns:**
```php
[
    'success' => bool,
    'items' => array,   // Trendyol API items array
    'errors' => array   // Error messages if success=false
]
```

**Example:**
```php
$product = Product::with(['variants', 'brand', 'category'])->find(1);
$result = $trendyolService->prepareProductPayloadWithMappings($product);

if ($result['success']) {
    $apiResult = $trendyolService->createProducts($result['items']);
} else {
    // Handle errors: $result['errors']
}
```

---

## Future Enhancements

1. **Bulk Auto-Match:** Run auto-match for all products
2. **Brand Mapping UI:** Create `/admin/trendyol/brands/mapping` page
3. **Category Mapping UI:** Create `/admin/trendyol/categories/mapping` page
4. **Mapping Statistics Dashboard:** Show mapping coverage per category
5. **Trendyol API Sync:** Fetch latest attributes/values from Trendyol
6. **Mapping Import/Export:** CSV import/export for bulk operations

---

## Support

For issues or questions:
- Check logs at `storage/logs/laravel.log`
- Review mapping interface at `/admin/trendyol/mapping`
- Validate database records in `trendyol_attribute_mappings` table

---

**Last Updated:** 2025-01-13
**System Version:** 2.0 (New Mapping System)

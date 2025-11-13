<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Option;
use App\Models\OptionValue;
use App\Models\Category;
use App\Models\TrendyolAttributeMapping;
use App\Services\TrendyolService;
use Illuminate\Http\Request;

class TrendyolMappingController extends Controller
{
    protected $trendyolService;

    public function __construct(TrendyolService $trendyolService)
    {
        $this->trendyolService = $trendyolService;
    }

    /**
     * Main mapping dashboard
     */
    public function index()
    {
        // Get all local options with their values
        $options = Option::with(['values' => function($query) {
            $query->where('is_active', true)->orderBy('sort_order');
        }])
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->get();

        // Get categories that have Trendyol mapping (for category-specific mappings)
        $categories = Category::whereHas('categoryMapping')
            ->with('categoryMapping')
            ->orderBy('name')
            ->get();

        // Statistics
        $stats = [
            'total_options' => Option::where('is_active', true)->count(),
            'total_values' => OptionValue::where('is_active', true)->count(),
            'mapped_values' => TrendyolAttributeMapping::active()->distinct('option_value_id')->count('option_value_id'),
            'unmapped_values' => OptionValue::where('is_active', true)->count() - 
                                TrendyolAttributeMapping::active()->distinct('option_value_id')->count('option_value_id'),
        ];

        return view('admin.trendyol.mapping', compact('options', 'categories', 'stats'));
    }

    /**
     * Get Trendyol attributes for a specific category (AJAX)
     */
    public function fetchTrendyolAttributes(Request $request)
    {
        $request->validate([
            'category_id' => 'required|string',
        ]);

        $result = $this->trendyolService->getCategoryAttributes($request->category_id);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Trendyol özellikleri alınamadı: ' . ($result['message'] ?? 'Bilinmeyen hata')
            ], 500);
        }

        $attributes = $result['data']['categoryAttributes'] ?? [];

        // Format for frontend
        $formatted = [];
        foreach ($attributes as $attrGroup) {
            $attr = $attrGroup['attribute'];
            $values = $attrGroup['attributeValues'] ?? [];

            $formatted[] = [
                'id' => $attr['id'],
                'name' => $attr['name'],
                'varianter' => $attrGroup['varianter'] ?? false,
                'required' => $attrGroup['required'] ?? false,
                'values' => array_map(function($val) {
                    return [
                        'id' => $val['id'],
                        'name' => $val['name'],
                    ];
                }, $values)
            ];
        }

        return response()->json([
            'success' => true,
            'attributes' => $formatted
        ]);
    }

    /**
     * Get mappings for a specific option (AJAX)
     */
    public function getOptionMappings(Request $request)
    {
        $request->validate([
            'option_id' => 'required|exists:options,id',
            'category_id' => 'nullable|string',
        ]);

        $optionId = $request->option_id;
        $categoryId = $request->category_id;

        // Get option with values
        $option = Option::with(['values' => function($query) {
            $query->where('is_active', true)->orderBy('sort_order');
        }])->findOrFail($optionId);

        // Get existing mappings
        $mappings = TrendyolAttributeMapping::where('option_id', $optionId)
            ->when($categoryId, function($query) use ($categoryId) {
                return $query->where('trendyol_category_id', $categoryId);
            })
            ->get()
            ->keyBy('option_value_id');

        // Format response
        $values = $option->values->map(function($value) use ($mappings) {
            $mapping = $mappings->get($value->id);
            
            return [
                'id' => $value->id,
                'value' => $value->value,
                'color_code' => $value->color_code,
                'is_mapped' => $mapping ? true : false,
                'trendyol_attribute_id' => $mapping->trendyol_attribute_id ?? null,
                'trendyol_attribute_name' => $mapping->trendyol_attribute_name ?? null,
                'trendyol_value_id' => $mapping->trendyol_value_id ?? null,
                'trendyol_value_name' => $mapping->trendyol_value_name ?? null,
            ];
        });

        return response()->json([
            'success' => true,
            'option' => [
                'id' => $option->id,
                'name' => $option->name,
                'type' => $option->type,
            ],
            'values' => $values
        ]);
    }

    /**
     * Save a single mapping (AJAX)
     */
    public function saveMapping(Request $request)
    {
        $request->validate([
            'option_id' => 'required|exists:options,id',
            'option_value_id' => 'required|exists:option_values,id',
            'trendyol_attribute_id' => 'required|string',
            'trendyol_attribute_name' => 'required|string',
            'trendyol_value_id' => 'required|string',
            'trendyol_value_name' => 'required|string',
            'trendyol_category_id' => 'nullable|string',
        ]);

        try {
            $mapping = TrendyolAttributeMapping::updateOrCreate(
                [
                    'option_value_id' => $request->option_value_id,
                    'trendyol_category_id' => $request->trendyol_category_id ?? 'global',
                ],
                [
                    'option_id' => $request->option_id,
                    'trendyol_attribute_id' => $request->trendyol_attribute_id,
                    'trendyol_attribute_name' => $request->trendyol_attribute_name,
                    'trendyol_value_id' => $request->trendyol_value_id,
                    'trendyol_value_name' => $request->trendyol_value_name,
                    'is_active' => true,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Eşleştirme kaydedildi!',
                'mapping' => $mapping
            ]);

        } catch (\Exception $e) {
            \Log::error('Mapping save error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Kayıt başarısız: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a mapping (AJAX)
     */
    public function deleteMapping(Request $request)
    {
        $request->validate([
            'option_value_id' => 'required|exists:option_values,id',
            'trendyol_category_id' => 'nullable|string',
        ]);

        $deleted = TrendyolAttributeMapping::where('option_value_id', $request->option_value_id)
            ->when($request->trendyol_category_id, function($query) use ($request) {
                return $query->where('trendyol_category_id', $request->trendyol_category_id);
            })
            ->delete();

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Eşleştirme silindi!'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Eşleştirme bulunamadı!'
        ], 404);
    }

    /**
     * Bulk mapping: Auto-match by name similarity (AJAX)
     */
    public function autoMatch(Request $request)
    {
        $request->validate([
            'option_id' => 'required|exists:options,id',
            'trendyol_category_id' => 'required|string',
            'trendyol_attribute_id' => 'required|string',
            'trendyol_attribute_name' => 'required|string',
            'trendyol_values' => 'required|array',
        ]);

        $option = Option::with('values')->findOrFail($request->option_id);
        $trendyolValues = collect($request->trendyol_values);
        
        $matchedCount = 0;

        foreach ($option->values as $localValue) {
            // Try exact match first
            $match = $trendyolValues->firstWhere('name', $localValue->value);
            
            // If no exact match, try case-insensitive
            if (!$match) {
                $match = $trendyolValues->first(function($tv) use ($localValue) {
                    return strtolower($tv['name']) === strtolower($localValue->value);
                });
            }

            // If still no match, try partial match
            if (!$match) {
                $match = $trendyolValues->first(function($tv) use ($localValue) {
                    return stripos($tv['name'], $localValue->value) !== false ||
                           stripos($localValue->value, $tv['name']) !== false;
                });
            }

            if ($match) {
                TrendyolAttributeMapping::updateOrCreate(
                    [
                        'option_value_id' => $localValue->id,
                        'trendyol_category_id' => $request->trendyol_category_id,
                    ],
                    [
                        'option_id' => $request->option_id,
                        'trendyol_attribute_id' => $request->trendyol_attribute_id,
                        'trendyol_attribute_name' => $request->trendyol_attribute_name,
                        'trendyol_value_id' => $match['id'],
                        'trendyol_value_name' => $match['name'],
                        'is_active' => true,
                    ]
                );
                $matchedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$matchedCount} değer otomatik eşleştirildi!",
            'matched_count' => $matchedCount
        ]);
    }
}

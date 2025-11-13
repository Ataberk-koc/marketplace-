<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\TrendyolCategory;
use App\Models\CategoryMapping;
use App\Models\TrendyolSize;
use App\Services\TrendyolService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Admin kategori yönetimi ve Trendyol eşleştirme controller
 */
class CategoryController extends Controller
{
    protected $trendyolService;

    public function __construct(TrendyolService $trendyolService)
    {
        $this->trendyolService = $trendyolService;
    }

    /**
     * Kategori listesini gösterir
     */
    public function index()
    {
        $categories = Category::with('parent')
            ->withCount('products')
            ->latest()
            ->paginate(25);

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Yeni kategori oluşturma formu
     */
    public function create()
    {
        $parentCategories = Category::whereNull('parent_id')->get();

        return view('admin.categories.create', compact('parentCategories'));
    }

    /**
     * Yeni kategori kaydeder
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'parent_id' => $request->parent_id,
            'is_active' => true,
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori başarıyla oluşturuldu!');
    }

    /**
     * Kategori düzenleme formu
     */
    public function edit(Category $category)
    {
        $parentCategories = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->get();

        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Kategoriyi günceller
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'parent_id' => $request->parent_id,
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori güncellendi!');
    }

    /**
     * Kategoriyi siler
     */
    public function destroy(Category $category)
    {
        $category->delete();

        return back()->with('success', 'Kategori silindi!');
    }

    /**
     * Trendyol kategorilerini senkronize eder
     * Artık veritabanına kaydetmiyor, sadece API'den çekip gösteriyor
     */
    public function syncTrendyolCategories()
    {
        $result = $this->trendyolService->getCategories();

        if (!$result['success']) {
            return back()->with('error', 'Trendyol kategorileri alınamadı: ' . $result['message']);
        }

        // Artık veritabanına kaydetmiyoruz, sadece session'a alıyoruz
        session(['trendyol_categories' => $result['data']['categories'] ?? []]);

        $count = count($result['data']['categories'] ?? []);
        return back()->with('success', "{$count} Trendyol kategorisi yüklendi!");
    }

    /**
     * Kategori eşleştirme sayfası
     */
    public function mapping(Category $category)
    {
        // Trendyol kategorilerini API'den çek (veya session'dan al)
        $trendyolCategories = session('trendyol_categories', []);
        
        // Eğer session boşsa API'den çek
        if (empty($trendyolCategories)) {
            $result = $this->trendyolService->getCategories();
            if ($result['success']) {
                $trendyolCategories = $result['data']['categories'] ?? [];
                session(['trendyol_categories' => $trendyolCategories]);
            }
        }

        $currentMapping = $category->trendyolMapping;

        return view('admin.categories.mapping', compact('category', 'trendyolCategories', 'currentMapping'));
    }

    /**
     * Kategori eşleştirmesini kaydeder
     */
    public function saveMapping(Request $request, Category $category)
    {
        // Eğer boş gönderilmişse eşleştirmeyi kaldır
        if (empty($request->trendyol_category_id)) {
            CategoryMapping::where('category_id', $category->id)->delete();
            return redirect()->route('admin.categories.index')
                ->with('success', 'Kategori eşleştirmesi kaldırıldı!');
        }

        $request->validate([
            'trendyol_category_id' => 'required|string',
            'trendyol_category_name' => 'nullable|string',
        ]);

        // Eğer category name boşsa, session'dan çek
        $categoryName = $request->trendyol_category_name;
        if (empty($categoryName)) {
            $trendyolCategories = session('trendyol_categories', []);
            foreach ($trendyolCategories as $tCat) {
                $catId = is_array($tCat) ? $tCat['id'] : $tCat->id;
                if ($catId == $request->trendyol_category_id) {
                    $categoryName = is_array($tCat) 
                        ? ($tCat['path'] ?? $tCat['name']) 
                        : ($tCat->path ?? $tCat->name);
                    break;
                }
            }
        }

        CategoryMapping::updateOrCreate(
            ['category_id' => $category->id],
            [
                'trendyol_category_id' => $request->trendyol_category_id,
                'trendyol_category_name' => $categoryName ?? 'Kategori Adı Bulunamadı',
                'is_active' => true
            ]
        );

        // ⭐ KRİTİK: Kategori eşleştirildiğinde Trendyol'dan özellikleri çek ve kaydet
        try {
            $attributesResult = $this->trendyolService->getCategoryAttributes($request->trendyol_category_id);
            
            if ($attributesResult['success'] && isset($attributesResult['data']['categoryAttributes'])) {
                $attributes = $attributesResult['data']['categoryAttributes'];
                $savedCount = 0;
                
                foreach ($attributes as $attrGroup) {
                    $attribute = $attrGroup['attribute'];
                    $attributeValues = $attrGroup['attributeValues'] ?? [];
                    
                    foreach ($attributeValues as $value) {
                        TrendyolSize::updateOrCreate(
                            [
                                'trendyol_attribute_id' => (string) $attribute['id'],
                                'trendyol_attribute_value_id' => (string) $value['id']
                            ],
                            [
                                'attribute_name' => $attribute['name'], // "Beden", "Renk", "Kumaş"
                                'value_name' => $value['name'], // "S", "M", "L", "Kırmızı"
                                'trendyol_category_id' => $request->trendyol_category_id
                            ]
                        );
                        $savedCount++;
                    }
                }
                
                return redirect()->route('admin.categories.index')
                    ->with('success', "Kategori eşleştirmesi kaydedildi! {$savedCount} özellik değeri Trendyol'dan çekildi.");
            }
        } catch (\Exception $e) {
            \Log::error('Trendyol özellikleri kaydedilemedi: ' . $e->getMessage());
        }

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori eşleştirmesi kaydedildi!');
    }

    /**
     * Kategori eşleştirmesini siler
     */
    public function deleteMapping(Category $category)
    {
        CategoryMapping::where('category_id', $category->id)->delete();
        
        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori eşleştirmesi silindi!');
    }
}

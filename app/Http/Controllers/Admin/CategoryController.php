<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\TrendyolCategory;
use App\Models\CategoryMapping;
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
     */
    public function syncTrendyolCategories()
    {
        $result = $this->trendyolService->getCategories();

        if (!$result['success']) {
            return back()->with('error', 'Trendyol kategorileri alınamadı: ' . $result['message']);
        }

        $syncCount = 0;
        foreach ($result['data']['categories'] ?? [] as $trendyolCategory) {
            TrendyolCategory::updateOrCreate(
                ['trendyol_category_id' => $trendyolCategory['id']],
                [
                    'name' => $trendyolCategory['name'],
                    'parent_id' => $trendyolCategory['parentId'] ?? null,
                ]
            );
            $syncCount++;
        }

        return back()->with('success', "{$syncCount} Trendyol kategorisi senkronize edildi!");
    }

    /**
     * Kategori eşleştirme sayfası
     */
    public function mapping(Category $category)
    {
        $trendyolCategories = TrendyolCategory::all();
        $currentMapping = $category->trendyolMapping;

        return view('admin.categories.mapping', compact('category', 'trendyolCategories', 'currentMapping'));
    }

    /**
     * Kategori eşleştirmesini kaydeder
     */
    public function saveMapping(Request $request, Category $category)
    {
        $request->validate([
            'trendyol_category_id' => 'required|exists:trendyol_categories,id',
        ]);

        CategoryMapping::updateOrCreate(
            ['category_id' => $category->id],
            ['trendyol_category_id' => $request->trendyol_category_id, 'is_active' => true]
        );

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori eşleştirmesi kaydedildi!');
    }
}

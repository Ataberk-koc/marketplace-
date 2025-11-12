<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\TrendyolBrand;
use App\Models\BrandMapping;
use App\Services\TrendyolService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Admin marka yönetimi ve Trendyol eşleştirme controller
 */
class BrandController extends Controller
{
    protected $trendyolService;

    public function __construct(TrendyolService $trendyolService)
    {
        $this->trendyolService = $trendyolService;
    }

    /**
     * Marka listesini gösterir
     */
    public function index()
    {
        $brands = Brand::with('trendyolMapping.trendyolBrand')
            ->withCount('products')
            ->latest()
            ->paginate(25);

        return view('admin.brands.index', compact('brands'));
    }

    /**
     * Yeni marka oluşturma formu
     */
    public function create()
    {
        return view('admin.brands.create');
    }

    /**
     * Yeni marka kaydeder
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:brands',
            'description' => 'nullable|string',
        ]);

        Brand::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'is_active' => true,
        ]);

        return redirect()->route('admin.brands.index')
            ->with('success', 'Marka başarıyla oluşturuldu!');
    }

    /**
     * Marka düzenleme formu
     */
    public function edit(Brand $brand)
    {
        return view('admin.brands.edit', compact('brand'));
    }

    /**
     * Markayı günceller
     */
    public function update(Request $request, Brand $brand)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,' . $brand->id,
            'description' => 'nullable|string',
        ]);

        $brand->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        return redirect()->route('admin.brands.index')
            ->with('success', 'Marka güncellendi!');
    }

    /**
     * Markayı siler
     */
    public function destroy(Brand $brand)
    {
        $brand->delete();

        return back()->with('success', 'Marka silindi!');
    }

    /**
     * Trendyol markalarını senkronize eder
     */
    public function syncTrendyolBrands()
    {
        $result = $this->trendyolService->getBrands();

        if (!$result['success']) {
            return back()->with('error', 'Trendyol markaları alınamadı: ' . $result['message']);
        }

        $syncCount = 0;
        foreach ($result['data']['brands'] ?? [] as $trendyolBrand) {
            TrendyolBrand::updateOrCreate(
                ['trendyol_brand_id' => $trendyolBrand['id']],
                ['name' => $trendyolBrand['name']]
            );
            $syncCount++;
        }

        return back()->with('success', "{$syncCount} Trendyol markası senkronize edildi!");
    }

    /**
     * Marka eşleştirme sayfası
     */
    public function mapping(Brand $brand)
    {
        $trendyolBrands = TrendyolBrand::all();
        $currentMapping = $brand->trendyolMapping;

        return view('admin.brands.mapping', compact('brand', 'trendyolBrands', 'currentMapping'));
    }

    /**
     * Marka eşleştirmesini kaydeder
     */
    public function saveMapping(Request $request, Brand $brand)
    {
        $request->validate([
            'trendyol_brand_id' => 'required|exists:trendyol_brands,id',
        ]);

        BrandMapping::updateOrCreate(
            ['brand_id' => $brand->id],
            ['trendyol_brand_id' => $request->trendyol_brand_id, 'is_active' => true]
        );

        return redirect()->route('admin.brands.index')
            ->with('success', 'Marka eşleştirmesi kaydedildi!');
    }
}

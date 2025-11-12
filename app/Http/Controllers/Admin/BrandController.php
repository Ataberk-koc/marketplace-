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
     * Artık veritabanına kaydetmiyor, sadece API'den çekip gösteriyor
     */
    public function syncTrendyolBrands()
    {
        $result = $this->trendyolService->getBrands();

        if (!$result['success']) {
            return back()->with('error', 'Trendyol markaları alınamadı: ' . $result['message']);
        }

        // Artık veritabanına kaydetmiyoruz, sadece session'a alıyoruz
        session(['trendyol_brands' => $result['data']['brands'] ?? []]);

        $count = count($result['data']['brands'] ?? []);
        return back()->with('success', "{$count} Trendyol markası yüklendi!");
    }

    /**
     * Marka eşleştirme sayfası
     */
    public function mapping(Brand $brand)
    {
        // Trendyol markalarını API'den çek (veya session'dan al)
        $trendyolBrands = session('trendyol_brands', []);
        
        // Eğer session boşsa API'den çek
        if (empty($trendyolBrands)) {
            $result = $this->trendyolService->getBrands();
            if ($result['success']) {
                $trendyolBrands = $result['data']['brands'] ?? [];
                session(['trendyol_brands' => $trendyolBrands]);
            }
        }

        $currentMapping = $brand->trendyolMapping;

        return view('admin.brands.mapping', compact('brand', 'trendyolBrands', 'currentMapping'));
    }

    /**
     * Marka eşleştirmesini kaydeder
     */
    public function saveMapping(Request $request, Brand $brand)
    {
        // Eğer boş gönderilmişse eşleştirmeyi kaldır
        if (empty($request->trendyol_brand_id)) {
            BrandMapping::where('brand_id', $brand->id)->delete();
            return redirect()->route('admin.brands.index')
                ->with('success', 'Marka eşleştirmesi kaldırıldı!');
        }

        $request->validate([
            'trendyol_brand_id' => 'required|string',
            'trendyol_brand_name' => 'nullable|string',
        ]);

        BrandMapping::updateOrCreate(
            ['brand_id' => $brand->id],
            [
                'trendyol_brand_id' => $request->trendyol_brand_id, // Trendyol'un kendi ID'si
                'trendyol_brand_name' => $request->trendyol_brand_name,
                'is_active' => true
            ]
        );

        return redirect()->route('admin.brands.index')
            ->with('success', 'Marka eşleştirmesi kaydedildi!');
    }

    /**
     * Marka eşleştirmesini siler
     */
    public function deleteMapping(Brand $brand)
    {
        BrandMapping::where('brand_id', $brand->id)->delete();
        
        return redirect()->route('admin.brands.index')
            ->with('success', 'Marka eşleştirmesi silindi!');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Size;
use App\Models\TrendyolSize;
use App\Models\SizeMapping;
use App\Services\TrendyolService;
use Illuminate\Http\Request;

/**
 * Admin beden yönetimi ve Trendyol eşleştirme controller
 */
class SizeController extends Controller
{
    protected $trendyolService;

    public function __construct(TrendyolService $trendyolService)
    {
        $this->trendyolService = $trendyolService;
    }

    /**
     * Beden listesini gösterir
     */
    public function index()
    {
        $sizes = Size::withCount('products')->latest()->paginate(25);

        return view('admin.sizes.index', compact('sizes'));
    }

    /**
     * Yeni beden oluşturma formu
     */
    public function create()
    {
        return view('admin.sizes.create');
    }

    /**
     * Yeni beden kaydeder
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:sizes',
        ]);

        Size::create([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.sizes.index')
            ->with('success', 'Beden eklendi!');
    }

    /**
     * Beden düzenleme formu
     */
    public function edit(Size $size)
    {
        return view('admin.sizes.edit', compact('size'));
    }

    /**
     * Bedeni günceller
     */
    public function update(Request $request, Size $size)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:sizes,name,' . $size->id,
        ]);

        $size->update([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.sizes.index')
            ->with('success', 'Beden güncellendi!');
    }

    /**
     * Bedeni siler
     */
    public function destroy(Size $size)
    {
        $size->delete();

        return back()->with('success', 'Beden silindi!');
    }

    /**
     * Trendyol bedenlerini senkronize eder
     */
    public function syncTrendyolSizes()
    {
        // Trendyol'dan kategori bazlı beden listesi çekilir
        // Basitleştirilmiş örnek - gerçekte kategori ID'sine göre çekilir
        $result = $this->trendyolService->getSizeAttributes();

        if (!$result['success']) {
            return back()->with('error', 'Trendyol bedenleri alınamadı: ' . $result['message']);
        }

        $syncCount = 0;
        foreach ($result['data']['attributes'] ?? [] as $sizeAttr) {
            TrendyolSize::updateOrCreate(
                ['trendyol_size_id' => $sizeAttr['id']],
                [
                    'name' => $sizeAttr['name'],
                    'attribute_type' => $sizeAttr['attributeType'] ?? 'size',
                ]
            );
            $syncCount++;
        }

        return back()->with('success', "{$syncCount} Trendyol bedeni senkronize edildi!");
    }

    /**
     * Beden eşleştirme sayfası
     */
    public function mapping(Size $size)
    {
        $trendyolSizes = TrendyolSize::orderBy('name')->get();
        $currentMapping = $size->trendyolMapping;

        return view('admin.sizes.mapping', compact('size', 'trendyolSizes', 'currentMapping'));
    }

    /**
     * Beden eşleştirmesini kaydeder
     */
    public function saveMapping(Request $request, Size $size)
    {
        // Eğer boş gönderilmişse eşleştirmeyi kaldır
        if (empty($request->trendyol_size_id)) {
            SizeMapping::where('size_id', $size->id)->delete();
            return redirect()->route('admin.sizes.index')
                ->with('success', 'Beden eşleştirmesi kaldırıldı!');
        }

        $request->validate([
            'trendyol_size_id' => 'required|exists:trendyol_sizes,id',
        ]);

        SizeMapping::updateOrCreate(
            ['size_id' => $size->id],
            ['trendyol_size_id' => $request->trendyol_size_id, 'is_active' => true]
        );

        return redirect()->route('admin.sizes.index')
            ->with('success', 'Beden eşleştirmesi kaydedildi!');
    }

    /**
     * Toplu eşleştirme sayfası
     */
    public function bulkMapping()
    {
        $sizes = Size::with('trendyolMapping')->get();
        $trendyolSizes = TrendyolSize::orderBy('name')->get();

        return view('admin.sizes.bulk-mapping', compact('sizes', 'trendyolSizes'));
    }

    /**
     * Toplu eşleştirmeyi kaydeder
     */
    public function saveBulkMapping(Request $request)
    {
        $mappings = $request->input('mappings', []);
        $savedCount = 0;

        foreach ($mappings as $sizeId => $trendyolSizeId) {
            if (!empty($trendyolSizeId)) {
                SizeMapping::updateOrCreate(
                    ['size_id' => $sizeId],
                    ['trendyol_size_id' => $trendyolSizeId, 'is_active' => true]
                );
                $savedCount++;
            }
        }

        return redirect()->route('admin.sizes.index')
            ->with('success', "{$savedCount} beden eşleştirmesi kaydedildi!");
    }
}

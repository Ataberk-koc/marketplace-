<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    /**
     * Stok listesi
     */
    public function index(Request $request)
    {
        $query = ProductVariant::with(['product', 'product.brand', 'product.category']);

        // Arama
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhereHas('product', function($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Stok durumu filtresi
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'low':
                    $query->whereRaw('(stock_quantity - reserved_quantity) <= low_stock_threshold');
                    break;
                case 'out':
                    $query->whereRaw('(stock_quantity - reserved_quantity) <= 0');
                    break;
                case 'available':
                    $query->whereRaw('(stock_quantity - reserved_quantity) > low_stock_threshold');
                    break;
            }
        }

        $variants = $query->paginate(50);

        return view('admin.stock.index', compact('variants'));
    }

    /**
     * Stok hareket geçmişi
     */
    public function movements(ProductVariant $variant)
    {
        $movements = $variant->stockMovements()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        return view('admin.stock.movements', compact('variant', 'movements'));
    }

    /**
     * Stok giriş formu
     */
    public function createMovement(Request $request)
    {
        $variant = null;
        if ($request->filled('variant_id')) {
            $variant = ProductVariant::findOrFail($request->variant_id);
        }

        return view('admin.stock.create-movement', compact('variant'));
    }

    /**
     * Stok hareketi kaydet
     */
    public function storeMovement(Request $request)
    {
        $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $variant = ProductVariant::findOrFail($request->product_variant_id);

            // Miktar hesaplama
            $quantity = $request->quantity;
            if ($request->type === 'out') {
                $quantity = -1 * abs($quantity);
            } elseif ($request->type === 'adjustment') {
                // Düzeltme: mevcut stok ile hedef stok arasındaki fark
                $quantity = $request->quantity - $variant->stock_quantity;
            }

            // Stok güncelleme
            $newStock = max(0, $variant->stock_quantity + $quantity);
            $variant->stock_quantity = $newStock;
            $variant->save();

            // Hareket kaydı
            StockMovement::create([
                'product_variant_id' => $variant->id,
                'type' => $request->type,
                'quantity' => $quantity,
                'balance_after' => $newStock,
                'note' => $request->note,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return redirect()
                ->route('admin.stock.index')
                ->with('success', 'Stok hareketi başarıyla kaydedildi.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Stok hareketi kaydedilemedi: ' . $e->getMessage()]);
        }
    }

    /**
     * Toplu stok güncelleme sayfası
     */
    public function bulkUpdate(Request $request)
    {
        $variants = ProductVariant::with(['product'])
            ->where('is_active', true)
            ->orderBy('sku')
            ->get();

        return view('admin.stock.bulk-update', compact('variants'));
    }

    /**
     * Toplu stok güncelleme kaydet
     */
    public function storeBulkUpdate(Request $request)
    {
        $request->validate([
            'stocks' => 'required|array',
            'stocks.*.variant_id' => 'required|exists:product_variants,id',
            'stocks.*.quantity' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->stocks as $stock) {
                $variant = ProductVariant::find($stock['variant_id']);
                if ($variant && $variant->stock_quantity != $stock['quantity']) {
                    $diff = $stock['quantity'] - $variant->stock_quantity;
                    
                    $variant->stock_quantity = $stock['quantity'];
                    $variant->save();

                    StockMovement::create([
                        'product_variant_id' => $variant->id,
                        'type' => 'adjustment',
                        'quantity' => $diff,
                        'balance_after' => $stock['quantity'],
                        'note' => 'Toplu stok güncelleme',
                        'user_id' => auth()->id(),
                    ]);
                }
            }

            DB::commit();
            return redirect()
                ->route('admin.stock.index')
                ->with('success', 'Stoklar başarıyla güncellendi.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Stok güncellenemedi: ' . $e->getMessage()]);
        }
    }
}

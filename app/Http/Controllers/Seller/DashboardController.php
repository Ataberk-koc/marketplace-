<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

/**
 * Satıcı dashboard controller
 */
class DashboardController extends Controller
{
    /**
     * Satıcı dashboard sayfasını gösterir
     */
    public function index()
    {
        $sellerId = auth()->id();

        // İstatistikler
        $stats = [
            'products' => Product::where('user_id', $sellerId)->count(),
            'orders' => Order::whereHas('items', function($q) use ($sellerId) {
                $q->where('seller_id', $sellerId);
            })->count(),
            'revenue' => OrderItem::where('seller_id', $sellerId)
                ->whereHas('order', function($q) {
                    $q->where('status', 'completed');
                })->sum('total'),
        ];

        // Son satışlar
        $recentOrders = Order::whereHas('items', function($q) use ($sellerId) {
            $q->where('seller_id', $sellerId);
        })
        ->with(['user', 'items'])
        ->withCount('items')
        ->latest()
        ->take(10)
        ->get();

        // Ürün istatistikleri
        $productStats = [
            'in_stock' => Product::where('user_id', $sellerId)->where('stock_quantity', '>', 10)->count(),
            'low_stock' => Product::where('user_id', $sellerId)->whereBetween('stock_quantity', [1, 10])->count(),
            'out_of_stock' => Product::where('user_id', $sellerId)->where('stock_quantity', 0)->count(),
            'trendyol_sent' => Product::where('user_id', $sellerId)->whereHas('trendyolMapping')->count(),
            'trendyol_not_sent' => Product::where('user_id', $sellerId)->whereDoesntHave('trendyolMapping')->count(),
        ];

        return view('seller.dashboard', compact('stats', 'recentOrders', 'productStats'));
    }
}

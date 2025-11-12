<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Http\Request;

/**
 * Satıcı sipariş yönetimi controller
 */
class OrderController extends Controller
{
    /**
     * Satıcının satışlarını listeler
     */
    public function index(Request $request)
    {
        $sellerId = auth()->id();
        
        $query = \App\Models\Order::whereHas('items', function($q) use ($sellerId) {
            $q->where('seller_id', $sellerId);
        })->with(['user', 'items']);

        // Sipariş durumu filtresi
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Tarih filtresi
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $orders = $query->latest()->paginate(25);

        return view('seller.orders.index', compact('orders'));
    }

    /**
     * Satış detayını gösterir
     */
    public function show(\App\Models\Order $order)
    {
        $sellerId = auth()->id();
        
        // Satıcının bu siparişte ürünü var mı kontrol et
        if (!$order->items()->where('seller_id', $sellerId)->exists()) {
            abort(403);
        }

        $order->load(['user', 'items.product', 'items.size']);

        return view('seller.orders.show', compact('order'));
    }
}

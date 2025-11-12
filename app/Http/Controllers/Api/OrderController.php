<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

/**
 * API Sipariş Controller
 */
class OrderController extends Controller
{
    /**
     * Kullanıcının siparişlerini döner
     * GET /api/v1/orders
     */
    public function index(Request $request)
    {
        $orders = Order::with('items.product')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return response()->json($orders);
    }

    /**
     * Tek bir siparişin detayını döner
     * GET /api/v1/orders/{id}
     */
    public function show(Request $request, $id)
    {
        $order = Order::with(['items.product', 'items.seller'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json($order);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\NetgsmService;
use Illuminate\Http\Request;

/**
 * Admin sipariş yönetimi controller
 */
class OrderController extends Controller
{
    protected $netgsmService;

    public function __construct(NetgsmService $netgsmService)
    {
        $this->netgsmService = $netgsmService;
    }

    /**
     * Sipariş listesini gösterir
     */
    public function index(Request $request)
    {
        $query = Order::with('user');

        // Durum filtresi
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Ödeme durumu filtresi
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Arama (sipariş numarası veya müşteri adı)
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('order_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function ($userQuery) use ($request) {
                      $userQuery->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $orders = $query->latest()->paginate(25);

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Sipariş detayını gösterir
     */
    public function show(Order $order)
    {
        $order->load(['user', 'items.product', 'items.seller', 'items.size']);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Sipariş durumunu günceller
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $oldStatus = $order->status;
        $order->update(['status' => $request->status]);

        // Durum değişikliği SMS'i gönder
        if ($order->user->phone && $oldStatus !== $request->status) {
            $this->netgsmService->sendOrderStatusSms($order, $request->status);
        }

        return back()->with('success', 'Sipariş durumu güncellendi!');
    }

    /**
     * Ödeme durumunu günceller
     */
    public function updatePaymentStatus(Request $request, Order $order)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,paid,failed,refunded',
        ]);

        $order->update(['payment_status' => $request->payment_status]);

        return back()->with('success', 'Ödeme durumu güncellendi!');
    }
}

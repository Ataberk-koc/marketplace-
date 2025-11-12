<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Ödeme listesi
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items.product.seller'])
            ->whereIn('status', ['completed', 'processing']);

        // Durum filtreleme
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Tarih filtreleme
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Satıcı filtreleme
        if ($request->filled('seller_id')) {
            $query->whereHas('items.product', function($q) use ($request) {
                $q->where('seller_id', $request->seller_id);
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        // İstatistikler
        $stats = [
            'total_pending' => Order::where('payment_status', 'pending')->sum('total'),
            'total_paid' => Order::where('payment_status', 'paid')->sum('total'),
            'total_refunded' => Order::where('payment_status', 'refunded')->sum('total'),
            'pending_count' => Order::where('payment_status', 'pending')->count(),
        ];

        // Satıcılar
        $sellers = User::where('role', 'seller')->get();

        return view('admin.payments.index', compact('orders', 'stats', 'sellers'));
    }

    /**
     * Ödeme detayı
     */
    public function show(Order $order)
    {
        $order->load(['user', 'items.product.seller']);

        // Satıcı bazlı ödeme dağılımı
        $sellerPayments = $order->items->groupBy('product.seller_id')->map(function($items) {
            return [
                'seller' => $items->first()->product->seller,
                'total' => $items->sum(function($item) {
                    return $item->quantity * $item->price;
                }),
                'items' => $items
            ];
        });

        return view('admin.payments.show', compact('order', 'sellerPayments'));
    }

    /**
     * Ödeme durumu güncelle
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,paid,failed,refunded',
        ]);

        $order->update([
            'payment_status' => $request->payment_status,
        ]);

        return redirect()
            ->route('admin.payments.show', $order)
            ->with('success', 'Ödeme durumu güncellendi.');
    }

    /**
     * Satıcı bazlı ödeme raporu
     */
    public function sellerPayments(Request $request)
    {
        $query = User::where('role', 'seller')
            ->with(['products.orderItems.order' => function($q) {
                $q->whereIn('status', ['completed', 'processing']);
            }]);

        // Tarih filtreleme
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            
            $query->with(['products.orderItems' => function($q) use ($startDate, $endDate) {
                $q->whereHas('order', function($q2) use ($startDate, $endDate) {
                    $q2->whereDate('created_at', '>=', $startDate)
                       ->whereDate('created_at', '<=', $endDate);
                });
            }]);
        }

        $sellers = $query->get()->map(function($seller) {
            $totalSales = 0;
            $totalOrders = 0;
            $commissionRate = 0.15; // %15 komisyon

            foreach ($seller->products as $product) {
                foreach ($product->orderItems as $item) {
                    if ($item->order && in_array($item->order->status, ['completed', 'processing'])) {
                        $totalSales += $item->quantity * $item->price;
                        $totalOrders++;
                    }
                }
            }

            $seller->total_sales = $totalSales;
            $seller->commission = $totalSales * $commissionRate;
            $seller->net_payment = $totalSales - $seller->commission;
            $seller->total_orders = $totalOrders;

            return $seller;
        });

        return view('admin.payments.sellers', compact('sellers'));
    }

    /**
     * Ödeme onayı
     */
    public function approve(Order $order)
    {
        if ($order->payment_status !== 'pending') {
            return back()->with('error', 'Bu ödeme zaten işleme alınmış.');
        }

        $order->update([
            'payment_status' => 'paid',
        ]);

        return back()->with('success', 'Ödeme onaylandı.');
    }

    /**
     * İade işlemi
     */
    public function refund(Request $request, Order $order)
    {
        $request->validate([
            'refund_reason' => 'required|string|max:500',
        ]);

        if ($order->payment_status !== 'paid') {
            return back()->with('error', 'Sadece ödenmiş siparişler iade edilebilir.');
        }

        $order->update([
            'payment_status' => 'refunded',
            'status' => 'cancelled',
            'notes' => ($order->notes ?? '') . "\n\nİade Nedeni: " . $request->refund_reason,
        ]);

        return back()->with('success', 'İade işlemi tamamlandı.');
    }
}

<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\NetgsmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Kullanıcı sipariş işlemlerini yöneten controller
 */
class OrderController extends Controller
{
    protected $netgsmService;

    public function __construct(NetgsmService $netgsmService)
    {
        $this->netgsmService = $netgsmService;
    }

    /**
     * Kullanıcının siparişlerini listeler
     */
    public function index()
    {
        $orders = Order::with('items.product')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('user.orders.index', compact('orders'));
    }

    /**
     * Sipariş detayını gösterir
     */
    public function show(Order $order)
    {
        // Kullanıcının kendi siparişi mi kontrol et
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load(['items.product', 'items.size', 'items.seller']);

        return view('user.orders.show', compact('order'));
    }

    /**
     * Ödeme sayfasını gösterir
     */
    public function checkout()
    {
        $cart = Cart::where('user_id', auth()->id())->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Sepetiniz boş!');
        }

        $cartItems = $cart->items()->with(['product', 'size'])->get();

        return view('user.orders.checkout', compact('cart', 'cartItems'));
    }

    /**
     * Siparişi oluşturur
     */
    public function store(Request $request)
    {
        $request->validate([
            'shipping_address' => 'required|string',
            'billing_address' => 'nullable|string',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $cart = Cart::where('user_id', auth()->id())->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Sepetiniz boş!');
        }

        DB::beginTransaction();
        try {
            // Sipariş oluştur
            $subtotal = $cart->items->sum(function ($item) {
                return $item->price * $item->quantity;
            });

            $tax = $subtotal * 0.18; // %18 KDV
            $shippingCost = 25; // Sabit kargo ücreti
            $total = $subtotal + $tax + $shippingCost;

            $order = Order::create([
                'user_id' => auth()->id(),
                'status' => 'pending',
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping_cost' => $shippingCost,
                'total' => $total,
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address ?? $request->shipping_address,
                'notes' => $request->notes,
            ]);

            // Sipariş kalemlerini oluştur
            foreach ($cart->items as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'seller_id' => $cartItem->product->user_id,
                    'size_id' => $cartItem->size_id,
                    'product_name' => $cartItem->product->name,
                    'product_sku' => $cartItem->product->sku,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                    'total' => $cartItem->price * $cartItem->quantity,
                ]);

                // Stoktan düş
                $cartItem->product->decrement('stock_quantity', $cartItem->quantity);
            }

            // Sepeti temizle
            $cart->items()->delete();

            // SMS gönder
            if (auth()->user()->phone) {
                $smsResult = $this->netgsmService->sendOrderConfirmationSms($order);
                
                if ($smsResult['success']) {
                    $order->update([
                        'sms_sent' => true,
                        'sms_sent_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('user.orders.show', $order)
                ->with('success', 'Siparişiniz başarıyla oluşturuldu!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Sipariş oluşturulurken bir hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Siparişi iptal eder
     */
    public function cancel(Order $order)
    {
        // Kullanıcının kendi siparişi mi kontrol et
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        // Sadece bekleyen siparişler iptal edilebilir
        if ($order->status !== 'pending') {
            return back()->with('error', 'Bu sipariş iptal edilemez!');
        }

        DB::beginTransaction();
        try {
            // Stokları geri ekle
            foreach ($order->items as $item) {
                $item->product->increment('stock_quantity', $item->quantity);
            }

            $order->update(['status' => 'cancelled']);

            DB::commit();

            return back()->with('success', 'Sipariş iptal edildi!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Sipariş iptal edilirken bir hata oluştu!');
        }
    }
}

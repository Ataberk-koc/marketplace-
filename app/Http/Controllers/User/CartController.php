<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * Sepet işlemlerini yöneten controller
 */
class CartController extends Controller
{
    /**
     * Sepet sayfasını gösterir
     */
    public function index()
    {
        $cart = $this->getOrCreateCart();
        $cartItems = $cart->items()->with(['product', 'size'])->get();

        return view('user.cart.index', compact('cart', 'cartItems'));
    }

    /**
     * Sepete ürün ekler
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'size_id' => 'nullable|exists:sizes,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        // Stok kontrolü
        if ($product->stock_quantity < $request->quantity) {
            return back()->with('error', 'Yetersiz stok!');
        }

        $cart = $this->getOrCreateCart();

        // Aynı ürün ve beden varsa miktarı artır
        $cartItem = $cart->items()
            ->where('product_id', $request->product_id)
            ->where('size_id', $request->size_id)
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
        } else {
            // Yeni ürün ekle
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $request->product_id,
                'size_id' => $request->size_id,
                'quantity' => $request->quantity,
                'price' => $product->final_price,
            ]);
        }

        return back()->with('success', 'Ürün sepete eklendi!');
    }

    /**
     * Sepetteki ürün miktarını günceller
     */
    public function update(Request $request, CartItem $cartItem)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        // Kullanıcının kendi sepetine ait olup olmadığını kontrol et
        if ($cartItem->cart->user_id !== auth()->id()) {
            abort(403);
        }

        // Stok kontrolü
        if ($cartItem->product->stock_quantity < $request->quantity) {
            return back()->with('error', 'Yetersiz stok!');
        }

        $cartItem->update(['quantity' => $request->quantity]);

        return back()->with('success', 'Miktar güncellendi!');
    }

    /**
     * Sepetten ürün çıkarır
     */
    public function remove(CartItem $cartItem)
    {
        // Kullanıcının kendi sepetine ait olup olmadığını kontrol et
        if ($cartItem->cart->user_id !== auth()->id()) {
            abort(403);
        }

        $cartItem->delete();

        return back()->with('success', 'Ürün sepetten çıkarıldı!');
    }

    /**
     * Sepeti temizler
     */
    public function clear()
    {
        $cart = $this->getOrCreateCart();
        $cart->items()->delete();

        return back()->with('success', 'Sepet temizlendi!');
    }

    /**
     * Kullanıcının sepetini getirir veya oluşturur
     */
    protected function getOrCreateCart()
    {
        return Cart::firstOrCreate([
            'user_id' => auth()->id(),
        ]);
    }
}

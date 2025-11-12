<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Ana raporlar sayfası
     */
    public function index()
    {
        // Genel istatistikler
        $stats = [
            'total_sales' => Order::where('status', 'completed')->sum('total_amount'),
            'total_orders' => Order::count(),
            'total_products' => Product::count(),
            'total_users' => User::where('role', 'user')->count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
        ];

        // Son 30 günün satış grafiği
        $salesChart = Order::where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(30))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        // En çok satan ürünler (top 10)
        $topProducts = Product::select('products.*', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->groupBy('products.id')
            ->orderBy('total_sold', 'desc')
            ->limit(10)
            ->get();

        // Kategori bazlı satışlar
        $categoryStats = Category::select('categories.*', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->groupBy('categories.id')
            ->get();

        return view('admin.reports.index', compact('stats', 'salesChart', 'topProducts', 'categoryStats'));
    }

    /**
     * Satış raporları
     */
    public function sales(Request $request)
    {
        $query = Order::with(['user', 'items.product'])
            ->where('status', 'completed');

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

        // Toplam satış
        $totalSales = $query->sum('total_amount');
        $totalOrders = $query->count();

        // Satıcılar listesi
        $sellers = User::where('role', 'seller')->get();

        return view('admin.reports.sales', compact('orders', 'totalSales', 'totalOrders', 'sellers'));
    }

    /**
     * Ürün raporları
     */
    public function products(Request $request)
    {
        $query = Product::with(['category', 'brand', 'seller']);

        // Kategori filtreleme
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Stok durumu filtreleme
        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') {
                $query->where('stock_quantity', '<=', 10);
            } elseif ($request->stock_status === 'out') {
                $query->where('stock_quantity', 0);
            }
        }

        // Satıcı filtreleme
        if ($request->filled('seller_id')) {
            $query->where('seller_id', $request->seller_id);
        }

        $products = $query->withCount(['orderItems as total_sold' => function($q) {
            $q->select(DB::raw('COALESCE(SUM(quantity), 0)'));
        }])->paginate(20);

        // Kategoriler ve satıcılar
        $categories = Category::all();
        $sellers = User::where('role', 'seller')->get();

        return view('admin.reports.products', compact('products', 'categories', 'sellers'));
    }

    /**
     * Kategori bazlı rapor
     */
    public function categories()
    {
        $categories = Category::withCount('products')
            ->with(['products' => function($q) {
                $q->withCount(['orderItems as total_sold' => function($q2) {
                    $q2->select(DB::raw('COALESCE(SUM(quantity), 0)'));
                }]);
            }])
            ->get()
            ->map(function($category) {
                $category->total_sold = $category->products->sum('total_sold');
                $category->total_revenue = $category->products->sum(function($product) {
                    return $product->total_sold * $product->price;
                });
                return $category;
            });

        return view('admin.reports.categories', compact('categories'));
    }

    /**
     * Satıcı bazlı rapor
     */
    public function sellers()
    {
        $sellers = User::where('role', 'seller')
            ->withCount('products')
            ->with(['products' => function($q) {
                $q->withCount(['orderItems as total_sold' => function($q2) {
                    $q2->select(DB::raw('COALESCE(SUM(quantity), 0)'));
                }]);
            }])
            ->get()
            ->map(function($seller) {
                $seller->total_sold = $seller->products->sum('total_sold');
                $seller->total_revenue = $seller->products->sum(function($product) {
                    return $product->total_sold * $product->price;
                });
                return $seller;
            });

        return view('admin.reports.sellers', compact('sellers'));
    }
}

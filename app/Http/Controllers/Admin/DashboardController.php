<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;

/**
 * Admin dashboard controller
 */
class DashboardController extends Controller
{
    /**
     * Admin dashboard sayfasını gösterir
     */
    public function index()
    {
        // İstatistikler
        $stats = [
            'users' => User::count(),
            'products' => Product::count(),
            'orders' => Order::count(),
            'revenue' => Order::where('status', 'completed')->sum('total'),
        ];

        // Son siparişler
        $recentOrders = Order::with('user')
            ->latest()
            ->take(10)
            ->get();

        // Son kullanıcılar
        $recentUsers = User::latest()->take(5)->get();

        // Son ürünler
        $recentProducts = Product::latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentOrders', 'recentUsers', 'recentProducts'));
    }
}

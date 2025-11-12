@extends('layouts.seller')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-speedometer2"></i> Dashboard</h2>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Ürünlerim</h6>
                        <h2 class="card-title mb-0">{{ $stats['products'] }}</h2>
                    </div>
                    <i class="bi bi-box-seam display-4 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Satışlarım</h6>
                        <h2 class="card-title mb-0">{{ $stats['orders'] }}</h2>
                    </div>
                    <i class="bi bi-cart-check display-4 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Ciro</h6>
                        <h2 class="card-title mb-0">{{ number_format($stats['revenue'], 2) }} ₺</h2>
                    </div>
                    <i class="bi bi-currency-dollar display-4 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-lightning"></i> Hızlı İşlemler</h5>
    </div>
    <div class="card-body">
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('seller.products.create') }}" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Yeni Ürün Ekle
            </a>
            <a href="{{ route('seller.products.index') }}" class="btn btn-primary">
                <i class="bi bi-box-seam"></i> Ürünlerim
            </a>
            <a href="{{ route('seller.orders.index') }}" class="btn btn-warning">
                <i class="bi bi-cart-check"></i> Satışlarım
            </a>
            <a href="{{ route('seller.trendyol.index') }}" class="btn btn-info">
                <i class="bi bi-cloud-upload"></i> Trendyol Entegrasyon
            </a>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-cart-check"></i> Son Satışlar</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Sipariş No</th>
                        <th>Müşteri</th>
                        <th>Ürün</th>
                        <th>Tutar</th>
                        <th>Durum</th>
                        <th>Tarih</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                    <tr>
                        <td>#{{ $order->id }}</td>
                        <td>{{ $order->user->name }}</td>
                        <td>{{ $order->items_count }} ürün</td>
                        <td>{{ number_format($order->total_amount, 2) }} ₺</td>
                        <td>
                            @if($order->status == 'pending')
                                <span class="badge bg-warning">Beklemede</span>
                            @elseif($order->status == 'processing')
                                <span class="badge bg-info">İşleniyor</span>
                            @elseif($order->status == 'completed')
                                <span class="badge bg-success">Tamamlandı</span>
                            @elseif($order->status == 'cancelled')
                                <span class="badge bg-danger">İptal</span>
                            @endif
                        </td>
                        <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">Henüz satış yok.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Product Stats -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Ürün Durumu</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Stok Durumu</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <tr>
                            <td>Stokta Var</td>
                            <td class="text-end">
                                <span class="badge bg-success">{{ $productStats['in_stock'] ?? 0 }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>Stok Azalıyor</td>
                            <td class="text-end">
                                <span class="badge bg-warning">{{ $productStats['low_stock'] ?? 0 }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>Stokta Yok</td>
                            <td class="text-end">
                                <span class="badge bg-danger">{{ $productStats['out_of_stock'] ?? 0 }}</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="col-md-6">
                <h6>Trendyol Durumu</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <tr>
                            <td>Gönderildi</td>
                            <td class="text-end">
                                <span class="badge bg-success">{{ $productStats['trendyol_sent'] ?? 0 }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>Gönderilmedi</td>
                            <td class="text-end">
                                <span class="badge bg-secondary">{{ $productStats['trendyol_not_sent'] ?? 0 }}</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

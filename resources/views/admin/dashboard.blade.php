@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-speedometer2"></i> Dashboard</h2>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Toplam Kullanıcı</h6>
                        <h2 class="card-title mb-0">{{ $stats['users'] }}</h2>
                    </div>
                    <i class="bi bi-people display-4 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Toplam Ürün</h6>
                        <h2 class="card-title mb-0">{{ $stats['products'] }}</h2>
                    </div>
                    <i class="bi bi-box-seam display-4 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Toplam Sipariş</h6>
                        <h2 class="card-title mb-0">{{ $stats['orders'] }}</h2>
                    </div>
                    <i class="bi bi-cart-check display-4 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Toplam Ciro</h6>
                        <h2 class="card-title mb-0">{{ number_format($stats['revenue'], 2) }} ₺</h2>
                    </div>
                    <i class="bi bi-currency-dollar display-4 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-cart-check"></i> Son Siparişler</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Sipariş No</th>
                        <th>Müşteri</th>
                        <th>Tutar</th>
                        <th>Durum</th>
                        <th>Tarih</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                    <tr>
                        <td>#{{ $order->id }}</td>
                        <td>{{ $order->user->name }}</td>
                        <td>{{ number_format($order->total, 2) }} ₺</td>
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
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">Henüz sipariş yok.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Recent Users & Products -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-people"></i> Son Kullanıcılar</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>İsim</th>
                                <th>Rol</th>
                                <th>Tarih</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentUsers as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>
                                    @if($user->role == 'admin')
                                        <span class="badge bg-danger">Admin</span>
                                    @elseif($user->role == 'seller')
                                        <span class="badge bg-success">Satıcı</span>
                                    @else
                                        <span class="badge bg-primary">Müşteri</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('d.m.Y') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">Veri yok.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-box-seam"></i> Son Ürünler</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Ürün</th>
                                <th>Fiyat</th>
                                <th>Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentProducts as $product)
                            <tr>
                                <td>{{ Str::limit($product->name, 30) }}</td>
                                <td>{{ number_format($product->price, 2) }} ₺</td>
                                <td>
                                    @if($product->stock > 10)
                                        <span class="badge bg-success">{{ $product->stock }}</span>
                                    @elseif($product->stock > 0)
                                        <span class="badge bg-warning">{{ $product->stock }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ $product->stock }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">Veri yok.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.admin')

@section('title', 'Sipariş Detayı')

@section('content')
<div class="mb-4">
    <h1><i class="bi bi-receipt"></i> Sipariş Detayı #{{ $order->order_number }}</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Siparişler</a></li>
            <li class="breadcrumb-item active">#{{ $order->order_number }}</li>
        </ol>
    </nav>
</div>

<div class="row">
    <!-- Sol Kolon -->
    <div class="col-md-8">
        <!-- Sipariş Durumu Güncelleme -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-arrow-repeat"></i> Sipariş Durumu</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="row g-3">
                    @csrf
                    @method('PATCH')
                    <div class="col-md-6">
                        <label class="form-label">Sipariş Durumu</label>
                        <select name="status" class="form-select" required>
                            <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Beklemede</option>
                            <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>İşleniyor</option>
                            <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Kargoya Verildi</option>
                            <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Tamamlandı</option>
                            <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>İptal Edildi</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ödeme Durumu</label>
                        <select name="payment_status" class="form-select" required>
                            <option value="pending" {{ $order->payment_status == 'pending' ? 'selected' : '' }}>Beklemede</option>
                            <option value="paid" {{ $order->payment_status == 'paid' ? 'selected' : '' }}>Ödendi</option>
                            <option value="failed" {{ $order->payment_status == 'failed' ? 'selected' : '' }}>Başarısız</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Güncelle
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sipariş Ürünleri -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-box-seam"></i> Ürünler</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Ürün</th>
                                <th>Satıcı</th>
                                <th>Beden</th>
                                <th>Fiyat</th>
                                <th>Adet</th>
                                <th>Toplam</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->product_name }}</strong><br>
                                    <small class="text-muted">SKU: {{ $item->product_sku }}</small>
                                </td>
                                <td>{{ $item->seller->name }}</td>
                                <td>
                                    @if($item->size)
                                        {{ $item->size->name }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ number_format($item->price, 2) }} ₺</td>
                                <td>{{ $item->quantity }}</td>
                                <td><strong>{{ number_format($item->total, 2) }} ₺</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-end">Ara Toplam:</th>
                                <th>{{ number_format($order->subtotal, 2) }} ₺</th>
                            </tr>
                            <tr>
                                <th colspan="5" class="text-end">Kargo:</th>
                                <th>{{ number_format($order->shipping_cost, 2) }} ₺</th>
                            </tr>
                            <tr>
                                <th colspan="5" class="text-end">KDV:</th>
                                <th>{{ number_format($order->tax_amount, 2) }} ₺</th>
                            </tr>
                            <tr class="table-primary">
                                <th colspan="5" class="text-end">TOPLAM:</th>
                                <th>{{ number_format($order->total, 2) }} ₺</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Teslimat Bilgileri -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-truck"></i> Teslimat Bilgileri</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Teslimat Adresi</h6>
                        <p class="mb-1"><strong>{{ $order->shipping_name }}</strong></p>
                        <p class="mb-1">{{ $order->shipping_address }}</p>
                        <p class="mb-1">{{ $order->shipping_city }} / {{ $order->shipping_state }}</p>
                        <p class="mb-1">{{ $order->shipping_zip }}</p>
                        <p class="mb-0"><i class="bi bi-telephone"></i> {{ $order->shipping_phone }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Fatura Adresi</h6>
                        <p class="mb-1"><strong>{{ $order->billing_name }}</strong></p>
                        <p class="mb-1">{{ $order->billing_address }}</p>
                        <p class="mb-1">{{ $order->billing_city }} / {{ $order->billing_state }}</p>
                        <p class="mb-1">{{ $order->billing_zip }}</p>
                        <p class="mb-0"><i class="bi bi-telephone"></i> {{ $order->billing_phone }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sağ Kolon -->
    <div class="col-md-4">
        <!-- Sipariş Bilgileri -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Sipariş Bilgileri</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>Sipariş No:</strong></td>
                        <td>#{{ $order->order_number }}</td>
                    </tr>
                    <tr>
                        <td><strong>Tarih:</strong></td>
                        <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Ödeme Yöntemi:</strong></td>
                        <td>{{ $order->payment_method }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Müşteri Bilgileri -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person"></i> Müşteri</h5>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>{{ $order->user->name }}</strong></p>
                <p class="mb-1"><i class="bi bi-envelope"></i> {{ $order->user->email }}</p>
                <p class="mb-1"><i class="bi bi-telephone"></i> {{ $order->user->phone ?? '-' }}</p>
                <hr>
                <a href="{{ route('admin.users.show', $order->user) }}" class="btn btn-sm btn-outline-primary w-100">
                    <i class="bi bi-eye"></i> Müşteri Detayı
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

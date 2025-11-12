@extends('layouts.seller')

@section('title', 'Sipariş Detayı')

@section('content')
<div class="mb-4">
    <h1><i class="bi bi-receipt"></i> Sipariş Detayı #{{ $order->order_number }}</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('seller.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('seller.orders.index') }}">Siparişler</a></li>
            <li class="breadcrumb-item active">#{{ $order->order_number }}</li>
        </ol>
    </nav>
</div>

<div class="row">
    <!-- Sol Kolon -->
    <div class="col-md-8">
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
                                <th>SKU</th>
                                <th>Beden</th>
                                <th>Fiyat</th>
                                <th>Adet</th>
                                <th>Toplam</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $sellerItems = $order->items->where('seller_id', auth()->id());
                            @endphp
                            @foreach($sellerItems as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->product_name }}</strong>
                                </td>
                                <td><code>{{ $item->product_sku }}</code></td>
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
                                <th colspan="5" class="text-end">Toplam:</th>
                                <th>{{ number_format($sellerItems->sum('total'), 2) }} ₺</th>
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
                        <td><strong>Durum:</strong></td>
                        <td>
                            @if($order->status == 'pending')
                                <span class="badge bg-warning text-dark">Beklemede</span>
                            @elseif($order->status == 'processing')
                                <span class="badge bg-info">İşleniyor</span>
                            @elseif($order->status == 'shipped')
                                <span class="badge bg-primary">Kargoya Verildi</span>
                            @elseif($order->status == 'completed')
                                <span class="badge bg-success">Tamamlandı</span>
                            @elseif($order->status == 'cancelled')
                                <span class="badge bg-danger">İptal Edildi</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Ödeme:</strong></td>
                        <td>
                            @if($order->payment_status == 'paid')
                                <span class="badge bg-success">Ödendi</span>
                            @else
                                <span class="badge bg-warning text-dark">Beklemede</span>
                            @endif
                        </td>
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
                <p class="mb-0"><i class="bi bi-telephone"></i> {{ $order->user->phone ?? '-' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

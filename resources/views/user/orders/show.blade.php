@extends('layouts.app')

@section('title', 'Sipariş Detayı')

@section('content')
<div class="container my-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Ana Sayfa</a></li>
            <li class="breadcrumb-item"><a href="{{ route('user.orders.index') }}">Siparişlerim</a></li>
            <li class="breadcrumb-item active">#{{ $order->order_number }}</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Sol Kolon -->
        <div class="col-md-8">
            <!-- Sipariş Durumu -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">Sipariş #{{ $order->order_number }}</h4>
                            <p class="text-muted mb-0">{{ $order->created_at->format('d.m.Y H:i') }}</p>
                        </div>
                        <div>
                            @if($order->status == 'pending')
                                <span class="badge bg-warning text-dark fs-6">Beklemede</span>
                            @elseif($order->status == 'processing')
                                <span class="badge bg-info fs-6">İşleniyor</span>
                            @elseif($order->status == 'shipped')
                                <span class="badge bg-primary fs-6">Kargoda</span>
                            @elseif($order->status == 'completed')
                                <span class="badge bg-success fs-6">Teslim Edildi</span>
                            @elseif($order->status == 'cancelled')
                                <span class="badge bg-danger fs-6">İptal Edildi</span>
                            @endif
                        </div>
                    </div>

                    <!-- Sipariş Takip -->
                    <div class="mt-4">
                        <div class="progress" style="height: 5px;">
                            @php
                                $progress = 0;
                                if($order->status == 'pending') $progress = 25;
                                elseif($order->status == 'processing') $progress = 50;
                                elseif($order->status == 'shipped') $progress = 75;
                                elseif($order->status == 'completed') $progress = 100;
                            @endphp
                            <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <small class="{{ $order->status == 'pending' ? 'text-primary fw-bold' : 'text-muted' }}">
                                <i class="bi bi-check-circle"></i> Alındı
                            </small>
                            <small class="{{ $order->status == 'processing' ? 'text-primary fw-bold' : 'text-muted' }}">
                                <i class="bi bi-box-seam"></i> Hazırlanıyor
                            </small>
                            <small class="{{ $order->status == 'shipped' ? 'text-primary fw-bold' : 'text-muted' }}">
                                <i class="bi bi-truck"></i> Kargoda
                            </small>
                            <small class="{{ $order->status == 'completed' ? 'text-success fw-bold' : 'text-muted' }}">
                                <i class="bi bi-check-circle-fill"></i> Teslim Edildi
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ürünler -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-box-seam"></i> Sipariş İçeriği</h5>
                </div>
                <div class="card-body">
                    @foreach($order->items as $item)
                    <div class="row align-items-center mb-3 pb-3 border-bottom">
                        <div class="col-md-2">
                            @if($item->product && $item->product->images && count(json_decode($item->product->images)) > 0)
                                <img src="{{ json_decode($item->product->images)[0] }}" 
                                     class="img-fluid rounded" alt="{{ $item->product_name }}">
                            @else
                                <div class="bg-secondary rounded" style="height: 80px;"></div>
                            @endif
                        </div>
                        <div class="col-md-5">
                            <h6 class="mb-1">{{ $item->product_name }}</h6>
                            <small class="text-muted">SKU: {{ $item->product_sku }}</small><br>
                            @if($item->size)
                                <small class="text-muted">Beden: {{ $item->size->name }}</small>
                            @endif
                        </div>
                        <div class="col-md-2 text-center">
                            <small class="text-muted">Adet</small><br>
                            <strong>{{ $item->quantity }}</strong>
                        </div>
                        <div class="col-md-3 text-end">
                            <small class="text-muted">Birim Fiyat</small><br>
                            <strong>{{ number_format($item->price, 2) }} ₺</strong><br>
                            <small class="text-primary">Toplam: {{ number_format($item->total, 2) }} ₺</small>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Teslimat Adresi -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-truck"></i> Teslimat Adresi</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>{{ $order->shipping_name }}</strong></p>
                    <p class="mb-1">{{ $order->shipping_address }}</p>
                    <p class="mb-1">{{ $order->shipping_city }} / {{ $order->shipping_state }}</p>
                    <p class="mb-1">{{ $order->shipping_zip }}</p>
                    <p class="mb-0"><i class="bi bi-telephone"></i> {{ $order->shipping_phone }}</p>
                </div>
            </div>
        </div>

        <!-- Sağ Kolon -->
        <div class="col-md-4">
            <!-- Sipariş Özeti -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-receipt"></i> Sipariş Özeti</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Ara Toplam:</span>
                        <strong>{{ number_format($order->subtotal, 2) }} ₺</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Kargo:</span>
                        <strong>{{ number_format($order->shipping_cost, 2) }} ₺</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>KDV:</span>
                        <strong>{{ number_format($order->tax_amount, 2) }} ₺</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Toplam:</strong>
                        <h4 class="text-primary mb-0">{{ number_format($order->total_amount, 2) }} ₺</h4>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span>Ödeme Yöntemi:</span>
                        <strong>{{ $order->payment_method }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Ödeme Durumu:</span>
                        @if($order->payment_status == 'paid')
                            <span class="badge bg-success">Ödendi</span>
                        @else
                            <span class="badge bg-warning text-dark">Beklemede</span>
                        @endif
                    </div>
                </div>
            </div>

            @if($order->status == 'pending')
            <div class="card mt-3">
                <div class="card-body">
                    <form action="{{ route('user.orders.cancel', $order) }}" method="POST" 
                          onsubmit="return confirm('Siparişi iptal etmek istediğinizden emin misiniz?')">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-x-circle"></i> Siparişi İptal Et
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Siparişlerim')

@section('content')
<div class="container my-5">
    <h1 class="mb-4"><i class="bi bi-cart-check"></i> Siparişlerim</h1>

    @if($orders->count() > 0)
    <div class="row">
        @foreach($orders as $order)
        <div class="col-12 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <small class="text-muted">Sipariş No</small>
                            <h5 class="mb-0">#{{ $order->order_number }}</h5>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">Tarih</small>
                            <p class="mb-0">{{ $order->created_at->format('d.m.Y') }}</p>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">Toplam</small>
                            <p class="mb-0"><strong>{{ number_format($order->total_amount, 2) }} ₺</strong></p>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">Ürün Sayısı</small>
                            <p class="mb-0">{{ $order->items_count }} adet</p>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">Durum</small>
                            @if($order->status == 'pending')
                                <span class="badge bg-warning text-dark">Beklemede</span>
                            @elseif($order->status == 'processing')
                                <span class="badge bg-info">İşleniyor</span>
                            @elseif($order->status == 'shipped')
                                <span class="badge bg-primary">Kargoda</span>
                            @elseif($order->status == 'completed')
                                <span class="badge bg-success">Teslim Edildi</span>
                            @elseif($order->status == 'cancelled')
                                <span class="badge bg-danger">İptal Edildi</span>
                            @endif
                        </div>
                        <div class="col-md-2 text-end">
                            <a href="{{ route('user.orders.show', $order) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-eye"></i> Detay
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $orders->links() }}
    </div>
    @else
    <div class="text-center py-5">
        <i class="bi bi-inbox display-1 text-muted"></i>
        <h3 class="mt-3">Henüz siparişiniz yok</h3>
        <p class="text-muted">Hemen alışverişe başlayın!</p>
        <a href="{{ route('products.index') }}" class="btn btn-primary">
            <i class="bi bi-shop"></i> Ürünleri İncele
        </a>
    </div>
    @endif
</div>
@endsection

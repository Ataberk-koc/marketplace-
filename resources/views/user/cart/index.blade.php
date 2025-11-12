@extends('layouts.app')

@section('title', 'Sepetim')

@section('content')
<div class="container">
    <h2 class="mb-4"><i class="bi bi-cart"></i> Sepetim</h2>

    @if($cart && $cart->items->isNotEmpty())
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        @foreach($cart->items as $item)
                        <div class="row mb-3 pb-3 border-bottom">
                            <div class="col-md-2">
                                @if($item->product->main_image)
                                    <img src="{{ $item->product->main_image }}" class="img-fluid rounded" alt="{{ $item->product->name }}">
                                @else
                                    <div class="bg-secondary rounded" style="height: 80px;"></div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <h6>{{ $item->product->name }}</h6>
                                <p class="text-muted small mb-1">{{ $item->product->brand->name }}</p>
                                @if($item->size)
                                    <p class="text-muted small mb-0">Beden: {{ $item->size->name }}</p>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="input-group" style="max-width: 120px;">
                                        <form action="{{ route('user.cart.update', $item) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <div class="input-group">
                                                <button type="submit" name="quantity" value="{{ $item->quantity - 1 }}" 
                                                        class="btn btn-sm btn-outline-secondary" 
                                                        {{ $item->quantity <= 1 ? 'disabled' : '' }}>-</button>
                                                <input type="text" class="form-control form-control-sm text-center" 
                                                       value="{{ $item->quantity }}" readonly>
                                                <button type="submit" name="quantity" value="{{ $item->quantity + 1 }}" 
                                                        class="btn btn-sm btn-outline-secondary">+</button>
                                            </div>
                                        </form>
                                    </div>
                                    <strong>{{ number_format($item->product->final_price * $item->quantity, 2) }} ₺</strong>
                                </div>
                                <form action="{{ route('user.cart.remove', $item) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger w-100">
                                        <i class="bi bi-trash"></i> Kaldır
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Sipariş Özeti</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Ara Toplam:</span>
                            <strong>{{ number_format($cart->total_amount, 2) }} ₺</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Kargo:</span>
                            <strong>ÜCRETSİZ</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Toplam:</strong>
                            <strong class="text-primary fs-5">{{ number_format($cart->total_amount, 2) }} ₺</strong>
                        </div>
                        
                        <a href="{{ route('user.orders.checkout') }}" class="btn btn-success w-100 btn-lg">
                            <i class="bi bi-credit-card"></i> Siparişi Tamamla
                        </a>
                        
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary w-100 mt-2">
                            <i class="bi bi-arrow-left"></i> Alışverişe Devam Et
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info">
            <i class="bi bi-cart-x"></i> Sepetiniz boş.
            <a href="{{ route('products.index') }}" class="alert-link">Alışverişe başlayın!</a>
        </div>
    @endif
</div>
@endsection

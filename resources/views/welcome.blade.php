@extends('layouts.app')

@section('title', 'Ana Sayfa')

@section('content')
<!-- Hero Section -->
<div class="bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-4"><i class="bi bi-shop"></i> Marketplace'e Hoş Geldiniz</h1>
                <p class="lead">Binlerce ürün, en uygun fiyatlarla!</p>
                <a href="{{ route('products.index') }}" class="btn btn-light btn-lg">
                    <i class="bi bi-search"></i> Ürünleri İncele
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Categories Section -->
<div class="container my-5">
    <h2 class="mb-4"><i class="bi bi-grid"></i> Kategoriler</h2>
    <div class="row">
        @foreach($categories as $category)
        <div class="col-md-3 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title">{{ $category->name }}</h5>
                    <p class="text-muted">{{ $category->products_count }} ürün</p>
                    <a href="{{ route('products.index', ['category' => $category->id]) }}" class="btn btn-outline-primary btn-sm">
                        Görüntüle
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Featured Products -->
<div class="container my-5">
    <h2 class="mb-4"><i class="bi bi-stars"></i> Öne Çıkan Ürünler</h2>
    <div class="row">
        @foreach($products as $product)
        <div class="col-md-3 mb-4">
            <div class="card h-100 shadow-sm">
                @if($product->main_image)
                    <img src="{{ $product->main_image }}" class="card-img-top" alt="{{ $product->name }}" style="height: 200px; object-fit: cover;">
                @else
                    <div class="bg-secondary" style="height: 200px;"></div>
                @endif
                <div class="card-body">
                    <h6 class="card-title">{{ Str::limit($product->name, 50) }}</h6>
                    <p class="text-muted small">{{ $product->brand->name }}</p>
                    <div class="d-flex justify-content-between align-items-center">
                        @if($product->discount_price)
                            <div>
                                <span class="text-decoration-line-through text-muted small">{{ number_format($product->price, 2) }} ₺</span><br>
                                <span class="text-danger fw-bold">{{ number_format($product->final_price, 2) }} ₺</span>
                            </div>
                        @else
                            <span class="fw-bold">{{ number_format($product->price, 2) }} ₺</span>
                        @endif
                    </div>
                </div>
                <div class="card-footer bg-white border-top-0">
                    <a href="{{ route('products.show', $product) }}" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-eye"></i> İncele
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Stats Section -->
<div class="bg-light py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3">
                <i class="bi bi-box-seam display-4 text-primary"></i>
                <h3 class="mt-2">{{ $stats['products'] }}</h3>
                <p class="text-muted">Ürün</p>
            </div>
            <div class="col-md-3">
                <i class="bi bi-people display-4 text-success"></i>
                <h3 class="mt-2">{{ $stats['users'] }}</h3>
                <p class="text-muted">Kullanıcı</p>
            </div>
            <div class="col-md-3">
                <i class="bi bi-cart-check display-4 text-warning"></i>
                <h3 class="mt-2">{{ $stats['orders'] }}</h3>
                <p class="text-muted">Sipariş</p>
            </div>
            <div class="col-md-3">
                <i class="bi bi-tag display-4 text-danger"></i>
                <h3 class="mt-2">{{ $stats['brands'] }}</h3>
                <p class="text-muted">Marka</p>
            </div>
        </div>
    </div>
</div>
@endsection

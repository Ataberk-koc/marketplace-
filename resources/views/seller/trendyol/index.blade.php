@extends('layouts.seller')

@section('title', 'Trendyol Entegrasyonu')

@section('content')
<div class="mb-4">
    <h1><i class="bi bi-globe"></i> Trendyol Entegrasyonu</h1>
</div>

<!-- Bağlantı Durumu -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="mb-2">
                    <i class="bi bi-plug"></i> Trendyol API Bağlantısı
                </h5>
                <p class="text-muted mb-0">
                    API anahtarınızı kullanarak Trendyol ile entegrasyonu sağlayabilirsiniz.
                </p>
            </div>
            <div class="col-md-4 text-end">
                <span class="badge bg-success fs-6">
                    <i class="bi bi-check-circle"></i> Aktif
                </span>
            </div>
        </div>
    </div>
</div>

<!-- İstatistikler -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-box-seam display-4 text-primary"></i>
                <h3 class="mt-2">{{ $stats['total_products'] }}</h3>
                <p class="text-muted mb-0">Toplam Ürün</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-check-circle display-4 text-success"></i>
                <h3 class="mt-2">{{ $stats['sent_products'] }}</h3>
                <p class="text-muted mb-0">Gönderilen</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-clock display-4 text-warning"></i>
                <h3 class="mt-2">{{ $stats['pending_products'] }}</h3>
                <p class="text-muted mb-0">Bekleyen</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-exclamation-circle display-4 text-danger"></i>
                <h3 class="mt-2">{{ $stats['error_products'] }}</h3>
                <p class="text-muted mb-0">Hatalı</p>
            </div>
        </div>
    </div>
</div>

<!-- Ürünler -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-list"></i> Ürünlerim</h5>
        <form method="POST" action="{{ route('seller.trendyol.sync-all') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success btn-sm">
                <i class="bi bi-arrow-repeat"></i> Tümünü Senkronize Et
            </button>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Ürün</th>
                        <th>SKU</th>
                        <th>Kategori</th>
                        <th>Fiyat</th>
                        <th>Stok</th>
                        <th>Trendyol Durumu</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                @php
                                    $images = is_string($product->images) ? json_decode($product->images, true) : $product->images;
                                @endphp
                                @if($images && is_array($images) && count($images) > 0)
                                    <img src="{{ $images[0] }}" alt="{{ $product->name }}" 
                                         style="width: 40px; height: 40px; object-fit: cover;" class="rounded me-2">
                                @endif
                                <strong>{{ Str::limit($product->name, 40) }}</strong>
                            </div>
                        </td>
                        <td><code>{{ $product->sku }}</code></td>
                        <td>{{ $product->category->name }}</td>
                        <td>{{ number_format($product->price, 2) }} ₺</td>
                        <td>
                            @if($product->stock_quantity > 10)
                                <span class="badge bg-success">{{ $product->stock_quantity }}</span>
                            @elseif($product->stock_quantity > 0)
                                <span class="badge bg-warning text-dark">{{ $product->stock_quantity }}</span>
                            @else
                                <span class="badge bg-danger">0</span>
                            @endif
                        </td>
                        <td>
                            @if($product->trendyolMapping)
                                @if($product->trendyolMapping->status == 'active')
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Aktif
                                    </span>
                                @elseif($product->trendyolMapping->status == 'pending')
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-clock"></i> Beklemede
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="bi bi-x-circle"></i> Hatalı
                                    </span>
                                @endif
                            @else
                                <span class="badge bg-secondary">
                                    <i class="bi bi-dash-circle"></i> Gönderilmedi
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($product->trendyolMapping)
                                <form method="POST" action="{{ route('seller.trendyol.update', $product) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary" title="Güncelle">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('seller.trendyol.send', $product) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Trendyol'a Gönder">
                                        <i class="bi bi-send"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <p class="text-muted mt-2">Henüz ürün bulunmuyor</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-center mt-4">
            {{ $products->links() }}
        </div>
    </div>
</div>
@endsection

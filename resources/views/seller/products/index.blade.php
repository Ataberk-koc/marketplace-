@extends('layouts.seller')

@section('title', 'Ürünlerim')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-box-seam"></i> Ürünlerim</h1>
    <a href="{{ route('seller.products.create') }}" class="btn btn-success">
        <i class="bi bi-plus-lg"></i> Yeni Ürün Ekle
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="productsTable" class="table table-hover">
                <thead>
                    <tr>
                        <th>Görsel</th>
                        <th>Ürün Adı</th>
                        <th>SKU</th>
                        <th>Kategori</th>
                        <th>Marka</th>
                        <th>Fiyat</th>
                        <th>Stok</th>
                        <th>Durum</th>
                        <th>Trendyol</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr>
                        <td>
                            @php
                                $images = is_string($product->images) ? json_decode($product->images, true) : $product->images;
                            @endphp
                            @if($images && is_array($images) && count($images) > 0)
                                <img src="{{ $images[0] }}" alt="{{ $product->name }}" style="width: 50px; height: 50px; object-fit: cover;" class="rounded">
                            @else
                                <div class="bg-secondary rounded" style="width: 50px; height: 50px;"></div>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $product->name }}</strong>
                            @if($product->is_featured)
                                <span class="badge bg-warning text-dark ms-1">
                                    <i class="bi bi-star-fill"></i> Öne Çıkan
                                </span>
                            @endif
                        </td>
                        <td><code>{{ $product->sku }}</code></td>
                        <td>{{ $product->category->name }}</td>
                        <td>{{ $product->brand->name }}</td>
                        <td>
                            @if($product->discount_price)
                                <div>
                                    <small class="text-muted text-decoration-line-through">{{ number_format($product->price, 2) }} ₺</small><br>
                                    <strong class="text-danger">{{ number_format($product->discount_price, 2) }} ₺</strong>
                                </div>
                            @else
                                <strong>{{ number_format($product->price, 2) }} ₺</strong>
                            @endif
                        </td>
                        <td>
                            @if($product->stock_quantity > 10)
                                <span class="badge bg-success">{{ $product->stock_quantity }} adet</span>
                            @elseif($product->stock_quantity > 0)
                                <span class="badge bg-warning text-dark">{{ $product->stock_quantity }} adet</span>
                            @else
                                <span class="badge bg-danger">Tükendi</span>
                            @endif
                        </td>
                        <td>
                            @if($product->is_active)
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Aktif
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="bi bi-x-circle"></i> Pasif
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($product->trendyolMapping)
                                <span class="badge bg-info">
                                    <i class="bi bi-check-lg"></i> Gönderildi
                                </span>
                            @else
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-x-lg"></i> Gönderilmedi
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('seller.products.edit', $product) }}" class="btn btn-outline-primary" title="Düzenle">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('seller.products.destroy', $product) }}" method="POST" class="d-inline" 
                                      onsubmit="return confirm('Bu ürünü silmek istediğinizden emin misiniz?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Sil">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-center mt-4">
            {{ $products->links() }}
        </div>
    </div>
</div>
@endsection

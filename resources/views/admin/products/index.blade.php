@extends('layouts.admin')

@section('title', 'Ürünler')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-box-seam"></i> Ürünler</h2>
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Yeni Ürün Ekle
    </a>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped datatable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Görsel</th>
                    <th>Ürün Adı</th>
                    <th>Marka</th>
                    <th>Kategori</th>
                    <th>Fiyat</th>
                    <th>İndirimli Fiyat</th>
                    <th>Stok</th>
                    <th>Satıcı</th>
                    <th>Durum</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr>
                    <td>{{ $product->id }}</td>
                    <td>
                        @if($product->main_image)
                            <img src="{{ $product->main_image }}" alt="{{ $product->name }}" 
                                 style="width: 50px; height: 50px; object-fit: cover;" class="rounded">
                        @else
                            <div class="bg-secondary rounded" style="width: 50px; height: 50px;"></div>
                        @endif
                    </td>
                    <td>{{ Str::limit($product->name, 30) }}</td>
                    <td>{{ $product->brand->name }}</td>
                    <td>{{ $product->category->name }}</td>
                    <td>{{ number_format($product->price, 2) }} ₺</td>
                    <td>
                        @if($product->discount_price)
                            <span class="text-danger">{{ number_format($product->discount_price, 2) }} ₺</span>
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($product->stock > 10)
                            <span class="badge bg-success">{{ $product->stock }}</span>
                        @elseif($product->stock > 0)
                            <span class="badge bg-warning">{{ $product->stock }}</span>
                        @else
                            <span class="badge bg-danger">{{ $product->stock }}</span>
                        @endif
                    </td>
                    <td>{{ $product->seller->name }}</td>
                    <td>
                        @if($product->is_active)
                            <span class="badge bg-success">Aktif</span>
                        @else
                            <span class="badge bg-danger">Pasif</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.products.show', $product) }}" 
                               class="btn btn-info"
                               title="Detaylar">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('admin.products.edit', $product) }}" 
                               class="btn btn-warning"
                               title="Düzenle">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="{{ route('admin.products.attributes', $product) }}" 
                               class="btn btn-success"
                               title="Özellikler">
                                <i class="bi bi-list-stars"></i>
                            </a>
                            <form action="{{ route('admin.products.toggle-active', $product) }}" method="POST" class="d-inline">
                                @csrf
                                @method('POST')
                                <button type="submit" class="btn btn-{{ $product->is_active ? 'secondary' : 'primary' }}"
                                        title="{{ $product->is_active ? 'Pasif Yap' : 'Aktif Yap' }}">
                                    <i class="bi bi-{{ $product->is_active ? 'pause' : 'play' }}"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

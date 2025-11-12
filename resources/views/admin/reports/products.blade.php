@extends('layouts.admin')

@section('title', 'Ürün Raporları')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="fas fa-boxes me-2"></i>Ürün Raporları</h1>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Geri
        </a>
    </div>

    <!-- Filtre -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Kategori</label>
                        <select name="category_id" class="form-select">
                            <option value="">Tümü</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Stok Durumu</label>
                        <select name="stock_status" class="form-select">
                            <option value="">Tümü</option>
                            <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Düşük Stok (≤10)</option>
                            <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Stokta Yok</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Satıcı</label>
                        <select name="seller_id" class="form-select">
                            <option value="">Tümü</option>
                            @foreach($sellers as $seller)
                            <option value="{{ $seller->id }}" {{ request('seller_id') == $seller->id ? 'selected' : '' }}>
                                {{ $seller->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i>Filtrele
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Ürün Tablosu -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Ürün</th>
                            <th>Kategori</th>
                            <th>Satıcı</th>
                            <th class="text-end">Fiyat</th>
                            <th class="text-center">Stok</th>
                            <th class="text-center">Satılan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr>
                            <td>
                                <strong>{{ $product->name }}</strong><br>
                                <small class="text-muted">SKU: {{ $product->sku }}</small>
                            </td>
                            <td>{{ $product->category->name ?? '-' }}</td>
                            <td>{{ $product->seller->name ?? '-' }}</td>
                            <td class="text-end">{{ number_format($product->price, 2) }} ₺</td>
                            <td class="text-center">
                                @if($product->stock_quantity == 0)
                                <span class="badge bg-danger">Yok</span>
                                @elseif($product->stock_quantity <= 10)
                                <span class="badge bg-warning">{{ $product->stock_quantity }}</span>
                                @else
                                <span class="badge bg-success">{{ $product->stock_quantity }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <strong>{{ $product->total_sold ?? 0 }}</strong>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Ürün bulunamadı</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $products->links() }}
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Ürünler')

@section('content')
<div class="container">
    <div class="row">
        <!-- Filters Sidebar -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtrele</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('products.index') }}">
                        <!-- Search -->
                        <div class="mb-3">
                            <label class="form-label">Arama</label>
                            <input type="text" class="form-control" name="search" 
                                   value="{{ request('search') }}" placeholder="Ürün ara...">
                        </div>

                        <!-- Category -->
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select class="form-select" name="category">
                                <option value="">Tümü</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" 
                                        {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Brand -->
                        <div class="mb-3">
                            <label class="form-label">Marka</label>
                            <select class="form-select" name="brand">
                                <option value="">Tümü</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" 
                                        {{ request('brand') == $brand->id ? 'selected' : '' }}>
                                        {{ $brand->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div class="mb-3">
                            <label class="form-label">Fiyat Aralığı</label>
                            <div class="row g-2">
                                <div class="col">
                                    <input type="number" class="form-control" name="min_price" 
                                           value="{{ request('min_price') }}" placeholder="Min">
                                </div>
                                <div class="col">
                                    <input type="number" class="form-control" name="max_price" 
                                           value="{{ request('max_price') }}" placeholder="Max">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Filtrele
                        </button>
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary w-100 mt-2">
                            <i class="bi bi-x-circle"></i> Temizle
                        </a>
                    </form>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3><i class="bi bi-box-seam"></i> Ürünler ({{ $products->total() }})</h3>
                <form method="GET" class="d-flex gap-2">
                    @foreach(request()->except('sort') as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <select class="form-select" name="sort" onchange="this.form.submit()" style="width: auto;">
                        <option value="">Sıralama</option>
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>En Yeni</option>
                        <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Fiyat (Artan)</option>
                        <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Fiyat (Azalan)</option>
                        <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>İsim (A-Z)</option>
                    </select>
                </form>
            </div>

            @if($products->isEmpty())
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Ürün bulunamadı.
                </div>
            @else
                <div class="row">
                    @foreach($products as $product)
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            @if($product->main_image)
                                <img src="{{ $product->main_image }}" class="card-img-top" 
                                     alt="{{ $product->name }}" style="height: 200px; object-fit: cover;">
                            @else
                                <div class="bg-secondary" style="height: 200px;"></div>
                            @endif
                            
                            @if($product->discount_price)
                                <div class="badge bg-danger position-absolute m-2">
                                    %{{ round((($product->price - $product->discount_price) / $product->price) * 100) }} İndirim
                                </div>
                            @endif

                            <div class="card-body">
                                <h6 class="card-title">{{ Str::limit($product->name, 50) }}</h6>
                                <p class="text-muted small mb-2">{{ $product->brand->name }}</p>
                                <p class="text-muted small mb-2">{{ $product->category->name }}</p>
                                
                                @if($product->stock > 0)
                                    <span class="badge bg-success">Stokta</span>
                                @else
                                    <span class="badge bg-danger">Stok Yok</span>
                                @endif

                                <div class="mt-2">
                                    @if($product->discount_price)
                                        <div>
                                            <span class="text-decoration-line-through text-muted small">
                                                {{ number_format($product->price, 2) }} ₺
                                            </span><br>
                                            <span class="text-danger fw-bold fs-5">
                                                {{ number_format($product->final_price, 2) }} ₺
                                            </span>
                                        </div>
                                    @else
                                        <span class="fw-bold fs-5">{{ number_format($product->price, 2) }} ₺</span>
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

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

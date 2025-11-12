@extends('layouts.seller')

@section('title', 'Ürün Düzenle')

@section('content')
<div class="mb-4">
    <h1><i class="bi bi-pencil"></i> Ürün Düzenle</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('seller.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('seller.products.index') }}">Ürünler</a></li>
            <li class="breadcrumb-item active">{{ $product->name }}</li>
        </ol>
    </nav>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('seller.products.update', $product) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="row">
                <!-- Sol Kolon -->
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="name" class="form-label">Ürün Adı <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $product->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Açıklama</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="5">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Fiyat (₺) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" 
                                   id="price" name="price" value="{{ old('price', $product->price) }}" required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="discount_price" class="form-label">İndirimli Fiyat (₺)</label>
                            <input type="number" step="0.01" class="form-control @error('discount_price') is-invalid @enderror" 
                                   id="discount_price" name="discount_price" value="{{ old('discount_price', $product->discount_price) }}">
                            @error('discount_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    @if($product->images && count(json_decode($product->images)) > 0)
                    <div class="mb-3">
                        <label class="form-label">Mevcut Görseller</label>
                        <div class="row">
                            @foreach(json_decode($product->images) as $image)
                            <div class="col-md-3 mb-2">
                                <img src="{{ $image }}" class="img-thumbnail" alt="Product Image">
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label for="images" class="form-label">Yeni Görseller Ekle</label>
                        <input type="file" class="form-control @error('images') is-invalid @enderror" 
                               id="images" name="images[]" multiple accept="image/*">
                        <small class="text-muted">Birden fazla görsel seçebilirsiniz (JPG, PNG)</small>
                        @error('images')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="attributes" class="form-label">Özellikler (JSON)</label>
                        <textarea class="form-control @error('attributes') is-invalid @enderror" 
                                  id="attributes" name="attributes" rows="3" 
                                  placeholder='{"renk": "Kırmızı", "materyal": "Pamuk"}'>{{ old('attributes', $product->attributes) }}</textarea>
                        <small class="text-muted">Ürün özelliklerini JSON formatında giriniz</small>
                        @error('attributes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Sağ Kolon -->
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="sku" class="form-label">SKU <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('sku') is-invalid @enderror" 
                               id="sku" name="sku" value="{{ old('sku', $product->sku) }}" required>
                        @error('sku')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="category_id" class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select class="form-select @error('category_id') is-invalid @enderror" 
                                id="category_id" name="category_id" required>
                            <option value="">Seçiniz</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="brand_id" class="form-label">Marka <span class="text-danger">*</span></label>
                        <select class="form-select @error('brand_id') is-invalid @enderror" 
                                id="brand_id" name="brand_id" required>
                            <option value="">Seçiniz</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('brand_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="stock_quantity" class="form-label">Stok Miktarı <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('stock_quantity') is-invalid @enderror" 
                               id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" required>
                        @error('stock_quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                   {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Aktif
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" 
                                   {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_featured">
                                Öne Çıkan Ürün
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <hr>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('seller.products.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-lg"></i> İptal
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-lg"></i> Değişiklikleri Kaydet
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

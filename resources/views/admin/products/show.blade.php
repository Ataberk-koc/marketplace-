@extends('layouts.admin')

@section('title', 'Ürün Detayları - ' . $product->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="bi bi-box-seam"></i> Ürün Detayları</h1>
        <p class="text-muted mb-0">SKU: {{ $product->sku }}</p>
    </div>
    <div>
        <a href="{{ route('admin.products.attributes', $product) }}" class="btn btn-info">
            <i class="bi bi-list-stars"></i> Özellikleri Düzenle
        </a>
        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Düzenle
        </a>
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Geri
        </a>
    </div>
</div>

<div class="row">
    <!-- Sol Kolon - Genel Bilgiler -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Genel Bilgiler</h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Ürün Adı:</dt>
                    <dd class="col-sm-8"><strong>{{ $product->name }}</strong></dd>

                    <dt class="col-sm-4">Marka:</dt>
                    <dd class="col-sm-8">
                        <span class="badge bg-secondary">{{ $product->brand->name }}</span>
                    </dd>

                    <dt class="col-sm-4">Kategori:</dt>
                    <dd class="col-sm-8">
                        <span class="badge bg-info">{{ $product->category->name }}</span>
                    </dd>

                    <dt class="col-sm-4">Satıcı:</dt>
                    <dd class="col-sm-8">{{ $product->seller->name }}</dd>

                    <dt class="col-sm-4">Fiyat:</dt>
                    <dd class="col-sm-8">
                        @if($product->discount_price)
                            <span class="text-decoration-line-through text-muted">{{ number_format($product->price, 2) }} ₺</span>
                            <strong class="text-danger">{{ number_format($product->discount_price, 2) }} ₺</strong>
                        @else
                            <strong>{{ number_format($product->price, 2) }} ₺</strong>
                        @endif
                    </dd>

                    <dt class="col-sm-4">Stok:</dt>
                    <dd class="col-sm-8">
                        <span class="badge {{ $product->stock_quantity > 0 ? 'bg-success' : 'bg-danger' }}">
                            {{ $product->stock_quantity }} adet
                        </span>
                    </dd>

                    <dt class="col-sm-4">Durum:</dt>
                    <dd class="col-sm-8">
                        @if($product->is_active)
                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> Aktif</span>
                        @else
                            <span class="badge bg-secondary"><i class="bi bi-x-circle"></i> Pasif</span>
                        @endif
                    </dd>

                    <dt class="col-sm-4">Öne Çıkan:</dt>
                    <dd class="col-sm-8 mb-0">
                        @if($product->is_featured)
                            <span class="badge bg-warning"><i class="bi bi-star-fill"></i> Evet</span>
                        @else
                            <span class="text-muted">Hayır</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>

        <!-- Açıklama -->
        @if($product->description)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-card-text"></i> Açıklama</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $product->description }}</p>
            </div>
        </div>
        @endif
    </div>

    <!-- Sağ Kolon - Görseller ve Bedenler -->
    <div class="col-md-6">
        <!-- Görseller -->
        @if($product->images && count($product->images) > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-images"></i> Ürün Görselleri</h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    @foreach($product->images as $image)
                    <div class="col-md-6">
                        <img src="{{ $image }}" alt="{{ $product->name }}" class="img-fluid rounded border">
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Bedenler -->
        @if($product->sizes && count($product->sizes) > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-rulers"></i> Mevcut Bedenler</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Beden</th>
                                <th>Stok</th>
                                <th>Ek Fiyat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product->sizes as $size)
                            <tr>
                                <td><strong>{{ $size->name }}</strong></td>
                                <td>
                                    <span class="badge {{ $size->pivot->stock_quantity > 0 ? 'bg-success' : 'bg-danger' }}">
                                        {{ $size->pivot->stock_quantity }}
                                    </span>
                                </td>
                                <td>
                                    @if($size->pivot->additional_price > 0)
                                        +{{ number_format($size->pivot->additional_price, 2) }} ₺
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Ürün Özellikleri -->
@if($product->productAttributes && count($product->productAttributes) > 0)
<div class="card">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="bi bi-list-stars"></i> Ürün Özellikleri</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="30">
                            <i class="bi bi-info-circle" title="İkon"></i>
                        </th>
                        <th>Özellik Adı</th>
                        <th>Değer</th>
                        <th width="100">Tür</th>
                        <th width="80" class="text-center">Zorunlu</th>
                        <th width="80" class="text-center">Varyant</th>
                        <th width="150">Trendyol ID</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($product->productAttributes->sortBy('display_order') as $attr)
                    <tr>
                        <td>
                            <i class="bi {{ $attr->icon }} text-primary"></i>
                        </td>
                        <td><strong>{{ $attr->attribute_name }}</strong></td>
                        <td>
                            @if($attr->attribute_type === 'color')
                                <span class="badge" style="background-color: {{ $attr->attribute_value }}">
                                    {{ $attr->attribute_value }}
                                </span>
                            @else
                                {{ $attr->attribute_value }}
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $attr->attribute_type }}</span>
                        </td>
                        <td class="text-center">
                            @if($attr->is_required)
                                <i class="bi bi-check-circle text-success"></i>
                            @else
                                <i class="bi bi-dash-circle text-muted"></i>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($attr->is_variant)
                                <i class="bi bi-check-circle text-warning"></i>
                            @else
                                <i class="bi bi-dash-circle text-muted"></i>
                            @endif
                        </td>
                        <td>
                            @if($attr->trendyol_attribute_id)
                                <code>{{ $attr->trendyol_attribute_id }}</code>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@else
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle"></i> 
    Bu ürüne henüz özellik eklenmemiş. 
    <a href="{{ route('admin.products.attributes', $product) }}" class="alert-link">Özellik eklemek için tıklayın</a>.
</div>
@endif

<!-- Trendyol Bilgileri -->
@if($product->trendyolMapping)
<div class="card mt-4">
    <div class="card-header" style="background-color: #F27A1A; color: white;">
        <h5 class="mb-0"><i class="bi bi-shop-window"></i> Trendyol Entegrasyon Bilgileri</h5>
    </div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">Durum:</dt>
            <dd class="col-sm-9">
                <span class="badge {{ $product->trendyolMapping->status === 'sent' ? 'bg-success' : 'bg-warning' }}">
                    {{ ucfirst($product->trendyolMapping->status) }}
                </span>
            </dd>

            @if($product->trendyolMapping->trendyol_product_id)
            <dt class="col-sm-3">Trendyol Ürün ID:</dt>
            <dd class="col-sm-9"><code>{{ $product->trendyolMapping->trendyol_product_id }}</code></dd>
            @endif

            @if($product->trendyolMapping->sent_at)
            <dt class="col-sm-3">Gönderilme Tarihi:</dt>
            <dd class="col-sm-9">{{ $product->trendyolMapping->sent_at->format('d.m.Y H:i') }}</dd>
            @endif

            @if($product->trendyolMapping->sent_by)
            <dt class="col-sm-3">Gönderen:</dt>
            <dd class="col-sm-9 mb-0">{{ $product->trendyolMapping->sent_by }}</dd>
            @endif
        </dl>
    </div>
</div>
@endif

@endsection

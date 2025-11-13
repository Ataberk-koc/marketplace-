@extends('layouts.admin')

@section('title', 'Ürünler')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-box-seam"></i> Ürünler</h2>
    <div>
        <button class="btn btn-outline-secondary me-2" onclick="window.print()">
            <i class="bi bi-printer"></i> Dışa Aktar
        </button>
        <button class="btn btn-outline-secondary me-2">
            <i class="bi bi-download"></i> İçe Aktar
        </button>
        <div class="btn-group me-2">
            <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                Daha fazla
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#"><i class="bi bi-funnel"></i> Filtre</a></li>
            </ul>
        </div>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Yeni Ürün
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" id="searchInput" placeholder="Tabloda arama yapın">
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px">
                            <input type="checkbox" class="form-check-input" id="selectAll">
                        </th>
                        <th style="width: 80px;">Ürün</th>
                        <th>Ürün Adı / Barkod</th>
                        <th style="width: 120px;">Satış Fiyatı</th>
                        <th style="width: 120px;">İndirimli Fiyat</th>
                        <th style="width: 100px;">Envanter</th>
                        <th style="width: 100px;">Durum</th>
                        <th style="width: 150px;">Satış Kanalları</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr class="product-row">
                        <td>
                            <input type="checkbox" class="form-check-input product-checkbox">
                        </td>
                        <td>
                            @if($product->main_image)
                                <img src="{{ $product->main_image }}" alt="{{ $product->name }}" 
                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                            @else
                                <div class="bg-secondary rounded" style="width: 50px; height: 50px; border-radius: 8px;"></div>
                            @endif
                        </td>
                        <td>
                            <div>
                                <strong>{{ $product->name }}</strong>
                                <div class="text-muted small">
                                    {{ $product->model_code }} / {{ $product->sku ?? 'SKU yok' }}
                                </div>
                            </div>
                        </td>
                        <td>
                            <strong>₺{{ number_format($product->price, 2) }}</strong>
                        </td>
                        <td>
                            @if($product->discount_price)
                                <span class="text-danger fw-bold">₺{{ number_format($product->discount_price, 2) }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($product->variants->count() > 0)
                                <span class="badge bg-info">
                                    {{ $product->variants->sum('stock_quantity') }} adet<br>
                                    <small>{{ $product->variants->count() }} varyant</small>
                                </span>
                            @else
                                @if($product->stock > 10)
                                    <span class="badge bg-success">{{ $product->stock }} adet</span>
                                @elseif($product->stock > 0)
                                    <span class="badge bg-warning">{{ $product->stock }} adet</span>
                                @else
                                    <span class="badge bg-danger">Stok yok</span>
                                @endif
                            @endif
                        </td>
                        <td>
                            @if($product->is_active)
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Satışta</span>
                            @else
                                <span class="badge bg-secondary">Pasif</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <span class="badge bg-light text-dark border">2 Satış Kanalı</span>
                                <button class="btn btn-sm" data-bs-toggle="dropdown">
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr class="border-0">
                        <td colspan="8" class="p-0">
                            <div class="collapse" id="product-{{ $product->id }}">
                                <div class="d-flex bg-light border-top border-bottom" style="min-height: 400px;">
                                    <div class="bg-dark text-white border-end" style="width: 180px;">
                                        <div class="list-group list-group-flush">
                                            <div class="list-group-item bg-dark text-white border-0 py-2 px-3" style="font-weight: 500; cursor: default;">
                                                Ürünler
                                            </div>
                                            <a href="javascript:void(0)" class="product-menu-item list-group-item list-group-item-action bg-dark text-white border-0 py-2 px-3 active" 
                                               data-product="{{ $product->id }}" data-section="details" style="font-size: 14px;">
                                                Ürünler
                                            </a>
                                            <a href="javascript:void(0)" class="product-menu-item list-group-item list-group-item-action bg-dark text-white border-0 py-2 px-3" 
                                               data-product="{{ $product->id }}" data-section="purchase" style="font-size: 14px;">
                                                Satın Alma
                                            </a>
                                            <a href="javascript:void(0)" class="product-menu-item list-group-item list-group-item-action bg-dark text-white border-0 py-2 px-3" 
                                               data-product="{{ $product->id }}" data-section="transfer" style="font-size: 14px;">
                                                Transferler
                                            </a>
                                            <a href="javascript:void(0)" class="product-menu-item list-group-item list-group-item-action bg-dark text-white border-0 py-2 px-3" 
                                               data-product="{{ $product->id }}" data-section="stock" style="font-size: 14px;">
                                                Stok Sayımı
                                            </a>
                                            <a href="javascript:void(0)" class="product-menu-item list-group-item list-group-item-action bg-dark text-white border-0 py-2 px-3" 
                                               data-product="{{ $product->id }}" data-section="definitions" style="font-size: 14px;">
                                                Tanımlamalar
                                            </a>
                                            <a href="javascript:void(0)" class="product-menu-item list-group-item list-group-item-action bg-dark text-white border-0 py-2 px-3" 
                                               data-product="{{ $product->id }}" data-section="pricelist" style="font-size: 14px;">
                                                Fiyat Listesi
                                            </a>
                                            <a href="javascript:void(0)" class="product-menu-item list-group-item list-group-item-action bg-dark text-white border-0 py-2 px-3" 
                                               data-product="{{ $product->id }}" data-section="barcode" style="font-size: 14px;">
                                                Ürün Barkod Etiketi
                                            </a>
                                        </div>
                                    </div>
                                    <div class="flex-fill p-3" id="content-{{ $product->id }}">
                                        <!-- Ürünler (Varyant Detayları) -->
                                        <div class="product-section" data-section="details">
                                            <h6 class="mb-3">Varyant Detayları</h6>
                                            @if($product->variants->count() > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered">
                                                        <thead>
                                                            <tr class="table-secondary">
                                                                <th>Varyant</th>
                                                                <th>SKU</th>
                                                                <th>Barkod</th>
                                                                <th>Fiyat</th>
                                                                <th>Stok</th>
                                                                <th>Durum</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($product->variants as $variant)
                                                            <tr>
                                                                <td><strong>{{ $variant->variant_name ?? 'Varyant' }}</strong></td>
                                                                <td><code>{{ $variant->sku }}</code></td>
                                                                <td><code>{{ $variant->barcode }}</code></td>
                                                                <td>₺{{ number_format($variant->price, 2) }}</td>
                                                                <td>
                                                                    @if($variant->stock_quantity > 10)
                                                                        <span class="badge bg-success">{{ $variant->stock_quantity }}</span>
                                                                    @elseif($variant->stock_quantity > 0)
                                                                        <span class="badge bg-warning">{{ $variant->stock_quantity }}</span>
                                                                    @else
                                                                        <span class="badge bg-danger">0</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($variant->is_active ?? true)
                                                                        <i class="bi bi-check-circle-fill text-success"></i>
                                                                    @else
                                                                        <i class="bi bi-x-circle-fill text-danger"></i>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="alert alert-info mb-0">
                                                    <i class="bi bi-info-circle"></i> Bu ürünün varyantı yok
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Satın Alma -->
                                        <div class="product-section" data-section="purchase" style="display:none;">
                                            <h6 class="mb-3">Satın Alma Bilgileri</h6>
                                            <div class="alert alert-info">
                                                <i class="bi bi-info-circle"></i> Bu ürün için satın alma işlemleri burada görüntülenecek
                                            </div>
                                        </div>

                                        <!-- Transferler -->
                                        <div class="product-section" data-section="transfer" style="display:none;">
                                            <h6 class="mb-3">Transfer İşlemleri</h6>
                                            <div class="alert alert-info">
                                                <i class="bi bi-info-circle"></i> Bu ürün için transfer işlemleri burada görüntülenecek
                                            </div>
                                        </div>

                                        <!-- Stok Sayımı -->
                                        <div class="product-section" data-section="stock" style="display:none;">
                                            <h6 class="mb-3">Stok Sayımı</h6>
                                            @if($product->variants->count() > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>Varyant</th>
                                                                <th>Mevcut Stok</th>
                                                                <th>Sayım Stok</th>
                                                                <th>Fark</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($product->variants as $variant)
                                                            <tr>
                                                                <td>{{ $variant->variant_name }}</td>
                                                                <td>{{ $variant->stock_quantity }}</td>
                                                                <td><input type="number" class="form-control form-control-sm" style="width:80px;"></td>
                                                                <td><span class="text-muted">-</span></td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Tanımlamalar -->
                                        <div class="product-section" data-section="definitions" style="display:none;">
                                            <h6 class="mb-3">Ürün Tanımlamaları</h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p><strong>Model Kodu:</strong> {{ $product->model_code }}</p>
                                                    <p><strong>Kategori:</strong> {{ $product->category->name }}</p>
                                                    <p><strong>Marka:</strong> {{ $product->brand->name }}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong>SKU:</strong> {{ $product->sku }}</p>
                                                    <p><strong>Durum:</strong> 
                                                        @if($product->is_active)
                                                            <span class="badge bg-success">Aktif</span>
                                                        @else
                                                            <span class="badge bg-danger">Pasif</span>
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Fiyat Listesi -->
                                        <div class="product-section" data-section="pricelist" style="display:none;">
                                            <h6 class="mb-3">Fiyat Listesi</h6>
                                            @if($product->variants->count() > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered">
                                                        <thead>
                                                            <tr class="table-secondary">
                                                                <th>Varyant</th>
                                                                <th>Satış Fiyatı</th>
                                                                <th>İndirimli Fiyat</th>
                                                                <th>Maliyet</th>
                                                                <th>Kar Marjı</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($product->variants as $variant)
                                                            <tr>
                                                                <td>{{ $variant->variant_name }}</td>
                                                                <td>₺{{ number_format($variant->price, 2) }}</td>
                                                                <td>
                                                                    @if($variant->discount_price)
                                                                        ₺{{ number_format($variant->discount_price, 2) }}
                                                                    @else
                                                                        -
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($variant->cost)
                                                                        ₺{{ number_format($variant->cost, 2) }}
                                                                    @else
                                                                        -
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($variant->cost)
                                                                        <span class="badge bg-success">
                                                                            %{{ number_format((($variant->price - $variant->cost) / $variant->price) * 100, 0) }}
                                                                        </span>
                                                                    @else
                                                                        -
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Ürün Barkod Etiketi -->
                                        <div class="product-section" data-section="barcode" style="display:none;">
                                            <h6 class="mb-3">Barkod Etiketleri</h6>
                                            @if($product->variants->count() > 0)
                                                <div class="row">
                                                    @foreach($product->variants as $variant)
                                                    <div class="col-md-4 mb-3">
                                                        <div class="card">
                                                            <div class="card-body text-center">
                                                                <h6>{{ $variant->variant_name }}</h6>
                                                                <svg id="barcode-{{ $variant->id }}"></svg>
                                                                <p class="mb-0 mt-2"><code>{{ $variant->barcode }}</code></p>
                                                                <small class="text-muted">₺{{ number_format($variant->price, 2) }}</small>
                                                                <div class="mt-2">
                                                                    <button class="btn btn-sm btn-primary" onclick="printBarcode({{ $variant->id }})">
                                                                        <i class="bi bi-printer"></i> Yazdır
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="border-0" style="height: 1px;">
                        <td colspan="8" class="p-0 text-center">
                            <button class="btn btn-link btn-sm text-decoration-none" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#product-{{ $product->id }}"
                                    style="margin-top: -10px;">
                                <i class="bi bi-chevron-down"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function(){
    // Arama
    $('#searchInput').on('keyup', function(){
        var value = $(this).val().toLowerCase();
        $('.product-row').filter(function(){
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    // Tümünü seç
    $('#selectAll').on('change', function(){
        $('.product-checkbox').prop('checked', $(this).is(':checked'));
    });

    // Collapse icon değiştir
    $('[data-bs-toggle="collapse"]').on('click', function(){
        var icon = $(this).find('i');
        icon.toggleClass('bi-chevron-down bi-chevron-up');
    });

    // Sol menü tab sistemi
    $('.product-menu-item').on('click', function(){
        var productId = $(this).data('product');
        var section = $(this).data('section');
        
        // Aktif menüyü değiştir
        $('#content-' + productId).closest('.collapse').find('.product-menu-item').removeClass('active');
        $(this).addClass('active');
        
        // İçeriği değiştir
        $('#content-' + productId + ' .product-section').hide();
        $('#content-' + productId + ' .product-section[data-section="' + section + '"]').show();
    });
});

function printBarcode(variantId) {
    alert('Barkod yazdırma özelliği: Varyant ID ' + variantId);
}
</script>
@endpush
@endsection

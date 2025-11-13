@extends('layouts.admin')

@section('title', 'Stok Takibi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-boxes"></i> Stok Takibi</h2>
    <div>
        <a href="{{ route('admin.stock.bulk-update') }}" class="btn btn-warning">
            <i class="bi bi-pencil-square"></i> Toplu Güncelle
        </a>
        <a href="{{ route('admin.stock.create-movement') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Stok Hareketi
        </a>
    </div>
</div>

<!-- Filtreler -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.stock.index') }}">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Arama (SKU, Barkod, Ürün Adı)</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Ara...">
                </div>
                <div class="col-md-3">
                    <label for="stock_status" class="form-label">Stok Durumu</label>
                    <select class="form-select" id="stock_status" name="stock_status">
                        <option value="">Tümü</option>
                        <option value="available" {{ request('stock_status') == 'available' ? 'selected' : '' }}>Yeterli</option>
                        <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Düşük</option>
                        <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Tükendi</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filtrele
                    </button>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <a href="{{ route('admin.stock.index') }}" class="btn btn-secondary w-100">
                        <i class="bi bi-x-circle"></i> Temizle
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Stok Listesi -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Barkod</th>
                        <th>Ürün</th>
                        <th>Varyant</th>
                        <th>Fiyat</th>
                        <th>Toplam Stok</th>
                        <th>Rezerve</th>
                        <th>Kullanılabilir</th>
                        <th>Durum</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($variants as $variant)
                    <tr>
                        <td><code>{{ $variant->sku }}</code></td>
                        <td>
                            @if($variant->barcode)
                                <small class="text-muted">{{ $variant->barcode }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $variant->product->name }}</strong><br>
                            <small class="text-muted">
                                {{ $variant->product->brand->name ?? 'Marka Yok' }} | 
                                {{ $variant->product->category->name ?? 'Kategori Yok' }}
                            </small>
                        </td>
                        <td>
                            @if($variant->attributes)
                                @foreach($variant->attributes as $key => $value)
                                    <span class="badge bg-secondary">{{ $value }}</span>
                                @endforeach
                            @else
                                <span class="text-muted">Standart</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ number_format($variant->final_price, 2) }} ₺</strong>
                            @if($variant->discount_price)
                                <br><small class="text-muted text-decoration-line-through">{{ number_format($variant->price, 2) }} ₺</small>
                            @endif
                        </td>
                        <td><strong>{{ $variant->stock_quantity }}</strong></td>
                        <td>
                            @if($variant->reserved_quantity > 0)
                                <span class="badge bg-warning">{{ $variant->reserved_quantity }}</span>
                            @else
                                <span class="text-muted">0</span>
                            @endif
                        </td>
                        <td><strong>{{ $variant->available_stock }}</strong></td>
                        <td>
                            @if($variant->isOutOfStock())
                                <span class="badge bg-danger">Tükendi</span>
                            @elseif($variant->isLowStock())
                                <span class="badge bg-warning">Düşük</span>
                            @else
                                <span class="badge bg-success">Yeterli</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.stock.movements', $variant) }}" 
                                   class="btn btn-info" title="Hareket Geçmişi">
                                    <i class="bi bi-clock-history"></i>
                                </a>
                                <a href="{{ route('admin.stock.create-movement', ['variant_id' => $variant->id]) }}" 
                                   class="btn btn-primary" title="Stok Hareketi">
                                    <i class="bi bi-plus-circle"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            Stok kaydı bulunamadı.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $variants->links() }}
        </div>
    </div>
</div>

<!-- Stok Özeti -->
<div class="row mt-4">
    <div class="col-md-4">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h5>Tükenen Ürünler</h5>
                <h2>{{ \App\Models\ProductVariant::whereRaw('(stock_quantity - reserved_quantity) <= 0')->count() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5>Düşük Stok</h5>
                <h2>{{ \App\Models\ProductVariant::whereRaw('(stock_quantity - reserved_quantity) > 0 AND (stock_quantity - reserved_quantity) <= low_stock_threshold')->count() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5>Yeterli Stok</h5>
                <h2>{{ \App\Models\ProductVariant::whereRaw('(stock_quantity - reserved_quantity) > low_stock_threshold')->count() }}</h2>
            </div>
        </div>
    </div>
</div>

@endsection

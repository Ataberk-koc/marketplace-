@extends('layouts.admin')

@section('title', 'Stok Hareketi Ekle')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-plus-circle"></i> Stok Hareketi Ekle</h2>
    <a href="{{ route('admin.stock.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Geri Dön
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.stock.store-movement') }}" method="POST">
                    @csrf

                    <!-- Varyant Seçimi -->
                    <div class="mb-3">
                        <label for="product_variant_id" class="form-label">Ürün Varyantı <span class="text-danger">*</span></label>
                        <select class="form-select @error('product_variant_id') is-invalid @enderror" 
                                id="product_variant_id" 
                                name="product_variant_id" 
                                required>
                            <option value="">-- Varyant Seçin --</option>
                            @foreach(\App\Models\ProductVariant::with('product')->get() as $v)
                                <option value="{{ $v->id }}" 
                                        {{ (old('product_variant_id', $variant->id ?? null) == $v->id) ? 'selected' : '' }}
                                        data-sku="{{ $v->sku }}"
                                        data-barcode="{{ $v->barcode }}"
                                        data-stock="{{ $v->stock_quantity }}"
                                        data-available="{{ $v->available_stock }}">
                                    {{ $v->product->name }} - {{ $v->name }} (SKU: {{ $v->sku }}) - Stok: {{ $v->available_stock }}
                                </option>
                            @endforeach
                        </select>
                        @error('product_variant_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Mevcut Stok Bilgisi -->
                    <div id="current_stock_info" class="alert alert-info" style="display:none;">
                        <h6>Mevcut Stok Bilgisi:</h6>
                        <ul class="mb-0">
                            <li>SKU: <strong id="info_sku">-</strong></li>
                            <li>Barkod: <strong id="info_barcode">-</strong></li>
                            <li>Toplam Stok: <strong id="info_total_stock">-</strong></li>
                            <li>Kullanılabilir Stok: <strong id="info_available_stock">-</strong></li>
                        </ul>
                    </div>

                    <!-- Hareket Tipi -->
                    <div class="mb-3">
                        <label for="type" class="form-label">İşlem Tipi <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" 
                                id="type" 
                                name="type" 
                                required>
                            <option value="">-- Seçin --</option>
                            <option value="in" {{ old('type') == 'in' ? 'selected' : '' }}>Stok Girişi (+)</option>
                            <option value="out" {{ old('type') == 'out' ? 'selected' : '' }}>Stok Çıkışı (-)</option>
                            <option value="adjustment" {{ old('type') == 'adjustment' ? 'selected' : '' }}>Düzeltme (Yeni Miktar)</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Düzeltme seçeneğinde, girdiğiniz miktar yeni toplam stok miktarı olacaktır.
                        </small>
                    </div>

                    <!-- Miktar -->
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Miktar <span class="text-danger">*</span></label>
                        <input type="number" 
                               class="form-control @error('quantity') is-invalid @enderror" 
                               id="quantity" 
                               name="quantity" 
                               value="{{ old('quantity', 1) }}" 
                               min="1"
                               required>
                        @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Not -->
                    <div class="mb-3">
                        <label for="note" class="form-label">Not / Açıklama</label>
                        <textarea class="form-control @error('note') is-invalid @enderror" 
                                  id="note" 
                                  name="note" 
                                  rows="3" 
                                  placeholder="İşlem hakkında not...">{{ old('note') }}</textarea>
                        @error('note')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle"></i> Stok Hareketi Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card bg-light">
            <div class="card-body">
                <h5><i class="bi bi-info-circle"></i> Bilgilendirme</h5>
                <hr>
                <p><strong>Stok Girişi:</strong> Yeni ürün geldiğinde veya sayım sonucu stok artışı için kullanılır.</p>
                <p><strong>Stok Çıkışı:</strong> Fire, kayıp veya manuel satış için kullanılır.</p>
                <p><strong>Düzeltme:</strong> Stok sayımı sonrası veya yanlış girişleri düzeltmek için kullanılır. Girdiğiniz değer yeni toplam stok miktarı olur.</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#product_variant_id').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Varyant Seçin --',
        allowClear: true
    });

    $('#product_variant_id').on('change', function() {
        const selected = $(this).find(':selected');
        if (selected.val()) {
            $('#info_sku').text(selected.data('sku'));
            $('#info_barcode').text(selected.data('barcode') || '-');
            $('#info_total_stock').text(selected.data('stock'));
            $('#info_available_stock').text(selected.data('available'));
            $('#current_stock_info').show();
        } else {
            $('#current_stock_info').hide();
        }
    });

    // Sayfa yüklendiğinde seçili varsa göster
    if ($('#product_variant_id').val()) {
        $('#product_variant_id').trigger('change');
    }
});
</script>
@endpush

@endsection

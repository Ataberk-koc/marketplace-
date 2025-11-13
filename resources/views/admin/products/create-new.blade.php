@extends('layouts.admin')

@section('title', 'Yeni Ürün Ekle')

@push('styles')
<style>
    .nav-tabs .nav-link {
        color: #495057;
    }
    .nav-tabs .nav-link.active {
        font-weight: bold;
    }
    .variant-table {
        font-size: 14px;
    }
    .variant-table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    .variant-table td {
        vertical-align: middle;
    }
    .variant-image {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border: 1px solid #dee2e6;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-plus-circle"></i> Yeni Ürün Ekle</h2>
    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Geri Dön
    </a>
</div>

<form action="{{ route('admin.products.store') }}" method="POST" id="productForm">
    @csrf
    
    <!-- Sekmeler -->
    <ul class="nav nav-tabs mb-3" id="productTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button">
                <i class="bi bi-info-circle"></i> ÜRÜN BİLGİLERİ
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="variants-tab" data-bs-toggle="tab" data-bs-target="#variants" type="button">
                <i class="bi bi-grid-3x3"></i> SATIŞ VE VARYANT BİLGİLERİ
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="attributes-tab" data-bs-toggle="tab" data-bs-target="#attributes" type="button">
                <i class="bi bi-list-stars"></i> ÜRÜN ÖZELLİKLERİ
            </button>
        </li>
    </ul>

    <div class="tab-content" id="productTabsContent">
        
        <!-- SEKME 1: ÜRÜN BİLGİLERİ -->
        <div class="tab-pane fade show active" id="info" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Ürün Adı <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="model_code" class="form-label">Model Kodu <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('model_code') is-invalid @enderror" 
                                       id="model_code" name="model_code" value="{{ old('model_code') }}" 
                                       placeholder="Örn: TW8323PLSG4" required>
                                @error('model_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Kategori <span class="text-danger">*</span></label>
                                <select class="form-select @error('category_id') is-invalid @enderror" 
                                        id="category_id" name="category_id" required>
                                    <option value="">-- Kategori Seçin --</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="brand_id" class="form-label">Marka <span class="text-danger">*</span></label>
                                <select class="form-select @error('brand_id') is-invalid @enderror" 
                                        id="brand_id" name="brand_id" required>
                                    <option value="">-- Marka Seçin --</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                            {{ $brand->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('brand_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="mb-3">
                                <label for="description" class="form-label">Açıklama</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="5">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEKME 2: SATIŞ VE VARYANT BİLGİLERİ -->
        <div class="tab-pane fade" id="variants" role="tabpanel">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Satış ve Varyant Bilgileri</h5>
                    <button type="button" class="btn btn-primary btn-sm" id="addVariantBtn">
                        <i class="bi bi-plus-circle"></i> Varyant Ekle
                    </button>
                </div>
                <div class="card-body">
                    
                    <!-- Varyant Tablosu -->
                    <div class="table-responsive">
                        <table class="table table-bordered variant-table" id="variantTable">
                            <thead>
                                <tr>
                                    <th width="60">Görsel</th>
                                    <th width="100">Renk</th>
                                    <th width="80">Beden</th>
                                    <th width="120">Barkod</th>
                                    <th width="100">Stok Kodu (SKU)</th>
                                    <th width="100">Fiyat (Satış Fiyatı)</th>
                                    <th width="100">İndirimli Fiyat</th>
                                    <th width="80">Stok</th>
                                    <th width="60">KDV</th>
                                    <th width="80">Stok Kodu</th>
                                    <th width="60">İşlem</th>
                                </tr>
                            </thead>
                            <tbody id="variantTableBody">
                                <tr id="noVariantRow">
                                    <td colspan="11" class="text-center text-muted">
                                        Henüz varyant eklenmedi. "Varyant Ekle" butonuna tıklayarak başlayın.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>

        <!-- SEKME 3: ÜRÜN ÖZELLİKLERİ -->
        <div class="tab-pane fade" id="attributes" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Ürün özellikleri ürün kaydedildikten sonra eklenebilir.
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Kaydet Butonu -->
    <div class="card mt-3">
        <div class="card-body">
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="bi bi-check-circle"></i> Ürünü Oluştur
                </button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> İptal
                </a>
            </div>
        </div>
    </div>

</form>

@push('scripts')
<script>
$(document).ready(function() {
    let variantIndex = 0;

    // Varyant Ekle
    $('#addVariantBtn').on('click', function() {
        addVariantRow();
    });

    function addVariantRow() {
        variantIndex++;
        
        const row = `
            <tr data-index="${variantIndex}">
                <td>
                    <input type="file" class="form-control form-control-sm" name="variants[${variantIndex}][image]" accept="image/*">
                </td>
                <td>
                    <select class="form-select form-select-sm" name="variants[${variantIndex}][color]" required>
                        <option value="">Seçin</option>
                        <option value="Kırmızı">Kırmızı</option>
                        <option value="Mavi">Mavi</option>
                        <option value="Yeşil">Yeşil</option>
                        <option value="Siyah">Siyah</option>
                        <option value="Beyaz">Beyaz</option>
                        <option value="Sarı">Sarı</option>
                        <option value="Turuncu">Turuncu</option>
                        <option value="Mor">Mor</option>
                        <option value="Pembe">Pembe</option>
                        <option value="Gri">Gri</option>
                    </select>
                </td>
                <td>
                    <select class="form-select form-select-sm" name="variants[${variantIndex}][size]" required>
                        <option value="">Seçin</option>
                        <option value="XS">XS</option>
                        <option value="S">S</option>
                        <option value="M">M</option>
                        <option value="L">L</option>
                        <option value="XL">XL</option>
                        <option value="XXL">XXL</option>
                        <option value="XXXL">XXXL</option>
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="variants[${variantIndex}][barcode]" 
                           placeholder="1234567890123" required>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="variants[${variantIndex}][sku]" 
                           placeholder="PRD-RED-S" required>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" name="variants[${variantIndex}][price]" 
                           step="0.01" min="0" placeholder="199.99" required>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" name="variants[${variantIndex}][discount_price]" 
                           step="0.01" min="0" placeholder="179.99">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" name="variants[${variantIndex}][stock]" 
                           min="0" value="0" required>
                </td>
                <td>
                    <select class="form-select form-select-sm" name="variants[${variantIndex}][vat_rate]">
                        <option value="20">%20</option>
                        <option value="10">%10</option>
                        <option value="1">%1</option>
                        <option value="0">%0</option>
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="variants[${variantIndex}][stock_code]" 
                           placeholder="STK-001">
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-variant-btn" title="Sil">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;

        $('#noVariantRow').remove();
        $('#variantTableBody').append(row);
    }

    // Varyant Sil
    $(document).on('click', '.remove-variant-btn', function() {
        $(this).closest('tr').remove();
        
        if ($('#variantTableBody tr').length === 0) {
            $('#variantTableBody').html(`
                <tr id="noVariantRow">
                    <td colspan="11" class="text-center text-muted">
                        Henüz varyant eklenmedi. "Varyant Ekle" butonuna tıklayarak başlayın.
                    </td>
                </tr>
            `);
        }
    });

    // Select2 initialization
    $('#category_id, #brand_id').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Seçin --'
    });
});
</script>
@endpush

@endsection

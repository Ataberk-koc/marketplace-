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

<form action="{{ route('admin.products.store') }}" method="POST" id="productForm" enctype="multipart/form-data">
    @csrf

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Hata!</strong> Lütfen aşağıdaki hataları düzeltin:
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
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
                                       id="name" name="name" value="{{ old('name') }}">
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
                                       placeholder="Örn: TW8323PLSG4">
                                @error('model_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Kategori <span class="text-danger">*</span></label>
                                <select class="form-select @error('category_id') is-invalid @enderror" 
                                        id="category_id" name="category_id">
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
                    <h6 class="mb-3"><i class="bi bi-list-stars"></i> Ürün Özellikleri</h6>
                    <p class="text-muted small">Ürününüzün teknik özelliklerini girebilirsiniz. Bu özellikler müşterilere ürün detaylarında gösterilir.</p>

                    <div class="row g-3" id="attributesContainer">
                        <!-- Sol Kolon -->
                        <div class="col-md-6">
                            <!-- YAŞ GENİŞLİĞİ -->
                            <div class="mb-3">
                                <label class="form-label">YAŞ GENİŞLİĞİ (OPSİYONEL)</label>
                                <select class="form-select" name="attributes[yas_genligi]">
                                    <option value="">Seçim Yapınız</option>
                                    <option value="0-3 Ay">0-3 Ay</option>
                                    <option value="3-6 Ay">3-6 Ay</option>
                                    <option value="6-9 Ay">6-9 Ay</option>
                                    <option value="9-12 Ay">9-12 Ay</option>
                                    <option value="12-18 Ay">12-18 Ay</option>
                                    <option value="18-24 Ay">18-24 Ay</option>
                                    <option value="2-3 Yaş">2-3 Yaş</option>
                                    <option value="3-4 Yaş">3-4 Yaş</option>
                                    <option value="4-5 Yaş">4-5 Yaş</option>
                                    <option value="5-6 Yaş">5-6 Yaş</option>
                                    <option value="6-7 Yaş">6-7 Yaş</option>
                                    <option value="7-8 Yaş">7-8 Yaş</option>
                                    <option value="8-9 Yaş">8-9 Yaş</option>
                                    <option value="9-10 Yaş">9-10 Yaş</option>
                                    <option value="10-11 Yaş">10-11 Yaş</option>
                                    <option value="11-12 Yaş">11-12 Yaş</option>
                                    <option value="12-13 Yaş">12-13 Yaş</option>
                                    <option value="13-14 Yaş">13-14 Yaş</option>
                                    <option value="14-15 Yaş">14-15 Yaş</option>
                                    <option value="15-16 Yaş">15-16 Yaş</option>
                                    <option value="Yetişkin">Yetişkin</option>
                                </select>
                            </div>

                            <!-- BEL YÜKSEKLIĞI -->
                            <div class="mb-3">
                                <label class="form-label">BEL YÜKSEKLİĞİ (OPSİYONEL)</label>
                                <select class="form-select" name="attributes[bel_yuksekligi]">
                                    <option value="">Seçim Yapınız</option>
                                    <option value="Düşük Bel">Düşük Bel</option>
                                    <option value="Normal Bel">Normal Bel</option>
                                    <option value="Yüksek Bel">Yüksek Bel</option>
                                </select>
                            </div>

                            <!-- MATERYAL -->
                            <div class="mb-3">
                                <label class="form-label">MATERYAL (OPSİYONEL)</label>
                                <select class="form-select" name="attributes[materyal]">
                                    <option value="">Seçim Yapınız</option>
                                    <option value="Pamuk">%100 Pamuk</option>
                                    <option value="Polyester">%100 Polyester</option>
                                    <option value="Pamuk-Polyester">Pamuk-Polyester Karışım</option>
                                    <option value="Viskon">Viskon</option>
                                    <option value="Keten">Keten</option>
                                    <option value="Yün">Yün</option>
                                    <option value="Naylon">Naylon</option>
                                    <option value="Denim">Denim</option>
                                    <option value="Kot">Kot</option>
                                    <option value="Triko">Triko</option>
                                </select>
                            </div>

                            <!-- KALIP -->
                            <div class="mb-3">
                                <label class="form-label">KALIP (OPSİYONEL)</label>
                                <select class="form-select" name="attributes[kalip]">
                                    <option value="">Seçim Yapınız</option>
                                    <option value="Dar Kalıp">Dar Kalıp</option>
                                    <option value="Slim Fit">Slim Fit</option>
                                    <option value="Regular Fit">Regular Fit</option>
                                    <option value="Oversize">Oversize</option>
                                    <option value="Bol Kalıp">Bol Kalıp</option>
                                </select>
                            </div>

                            <!-- KOL BOYU -->
                            <div class="mb-3">
                                <label class="form-label">KOL BOYU (OPSİYONEL)</label>
                                <select class="form-select" name="attributes[kol_boyu]">
                                    <option value="">Seçim Yapınız</option>
                                    <option value="Kolsuz">Kolsuz</option>
                                    <option value="Kısa Kol">Kısa Kol</option>
                                    <option value="3/4 Kol">3/4 Kol</option>
                                    <option value="Uzun Kol">Uzun Kol</option>
                                </select>
                            </div>

                            <!-- YAKA TİPİ -->
                            <div class="mb-3">
                                <label class="form-label">YAKA TİPİ (OPSİYONEL)</label>
                                <select class="form-select" name="attributes[yaka_tipi]">
                                    <option value="">Seçim Yapınız</option>
                                    <option value="Bisiklet Yaka">Bisiklet Yaka</option>
                                    <option value="Polo Yaka">Polo Yaka</option>
                                    <option value="V Yaka">V Yaka</option>
                                    <option value="Gömlek Yaka">Gömlek Yaka</option>
                                    <option value="Hakim Yaka">Hakim Yaka</option>
                                    <option value="Kapüşonlu">Kapüşonlu</option>
                                    <option value="Balıkçı Yaka">Balıkçı Yaka</option>
                                    <option value="Boğazlı">Boğazlı</option>
                                </select>
                            </div>
                        </div>

                        <!-- Sağ Kolon -->
                        <div class="col-md-6">
                            <!-- CİNSİYET -->
                            <div class="mb-3">
                                <label class="form-label">CİNSİYET (OPSİYONEL)</label>
                                <select class="form-select" name="attributes[cinsiyet]">
                                    <option value="">Seçim Yapınız</option>
                                    <option value="Erkek">Erkek</option>
                                    <option value="Kadın">Kadın</option>
                                    <option value="Unisex">Unisex</option>
                                    <option value="Kız Çocuk">Kız Çocuk</option>
                                    <option value="Erkek Çocuk">Erkek Çocuk</option>
                                </select>
                            </div>

                            <!-- DESEN -->
                            <div class="mb-3">
                                <label class="form-label">DESEN (OPSİYONEL)</label>
                                <select class="form-select" name="attributes[desen]">
                                    <option value="">Seçim Yapınız</option>
                                    <option value="Düz">Düz</option>
                                    <option value="Çizgili">Çizgili</option>
                                    <option value="Desenli">Desenli</option>
                                    <option value="Baskılı">Baskılı</option>
                                    <option value="Kareli">Kareli</option>
                                    <option value="Puantiyeli">Puantiyeli</option>
                                    <option value="Çiçek Desenli">Çiçek Desenli</option>
                                </select>
                            </div>

                            <!-- KUMAŞ TİPİ -->
                            <div class="mb-3">
                                <label class="form-label">KUMAŞ TİPİ (OPSİYONEL)</label>
                                <select class="form-select" name="attributes[kumas_tipi]">
                                    <option value="">Seçim Yapınız</option>
                                    <option value="Örme">Örme</option>
                                    <option value="Dokuma">Dokuma</option>
                                    <option value="Triko">Triko</option>
                                    <option value="Polar">Polar</option>
                                    <option value="Süet">Süet</option>
                                    <option value="Deri">Deri</option>
                                </select>
                            </div>

                            <!-- PAÇA TİPİ -->
                            <div class="mb-3">
                                <label class="form-label">PAÇA TİPİ (OPSİYONEL)</label>
                                <select class="form-select" name="attributes[paca_tipi]">
                                    <option value="">Seçim Yapınız</option>
                                    <option value="Dar Paça">Dar Paça</option>
                                    <option value="Düz Paça">Düz Paça</option>
                                    <option value="Bol Paça">Bol Paça</option>
                                    <option value="İspanyol Paça">İspanyol Paça</option>
                                </select>
                            </div>

                            <!-- KOLEKSİYON -->
                            <div class="mb-3">
                                <label class="form-label">KOLEKSİYON (OPSİYONEL)</label>
                                <select class="form-select" name="attributes[koleksiyon]">
                                    <option value="">Seçim Yapınız</option>
                                    <option value="İlkbahar-Yaz">İlkbahar-Yaz</option>
                                    <option value="Sonbahar-Kış">Sonbahar-Kış</option>
                                    <option value="4 Mevsim">4 Mevsim</option>
                                </select>
                            </div>

                            <!-- TREND -->
                            <div class="mb-3">
                                <label class="form-label">TREND (OPSİYONEL)</label>
                                <select class="form-select" name="attributes[trend]">
                                    <option value="">Seçim Yapınız</option>
                                    <option value="Basic">Basic</option>
                                    <option value="Casual">Casual</option>
                                    <option value="Sporty">Sporty</option>
                                    <option value="Classic">Classic</option>
                                    <option value="Modern">Modern</option>
                                    <option value="Vintage">Vintage</option>
                                </select>
                            </div>
                        </div>

                        <!-- Ekstra Özellikler Bölümü -->
                        <div class="col-12">
                            <hr>
                            <h6 class="mb-3"><i class="bi bi-plus-circle"></i> Ekstra Özellikler</h6>
                            <div id="extraAttributesContainer"></div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addExtraAttributeBtn">
                                <i class="bi bi-plus"></i> Özellik Ekle
                            </button>
                        </div>
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
                    <select class="form-select form-select-sm" name="variants[${variantIndex}][color]">
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
                    <select class="form-select form-select-sm" name="variants[${variantIndex}][size]">
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
                           placeholder="1234567890123">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="variants[${variantIndex}][sku]" 
                           placeholder="PRD-RED-S">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" name="variants[${variantIndex}][price]" 
                           step="0.01" min="0" placeholder="199.99">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" name="variants[${variantIndex}][discount_price]" 
                           step="0.01" min="0" placeholder="179.99">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" name="variants[${variantIndex}][stock]" 
                           min="0" value="0">
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

    // Ekstra Özellik Ekle
    let extraAttrIndex = 0;
    $('#addExtraAttributeBtn').on('click', function() {
        const attrRow = `
            <div class="row mb-2 extra-attr-row" data-index="${extraAttrIndex}">
                <div class="col-5">
                    <input type="text" class="form-control form-control-sm" 
                           name="extra_attributes[${extraAttrIndex}][name]" 
                           placeholder="Özellik Adı (ör: Cep Sayısı)">
                </div>
                <div class="col-5">
                    <input type="text" class="form-control form-control-sm" 
                           name="extra_attributes[${extraAttrIndex}][value]" 
                           placeholder="Değer (ör: 2 Adet)">
                </div>
                <div class="col-2">
                    <button type="button" class="btn btn-danger btn-sm remove-extra-attr-btn w-100">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
        $('#extraAttributesContainer').append(attrRow);
        extraAttrIndex++;
    });

    // Ekstra Özellik Sil
    $(document).on('click', '.remove-extra-attr-btn', function() {
        $(this).closest('.extra-attr-row').remove();
    });

    // Select2 initialization
    $('#category_id, #brand_id').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Seçin --'
    });

    // Form validasyonu ve submit
    $('#productForm').on('submit', function(e) {
        console.log('Form submit triggered');
        
        // Varyant kontrolü
        const variantCount = $('#variantTableBody tr').not('#noVariantRow').length;
        console.log('Variant count:', variantCount);
        
        if (variantCount === 0) {
            e.preventDefault();
            alert('Lütfen en az bir varyant ekleyin!');
            $('#variants-tab').tab('show');
            return false;
        }

        // Gerekli alanların kontrolü
        let isValid = true;
        let errorMessage = '';

        // Ürün adı kontrolü
        if (!$('#name').val().trim()) {
            isValid = false;
            errorMessage = 'Ürün adı zorunludur!';
            $('#info-tab').tab('show');
            $('#name').focus();
        }

        // Model kodu kontrolü
        else if (!$('#model_code').val().trim()) {
            isValid = false;
            errorMessage = 'Model kodu zorunludur!';
            $('#info-tab').tab('show');
            $('#model_code').focus();
        }

        // Kategori kontrolü
        else if (!$('#category_id').val()) {
            isValid = false;
            errorMessage = 'Kategori seçmelisiniz!';
            $('#info-tab').tab('show');
            $('#category_id').focus();
        }

        // Varyant alanlarını kontrol et
        else {
            $('#variantTableBody tr').not('#noVariantRow').each(function(index) {
                const row = $(this);
                const variantNum = index + 1;
                
                if (!row.find('select[name*="[color]"]').val()) {
                    isValid = false;
                    errorMessage = `Varyant ${variantNum}: Renk seçmelisiniz!`;
                    $('#variants-tab').tab('show');
                    row.find('select[name*="[color]"]').focus();
                    return false;
                }
                
                if (!row.find('select[name*="[size]"]').val()) {
                    isValid = false;
                    errorMessage = `Varyant ${variantNum}: Beden seçmelisiniz!`;
                    $('#variants-tab').tab('show');
                    row.find('select[name*="[size]"]').focus();
                    return false;
                }
                
                if (!row.find('input[name*="[barcode]"]').val().trim()) {
                    isValid = false;
                    errorMessage = `Varyant ${variantNum}: Barkod zorunludur!`;
                    $('#variants-tab').tab('show');
                    row.find('input[name*="[barcode]"]').focus();
                    return false;
                }
                
                if (!row.find('input[name*="[sku]"]').val().trim()) {
                    isValid = false;
                    errorMessage = `Varyant ${variantNum}: SKU zorunludur!`;
                    $('#variants-tab').tab('show');
                    row.find('input[name*="[sku]"]').focus();
                    return false;
                }
                
                if (!row.find('input[name*="[price]"]').val() || parseFloat(row.find('input[name*="[price]"]').val()) <= 0) {
                    isValid = false;
                    errorMessage = `Varyant ${variantNum}: Geçerli bir fiyat giriniz!`;
                    $('#variants-tab').tab('show');
                    row.find('input[name*="[price]"]').focus();
                    return false;
                }
            });
        }

        if (!isValid) {
            e.preventDefault();
            alert(errorMessage);
            return false;
        }

        console.log('Form is valid, submitting...');
        
        // Submit butonu disable
        $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Kaydediliyor...');
        
        // Form verilerini logla
        const formData = new FormData(this);
        console.log('Form data entries:');
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }
    });
});
</script>
@endpush

@endsection

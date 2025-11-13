@extends('layouts.admin')

@section('title', 'Yeni Ürün Ekle')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-plus-circle"></i> Yeni Ürün Ekle</h2>
    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Geri Dön
    </a>
</div>

<form action="{{ route('admin.products.store') }}" method="POST" id="productForm">
    @csrf
    
    <div class="row">
        <!-- Sol Kolon -->
        <div class="col-lg-8">
            <!-- Genel Bilgiler -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Genel Bilgiler</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Ürün Adı <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}" 
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="sku" class="form-label">SKU (Stok Kodu) <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('sku') is-invalid @enderror" 
                               id="sku" 
                               name="sku" 
                               value="{{ old('sku') }}" 
                               placeholder="Örn: PRD-001"
                               required>
                        <small class="form-text text-muted">Benzersiz ürün kodu olmalıdır</small>
                        @error('sku')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Açıklama</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" 
                                  name="description" 
                                  rows="5">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Fiyat ve Stok -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-currency-dollar"></i> Fiyat ve Stok Bilgileri</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="price" class="form-label">Fiyat (₺) <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('price') is-invalid @enderror" 
                                   id="price" 
                                   name="price" 
                                   value="{{ old('price') }}" 
                                   step="0.01" 
                                   min="0"
                                   required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="discount_price" class="form-label">İndirimli Fiyat (₺)</label>
                            <input type="number" 
                                   class="form-control @error('discount_price') is-invalid @enderror" 
                                   id="discount_price" 
                                   name="discount_price" 
                                   value="{{ old('discount_price') }}" 
                                   step="0.01" 
                                   min="0">
                            @error('discount_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="stock_quantity" class="form-label">Stok Miktarı <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('stock_quantity') is-invalid @enderror" 
                                   id="stock_quantity" 
                                   name="stock_quantity" 
                                   value="{{ old('stock_quantity', 0) }}" 
                                   min="0"
                                   required>
                            @error('stock_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Görseller -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-images"></i> Ürün Görselleri</h5>
                </div>
                <div class="card-body">
                    <div id="imageInputs">
                        <div class="mb-3 image-input-group">
                            <label class="form-label">Görsel URL 1 (Ana Görsel)</label>
                            <div class="input-group">
                                <input type="url" 
                                       class="form-control" 
                                       name="images[]" 
                                       value="{{ old('images.0') }}" 
                                       placeholder="https://example.com/image.jpg">
                                <button type="button" class="btn btn-outline-secondary" onclick="previewImage(this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="image-preview mt-2" style="display: none;">
                                <img src="" alt="Preview" style="max-width: 200px; max-height: 200px;" class="img-thumbnail">
                            </div>
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addImageInput()">
                        <i class="bi bi-plus-circle"></i> Görsel Ekle
                    </button>
                </div>
            </div>
        </div>

        <!-- Sağ Kolon -->
        <div class="col-lg-4">
            <!-- Kategori ve Marka -->
            <div class="card mb-4">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="bi bi-tag"></i> Kategori ve Marka</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select class="form-select @error('category_id') is-invalid @enderror" 
                                id="category_id" 
                                name="category_id" 
                                required>
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

                    <!-- Trendyol Kategori Eşleştirme ve Özellikler -->
                    <div id="trendyol_category_info" style="display:none;">
                        <div class="alert alert-info mb-2" id="trendyol_category_alert" style="display:none;"></div>
                        <div id="trendyol_attributes_section" style="display:none;">
                            <label class="form-label"><i class="bi bi-tags"></i> Trendyol Kategori Özellikleri</label>
                            <div id="trendyol_attributes_table"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="brand_id" class="form-label">Marka <span class="text-danger">*</span></label>
                        <select class="form-select @error('brand_id') is-invalid @enderror" 
                                id="brand_id" 
                                name="brand_id" 
                                required>
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
            </div>

            <!-- Durum Ayarları -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-toggles"></i> Durum Ayarları</h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="is_active" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            <i class="bi bi-check-circle text-success"></i> Aktif
                        </label>
                        <small class="form-text text-muted d-block">
                            Aktif ürünler sitede görünür
                        </small>
                    </div>

                    <div class="form-check form-switch">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="is_featured" 
                               name="is_featured" 
                               value="1"
                               {{ old('is_featured', false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_featured">
                            <i class="bi bi-star text-warning"></i> Öne Çıkan
                        </label>
                        <small class="form-text text-muted d-block">
                            Öne çıkan ürünler ana sayfada gösterilir
                        </small>
                    </div>
                </div>
            </div>

            <!-- ÜRÜN VARYANTLARI (YENİ) -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-palette"></i> Ürün Varyantları (Renk, Beden vs.)</h5>
                    <button type="button" class="btn btn-sm btn-light" id="addVariantBtn">
                        <i class="bi bi-plus-circle"></i> Varyant Ekle
                    </button>
                </div>
                <div class="card-body">
                    <p class="text-muted">Ürününüzün farklı renk, beden veya diğer varyasyonları varsa buradan ekleyebilirsiniz. Her varyant için ayrı SKU, barkod, fiyat ve stok girebilirsiniz.</p>
                    
                    <div id="variantsContainer">
                        <!-- Varyantlar buraya eklenecek -->
                    </div>

                    <div id="noVariantsMessage" class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Henüz varyant eklenmedi. "Varyant Ekle" butonuna tıklayarak başlayın.
                    </div>
                </div>
            </div>

            <!-- Kaydet Butonu -->
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle"></i> Ürünü Kaydet
                        </button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> İptal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
let imageCount = 1;

function addImageInput() {
    imageCount++;
    const html = `
        <div class="mb-3 image-input-group">
            <label class="form-label">Görsel URL ${imageCount}</label>
            <div class="input-group">
                <input type="url" 
                       class="form-control" 
                       name="images[]" 
                       placeholder="https://example.com/image.jpg">
                <button type="button" class="btn btn-outline-secondary" onclick="previewImage(this)">
                    <i class="bi bi-eye"></i>
                </button>
                <button type="button" class="btn btn-outline-danger" onclick="removeImageInput(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <div class="image-preview mt-2" style="display: none;">
                <img src="" alt="Preview" style="max-width: 200px; max-height: 200px;" class="img-thumbnail">
            </div>
        </div>
    `;
    document.getElementById('imageInputs').insertAdjacentHTML('beforeend', html);
}

function removeImageInput(btn) {
    btn.closest('.image-input-group').remove();
}

function previewImage(btn) {
    const input = btn.previousElementSibling;
    const previewDiv = btn.closest('.input-group').nextElementSibling;
    const img = previewDiv.querySelector('img');
    
    if (input.value) {
        img.src = input.value;
        previewDiv.style.display = 'block';
    } else {
        previewDiv.style.display = 'none';
    }
}

// Select2 initialization
$(document).ready(function() {
    $('#category_id').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Kategori Seçin --',
        allowClear: true,
        language: 'tr'
    });

    $('#brand_id').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Marka Seçin --',
        allowClear: true,
        language: 'tr'

        // Trendyol kategori ve attribute entegrasyonu
        const categoryMappings = @json(\App\Models\CategoryMapping::with('category')->get()->keyBy('category_id'));

        $('#category_id').on('change', function() {
            const categoryId = $(this).val();
            $('#trendyol_category_info').hide();
            $('#trendyol_category_alert').hide();
            $('#trendyol_attributes_section').hide();
            $('#trendyol_attributes_table').html('');

            if (!categoryId || !categoryMappings[categoryId]) {
                $('#trendyol_category_alert').text('Bu kategori için Trendyol eşleştirmesi yok.').show();
                $('#trendyol_category_info').show();
                return;
            }

            // Trendyol kategori ID'si
            const trendyolCategoryId = categoryMappings[categoryId].trendyol_category_id;
            const trendyolCategoryName = categoryMappings[categoryId].trendyol_category_name;
            $('#trendyol_category_alert').html('<b>Trendyol Kategori:</b> ' + trendyolCategoryName + ' <span class="badge bg-secondary">ID: ' + trendyolCategoryId + '</span>').show();
            $('#trendyol_category_info').show();

            // AJAX ile Trendyol attribute'larını getir
            $.ajax({
                url: '/admin/ajax/trendyol-category-attributes/' + trendyolCategoryId,
                method: 'GET',
                success: function(res) {
                    if (res.success && res.attributes.length > 0) {
                        let html = '<table class="table table-bordered table-sm"><thead><tr><th>Özellik</th><th>Zorunlu</th><th>Varyant</th><th>Değerler (ilk 5)</th></tr></thead><tbody>';
                        res.attributes.forEach(function(attr) {
                            html += '<tr>';
                            html += '<td><b>' + attr.attribute.name + '</b> <br><small>ID: ' + attr.attribute.id + '</small></td>';
                            html += '<td>' + (attr.attribute.required ? '<span class="badge bg-danger">Evet</span>' : '<span class="badge bg-secondary">Hayır</span>') + '</td>';
                            html += '<td>' + (attr.attribute.varianter ? '<span class="badge bg-info">Varyant</span>' : '-') + '</td>';
                            html += '<td>';
                            if(attr.attributeValues && attr.attributeValues.length > 0) {
                                html += attr.attributeValues.slice(0,5).map(v => v.name).join(', ');
                                if(attr.attributeValues.length > 5) html += ' ...';
                            } else {
                                html += '-';
                            }
                            html += '</td>';
                            html += '</tr>';
                        });
                        html += '</tbody></table>';
                        $('#trendyol_attributes_table').html(html);
                        $('#trendyol_attributes_section').show();
                    } else {
                        $('#trendyol_attributes_table').html('<div class="alert alert-warning">Bu Trendyol kategorisi için özellik bulunamadı.</div>');
                        $('#trendyol_attributes_section').show();
                    }
                },
                error: function() {
                    $('#trendyol_attributes_table').html('<div class="alert alert-danger">Trendyol özellikleri alınamadı!</div>');
                    $('#trendyol_attributes_section').show();
                }
            });
        });

        // Sayfa yüklendiğinde eski seçim varsa tetikle
        if($('#category_id').val()) {
            $('#category_id').trigger('change');
        }

    // ====== VARYANT YÖNETİMİ ======
    let variantIndex = 0;

    $('#addVariantBtn').on('click', function() {
        addVariant();
    });

    function addVariant() {
        variantIndex++;
        const html = `
            <div class="variant-item border rounded p-3 mb-3" data-index="${variantIndex}">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0"><i class="bi bi-tag"></i> Varyant #${variantIndex}</h6>
                    <button type="button" class="btn btn-sm btn-danger remove-variant-btn">
                        <i class="bi bi-trash"></i> Sil
                    </button>
                </div>

                <div class="row g-3">
                    <!-- SKU -->
                    <div class="col-md-4">
                        <label class="form-label">SKU / Model Kodu <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="variants[${variantIndex}][sku]" required>
                    </div>

                    <!-- Barkod -->
                    <div class="col-md-4">
                        <label class="form-label">Barkod</label>
                        <input type="text" class="form-control" name="variants[${variantIndex}][barcode]">
                    </div>

                    <!-- Stok -->
                    <div class="col-md-4">
                        <label class="form-label">Stok Miktarı <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="variants[${variantIndex}][stock_quantity]" min="0" value="0" required>
                    </div>

                    <!-- Renk -->
                    <div class="col-md-3">
                        <label class="form-label">Renk</label>
                        <input type="text" class="form-control variant-attr" name="variants[${variantIndex}][attributes][Renk]" placeholder="Kırmızı, Mavi...">
                    </div>

                    <!-- Beden -->
                    <div class="col-md-3">
                        <label class="form-label">Beden</label>
                        <input type="text" class="form-control variant-attr" name="variants[${variantIndex}][attributes][Beden]" placeholder="S, M, L, XL...">
                    </div>

                    <!-- Fiyat -->
                    <div class="col-md-3">
                        <label class="form-label">Fiyat <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="variants[${variantIndex}][price]" step="0.01" min="0" required>
                    </div>

                    <!-- İndirimli Fiyat -->
                    <div class="col-md-3">
                        <label class="form-label">İndirimli Fiyat</label>
                        <input type="number" class="form-control" name="variants[${variantIndex}][discount_price]" step="0.01" min="0">
                    </div>

                    <!-- Ekstra Özellikler -->
                    <div class="col-12">
                        <div class="border rounded p-2 bg-light">
                            <small class="text-muted d-block mb-2"><i class="bi bi-info-circle"></i> İsteğe bağlı ekstra özellikler ekleyebilirsiniz:</small>
                            <div class="extra-attributes-${variantIndex}"></div>
                            <button type="button" class="btn btn-sm btn-outline-secondary add-extra-attr-btn" data-index="${variantIndex}">
                                <i class="bi bi-plus"></i> Özellik Ekle
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('#variantsContainer').append(html);
        $('#noVariantsMessage').hide();
    }

    // Varyant silme
    $(document).on('click', '.remove-variant-btn', function() {
        $(this).closest('.variant-item').remove();
        if ($('.variant-item').length === 0) {
            $('#noVariantsMessage').show();
        }
    });

    // Ekstra özellik ekleme
    $(document).on('click', '.add-extra-attr-btn', function() {
        const index = $(this).data('index');
        const attrHtml = `
            <div class="input-group input-group-sm mb-2">
                <input type="text" class="form-control" placeholder="Özellik Adı" name="variants[${index}][attributes_keys][]">
                <input type="text" class="form-control" placeholder="Değer" name="variants[${index}][attributes_values][]">
                <button type="button" class="btn btn-danger remove-extra-attr-btn"><i class="bi bi-x"></i></button>
            </div>
        `;
        $(`.extra-attributes-${index}`).append(attrHtml);
    });

    // Ekstra özellik silme
    $(document).on('click', '.remove-extra-attr-btn', function() {
        $(this).closest('.input-group').remove();
    });

});

// Fiyat validasyonu
document.getElementById('discount_price').addEventListener('input', function() {
    const price = parseFloat(document.getElementById('price').value) || 0;
    const discountPrice = parseFloat(this.value) || 0;
    
    if (discountPrice >= price && price > 0) {
        this.setCustomValidity('İndirimli fiyat, normal fiyattan düşük olmalıdır!');
    } else {
        this.setCustomValidity('');
    }
});
</script>
@endpush
@endsection

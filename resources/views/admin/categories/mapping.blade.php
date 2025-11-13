@extends('layouts.admin')

@section('title', 'Kategori Eşleştirme - ' . $category->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="bi bi-link-45deg"></i> Kategori Eşleştirme</h1>
        <p class="text-muted mb-0">{{ $category->name }} kategorisini Trendyol kategorisi ile eşleştirin</p>
    </div>
    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Geri Dön
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-grid"></i> Kendi Kategoriniz</h5>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Kategori ID:</dt>
                    <dd class="col-sm-8">{{ $category->id }}</dd>
                    
                    <dt class="col-sm-4">Kategori Adı:</dt>
                    <dd class="col-sm-8"><strong>{{ $category->name }}</strong></dd>
                    
                    <dt class="col-sm-4">Slug:</dt>
                    <dd class="col-sm-8"><code>{{ $category->slug }}</code></dd>
                    
                    <dt class="col-sm-4">Ürün Sayısı:</dt>
                    <dd class="col-sm-8">
                        <span class="badge bg-info">{{ $category->products_count ?? 0 }} ürün</span>
                    </dd>
                    
                    <dt class="col-sm-4">Üst Kategori:</dt>
                    <dd class="col-sm-8">
                        @if($category->parent)
                            {{ $category->parent->name }}
                        @else
                            <span class="text-muted">Ana Kategori</span>
                        @endif
                    </dd>
                    
                    <dt class="col-sm-4">Durum:</dt>
                    <dd class="col-sm-8 mb-0">
                        @if($category->trendyolMapping)
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Eşleştirilmiş
                            </span>
                        @else
                            <span class="badge bg-warning">
                                <i class="bi bi-exclamation-circle"></i> Eşleştirilmemiş
                            </span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background-color: #F27A1A; color: white;">
                <h5 class="mb-0"><i class="bi bi-shop-window"></i> Trendyol Kategorisi</h5>
            </div>
            <div class="card-body">
                @if($category->trendyolMapping)
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Trendyol ID:</dt>
                        <dd class="col-sm-8">{{ $category->trendyolMapping->trendyol_category_id }}</dd>
                        
                        <dt class="col-sm-4">Kategori Adı:</dt>
                        <dd class="col-sm-8"><strong>{{ $category->trendyolMapping->trendyolCategory->name ?? 'N/A' }}</strong></dd>
                        
                        <dt class="col-sm-4">Kategori Yolu:</dt>
                        <dd class="col-sm-8">
                            <small class="text-muted">
                                {{ $category->trendyolMapping->trendyolCategory->path ?? 'N/A' }}
                            </small>
                        </dd>
                        
                        <dt class="col-sm-4">Eşleştirme Tarihi:</dt>
                        <dd class="col-sm-8 mb-0">{{ $category->trendyolMapping->created_at->format('d.m.Y H:i') }}</dd>
                    </dl>
                    
                    <form action="{{ route('admin.categories.save-mapping', $category) }}" method="POST" class="mt-3">
                        @csrf
                        <input type="hidden" name="trendyol_category_id" value="">
                        <button type="submit" class="btn btn-danger btn-sm w-100" onclick="return confirm('Eşleştirmeyi kaldırmak istediğinizden emin misiniz?')">
                            <i class="bi bi-trash"></i> Eşleştirmeyi Kaldır
                        </button>
                    </form>
                @else
                    <p class="text-muted">Bu kategori henüz Trendyol kategorisi ile eşleştirilmemiş.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-search"></i> Trendyol Kategorisi Seç</h5>
        <form action="{{ route('admin.categories.sync-trendyol') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-sm btn-warning">
                <i class="bi bi-arrow-repeat"></i> Trendyol Kategorilerini Senkronize Et
            </button>
        </form>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> 
            <strong>Önemli:</strong> Trendyol kategorileri hiyerarşik yapıdadır. Ürün gönderirken en alt seviye (leaf) kategori seçilmelidir.
        </div>

        <form action="{{ route('admin.categories.save-mapping', $category) }}" method="POST" id="mappingForm">
            @csrf
            
            <div class="row mb-3">
                <div class="col-md-12">
                    <label for="trendyol_category_id" class="form-label">Trendyol Kategorisi <span class="text-danger">*</span></label>
                    <select name="trendyol_category_id" id="trendyol_category_id" class="form-select select2" required style="width: 100%;">
                        <option value="">-- Trendyol kategorisi arayın veya seçin --</option>
                        @foreach($trendyolCategories as $trendyolCategory)
                            @php
                                $catId = is_array($trendyolCategory) ? $trendyolCategory['id'] : $trendyolCategory->id;
                                $catPath = is_array($trendyolCategory) ? ($trendyolCategory['path'] ?? $trendyolCategory['name']) : ($trendyolCategory->path ?? $trendyolCategory->name);
                                $catLeaf = is_array($trendyolCategory) ? ($trendyolCategory['leaf'] ?? false) : ($trendyolCategory->leaf ?? false);
                                // Boolean'ı kesinlikle string'e çevir
                                $leafString = $catLeaf ? '1' : '0';
                            @endphp
                            <option value="{{ $catId }}" 
                                    data-leaf="{{ $leafString }}"
                                    data-category-name="{{ $catPath }}"
                                    {{ old('trendyol_category_id', $category->categoryMapping->trendyol_category_id ?? '') == $catId ? 'selected' : '' }}>
                                {{ $catPath }} 
                                @if($catLeaf)
                                    ✓
                                @endif
                                (ID: {{ $catId }})
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="trendyol_category_name" id="trendyol_category_name">
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i> ✓ işareti olan kategoriler son seviye (leaf) kategorilerdir ve ürün gönderimi için uygundur.
                    </small>
                </div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-save"></i> Eşleştirmeyi Kaydet
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Select2 başlat
    $('#trendyol_category_id').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Trendyol kategorisi arayın veya seçin --',
        allowClear: true,
        language: {
            noResults: function() {
                return "Sonuç bulunamadı";
            },
            searching: function() {
                return "Aranıyor...";
            },
            inputTooShort: function() {
                return "En az 1 karakter girin";
            }
        },
        width: '100%',
        templateResult: formatCategory,
        templateSelection: formatCategorySelection
    });

    // Kategori görünümünü özelleştir (dropdown'da)
    function formatCategory(category) {
        if (!category.id) {
            return category.text;
        }
        
        const $category = $(category.element);
        const isLeaf = $category.attr('data-leaf') === '1';
        const text = category.text;
        
        if (isLeaf) {
            return $('<span><i class="bi bi-check-circle text-success"></i> ' + text + '</span>');
        }
        
        return $('<span class="text-muted">' + text + '</span>');
    }

    // Seçilen kategori görünümü
    function formatCategorySelection(category) {
        return category.text;
    }

    // Kategori seçildiğinde
    $('#trendyol_category_id').on('change', function() {
        const selectedOption = $(this).find(':selected');
        
        if (selectedOption.val()) {
            // Category name'i doldur
            const catName = selectedOption.data('category-name') || selectedOption.text().split(' (ID:')[0].trim();
            $('#trendyol_category_name').val(catName);
            
            // Leaf kontrolü - 1 = true, 0 = false
            const leafAttr = selectedOption.attr('data-leaf');
            const isLeaf = (leafAttr === '1');
            
            console.log('Category name:', catName);
            console.log('Leaf attribute:', leafAttr);
            console.log('Is leaf:', isLeaf);
            
            if (!isLeaf) {
                alert('⚠️ Uyarı: Seçtiğiniz kategori son seviye (leaf) kategori değil.\n\nTrendyol\'a ürün gönderimi için son seviye kategori (✓ işaretli) seçmeniz gerekmektedir.');
            }
        } else {
            $('#trendyol_category_name').val('');
        }
    });
    
    // Sayfa yüklendiğinde mevcut seçimi kontrol et
    if ($('#trendyol_category_id').val()) {
        $('#trendyol_category_id').trigger('change');
    }
});
</script>
@endpush
@endsection

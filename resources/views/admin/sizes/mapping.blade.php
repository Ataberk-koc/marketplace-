@extends('layouts.admin')

@section('title', 'Beden Eşleştirme - ' . $size->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="bi bi-link-45deg"></i> Beden Eşleştirme</h1>
        <p class="text-muted mb-0">{{ $size->name }} bedenini Trendyol bedeni ile eşleştirin</p>
    </div>
    <a href="{{ route('admin.sizes.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Geri Dön
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-rulers"></i> Kendi Bedeniniz</h5>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Beden ID:</dt>
                    <dd class="col-sm-8">{{ $size->id }}</dd>
                    
                    <dt class="col-sm-4">Beden Adı:</dt>
                    <dd class="col-sm-8"><strong>{{ $size->name }}</strong></dd>
                    
                    <dt class="col-sm-4">Ürün Sayısı:</dt>
                    <dd class="col-sm-8">
                        <span class="badge bg-info">{{ $size->products_count ?? 0 }} ürün</span>
                    </dd>
                    
                    <dt class="col-sm-4">Durum:</dt>
                    <dd class="col-sm-8 mb-0">
                        @if($size->trendyolMapping)
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
                <h5 class="mb-0"><i class="bi bi-shop-window"></i> Trendyol Bedeni</h5>
            </div>
            <div class="card-body">
                @if($size->trendyolMapping)
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Trendyol ID:</dt>
                        <dd class="col-sm-8">{{ $size->trendyolMapping->trendyol_size_id }}</dd>
                        
                        <dt class="col-sm-4">Beden Adı:</dt>
                        <dd class="col-sm-8"><strong>{{ $size->trendyolMapping->trendyolSize->name ?? 'N/A' }}</strong></dd>
                        
                        <dt class="col-sm-4">Özellik Tipi:</dt>
                        <dd class="col-sm-8">
                            <code>{{ $size->trendyolMapping->trendyolSize->attribute_type ?? 'N/A' }}</code>
                        </dd>
                        
                        <dt class="col-sm-4">Eşleştirme Tarihi:</dt>
                        <dd class="col-sm-8 mb-0">{{ $size->trendyolMapping->created_at->format('d.m.Y H:i') }}</dd>
                    </dl>
                    
                    <form action="{{ route('admin.sizes.save-mapping', $size) }}" method="POST" class="mt-3">
                        @csrf
                        <input type="hidden" name="trendyol_size_id" value="">
                        <button type="submit" class="btn btn-danger btn-sm w-100" onclick="return confirm('Eşleştirmeyi kaldırmak istediğinizden emin misiniz?')">
                            <i class="bi bi-trash"></i> Eşleştirmeyi Kaldır
                        </button>
                    </form>
                @else
                    <p class="text-muted">Bu beden henüz Trendyol bedeni ile eşleştirilmemiş.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-search"></i> Trendyol Bedeni Seç</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> 
            <strong>Önemli:</strong> Trendyol bedenleri kategoriye göre değişir. Önce kategoriye uygun bedenleri senkronize ettiğinizden emin olun.
        </div>

        <form action="{{ route('admin.sizes.save-mapping', $size) }}" method="POST">
            @csrf
            
            <div class="row mb-3">
                <div class="col-md-12">
                    <label for="trendyol_size_id" class="form-label">Trendyol Bedeni <span class="text-danger">*</span></label>
                    <select name="trendyol_size_id" id="trendyol_size_id" class="form-select select2" required style="width: 100%;">
                        <option value="">-- Trendyol bedeni arayın veya seçin --</option>
                        @foreach($trendyolSizes as $trendyolSize)
                            <option value="{{ $trendyolSize->id }}" 
                                    data-attribute-type="{{ $trendyolSize->attribute_type ?? 'size' }}"
                                    {{ old('trendyol_size_id', $size->sizeMapping->trendyol_size_id ?? '') == $trendyolSize->id ? 'selected' : '' }}>
                                {{ $trendyolSize->name }} 
                                ({{ $trendyolSize->attribute_type ?? 'size' }})
                                - ID: {{ $trendyolSize->id }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i> Önce "Trendyol Bedenlerini Senkronize Et" butonuna tıklayın, ardından arama yaparak beden seçin.
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
    $('#trendyol_size_id').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Trendyol bedeni arayın veya seçin --',
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
        templateResult: formatSize
    });

    // Beden görünümünü özelleştir (dropdown'da)
    function formatSize(size) {
        if (!size.id) {
            return size.text;
        }
        
        const $size = $(size.element);
        const attributeType = $size.data('attribute-type') || 'size';
        const text = size.text;
        
        // Özellik tipine göre ikon ekle
        let icon = '<i class="bi bi-rulers"></i>';
        if (attributeType === 'color') {
            icon = '<i class="bi bi-palette"></i>';
        }
        
        return $('<span>' + icon + ' ' + text + '</span>');
    }
    
    // Sayfa yüklendiğinde mevcut seçimi kontrol et
    if ($('#trendyol_size_id').val()) {
        $('#trendyol_size_id').trigger('change');
    }
});
</script>
@endpush
@endsection

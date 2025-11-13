@extends('layouts.admin')

@section('title', 'Marka Eşleştirme - ' . $brand->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="bi bi-link-45deg"></i> Marka Eşleştirme</h1>
        <p class="text-muted mb-0">{{ $brand->name }} markasını Trendyol markası ile eşleştirin</p>
    </div>
    <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary">
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

@if(count($trendyolBrands) === 0)
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i> 
        Henüz Trendyol markası yüklenmemiş. Lütfen "Trendyol Markalarını Senkronize Et" butonuna tıklayın.
    </div>
@else
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> 
        {{ count($trendyolBrands) }} Trendyol markası yüklendi.
    </div>
@endif

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-shop"></i> Kendi Markanız</h5>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Marka ID:</dt>
                    <dd class="col-sm-8">{{ $brand->id }}</dd>
                    
                    <dt class="col-sm-4">Marka Adı:</dt>
                    <dd class="col-sm-8"><strong>{{ $brand->name }}</strong></dd>
                    
                    <dt class="col-sm-4">Slug:</dt>
                    <dd class="col-sm-8"><code>{{ $brand->slug }}</code></dd>
                    
                    <dt class="col-sm-4">Ürün Sayısı:</dt>
                    <dd class="col-sm-8">
                        <span class="badge bg-info">{{ $brand->products_count ?? 0 }} ürün</span>
                    </dd>
                    
                    <dt class="col-sm-4">Durum:</dt>
                    <dd class="col-sm-8 mb-0">
                        @if($brand->trendyolMapping)
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
                <h5 class="mb-0"><i class="bi bi-shop-window"></i> Trendyol Markası</h5>
            </div>
            <div class="card-body">
                @if($brand->trendyolMapping)
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Trendyol ID:</dt>
                        <dd class="col-sm-8">{{ $brand->trendyolMapping->trendyol_brand_id }}</dd>
                        
                        <dt class="col-sm-4">Marka Adı:</dt>
                        <dd class="col-sm-8"><strong>{{ $brand->trendyolMapping->trendyol_brand_name ?? 'N/A' }}</strong></dd>
                        
                        <dt class="col-sm-4">Eşleştirme Tarihi:</dt>
                        <dd class="col-sm-8 mb-0">{{ $brand->trendyolMapping->created_at->format('d.m.Y H:i') }}</dd>
                    </dl>
                    
                    <form action="{{ route('admin.brands.save-mapping', $brand) }}" method="POST" class="mt-3">
                        @csrf
                        <input type="hidden" name="trendyol_brand_id" value="">
                        <button type="submit" class="btn btn-danger btn-sm w-100" onclick="return confirm('Eşleştirmeyi kaldırmak istediğinizden emin misiniz?')">
                            <i class="bi bi-trash"></i> Eşleştirmeyi Kaldır
                        </button>
                    </form>
                @else
                    <p class="text-muted">Bu marka henüz Trendyol markası ile eşleştirilmemiş.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-search"></i> Trendyol Markası Seç</h5>
        <form action="{{ route('admin.brands.sync-trendyol') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-sm btn-warning">
                <i class="bi bi-arrow-repeat"></i> Trendyol Markalarını Senkronize Et
            </button>
        </form>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.brands.save-mapping', $brand) }}" method="POST">
            @csrf
            
            <div class="row mb-3">
                <div class="col-md-12">
                    <label for="search" class="form-label">Marka Ara</label>
                    <input type="text" id="search" class="form-control" placeholder="Trendyol marka adı ile arama yapın...">
                    <small class="text-muted">Önce Trendyol'dan markaları senkronize etmeyi unutmayın!</small>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12">
                    <label for="trendyol_brand_id" class="form-label">Trendyol Markası <span class="text-danger">*</span></label>
                    <select name="trendyol_brand_id" id="trendyol_brand_id" class="form-select" required>
                        <option value="">-- Trendyol markası seçin --</option>
                        @foreach($trendyolBrands as $trendyolBrand)
                            @php
                                $brandId = is_array($trendyolBrand) ? $trendyolBrand['id'] : $trendyolBrand->id;
                                $brandName = is_array($trendyolBrand) ? $trendyolBrand['name'] : $trendyolBrand->name;
                            @endphp
                            <option value="{{ $brandId }}" 
                                    data-brand-name="{{ $brandName }}"
                                    {{ old('trendyol_brand_id', $brand->trendyolMapping->trendyol_brand_id ?? '') == $brandId ? 'selected' : '' }}>
                                {{ $brandName }} (ID: {{ $brandId }})
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="trendyol_brand_name" id="trendyol_brand_name">
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
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const selectElement = document.getElementById('trendyol_brand_id');
    const brandNameInput = document.getElementById('trendyol_brand_name');
    const options = Array.from(selectElement.options);

    // Marka seçildiğinde otomatik olarak brand name'i doldur
    selectElement.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            brandNameInput.value = selectedOption.getAttribute('data-brand-name') || '';
        } else {
            brandNameInput.value = '';
        }
    });

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        // İlk option'ı (placeholder) sakla
        const firstOption = options[0];
        selectElement.innerHTML = '';
        selectElement.appendChild(firstOption);
        
        // Filtreleme
        options.slice(1).forEach(option => {
            if (option.text.toLowerCase().includes(searchTerm)) {
                selectElement.appendChild(option);
            }
        });
    });
});
</script>
@endpush
@endsection

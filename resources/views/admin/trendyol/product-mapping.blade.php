@extends('layouts.admin')

@section('title', 'Ürün Eşleştirme - Trendyol')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-box-open"></i> Ürün Eşleştirme (Tek Tablo Sistemi)</h2>
        <a href="{{ route('admin.trendyol.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Geri Dön
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Sol Panel: Eşleştirme Formu -->
        <div class="col-lg-5">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Yeni Eşleştirme</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.trendyol.save-product-mapping') }}" method="POST" id="mappingForm">
                        @csrf

                        <!-- 1. Ürün Seç -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">1. Ürünü Seçin</label>
                            <select name="product_id" id="productSelect" class="form-select" required>
                                <option value="">Ürün seçin...</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" 
                                        data-has-mapping="{{ $product->trendyolMapping ? 'true' : 'false' }}"
                                        {{ $product->trendyolMapping ? 'disabled' : '' }}>
                                        {{ $product->name }} 
                                        @if($product->trendyolMapping)
                                            (✓ Eşleştirilmiş)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Eşleştirilmiş ürünler devre dışı</small>
                        </div>

                        <!-- 2. Trendyol Kategori Seç -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">2. Trendyol Kategorisi</label>
                            <select name="trendyol_category_id" id="categorySelect" class="form-select" required>
                                <option value="">Kategori seçin...</option>
                                @foreach($trendyolCategories as $category)
                                    <option value="{{ $category->id }}" 
                                        data-parent="{{ $category->parent_id }}"
                                        data-leaf="{{ $category->is_leaf ? 'true' : 'false' }}">
                                        @if($category->parent_id)
                                            └─ {{ $category->name }}
                                            @if($category->is_leaf)
                                                <span class="text-success">✓</span>
                                            @endif
                                        @else
                                            <strong>{{ $category->name }}</strong>
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Kategori seçilince özellikler yüklenecek</small>
                        </div>

                        <!-- 3. Trendyol Marka Seç -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">3. Trendyol Markası</label>
                            <select name="trendyol_brand_id" id="brandSelect" class="form-select" required>
                                <option value="">Marka seçin...</option>
                                @foreach($trendyolBrands as $brand)
                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 4. Özellik Eşleştirmeleri (Dinamik Yüklenecek) -->
                        <div id="attributesSection" style="display:none;">
                            <hr>
                            <h6 class="fw-bold mb-3">4. Özellik Eşleştirmeleri</h6>
                            <div id="attributeInputs">
                                <!-- AJAX ile dinamik yüklenecek -->
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mt-3">
                            <i class="bi bi-check-circle"></i> Eşleştirmeyi Kaydet
                        </button>
                    </form>
                </div>
            </div>

            <!-- İstatistikler -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="bi bi-bar-chart"></i> İstatistikler</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <h4 class="text-primary mb-0">{{ $stats['total_products'] }}</h4>
                            <small class="text-muted">Toplam</small>
                        </div>
                        <div class="col-4">
                            <h4 class="text-success mb-0">{{ $stats['mapped_products'] }}</h4>
                            <small class="text-muted">Eşleşmiş</small>
                        </div>
                        <div class="col-4">
                            <h4 class="text-warning mb-0">{{ $stats['unmapped_products'] }}</h4>
                            <small class="text-muted">Bekleyen</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sağ Panel: Mevcut Eşleştirmeler -->
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-list-check"></i> Eşleştirilmiş Ürünler ({{ $existingMappings->count() }})</h5>
                </div>
                <div class="card-body">
                    @if($existingMappings->isEmpty())
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Henüz eşleştirilmiş ürün yok. Sol panelden yeni eşleştirme yapın.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Ürün</th>
                                        <th>Trendyol Kategori</th>
                                        <th>Trendyol Marka</th>
                                        <th>Özellikler</th>
                                        <th>İşlem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($existingMappings as $mapping)
                                    <tr>
                                        <td>
                                            <strong>{{ $mapping->product->name }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                {{ $mapping->product->brand->name ?? 'N/A' }} - 
                                                {{ $mapping->product->category->name ?? 'N/A' }}
                                            </small>
                                        </td>
                                        <td>{{ $mapping->trendyol_category_name }}</td>
                                        <td>{{ $mapping->trendyol_brand_name }}</td>
                                        <td>
                                            @if($mapping->attribute_mappings && count($mapping->attribute_mappings) > 0)
                                                <span class="badge bg-info">{{ count($mapping->attribute_mappings) }} özellik</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.trendyol.delete-product-mapping', $mapping->id) }}" method="POST" 
                                                  onsubmit="return confirm('Bu eşleştirmeyi silmek istediğinizden emin misiniz?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            @if($stats['unmapped_products'] > 0)
            <div class="alert alert-warning mt-3">
                <i class="bi bi-exclamation-triangle"></i> 
                <strong>{{ $stats['unmapped_products'] }}</strong> ürün henüz Trendyol ile eşleştirilmemiş.
            </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('categorySelect');
    const attributesSection = document.getElementById('attributesSection');
    const attributeInputs = document.getElementById('attributeInputs');

    // Kategori değiştiğinde attributes yükle
    categorySelect.addEventListener('change', function() {
        const categoryId = this.value;
        
        if (!categoryId) {
            attributesSection.style.display = 'none';
            return;
        }

        // AJAX ile kategori attributes getir
        fetch(`/admin/trendyol/category-attributes/${categoryId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.attributes.length > 0) {
                    attributeInputs.innerHTML = '';
                    
                    data.attributes.forEach(attr => {
                        const attrDiv = document.createElement('div');
                        attrDiv.className = 'mb-3';
                        attrDiv.innerHTML = `
                            <label class="form-label">${attr.attribute.name} ${attr.required ? '<span class="text-danger">*</span>' : ''}</label>
                            <select name="attribute_mappings[${attr.attribute.name}]" class="form-select" ${attr.required ? 'required' : ''}>
                                <option value="">Seçiniz...</option>
                                ${attr.attributeValues.map(val => `
                                    <option value="${val.id}">${val.name}</option>
                                `).join('')}
                            </select>
                            ${attr.varianter ? '<small class="text-muted">Varyant özelliği</small>' : ''}
                        `;
                        attributeInputs.appendChild(attrDiv);
                    });
                    
                    attributesSection.style.display = 'block';
                } else {
                    attributesSection.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Özellikler yüklenirken hata:', error);
                alert('Özellikler yüklenirken hata oluştu!');
            });
    });
});
</script>
@endsection

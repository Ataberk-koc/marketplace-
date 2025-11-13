@extends('layouts.admin')

@section('title', 'Ürün Özellikleri - ' . $product->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="bi bi-list-stars"></i> Ürün Özellikleri</h1>
        <p class="text-muted mb-0">{{ $product->name }} - SKU: {{ $product->sku }}</p>
    </div>
    <div>
        <a href="{{ route('admin.products.show', $product) }}" class="btn btn-info">
            <i class="bi bi-eye"></i> Detaylar
        </a>
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Geri
        </a>
    </div>
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

<!-- Ürün ve Kategori Bilgileri -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bi bi-box"></i> Ürün Bilgileri</h6>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Kategori:</strong> {{ $product->category->name }}</p>
                <p class="mb-1"><strong>Marka:</strong> {{ $product->brand->name }}</p>
                @if($product->category->categoryMapping)
                    <p class="mb-0">
                        <strong>Trendyol Kategorisi:</strong> 
                        <span class="badge bg-success">{{ $product->category->categoryMapping->trendyol_category_name }}</span>
                    </p>
                @else
                    <p class="mb-0">
                        <span class="badge bg-warning">⚠️ Kategori Trendyol ile eşleştirilmemiş</span>
                    </p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background-color: #F27A1A; color: white;">
                <h6 class="mb-0"><i class="bi bi-cloud-download"></i> Trendyol Özellikleri</h6>
            </div>
            <div class="card-body">
                @if($product->category->categoryMapping)
                    <form action="{{ route('admin.products.sync-category-attributes') }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="trendyol_category_id" value="{{ $product->category->categoryMapping->trendyol_category_id }}">
                        <button type="submit" class="btn btn-warning btn-sm">
                            <i class="bi bi-arrow-repeat"></i> Trendyol'dan Özellikleri Yükle
                        </button>
                    </form>
                    <p class="text-muted small mb-0 mt-2">
                        Bu kategoriye ait tüm Trendyol özelliklerini senkronize eder.
                    </p>
                @else
                    <p class="text-muted mb-0">
                        Önce <a href="{{ route('admin.categories.mapping', $product->category) }}">kategori eşleştirmesi</a> yapmalısınız.
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Özellik Yönetim Formu -->
<div class="card" x-data="productAttributes()">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Ürün Özelliklerini Yönet</h5>
        <button type="button" class="btn btn-light btn-sm" @click="addAttribute()">
            <i class="bi bi-plus-lg"></i> Yeni Özellik Ekle
        </button>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.products.save-attributes', $product) }}" method="POST" id="attributesForm">
            @csrf

            <!-- Trendyol Önerilen Özellikler -->
            @if(isset($trendyolAttributes[1]) && count($trendyolAttributes[1]) > 0)
            <div class="alert alert-info">
                <h6><i class="bi bi-info-circle"></i> Zorunlu Trendyol Özellikleri</h6>
                <p class="mb-2">Bu özellikler Trendyol'a ürün gönderimi için zorunludur:</p>
                <div class="row">
                    @foreach($trendyolAttributes[1] as $attr)
                    <div class="col-md-6 mb-2">
                        <button type="button" class="btn btn-sm btn-outline-primary w-100" 
                                @click="addTrendyolAttribute('{{ $attr->attribute_name }}', '{{ $attr->attribute_id }}', '{{ $attr->attribute_type }}', true, {{ $attr->is_variant_based ? 'true' : 'false' }}, {{ json_encode($attr->allowed_values ?? []) }})">
                            <i class="bi bi-plus"></i> {{ $attr->attribute_name }}
                            <small class="text-muted">({{ $attr->attribute_type }})</small>
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if(isset($trendyolAttributes[0]) && count($trendyolAttributes[0]) > 0)
            <div class="alert alert-secondary">
                <h6><i class="bi bi-bookmark"></i> Opsiyonel Trendyol Özellikleri</h6>
                <div class="row">
                    @foreach($trendyolAttributes[0] as $attr)
                    <div class="col-md-4 mb-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100 text-truncate" 
                                @click="addTrendyolAttribute('{{ $attr->attribute_name }}', '{{ $attr->attribute_id }}', '{{ $attr->attribute_type }}', false, {{ $attr->is_variant_based ? 'true' : 'false' }}, {{ json_encode($attr->allowed_values ?? []) }})">
                            <i class="bi bi-plus"></i> {{ $attr->attribute_name }}
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Mevcut Özellikler Tablosu -->
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th width="25">#</th>
                            <th width="200">Özellik Adı <span class="text-danger">*</span></th>
                            <th width="200">Değer <span class="text-danger">*</span></th>
                            <th width="120">Tür</th>
                            <th width="150">Trendyol ID</th>
                            <th width="80" class="text-center">Zorunlu</th>
                            <th width="80" class="text-center">Varyant</th>
                            <th width="60" class="text-center">Sıra</th>
                            <th width="60"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(attr, index) in attributes" :key="index">
                            <tr>
                                <td class="text-center" x-text="index + 1"></td>
                                <td>
                                    <input type="text" 
                                           class="form-control form-control-sm" 
                                           :name="'attributes[' + index + '][attribute_name]'" 
                                           x-model="attr.attribute_name" 
                                           required>
                                </td>
                                <td>
                                    <!-- Eğer allowed values varsa select -->
                                    <template x-if="attr.allowed_values && attr.allowed_values.length > 0">
                                        <select class="form-select form-select-sm"
                                                :name="'attributes[' + index + '][attribute_value]'"
                                                x-model="attr.attribute_value"
                                                required>
                                            <option value="">Seçiniz...</option>
                                            <template x-for="val in attr.allowed_values" :key="val.id">
                                                <option :value="val.name" x-text="val.name"></option>
                                            </template>
                                        </select>
                                    </template>
                                    <!-- Değilse input -->
                                    <template x-if="!attr.allowed_values || attr.allowed_values.length === 0">
                                        <input :type="attr.attribute_type === 'color' ? 'color' : (attr.attribute_type === 'number' ? 'number' : 'text')"
                                               class="form-control form-control-sm" 
                                               :name="'attributes[' + index + '][attribute_value]'" 
                                               x-model="attr.attribute_value" 
                                               required>
                                    </template>
                                </td>
                                <td>
                                    <select class="form-select form-select-sm" 
                                            :name="'attributes[' + index + '][attribute_type]'" 
                                            x-model="attr.attribute_type">
                                        <option value="text">Text</option>
                                        <option value="color">Renk</option>
                                        <option value="size">Beden</option>
                                        <option value="number">Sayı</option>
                                        <option value="select">Seçim</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" 
                                           class="form-control form-control-sm" 
                                           :name="'attributes[' + index + '][trendyol_attribute_id]'" 
                                           x-model="attr.trendyol_attribute_id"
                                           placeholder="ID">
                                    <input type="hidden" 
                                           :name="'attributes[' + index + '][trendyol_attribute_name]'" 
                                           x-model="attr.trendyol_attribute_name">
                                </td>
                                <td class="text-center">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           :name="'attributes[' + index + '][is_required]'" 
                                           x-model="attr.is_required"
                                           value="1">
                                </td>
                                <td class="text-center">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           :name="'attributes[' + index + '][is_variant]'" 
                                           x-model="attr.is_variant"
                                           value="1">
                                </td>
                                <td>
                                    <input type="number" 
                                           class="form-control form-control-sm" 
                                           :name="'attributes[' + index + '][display_order]'" 
                                           x-model="attr.display_order"
                                           min="0">
                                </td>
                                <td class="text-center">
                                    <button type="button" 
                                            class="btn btn-danger btn-sm" 
                                            @click="removeAttribute(index)"
                                            title="Sil">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>

                        <tr x-show="attributes.length === 0">
                            <td colspan="9" class="text-center text-muted">
                                <i class="bi bi-inbox"></i> Henüz özellik eklenmemiş. "Yeni Özellik Ekle" butonuna tıklayın.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <span class="text-muted" x-text="'Toplam ' + attributes.length + ' özellik'"></span>
                </div>
                <div>
                    <button type="submit" class="btn btn-success btn-lg" :disabled="attributes.length === 0">
                        <i class="bi bi-save"></i> Özellikleri Kaydet
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function productAttributes() {
    return {
        attributes: @json($product->productAttributes->map(function($attr) {
            return [
                'attribute_name' => $attr->attribute_name,
                'attribute_value' => $attr->attribute_value,
                'attribute_type' => $attr->attribute_type,
                'trendyol_attribute_id' => $attr->trendyol_attribute_id,
                'trendyol_attribute_name' => $attr->trendyol_attribute_name,
                'is_required' => $attr->is_required,
                'is_variant' => $attr->is_variant,
                'display_order' => $attr->display_order,
                'allowed_values' => []
            ];
        })->values()),

        addAttribute() {
            this.attributes.push({
                attribute_name: '',
                attribute_value: '',
                attribute_type: 'text',
                trendyol_attribute_id: '',
                trendyol_attribute_name: '',
                is_required: false,
                is_variant: false,
                display_order: this.attributes.length,
                allowed_values: []
            });
        },

        addTrendyolAttribute(name, id, type, isRequired, isVariant, allowedValues) {
            // Zaten var mı kontrol et
            const exists = this.attributes.some(attr => attr.trendyol_attribute_id === id);
            if (exists) {
                alert('Bu özellik zaten eklenmiş!');
                return;
            }

            this.attributes.push({
                attribute_name: name,
                attribute_value: '',
                attribute_type: this.mapTrendyolType(type),
                trendyol_attribute_id: id,
                trendyol_attribute_name: name,
                is_required: isRequired,
                is_variant: isVariant,
                display_order: this.attributes.length,
                allowed_values: allowedValues || []
            });
        },

        removeAttribute(index) {
            if (confirm('Bu özelliği silmek istediğinizden emin misiniz?')) {
                this.attributes.splice(index, 1);
                // Sıraları yeniden düzenle
                this.attributes.forEach((attr, idx) => {
                    attr.display_order = idx;
                });
            }
        },

        mapTrendyolType(trendyolType) {
            const typeMap = {
                'text': 'text',
                'select': 'select',
                'multiSelect': 'select',
                'numeric': 'number'
            };
            return typeMap[trendyolType] || 'text';
        }
    }
}
</script>
@endpush

@endsection

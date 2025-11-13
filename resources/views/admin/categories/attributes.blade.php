@extends('layouts.admin')

@section('title', 'Kategori Özellikleri - ' . $category->name)

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">Kategoriler</a></li>
            <li class="breadcrumb-item active">{{ $category->name }} - Özellikler</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-light">
                <div class="card-body">
                    <h4 class="mb-2">
                        <i class="bi bi-tags"></i> {{ $category->name }}
                    </h4>
                    <p class="mb-1">
                        <strong>Trendyol Kategorisi:</strong> 
                        <span class="badge bg-success">{{ $mapping->trendyol_category_name }}</span>
                    </p>
                    <p class="mb-0 text-muted">
                        <i class="bi bi-info-circle"></i> 
                        Bu kategoriye ait Trendyol özelliklerini yerel özelliklerinizle eşleştirin.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Trendyol Özellikleri -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-cloud-download"></i> 
                        Trendyol Kategori Özellikleri
                        <span class="badge bg-light text-dark ms-2">{{ count($trendyolAttributes) }} özellik</span>
                    </h5>
                </div>
                <div class="card-body">
                    @if(empty($trendyolAttributes))
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            Bu kategori için Trendyol'da özellik bulunamadı!
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="25%">Özellik Adı</th>
                                        <th width="15%">Zorunlu</th>
                                        <th width="15%">Çoklu Seçim</th>
                                        <th width="40%">Değerler (İlk 10)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($trendyolAttributes as $index => $attrGroup)
                                        @php
                                            $attribute = $attrGroup['attribute'];
                                            $values = $attrGroup['attributeValues'] ?? [];
                                            $required = $attribute['required'] ?? false;
                                            $allowCustom = $attribute['allowCustom'] ?? false;
                                            $varianter = $attribute['varianter'] ?? false;
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $attribute['name'] }}</strong>
                                                <br>
                                                <small class="text-muted">ID: {{ $attribute['id'] }}</small>
                                            </td>
                                            <td>
                                                @if($required)
                                                    <span class="badge bg-danger">Zorunlu</span>
                                                @else
                                                    <span class="badge bg-secondary">İsteğe Bağlı</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($varianter)
                                                    <span class="badge bg-info">Varyant</span>
                                                @elseif($allowCustom)
                                                    <span class="badge bg-warning text-dark">Özel Değer</span>
                                                @else
                                                    <span class="badge bg-secondary">Tekli</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(!empty($values))
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach(array_slice($values, 0, 10) as $value)
                                                            <span class="badge bg-light text-dark border">
                                                                {{ $value['name'] }}
                                                            </span>
                                                        @endforeach
                                                        @if(count($values) > 10)
                                                            <span class="badge bg-secondary">
                                                                +{{ count($values) - 10 }} daha
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <small class="text-muted d-block mt-1">
                                                        Toplam: {{ count($values) }} değer
                                                    </small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Local Özellikler ve Eşleştirme -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-folder"></i> 
                        Yerel Özellikleriniz
                        <span class="badge bg-light text-dark ms-2">{{ $localAttributes->count() }} özellik</span>
                    </h5>
                </div>
                <div class="card-body">
                    @if($localAttributes->isEmpty())
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            Bu kategoriye henüz yerel özellik eklenmedi!
                            <a href="{{ route('admin.products.create') }}" class="alert-link">Ürün Ekle</a> sayfasından özellik tanımlayabilirsiniz.
                        </div>
                    @else
                        <form action="{{ route('admin.categories.save-attribute-mapping', $category) }}" method="POST">
                            @csrf
                            
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="30%">Yerel Özellik</th>
                                            <th width="50%">Trendyol Özelliği Seç</th>
                                            <th width="20%">Durum</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($localAttributes as $localAttr)
                                            <tr>
                                                <td>
                                                    <strong>{{ $localAttr->name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $localAttr->type }}</small>
                                                </td>
                                                <td>
                                                    <select 
                                                        name="mappings[{{ $localAttr->id }}][trendyol_attribute_id]" 
                                                        class="form-select"
                                                        id="mapping_{{ $localAttr->id }}"
                                                        onchange="updateAttributeName({{ $localAttr->id }})"
                                                    >
                                                        <option value="">-- Eşleştirme Yok --</option>
                                                        @foreach($trendyolAttributes as $tAttr)
                                                            @php
                                                                $attr = $tAttr['attribute'];
                                                                $currentMapping = $localAttr->trendyolMapping;
                                                                $selected = $currentMapping && $currentMapping->trendyol_attribute_id == $attr['id'];
                                                            @endphp
                                                            <option 
                                                                value="{{ $attr['id'] }}" 
                                                                data-name="{{ $attr['name'] }}"
                                                                {{ $selected ? 'selected' : '' }}
                                                            >
                                                                {{ $attr['name'] }} (ID: {{ $attr['id'] }})
                                                                @if($attr['required'] ?? false) - ZORUNLU @endif
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <input 
                                                        type="hidden" 
                                                        name="mappings[{{ $localAttr->id }}][local_attribute_id]" 
                                                        value="{{ $localAttr->id }}"
                                                    >
                                                    <input 
                                                        type="hidden" 
                                                        name="mappings[{{ $localAttr->id }}][trendyol_attribute_name]" 
                                                        id="mapping_name_{{ $localAttr->id }}"
                                                        value="{{ $localAttr->trendyolMapping->trendyol_attribute_name ?? '' }}"
                                                    >
                                                </td>
                                                <td>
                                                    @if($localAttr->trendyolMapping)
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-check-circle"></i> Eşleştirildi
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning text-dark">
                                                            <i class="bi bi-exclamation-circle"></i> Eşleştirilmedi
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-between mt-3">
                                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Geri
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Eşleştirmeleri Kaydet
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Seçili Trendyol özelliğinin adını hidden input'a yaz
function updateAttributeName(localAttrId) {
    const select = document.getElementById('mapping_' + localAttrId);
    const selectedOption = select.options[select.selectedIndex];
    const name = selectedOption.getAttribute('data-name') || '';
    
    document.getElementById('mapping_name_' + localAttrId).value = name;
}
</script>
@endpush
@endsection

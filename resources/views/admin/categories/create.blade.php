@extends('layouts.admin')

@section('title', 'Yeni Kategori Ekle')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-plus-circle"></i> Yeni Kategori Ekle</h2>
    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Geri Dön
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-folder"></i> Kategori Bilgileri</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.categories.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">
                            Kategori Adı <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}" 
                               placeholder="Örn: Giyim, Elektronik, Mobilya"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Kategori adı benzersiz olmalıdır
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="parent_id" class="form-label">Üst Kategori</label>
                        <select class="form-select @error('parent_id') is-invalid @enderror" 
                                id="parent_id" 
                                name="parent_id">
                            <option value="">-- Ana Kategori (Üst kategori yok) --</option>
                            @foreach($parentCategories as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Alt kategori oluşturmak istiyorsanız üst kategoriyi seçin
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Açıklama</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" 
                                  name="description" 
                                  rows="4"
                                  placeholder="Kategori hakkında kısa açıklama...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 
                        <strong>Bilgilendirme:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Kategori oluşturulduktan sonra otomatik olarak aktif duruma gelir</li>
                            <li>Kategori slug'ı (URL-uyumlu hali) otomatik olarak oluşturulur</li>
                            <li>Alt kategori oluşturmak için bir üst kategori seçin</li>
                            <li>Ana kategori yapmak için üst kategoriyi boş bırakın</li>
                            <li>Trendyol eşleştirmesini kategori listesinden yapabilirsiniz</li>
                        </ul>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle"></i> Kategoriyi Kaydet
                        </button>
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> İptal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Select2 initialization
$(document).ready(function() {
    $('#parent_id').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Ana Kategori (Üst kategori yok) --',
        allowClear: true,
        language: 'tr'
    });
});
</script>
@endpush
@endsection

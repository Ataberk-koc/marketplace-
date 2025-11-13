@extends('layouts.admin')

@section('title', 'Kategori Düzenle')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-pencil-square"></i> Kategori Düzenle: {{ $category->name }}</h2>
    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Geri Dön
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-warning">
                <h5 class="mb-0"><i class="bi bi-folder"></i> Kategori Bilgileri</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.categories.update', $category) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">
                            Kategori Adı <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $category->name) }}" 
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
                                <option value="{{ $parent->id }}" 
                                    {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>
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
                                  placeholder="Kategori hakkında kısa açıklama...">{{ old('description', $category->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if($category->children()->count() > 0)
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> 
                        <strong>Uyarı:</strong>
                        Bu kategorinin <strong>{{ $category->children()->count() }} adet alt kategorisi</strong> bulunmaktadır.
                    </div>
                    @endif

                    @if($category->products()->count() > 0)
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 
                        <strong>Bilgi:</strong>
                        Bu kategoride <strong>{{ $category->products()->count() }} adet ürün</strong> bulunmaktadır.
                    </div>
                    @endif

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-warning btn-lg">
                            <i class="bi bi-check-circle"></i> Değişiklikleri Kaydet
                        </button>
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> İptal
                        </a>
                        <hr>
                        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" 
                              onsubmit="return confirm('Bu kategoriyi silmek istediğinizden emin misiniz?{{ $category->children()->count() > 0 ? ' Alt kategorileri de silinecektir!' : '' }}{{ $category->products()->count() > 0 ? ' Kategorideki ürünler kategorisiz kalacaktır!' : '' }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100"
                                {{ ($category->children()->count() > 0 || $category->products()->count() > 0) ? 'disabled' : '' }}>
                                <i class="bi bi-trash"></i> Kategoriyi Sil
                            </button>
                        </form>
                        @if($category->children()->count() > 0 || $category->products()->count() > 0)
                        <small class="text-muted text-center">
                            * Alt kategorileri veya ürünleri olan kategoriler silinemez
                        </small>
                        @endif
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

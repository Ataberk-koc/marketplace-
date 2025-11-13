@extends('layouts.admin')

@section('title', 'Beden Düzenle')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-pencil-square"></i> Beden Düzenle: {{ $size->name }}</h2>
    <a href="{{ route('admin.sizes.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Geri Dön
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-warning">
                <h5 class="mb-0"><i class="bi bi-rulers"></i> Beden Bilgileri</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.sizes.update', $size) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">
                            Beden Adı <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $size->name) }}" 
                               placeholder="Örn: S, M, L, XL, 38, 40, 42"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Beden adı benzersiz olmalıdır
                        </small>
                    </div>

                    @if($size->trendyolMapping)
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> 
                        <strong>Trendyol Eşleştirmesi:</strong>
                        <div class="mt-2">
                            <strong>{{ $size->trendyolMapping->trendyol_size_name }}</strong>
                            <br>
                            <small class="text-muted">Attribute ID: {{ $size->trendyolMapping->trendyol_attribute_id }}</small>
                        </div>
                    </div>
                    @endif

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-warning btn-lg">
                            <i class="bi bi-check-circle"></i> Değişiklikleri Kaydet
                        </button>
                        <a href="{{ route('admin.sizes.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> İptal
                        </a>
                        <hr>
                        <form action="{{ route('admin.sizes.destroy', $size) }}" method="POST" 
                              onsubmit="return confirm('Bu bedeni silmek istediğinizden emin misiniz?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="bi bi-trash"></i> Bedeni Sil
                            </button>
                        </form>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

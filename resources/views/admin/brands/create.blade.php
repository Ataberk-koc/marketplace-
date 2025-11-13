@extends('layouts.admin')

@section('title', 'Yeni Marka Ekle')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-plus-circle"></i> Yeni Marka Ekle</h2>
    <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Geri Dön
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-tag"></i> Marka Bilgileri</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.brands.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">
                            Marka Adı <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}" 
                               placeholder="Örn: Nike, Adidas, Zara"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Marka adı benzersiz olmalıdır
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Açıklama</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" 
                                  name="description" 
                                  rows="4"
                                  placeholder="Marka hakkında kısa açıklama...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 
                        <strong>Bilgilendirme:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Marka oluşturulduktan sonra otomatik olarak aktif duruma gelir</li>
                            <li>Marka slug'ı (URL-uyumlu hali) otomatik olarak oluşturulur</li>
                            <li>Trendyol eşleştirmesini marka listesinden yapabilirsiniz</li>
                        </ul>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle"></i> Markayı Kaydet
                        </button>
                        <a href="{{ route('admin.brands.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> İptal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

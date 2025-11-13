@extends('layouts.admin')

@section('title', 'Yeni Beden Ekle')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-plus-circle"></i> Yeni Beden Ekle</h2>
    <a href="{{ route('admin.sizes.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Geri Dön
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-rulers"></i> Beden Bilgileri</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.sizes.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">
                            Beden Adı <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}" 
                               placeholder="Örn: S, M, L, XL, 38, 40, 42"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Beden adı benzersiz olmalıdır
                        </small>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 
                        <strong>Bilgilendirme:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Yaygın bedenler: XS, S, M, L, XL, XXL, XXXL</li>
                            <li>Ayakkabı bedenleri: 36, 37, 38, 39, 40, 41, 42...</li>
                            <li>Trendyol eşleştirmesini beden listesinden yapabilirsiniz</li>
                            <li>Toplu eşleştirme için "Toplu Beden Eşleştirme" sayfasını kullanabilirsiniz</li>
                        </ul>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle"></i> Bedeni Kaydet
                        </button>
                        <a href="{{ route('admin.sizes.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> İptal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

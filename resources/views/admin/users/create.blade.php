@extends('layouts.admin')

@section('title', 'Yeni Kullanıcı Ekle')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-person-plus"></i> Yeni Kullanıcı Ekle</h1>
    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Geri Dön
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Ad Soyad <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">E-posta <span class="text-danger">*</span></label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                           id="email" name="email" value="{{ old('email') }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">Telefon</label>
                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                           id="phone" name="phone" value="{{ old('phone') }}" placeholder="+90 5XX XXX XX XX">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="role" class="form-label">Rol <span class="text-danger">*</span></label>
                    <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                        <option value="">-- Rol Seçin --</option>
                        <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>Müşteri</option>
                        <option value="seller" {{ old('role') == 'seller' ? 'selected' : '' }}>Satıcı</option>
                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Şifre <span class="text-danger">*</span></label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                           id="password" name="password" required minlength="8">
                    <small class="text-muted">En az 8 karakter</small>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="password_confirmation" class="form-label">Şifre Tekrar <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" 
                           id="password_confirmation" name="password_confirmation" required minlength="8">
                </div>

                <div class="col-12 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                               {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Hesap Aktif
                        </label>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-save"></i> Kullanıcı Oluştur
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-lg"></i> İptal
                </a>
            </div>
        </form>
    </div>
</div>

<div class="alert alert-info mt-4">
    <h5><i class="bi bi-info-circle"></i> Bilgilendirme:</h5>
    <ul class="mb-0">
        <li>Admin tarafından oluşturulan kullanıcılar otomatik olarak e-posta doğrulanmış sayılır</li>
        <li>Kullanıcı bilgileri daha sonra düzenlenebilir</li>
        <li>Şifre en az 8 karakter uzunluğunda olmalıdır</li>
    </ul>
</div>
@endsection

@extends('layouts.admin')

@section('title', 'Kullanıcı Düzenle - ' . $user->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-person-gear"></i> Kullanıcı Düzenle</h1>
    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Geri Dön
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Ad Soyad <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="name" name="name" value="{{ old('name', $user->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">E-posta <span class="text-danger">*</span></label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                           id="email" name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">Telefon</label>
                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                           id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+90 5XX XXX XX XX">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="role" class="form-label">Rol <span class="text-danger">*</span></label>
                    <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                        <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>Müşteri</option>
                        <option value="seller" {{ old('role', $user->role) == 'seller' ? 'selected' : '' }}>Satıcı</option>
                        <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 mb-3">
                    <hr>
                    <h5>Şifre Değiştir <small class="text-muted">(Değiştirmek istemiyorsanız boş bırakın)</small></h5>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Yeni Şifre</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                           id="password" name="password" minlength="8">
                    <small class="text-muted">En az 8 karakter</small>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="password_confirmation" class="form-label">Yeni Şifre Tekrar</label>
                    <input type="password" class="form-control" 
                           id="password_confirmation" name="password_confirmation" minlength="8">
                </div>

                <div class="col-12 mb-3">
                    <hr>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                               {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Hesap Aktif
                        </label>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-save"></i> Değişiklikleri Kaydet
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-lg"></i> İptal
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Kullanıcı Bilgileri</h5>
    </div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">Kayıt Tarihi:</dt>
            <dd class="col-sm-9">{{ $user->created_at->format('d.m.Y H:i') }}</dd>
            
            <dt class="col-sm-3">Son Güncelleme:</dt>
            <dd class="col-sm-9">{{ $user->updated_at->format('d.m.Y H:i') }}</dd>
            
            <dt class="col-sm-3">E-posta Doğrulama:</dt>
            <dd class="col-sm-9 mb-0">
                @if($user->email_verified_at)
                    <span class="badge bg-success">
                        <i class="bi bi-check-circle"></i> Doğrulanmış ({{ $user->email_verified_at->format('d.m.Y H:i') }})
                    </span>
                @else
                    <span class="badge bg-warning">
                        <i class="bi bi-exclamation-circle"></i> Doğrulanmamış
                    </span>
                @endif
            </dd>
        </dl>
    </div>
</div>
@endsection

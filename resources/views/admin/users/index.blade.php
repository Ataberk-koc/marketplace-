@extends('layouts.admin')

@section('title', 'Kullanıcılar')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-people"></i> Kullanıcılar</h2>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped datatable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>İsim</th>
                    <th>E-posta</th>
                    <th>Telefon</th>
                    <th>Rol</th>
                    <th>E-posta Doğrulama</th>
                    <th>Aktif</th>
                    <th>Kayıt Tarihi</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->phone }}</td>
                    <td>
                        @if($user->role == 'admin')
                            <span class="badge bg-danger">Admin</span>
                        @elseif($user->role == 'seller')
                            <span class="badge bg-success">Satıcı</span>
                        @else
                            <span class="badge bg-primary">Müşteri</span>
                        @endif
                    </td>
                    <td>
                        @if($user->email_verified_at)
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Doğrulandı
                            </span>
                        @else
                            <span class="badge bg-warning">
                                <i class="bi bi-clock"></i> Bekliyor
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($user->is_active)
                            <span class="badge bg-success">Aktif</span>
                        @else
                            <span class="badge bg-danger">Pasif</span>
                        @endif
                    </td>
                    <td>{{ $user->created_at->format('d.m.Y') }}</td>
                    <td>
                        <form action="{{ route('admin.users.toggle-active', $user) }}" method="POST" class="d-inline">
                            @csrf
                            @method('POST')
                            <button type="submit" class="btn btn-sm btn-{{ $user->is_active ? 'warning' : 'success' }}">
                                <i class="bi bi-{{ $user->is_active ? 'pause' : 'play' }}"></i>
                                {{ $user->is_active ? 'Pasifleştir' : 'Aktifleştir' }}
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

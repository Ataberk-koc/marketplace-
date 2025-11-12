@extends('layouts.admin')

@section('title', 'Beden Yönetimi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-rulers"></i> Beden Yönetimi</h1>
    <div>
        <button type="button" class="btn btn-warning me-2" onclick="window.location.href='{{ route('admin.sizes.bulk-mapping') }}'">
            <i class="bi bi-grid-3x3"></i> Toplu Eşleştir
        </button>
        <form action="{{ route('admin.sizes.sync-trendyol') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-info me-2">
                <i class="bi bi-arrow-repeat"></i> Trendyol'dan Senkronize Et
            </button>
        </form>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="bi bi-plus-lg"></i> Yeni Beden
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <table id="sizesTable" class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Beden Adı</th>
                    <th>Ürün Sayısı</th>
                    <th>Trendyol Eşleşmesi</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sizes as $size)
                <tr>
                    <td>{{ $size->id }}</td>
                    <td><strong>{{ $size->name }}</strong></td>
                    <td>
                        <span class="badge bg-info">{{ $size->products_count ?? 0 }} ürün</span>
                    </td>
                    <td>
                        @if($size->trendyolMapping)
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> 
                                {{ $size->trendyolMapping->trendyolSize->name ?? 'Eşleştirilmiş' }}
                            </span>
                        @else
                            <span class="badge bg-warning">
                                <i class="bi bi-exclamation-circle"></i> Eşleştirilmemiş
                            </span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.sizes.mapping', $size) }}" class="btn btn-info" title="Trendyol Eşleştir">
                                <i class="bi bi-link-45deg"></i>
                            </a>
                            <button class="btn btn-primary" onclick="editSize({{ $size->id }}, '{{ $size->name }}')">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="{{ route('admin.sizes.destroy', $size) }}" method="POST" class="d-inline" onsubmit="return confirm('Bedeni silmek istediğinizden emin misiniz?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="mt-3">
            {{ $sizes->links() }}
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.sizes.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Yeni Beden Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Beden Adı <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required placeholder="Örn: S, M, L, XL, 38, 40">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Beden Düzenle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Beden Adı <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Güncelle</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function editSize(id, name) {
    document.getElementById('editForm').action = `/admin/sizes/${id}`;
    document.getElementById('edit_name').value = name;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

$(document).ready(function() {
    $('#sizesTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/tr.json'
        },
        order: [[0, 'desc']]
    });
});
</script>
@endpush
@endsection

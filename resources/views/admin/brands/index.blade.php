@extends('layouts.admin')

@section('title', 'Markalar')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-tag"></i> Markalar</h2>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped datatable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Marka Adı</th>
                    <th>Slug</th>
                    <th>Trendyol Eşleştirme</th>
                    <th>Ürün Sayısı</th>
                    <th>Durum</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @foreach($brands as $brand)
                <tr>
                    <td>{{ $brand->id }}</td>
                    <td>{{ $brand->name }}</td>
                    <td>{{ $brand->slug }}</td>
                    <td>
                        @if($brand->trendyolMapping)
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> 
                                {{ $brand->trendyolMapping->trendyol_brand_name ?? 'Eşleştirilmiş' }}
                            </span>
                        @else
                            <span class="badge bg-secondary">
                                <i class="bi bi-x-circle"></i> Eşleştirilmemiş
                            </span>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-primary">{{ $brand->products_count }}</span>
                    </td>
                    <td>
                        @if($brand->is_active)
                            <span class="badge bg-success">Aktif</span>
                        @else
                            <span class="badge bg-danger">Pasif</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.brands.mapping', $brand) }}" class="btn btn-info" title="Trendyol Eşleştir">
                                <i class="bi bi-link-45deg"></i>
                            </a>
                            <button class="btn btn-primary" onclick="editBrand({{ $brand->id }}, '{{ $brand->name }}', '{{ $brand->slug }}')">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="{{ route('admin.brands.destroy', $brand) }}" method="POST" class="d-inline" onsubmit="return confirm('Markayı silmek istediğinizden emin misiniz?')">
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
    </div>
</div>
@endsection

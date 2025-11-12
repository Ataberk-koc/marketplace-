@extends('layouts.admin')

@section('title', 'Toplu Beden Eşleştirme')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="bi bi-grid-3x3"></i> Toplu Beden Eşleştirme</h1>
        <p class="text-muted mb-0">Tüm bedenleri tek seferde Trendyol bedenleri ile eşleştirin</p>
    </div>
    <a href="{{ route('admin.sizes.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Geri Dön
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.sizes.save-bulk-mapping') }}" method="POST">
            @csrf
            
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th width="10%">Beden ID</th>
                            <th width="20%">Kendi Bedeniniz</th>
                            <th width="20%">Mevcut Eşleşme</th>
                            <th width="50%">Trendyol Bedeni Seç</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sizes as $size)
                        <tr>
                            <td>{{ $size->id }}</td>
                            <td><strong>{{ $size->name }}</strong></td>
                            <td>
                                @if($size->trendyolMapping)
                                    <span class="badge bg-success">
                                        {{ $size->trendyolMapping->trendyolSize->name ?? 'N/A' }}
                                    </span>
                                @else
                                    <span class="badge bg-warning">Eşleştirilmemiş</span>
                                @endif
                            </td>
                            <td>
                                <select name="mappings[{{ $size->id }}]" class="form-select">
                                    <option value="">-- Seçin veya değiştirmeyin --</option>
                                    @foreach($trendyolSizes as $trendyolSize)
                                        <option value="{{ $trendyolSize->id }}" 
                                                {{ ($size->trendyolMapping && $size->trendyolMapping->trendyol_size_id == $trendyolSize->id) ? 'selected' : '' }}>
                                            {{ $trendyolSize->name }} ({{ $trendyolSize->attribute_type ?? 'size' }})
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-save"></i> Tüm Eşleştirmeleri Kaydet
                </button>
            </div>
        </form>
    </div>
</div>

<div class="alert alert-info mt-4">
    <h5><i class="bi bi-info-circle"></i> Kullanım İpuçları:</h5>
    <ul class="mb-0">
        <li>Sadece değiştirmek istediğiniz bedenleri seçin</li>
        <li>Boş bırakılan bedenler mevcut eşleşmelerini koruyacaktır</li>
        <li>Otomatik eşleştirme için aynı isimdeki bedenleri seçebilirsiniz</li>
    </ul>
</div>
@endsection

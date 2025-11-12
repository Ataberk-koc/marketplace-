@extends('layouts.admin')

@section('title', 'Marka Eşleştirme - Trendyol')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-link"></i> Marka Eşleştirme</h2>
            <p class="text-muted">Kendi markalarınızı Trendyol markaları ile eşleştirin</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Yeni Eşleştirme Formu -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Yeni Eşleştirme</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.trendyol.save-brand-mapping') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Kendi Markanız</label>
                            <select name="brand_id" class="form-select" required>
                                <option value="">Marka Seçin</option>
                                @foreach($localBrands as $brand)
                                    <option value="{{ $brand->id }}" 
                                        {{ $brand->trendyolMapping ? 'disabled' : '' }}>
                                        {{ $brand->name }}
                                        @if($brand->trendyolMapping)
                                            (Zaten eşleştirilmiş)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Trendyol Markası</label>
                            <select name="trendyol_brand_id" class="form-select" required>
                                <option value="">Trendyol Markası Seçin</option>
                                @foreach($trendyolBrands as $tBrand)
                                    <option value="{{ $tBrand->id }}">
                                        {{ $tBrand->name }} (ID: {{ $tBrand->trendyol_brand_id }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">
                                Toplam {{ $trendyolBrands->count() }} Trendyol markası
                            </small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> Eşleştir
                        </button>
                    </form>
                </div>
            </div>

            <!-- İstatistikler -->
            <div class="card shadow-sm mt-3">
                <div class="card-body">
                    <h6 class="text-muted mb-3">İstatistikler</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Toplam Marka:</span>
                        <strong>{{ $localBrands->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Eşleştirilmiş:</span>
                        <strong class="text-success">{{ $localBrands->filter(fn($b) => $b->trendyolMapping)->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Eşleştirilmemiş:</span>
                        <strong class="text-warning">{{ $localBrands->filter(fn($b) => !$b->trendyolMapping)->count() }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mevcut Eşleştirmeler -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Mevcut Eşleştirmeler</h5>
                </div>
                <div class="card-body">
                    @php
                        $mappedBrands = $localBrands->filter(fn($b) => $b->trendyolMapping);
                    @endphp

                    @if($mappedBrands->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Kendi Markanız</th>
                                        <th><i class="fas fa-arrow-right text-muted"></i></th>
                                        <th>Trendyol Markası</th>
                                        <th>Trendyol ID</th>
                                        <th class="text-end">İşlem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($mappedBrands as $brand)
                                        <tr>
                                            <td>
                                                <strong>{{ $brand->name }}</strong>
                                            </td>
                                            <td class="text-center">
                                                <i class="fas fa-link text-success"></i>
                                            </td>
                                            <td>
                                                {{ $brand->trendyolMapping->trendyol_brand_name }}
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $brand->trendyolMapping->trendyol_brand_id }}</span>
                                            </td>
                                            <td class="text-end">
                                                <form method="POST" 
                                                      action="{{ route('admin.trendyol.delete-brand-mapping', $brand->trendyolMapping->id) }}"
                                                      style="display: inline;"
                                                      onsubmit="return confirm('Bu eşleştirmeyi silmek istediğinizden emin misiniz?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-unlink fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Henüz eşleştirme yok</h5>
                            <p class="text-muted">Sol taraftaki formu kullanarak eşleştirme yapabilirsiniz.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Eşleştirilmemiş Markalar -->
            @php
                $unmappedBrands = $localBrands->filter(fn($b) => !$b->trendyolMapping);
            @endphp

            @if($unmappedBrands->count() > 0)
                <div class="card shadow-sm mt-3">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="fas fa-exclamation-triangle"></i> 
                            Eşleştirilmemiş Markalar ({{ $unmappedBrands->count() }})
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($unmappedBrands as $brand)
                                <div class="col-md-4 mb-2">
                                    <span class="badge bg-secondary">{{ $brand->name }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Geri Dön Butonu -->
    <div class="row mt-4">
        <div class="col-12">
            <a href="{{ route('admin.trendyol.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Trendyol Yönetimine Dön
            </a>
        </div>
    </div>
</div>
@endsection

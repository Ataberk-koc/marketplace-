@extends('layouts.admin')

@section('title', 'Kategori Eşleştirme - Trendyol')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-sitemap"></i> Kategori Eşleştirme</h2>
            <p class="text-muted">Kendi kategorilerinizi Trendyol kategorileri ile eşleştirin</p>
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
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Yeni Eşleştirme</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.trendyol.save-category-mapping') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Kendi Kategoriniz</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Kategori Seçin</option>
                                @foreach($localCategories as $category)
                                    <option value="{{ $category->id }}" 
                                        {{ $category->trendyolMapping ? 'disabled' : '' }}>
                                        {{ $category->name }}
                                        @if($category->trendyolMapping)
                                            (Zaten eşleştirilmiş)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Trendyol Kategorisi</label>
                            <select name="trendyol_category_id" class="form-select" required id="trendyol-category-select">
                                <option value="">Trendyol Kategorisi Seçin</option>
                                @foreach($trendyolCategories as $tCategory)
                                    <option value="{{ $tCategory->id }}"
                                            data-parent="{{ $tCategory->parent_id }}"
                                            data-leaf="{{ $tCategory->is_leaf ? 'true' : 'false' }}">
                                        @if($tCategory->parent_id)
                                            &nbsp;&nbsp;└─
                                        @endif
                                        {{ $tCategory->name }} 
                                        (ID: {{ $tCategory->trendyol_category_id }})
                                        @if($tCategory->is_leaf)
                                            <span class="text-success">✓</span>
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">
                                Toplam {{ $trendyolCategories->count() }} Trendyol kategorisi
                                <br>
                                <i class="fas fa-info-circle"></i> Yeşil işaret (✓) = Son seviye kategori
                            </small>
                        </div>

                        <button type="submit" class="btn btn-success w-100">
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
                        <span>Toplam Kategori:</span>
                        <strong>{{ $localCategories->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Eşleştirilmiş:</span>
                        <strong class="text-success">{{ $localCategories->filter(fn($c) => $c->trendyolMapping)->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Eşleştirilmemiş:</span>
                        <strong class="text-warning">{{ $localCategories->filter(fn($c) => !$c->trendyolMapping)->count() }}</strong>
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
                        $mappedCategories = $localCategories->filter(fn($c) => $c->trendyolMapping);
                    @endphp

                    @if($mappedCategories->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Kendi Kategoriniz</th>
                                        <th><i class="fas fa-arrow-right text-muted"></i></th>
                                        <th>Trendyol Kategorisi</th>
                                        <th>Trendyol ID</th>
                                        <th class="text-end">İşlem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($mappedCategories as $category)
                                        <tr>
                                            <td>
                                                <strong>{{ $category->name }}</strong>
                                            </td>
                                            <td class="text-center">
                                                <i class="fas fa-link text-success"></i>
                                            </td>
                                            <td>
                                                {{ $category->trendyolMapping->trendyol_category_name }}
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $category->trendyolMapping->trendyol_category_id }}</span>
                                            </td>
                                            <td class="text-end">
                                                <form method="POST" 
                                                      action="{{ route('admin.trendyol.delete-category-mapping', $category->trendyolMapping->id) }}"
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

            <!-- Eşleştirilmemiş Kategoriler -->
            @php
                $unmappedCategories = $localCategories->filter(fn($c) => !$c->trendyolMapping);
            @endphp

            @if($unmappedCategories->count() > 0)
                <div class="card shadow-sm mt-3">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="fas fa-exclamation-triangle"></i> 
                            Eşleştirilmemiş Kategoriler ({{ $unmappedCategories->count() }})
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($unmappedCategories as $category)
                                <div class="col-md-4 mb-2">
                                    <span class="badge bg-secondary">{{ $category->name }}</span>
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

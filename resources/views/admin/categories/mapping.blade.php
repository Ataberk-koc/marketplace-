@extends('layouts.admin')

@section('title', 'Kategori Eşleştirme - ' . $category->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="bi bi-link-45deg"></i> Kategori Eşleştirme</h1>
        <p class="text-muted mb-0">{{ $category->name }} kategorisini Trendyol kategorisi ile eşleştirin</p>
    </div>
    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Geri Dön
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-grid"></i> Kendi Kategoriniz</h5>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Kategori ID:</dt>
                    <dd class="col-sm-8">{{ $category->id }}</dd>
                    
                    <dt class="col-sm-4">Kategori Adı:</dt>
                    <dd class="col-sm-8"><strong>{{ $category->name }}</strong></dd>
                    
                    <dt class="col-sm-4">Slug:</dt>
                    <dd class="col-sm-8"><code>{{ $category->slug }}</code></dd>
                    
                    <dt class="col-sm-4">Ürün Sayısı:</dt>
                    <dd class="col-sm-8">
                        <span class="badge bg-info">{{ $category->products_count ?? 0 }} ürün</span>
                    </dd>
                    
                    <dt class="col-sm-4">Üst Kategori:</dt>
                    <dd class="col-sm-8">
                        @if($category->parent)
                            {{ $category->parent->name }}
                        @else
                            <span class="text-muted">Ana Kategori</span>
                        @endif
                    </dd>
                    
                    <dt class="col-sm-4">Durum:</dt>
                    <dd class="col-sm-8 mb-0">
                        @if($category->trendyolMapping)
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Eşleştirilmiş
                            </span>
                        @else
                            <span class="badge bg-warning">
                                <i class="bi bi-exclamation-circle"></i> Eşleştirilmemiş
                            </span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background-color: #F27A1A; color: white;">
                <h5 class="mb-0"><i class="bi bi-shop-window"></i> Trendyol Kategorisi</h5>
            </div>
            <div class="card-body">
                @if($category->trendyolMapping)
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Trendyol ID:</dt>
                        <dd class="col-sm-8">{{ $category->trendyolMapping->trendyol_category_id }}</dd>
                        
                        <dt class="col-sm-4">Kategori Adı:</dt>
                        <dd class="col-sm-8"><strong>{{ $category->trendyolMapping->trendyolCategory->name ?? 'N/A' }}</strong></dd>
                        
                        <dt class="col-sm-4">Kategori Yolu:</dt>
                        <dd class="col-sm-8">
                            <small class="text-muted">
                                {{ $category->trendyolMapping->trendyolCategory->path ?? 'N/A' }}
                            </small>
                        </dd>
                        
                        <dt class="col-sm-4">Eşleştirme Tarihi:</dt>
                        <dd class="col-sm-8 mb-0">{{ $category->trendyolMapping->created_at->format('d.m.Y H:i') }}</dd>
                    </dl>
                    
                    <form action="{{ route('admin.categories.save-mapping', $category) }}" method="POST" class="mt-3">
                        @csrf
                        <input type="hidden" name="trendyol_category_id" value="">
                        <button type="submit" class="btn btn-danger btn-sm w-100" onclick="return confirm('Eşleştirmeyi kaldırmak istediğinizden emin misiniz?')">
                            <i class="bi bi-trash"></i> Eşleştirmeyi Kaldır
                        </button>
                    </form>
                @else
                    <p class="text-muted">Bu kategori henüz Trendyol kategorisi ile eşleştirilmemiş.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-search"></i> Trendyol Kategorisi Ara ve Seç</h5>
        <form action="{{ route('admin.categories.sync-trendyol') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-sm btn-warning">
                <i class="bi bi-arrow-repeat"></i> Trendyol Kategorilerini Yükle
            </button>
        </form>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> 
            <strong>BİLGİ:</strong> Trendyol'da ürün eklemek için genellikle <strong>en alt seviye (✓ LEAF)</strong> kategoriler tercih edilir. 
            Parent kategorileri de seçebilirsiniz ancak bazı özelliklerde kısıtlama olabilir.
        </div>

        <form action="{{ route('admin.categories.save-mapping', $category) }}" method="POST" id="mappingForm">
            @csrf
            
            <div class="row mb-3">
                <div class="col-md-12">
                    <label for="category_search" class="form-label">Kategori Ara</label>
                    <input type="text" 
                           class="form-control form-control-lg" 
                           id="category_search" 
                           placeholder="Kategori adı veya yol yazın... (örn: Giyim, Elbise, Kadın > Giyim)" 
                           autocomplete="off">
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i> En az 2 karakter girin, eşleşen kategoriler aşağıda listelenecek. ✓ LEAF = Ürün eklenebilir kategori.
                    </small>
                </div>
            </div>

            <!-- Arama Sonuçları -->
            <div id="search_results" class="mb-3" style="display: none;">
                <div class="card">
                    <div class="card-header bg-light">
                        <strong>Arama Sonuçları</strong> <span id="result_count" class="badge bg-primary">0</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush" id="results_list" style="max-height: 500px; overflow-y: auto;">
                            <!-- Sonuçlar buraya gelecek -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seçilen Kategori -->
            <div id="selected_category_section" style="display: none;">
                <div class="alert alert-success">
                    <h6><i class="bi bi-check-circle"></i> Seçilen Kategori:</h6>
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong id="selected_category_path"></strong>
                            <br>
                            <small class="text-muted">
                                ID: <span id="selected_category_id"></span>
                                <span id="selected_category_leaf_badge" class="badge ms-2"></span>
                            </small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger" id="clear_selection">
                            <i class="bi bi-x-circle"></i> Temizle
                        </button>
                    </div>
                </div>
            </div>

            <input type="hidden" name="trendyol_category_id" id="trendyol_category_id">
            <input type="hidden" name="trendyol_category_name" id="trendyol_category_name">
            <input type="hidden" name="trendyol_category_path" id="trendyol_category_path">

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg" id="save_button" disabled>
                    <i class="bi bi-save"></i> Eşleştirmeyi Kaydet
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Trendyol kategorileri (session'dan - düz liste)
    const trendyolCategories = @json($trendyolCategories);
    
    console.log('Yüklü kategori sayısı:', trendyolCategories.length);

    // Kategori arama
    let searchTimeout;
    $('#category_search').on('input', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val().trim().toLowerCase();
        
        if (query.length < 2) {
            $('#search_results').hide();
            return;
        }

        searchTimeout = setTimeout(function() {
            searchCategories(query);
        }, 300); // 300ms debounce
    });

    function searchCategories(query) {
        // TÜM kategorilerde ara (LEAF zorunluluğu kaldırıldı)
        const results = trendyolCategories.filter(cat => {
            const catName = (cat.name || '').toLowerCase();
            const catPath = (cat.path || '').toLowerCase();
            
            return catName.includes(query) || catPath.includes(query);
        });

        displayResults(results, query);
    }

    function displayResults(results, query) {
        const $resultsList = $('#results_list');
        $resultsList.empty();

        if (results.length === 0) {
            $resultsList.html(`
                <div class="p-3 text-center text-muted">
                    <i class="bi bi-search"></i> 
                    "<strong>${escapeHtml(query)}</strong>" için kategori bulunamadı
                </div>
            `);
        } else {
            results.slice(0, 100).forEach(cat => { // İlk 100 sonuç
                const catId = cat.id;
                const catPath = cat.path || cat.name;
                const isLeaf = cat.leaf === true;
                const highlightedPath = highlightMatch(catPath, query);
                const leafBadge = isLeaf ? '<span class="badge bg-success ms-1">✓ LEAF</span>' : '<span class="badge bg-secondary ms-1">Parent</span>';

                $resultsList.append(`
                    <a href="#" class="list-group-item list-group-item-action search-result-item" 
                       data-category-id="${catId}" 
                       data-category-path="${escapeHtml(catPath)}"
                       data-category-name="${escapeHtml(cat.name)}"
                       data-is-leaf="${isLeaf}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div>${highlightedPath}</div>
                                <small class="text-muted">ID: ${catId} ${leafBadge}</small>
                            </div>
                            <i class="bi bi-arrow-right-circle text-primary ms-2"></i>
                        </div>
                    </a>
                `);
            });

            if (results.length > 100) {
                $resultsList.append(`
                    <div class="p-2 text-center text-muted bg-light">
                        <small>+${results.length - 100} daha fazla sonuç. Daha spesifik arama yapın.</small>
                    </div>
                `);
            }
        }

        $('#result_count').text(results.length);
        $('#search_results').show();
    }

    function highlightMatch(text, query) {
        const regex = new RegExp(`(${escapeRegex(query)})`, 'gi');
        return escapeHtml(text).replace(regex, '<mark>$1</mark>');
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function escapeRegex(str) {
        return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    // Arama sonucundan kategori seçme
    $(document).on('click', '.search-result-item', function(e) {
        e.preventDefault();
        const catId = $(this).data('category-id');
        const catPath = $(this).data('category-path');
        const catName = $(this).data('category-name');
        const isLeaf = $(this).data('is-leaf');
        
        selectCategory(catId, catPath, catName, isLeaf);
    });

    function selectCategory(catId, catPath, catName, isLeaf) {
        // Hidden input'lara yaz
        $('#trendyol_category_id').val(catId);
        $('#trendyol_category_name').val(catName);
        $('#trendyol_category_path').val(catPath);
        
        // Seçili kategori göster
        $('#selected_category_id').text(catId);
        $('#selected_category_path').text(catPath);
        
        // LEAF badge göster
        const leafBadge = isLeaf 
            ? '<span class="badge bg-success">✓ LEAF Kategori</span>' 
            : '<span class="badge bg-secondary">Parent Kategori</span>';
        $('#selected_category_leaf_badge').html(leafBadge);
        
        $('#selected_category_section').show();
        
        // Arama sonuçlarını gizle        // Arama sonuçlarını gizle
        $('#search_results').hide();
        $('#category_search').val('');
        
        // Kaydet butonunu aktif et
        $('#save_button').prop('disabled', false);
        
        // Bildirim göster
        showNotification('success', `<strong>${catPath}</strong> seçildi!`);
    }

    // Seçimi temizle
    $('#clear_selection').on('click', function() {
        $('#trendyol_category_id').val('');
        $('#trendyol_category_name').val('');
        $('#trendyol_category_path').val('');
        $('#selected_category_section').hide();
        $('#save_button').prop('disabled', true);
        $('#category_search').val('').focus();
    });

    // Bildirim göster
    function showNotification(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-info';
        const notification = $(`
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="bi bi-check-circle"></i> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        $('body').append(notification);
        setTimeout(() => notification.remove(), 3000);
    }

    // Sayfa yüklendiğinde mevcut eşleştirme varsa göster
    @if($category->trendyolMapping)
        // Mevcut kategoriyi session'dan bul ve leaf durumunu kontrol et
        const currentCatId = '{{ $category->trendyolMapping->trendyol_category_id }}';
        const currentCat = trendyolCategories.find(c => c.id == currentCatId);
        const isCurrentLeaf = currentCat ? currentCat.leaf === true : false;
        
        selectCategory(
            currentCatId,
            '{{ $category->trendyolMapping->trendyol_category_path ?? $category->trendyolMapping->trendyol_category_name }}',
            '{{ $category->trendyolMapping->trendyol_category_name }}',
            isCurrentLeaf
        );
    @endif

    // Trendyol kategorisi yüklenmemişse uyarı
    @if(count($trendyolCategories) === 0)
        $('#category_search').prop('disabled', true).attr('placeholder', 'Önce "Trendyol Kategorilerini Yükle" butonuna tıklayın');
    @endif
});
</script>
@endpush
@endsection

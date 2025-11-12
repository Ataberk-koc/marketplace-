@extends('layouts.admin')

@section('title', 'Ürün Eşleştirme - Trendyol')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-box-open"></i> Ürün Eşleştirme (Tek Tablo Sistemi)</h2>
        <a href="{{ route('admin.trendyol.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Geri Dön
        </a>
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
        <!-- Sol Panel: Eşleştirme Formu -->
        <div class="col-lg-5">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Yeni Eşleştirme</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.trendyol.save-product-mapping') }}" method="POST" id="mappingForm">
                        @csrf

                        <!-- 1. Ürün Seç -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">1. Ürünü Seçin</label>
                            <select name="product_id" id="productSelect" class="form-select" required>
                                <option value="">Ürün seçin...</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" 
                                        data-has-mapping="{{ $product->trendyolMapping ? 'true' : 'false' }}"
                                        {{ $product->trendyolMapping ? 'disabled' : '' }}>
                                        {{ $product->name }} 
                                        @if($product->trendyolMapping)
                                            (✓ Eşleştirilmiş)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Eşleştirilmiş ürünler devre dışı</small>
                        </div>

                        <!-- 2. Trendyol Kategori Seç -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">2. Trendyol Kategorisi</label>
                            <select name="trendyol_category_id" id="categorySelect" class="form-select" required>
                                <option value="">Kategori seçin...</option>
                                @foreach($trendyolCategories as $category)
                                    <option value="{{ $category->id }}" 
                                        data-parent="{{ $category->parent_id }}"
                                        data-leaf="{{ $category->is_leaf ? 'true' : 'false' }}">
                                        @if($category->parent_id)
                                            └─ {{ $category->name }}
                                            @if($category->is_leaf)
                                                <span class="text-success">✓</span>
                                            @endif
                                        @else
                                            <strong>{{ $category->name }}</strong>
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">
                                <i class="fas fa-filter"></i> Ürününüzün kategorisine göre filtreleniyor
                            </small>
                        </div>

                        <!-- 3. Trendyol Marka Seç -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">3. Trendyol Markası</label>
                            <select name="trendyol_brand_id" id="brandSelect" class="form-select" required>
                                <option value="">Marka seçin...</option>
                                @foreach($trendyolBrands as $brand)
                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 4. Özellik Eşleştirmeleri (Dinamik Yüklenecek) -->
                        <div id="attributesSection" style="display:none;">
                            <hr>
                            <h6 class="fw-bold mb-3">4. Özellik Eşleştirmeleri</h6>
                            <div class="alert alert-info">
                                <small>
                                    <i class="fas fa-lightbulb"></i> 
                                    <strong>Nasıl Çalışır:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Ürününüzde olan özellikleri Trendyol karşılıkları ile eşleştirin</li>
                                        <li><strong>Varyant özellikler</strong> (Beden, Renk) farklı kombinasyonlar oluşturur</li>
                                        <li><strong>Genel özellikler</strong> (Kumaş, Desen) tüm varyantlarda aynıdır</li>
                                    </ul>
                                </small>
                            </div>
                            <div id="attributeInputs">
                                <!-- AJAX ile dinamik yüklenecek -->
                            </div>
                        </div>

                        <!-- 5. Fiyat Bilgileri (Opsiyonel) -->
                        <div class="mt-4">
                            <hr>
                            <h6 class="fw-bold mb-3">5. Trendyol Fiyatları (Opsiyonel)</h6>
                            <div class="alert alert-warning">
                                <small>
                                    <i class="fas fa-info-circle"></i> 
                                    <strong>Not:</strong> Boş bırakırsanız ürününüzün kendi fiyatları kullanılır.
                                </small>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Liste Fiyatı (₺)</label>
                                        <input type="number" 
                                               name="custom_price" 
                                               class="form-control" 
                                               step="0.01" 
                                               placeholder="Ör: 299.99">
                                        <small class="text-muted">
                                            Varsayılan: <strong id="defaultPrice">-</strong>
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">İndirimli Fiyat (₺)</label>
                                        <input type="number" 
                                               name="custom_sale_price" 
                                               class="form-control" 
                                               step="0.01" 
                                               placeholder="Ör: 249.99">
                                        <small class="text-muted">
                                            Varsayılan: <strong id="defaultSalePrice">-</strong>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mt-3">
                            <i class="bi bi-check-circle"></i> Eşleştirmeyi Kaydet
                        </button>
                    </form>
                </div>
            </div>

            <!-- İstatistikler -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="bi bi-bar-chart"></i> İstatistikler</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-3">
                            <h4 class="text-primary mb-0">{{ $stats['total_products'] }}</h4>
                            <small class="text-muted">Toplam</small>
                        </div>
                        <div class="col-3">
                            <h4 class="text-warning mb-0">{{ $stats['mapped_products'] }}</h4>
                            <small class="text-muted">Bekleyen</small>
                        </div>
                        <div class="col-3">
                            <h4 class="text-success mb-0">{{ $stats['sent_products'] }}</h4>
                            <small class="text-muted">Gönderildi</small>
                        </div>
                        <div class="col-3">
                            <h4 class="text-muted mb-0">{{ $stats['unmapped_products'] }}</h4>
                            <small class="text-muted">Eşleşmemiş</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sağ Panel: Mevcut Eşleştirmeler -->
        <div class="col-lg-7">
            <!-- Tab Navigation -->
            <ul class="nav nav-tabs mb-3" id="productTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                        <i class="bi bi-clock"></i> Bekleyen ({{ $existingMappings->count() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="sent-tab" data-bs-toggle="tab" data-bs-target="#sent" type="button" role="tab">
                        <i class="bi bi-check-circle"></i> Gönderildi ({{ $sentProducts->count() }})
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="productTabsContent">
                <!-- Bekleyen Ürünler -->
                <div class="tab-pane fade show active" id="pending" role="tabpanel">
                    <div class="card">
                        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-list-check"></i> Eşleştirilmiş Ürünler</h5>
                            @if($existingMappings->count() > 0)
                                <form action="{{ route('admin.trendyol.bulk-send') }}" method="POST" style="display: inline;"
                                      onsubmit="return confirm('{{ $existingMappings->count() }} ürünü Trendyol\'a göndermek istediğinize emin misiniz?');">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="bi bi-cloud-upload"></i> Hepsini Gönder
                                    </button>
                                </form>
                            @endif
                        </div>
                        <div class="card-body">
                            @if($existingMappings->isEmpty())
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> Henüz gönderilmemiş ürün yok.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Ürün</th>
                                                <th>Trendyol Kategori</th>
                                                <th>Trendyol Marka</th>
                                                <th>Fiyat</th>
                                                <th>Özellikler</th>
                                                <th>İşlem</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($existingMappings as $mapping)
                                            <tr>
                                                <td>
                                                    <strong>{{ $mapping->product->name }}</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        {{ $mapping->product->brand->name ?? 'N/A' }} - 
                                                        {{ $mapping->product->category->name ?? 'N/A' }}
                                                    </small>
                                                </td>
                                                <td>{{ $mapping->trendyol_category_name }}</td>
                                                <td>{{ $mapping->trendyol_brand_name }}</td>
                                                <td>
                                                    @php
                                                        $listPrice = $mapping->custom_price ?? $mapping->product->price;
                                                        $salePrice = $mapping->custom_sale_price ?? $mapping->product->discount_price ?? $mapping->product->price;
                                                    @endphp
                                                    <div>
                                                        <strong class="text-success">{{ number_format($salePrice, 2) }} ₺</strong>
                                                        @if($listPrice != $salePrice)
                                                            <br>
                                                            <small class="text-muted text-decoration-line-through">{{ number_format($listPrice, 2) }} ₺</small>
                                                        @endif
                                                    </div>
                                                    @if($mapping->custom_price || $mapping->custom_sale_price)
                                                        <small class="badge bg-info">Özel</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($mapping->attribute_mappings && count($mapping->attribute_mappings) > 0)
                                                        @foreach($mapping->attribute_mappings as $attrName => $attrValue)
                                                            <span class="badge bg-secondary me-1 mb-1">
                                                                {{ $attrName }}: {{ $attrValue }}
                                                            </span>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <form action="{{ route('admin.trendyol.send-single-product', $mapping->id) }}" method="POST" 
                                                              style="display: inline;"
                                                              onsubmit="return confirm('{{ $mapping->product->name }} ürününü Trendyol\'a göndermek istediğinize emin misiniz?');">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success" title="Trendyol'a Gönder">
                                                                <i class="bi bi-send"></i>
                                                            </button>
                                                        </form>
                                                        
                                                        <form action="{{ route('admin.trendyol.delete-product-mapping', $mapping->id) }}" method="POST" 
                                                              style="display: inline;"
                                                              onsubmit="return confirm('Bu eşleştirmeyi silmek istediğinizden emin misiniz?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger" title="Eşleştirmeyi Sil">
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
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Gönderilen Ürünler -->
                <div class="tab-pane fade" id="sent" role="tabpanel">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-check-circle"></i> Gönderilen Ürünler</h5>
                        </div>
                        <div class="card-body">
                            @if($sentProducts->isEmpty())
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> Henüz Trendyol'a gönderilmiş ürün yok.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Ürün</th>
                                                <th>Kategori & Marka</th>
                                                <th>Fiyat</th>
                                                <th>Durum</th>
                                                <th>Gönderim Tarihi</th>
                                                <th>Batch ID</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($sentProducts as $mapping)
                                            <tr>
                                                <td>
                                                    <strong>{{ $mapping->product->name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $mapping->product->sku }}</small>
                                                </td>
                                                <td>
                                                    <small>
                                                        <strong>Kat:</strong> {{ $mapping->trendyol_category_name }}<br>
                                                        <strong>Marka:</strong> {{ $mapping->trendyol_brand_name }}
                                                    </small>
                                                </td>
                                                <td>
                                                    @php
                                                        $listPrice = $mapping->custom_price ?? $mapping->product->price;
                                                        $salePrice = $mapping->custom_sale_price ?? $mapping->product->discount_price ?? $mapping->product->price;
                                                    @endphp
                                                    <div>
                                                        <strong class="text-success">{{ number_format($salePrice, 2) }} ₺</strong>
                                                        @if($listPrice != $salePrice)
                                                            <br>
                                                            <small class="text-muted text-decoration-line-through">{{ number_format($listPrice, 2) }} ₺</small>
                                                        @endif
                                                    </div>
                                                    @if($mapping->custom_price || $mapping->custom_sale_price)
                                                        <small class="badge bg-info">Özel</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($mapping->status === 'sent')
                                                        <span class="badge bg-primary">Gönderildi</span>
                                                    @elseif($mapping->status === 'approved')
                                                        <span class="badge bg-success">Onaylandı</span>
                                                    @elseif($mapping->status === 'rejected')
                                                        <span class="badge bg-danger">Reddedildi</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <small>{{ $mapping->sent_at ? $mapping->sent_at->format('d.m.Y H:i') : '-' }}</small>
                                                </td>
                                                <td>
                                                    @if($mapping->batch_request_id)
                                                        <small class="font-monospace">{{ $mapping->batch_request_id }}</small>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if($stats['unmapped_products'] > 0)
            <div class="alert alert-warning mt-3">
                <i class="bi bi-exclamation-triangle"></i> 
                <strong>{{ $stats['unmapped_products'] }}</strong> ürün henüz Trendyol ile eşleştirilmemiş.
            </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('productSelect');
    const categorySelect = document.getElementById('categorySelect');
    const attributesSection = document.getElementById('attributesSection');
    const attributeInputs = document.getElementById('attributeInputs');

    let currentProduct = null;
    let allCategories = []; // Tüm kategorileri sakla

    // Sayfa yüklendiğinde tüm kategorileri sakla
    document.querySelectorAll('#categorySelect option').forEach(option => {
        if (option.value) {
            allCategories.push({
                value: option.value,
                text: option.text,
                dataParent: option.getAttribute('data-parent'),
                dataLeaf: option.getAttribute('data-leaf')
            });
        }
    });

    // Ürün seçildiğinde ürün bilgilerini al
    productSelect.addEventListener('change', function() {
        const productId = this.value;
        if (!productId) {
            currentProduct = null;
            resetCategories();
            return;
        }

        // Ürün detaylarını AJAX ile al
        fetch(`/api/v1/products/${productId}`)
            .then(response => response.json())
            .then(data => {
                currentProduct = data;
                console.log('📦 Ürün Bilgisi:', currentProduct);
                
                // Fiyat bilgilerini göster
                document.getElementById('defaultPrice').textContent = 
                    currentProduct.price ? currentProduct.price.toFixed(2) + ' ₺' : '-';
                document.getElementById('defaultSalePrice').textContent = 
                    currentProduct.discount_price ? currentProduct.discount_price.toFixed(2) + ' ₺' : '-';
                
                // Ürünün kategorisine göre Trendyol kategorilerini filtrele
                filterCategoriesByProduct(currentProduct);
                
                // Kategori değiştiyse attributes'ı yeniden yükle
                if (categorySelect.value) {
                    loadCategoryAttributes(categorySelect.value);
                }
            })
            .catch(error => {
                console.error('Ürün bilgileri alınamadı:', error);
                resetCategories();
                document.getElementById('defaultPrice').textContent = '-';
                document.getElementById('defaultSalePrice').textContent = '-';
            });
    });

    // Ürünün kategorisine göre Trendyol kategorilerini filtrele
    function filterCategoriesByProduct(product) {
        if (!product.category) {
            resetCategories();
            return;
        }

        const productCategoryName = product.category.name.toLowerCase();
        console.log('🏷️ Ürün Kategorisi:', productCategoryName);

        // Kategori eşleşme kuralları
        const categoryMappings = {
            'giyim': ['giyim', 'elbise', 'bluz', 'pantolon', 'etek', 'kazak', 'mont', 'kaban', 'ceket', 'tişört', 'gömlek'],
            'ayakkabı': ['ayakkabı', 'bot', 'terlik', 'spor ayakkabı', 'sandalet'],
            'aksesuar': ['aksesuar', 'takı', 'saat', 'çanta', 'kemer', 'şapka', 'atkı', 'eldiven'],
            'çanta': ['çanta', 'sırt çantası', 'el çantası', 'omuz çantası'],
            'ev': ['ev', 'yaşam', 'dekorasyon', 'mutfak', 'banyo', 'tekstil']
        };

        // Ürün kategorisini tespit et
        let matchedCategory = null;
        for (const [key, keywords] of Object.entries(categoryMappings)) {
            if (keywords.some(keyword => productCategoryName.includes(keyword))) {
                matchedCategory = key;
                break;
            }
        }

        console.log('🎯 Eşleşen Kategori Tipi:', matchedCategory);

        // Kategorileri filtrele
        categorySelect.innerHTML = '<option value="">Kategori seçin...</option>';
        
        allCategories.forEach(cat => {
            const catText = cat.text.toLowerCase();
            let shouldShow = false;

            if (matchedCategory) {
                // Eşleşen kategoriye göre filtrele
                const keywords = categoryMappings[matchedCategory];
                shouldShow = keywords.some(keyword => catText.includes(keyword));
            } else {
                // Eşleşme yoksa tüm kategorileri göster
                shouldShow = true;
            }

            if (shouldShow) {
                const option = document.createElement('option');
                option.value = cat.value;
                option.text = cat.text;
                option.setAttribute('data-parent', cat.dataParent);
                option.setAttribute('data-leaf', cat.dataLeaf);
                categorySelect.appendChild(option);
            }
        });

        const filteredCount = categorySelect.options.length - 1;
        console.log(`✅ ${filteredCount} kategori gösteriliyor`);
    }

    // Tüm kategorileri geri yükle
    function resetCategories() {
        categorySelect.innerHTML = '<option value="">Kategori seçin...</option>';
        allCategories.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat.value;
            option.text = cat.text;
            option.setAttribute('data-parent', cat.dataParent);
            option.setAttribute('data-leaf', cat.dataLeaf);
            categorySelect.appendChild(option);
        });
    }

    // Kategori değiştiğinde attributes yükle
    categorySelect.addEventListener('change', function() {
        const categoryId = this.value;
        
        if (!categoryId) {
            attributesSection.style.display = 'none';
            return;
        }

        loadCategoryAttributes(categoryId);
    });

    function loadCategoryAttributes(categoryId) {
        console.log('🔍 Kategori ID:', categoryId); // DEBUG
        
        // AJAX ile kategori attributes getir
        fetch(`/admin/trendyol/category-attributes/${categoryId}`)
            .then(response => response.json())
            .then(data => {
                console.log('📦 Gelen Attributes:', data); // DEBUG
                
                if (data.success && data.attributes.length > 0) {
                    attributeInputs.innerHTML = '';
                    
                    data.attributes.forEach(attr => {
                        const attrDiv = document.createElement('div');
                        attrDiv.className = 'mb-4 p-3 border rounded bg-light';
                        
                        // Attribute başlığı
                        let headerHTML = `
                            <h6 class="fw-bold mb-3">
                                ${attr.attribute.name} 
                                ${attr.required ? '<span class="text-danger">*</span>' : ''}
                                ${attr.varianter ? '<span class="badge bg-info ms-2">Varyant</span>' : ''}
                            </h6>
                        `;

                        // Ürünün mevcut değerlerini göster
                        let productValuesHTML = '';
                        if (currentProduct && attr.attribute.name === 'Beden') {
                            // Beden bilgilerini product_size'dan al
                            productValuesHTML = `
                                <div class="mb-3">
                                    <small class="text-muted d-block mb-2">
                                        <i class="fas fa-info-circle"></i> Ürününüzdeki bedenler:
                                    </small>
                                    <div class="alert alert-info py-2">
                                        <small><strong>Not:</strong> Ürününüzün bedenlerini Trendyol bedenleriyle manuel eşleştirin.</small>
                                    </div>
                                </div>
                            `;
                        } else if (currentProduct && currentProduct.attributes) {
                            // Diğer özellikler için attributes JSON'dan al
                            const attrKey = attr.attribute.name.toLowerCase();
                            if (currentProduct.attributes[attrKey]) {
                                const values = Array.isArray(currentProduct.attributes[attrKey]) 
                                    ? currentProduct.attributes[attrKey] 
                                    : [currentProduct.attributes[attrKey]];
                                
                                productValuesHTML = `
                                    <div class="mb-3">
                                        <small class="text-muted d-block mb-2">
                                            <i class="fas fa-box"></i> Ürününüzde: 
                                            <strong>${values.join(', ')}</strong>
                                        </small>
                                    </div>
                                `;
                            }
                        }

                        // Trendyol değerlerini dropdown olarak göster
                        let selectHTML = `
                            <label class="form-label">Trendyol ${attr.attribute.name} Değeri</label>
                            <select name="attribute_mappings[${attr.attribute.name}]" class="form-select" ${attr.required ? 'required' : ''}>
                                <option value="">Seçiniz...</option>
                                ${attr.attributeValues.map(val => `
                                    <option value="${val.id}">${val.name}</option>
                                `).join('')}
                            </select>
                            <small class="text-muted">
                                ${attr.varianter ? '⚠️ Bu özellik varyant oluşturur' : 'ℹ️ Genel özellik'}
                            </small>
                        `;

                        attrDiv.innerHTML = headerHTML + productValuesHTML + selectHTML;
                        attributeInputs.appendChild(attrDiv);
                    });
                    
                    attributesSection.style.display = 'block';
                } else {
                    attributesSection.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Özellikler yüklenirken hata:', error);
                alert('Özellikler yüklenirken hata oluştu!');
            });
    }
});
</script>
@endsection

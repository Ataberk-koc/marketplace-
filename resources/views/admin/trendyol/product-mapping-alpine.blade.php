@extends('layouts.admin')

@section('title', 'Ürün Eşleştirme - Trendyol')

@section('content')
<div class="container-fluid" x-data="productMapping()">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-box-open"></i> Ürün Eşleştirme (Marka → Kategori → Ürün)</h2>
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
                    <form action="{{ route('admin.trendyol.save-product-mapping') }}" method="POST" @submit="validateForm">
                        @csrf

                        <!-- ADIM 1: Yerel Marka Seç -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <span class="badge bg-primary me-2">1</span> Yerel Marka Seçin
                            </label>
                            <select x-model="selectedBrand" 
                                    @change="onBrandChange()"
                                    class="form-select form-select-lg" 
                                    required>
                                <option value="">Marka seçin...</option>
                                @foreach($localBrands as $brand)
                                    <option value="{{ $brand->id }}">
                                        {{ $brand->name }} ({{ $brand->products_count }} ürün)
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">İlk olarak yerel markanızı seçin</small>
                        </div>

                        <!-- ADIM 2: Yerel Kategori Seç -->
                        <div class="mb-4" x-show="categories.length > 0" x-transition>
                            <label class="form-label fw-bold">
                                <span class="badge bg-primary me-2">2</span> Yerel Kategori Seçin
                            </label>
                            <template x-if="loadingCategories">
                                <div class="text-center py-3">
                                    <div class="spinner-border spinner-border-sm" role="status">
                                        <span class="visually-hidden">Yükleniyor...</span>
                                    </div>
                                    <span class="ms-2">Kategoriler yükleniyor...</span>
                                </div>
                            </template>
                            <select x-model="selectedCategory" 
                                    @change="onCategoryChange()"
                                    class="form-select form-select-lg"
                                    x-show="!loadingCategories"
                                    required>
                                <option value="">Kategori seçin...</option>
                                <template x-for="category in categories" :key="category.id">
                                    <option :value="category.id" x-text="`${category.name} (${category.products_count} ürün)`"></option>
                                </template>
                            </select>
                            <small class="text-muted">Seçilen markaya ait kategoriler</small>
                        </div>

                        <!-- ADIM 3: Ürün Seç -->
                        <div class="mb-4" x-show="products.length > 0" x-transition>
                            <label class="form-label fw-bold">
                                <span class="badge bg-primary me-2">3</span> Ürün Seçin
                            </label>
                            <template x-if="loadingProducts">
                                <div class="text-center py-3">
                                    <div class="spinner-border spinner-border-sm" role="status">
                                        <span class="visually-hidden">Yükleniyor...</span>
                                    </div>
                                    <span class="ms-2">Ürünler yükleniyor...</span>
                                </div>
                            </template>
                            <select x-model="selectedProduct" 
                                    name="product_id"
                                    @change="onProductChange()"
                                    class="form-select form-select-lg"
                                    x-show="!loadingProducts"
                                    required>
                                <option value="">Ürün seçin...</option>
                                <template x-for="product in products" :key="product.id">
                                    <option :value="product.id" x-text="`${product.name} - ${product.sku}`"></option>
                                </template>
                            </select>
                            <small class="text-muted">Seçilen marka ve kategoriye ait ürünler</small>
                        </div>

                        <hr class="my-4" x-show="selectedProduct">

                        <!-- ADIM 4: Trendyol Marka Gir (DATALIST AUTOCOMPLETE) -->
                        <div class="mb-4" x-show="selectedProduct" x-transition>
                            <label class="form-label fw-bold">
                                <span class="badge bg-success me-2">4</span> Trendyol Markası
                            </label>
                            <input 
                                type="text" 
                                name="trendyol_brand_name"
                                x-model="selectedTrendyolBrandName"
                                list="trendyolBrandsList"
                                class="form-control form-control-lg" 
                                placeholder="Marka adını yazın (örn: Nike, Adidas, Puma...)"
                                autocomplete="off"
                                required
                            >
                            <datalist id="trendyolBrandsList">
                                @foreach($trendyolBrands as $brand)
                                    <option value="{{ $brand['name'] }}">
                                @endforeach
                            </datalist>
                            <small class="text-muted d-block mt-1">
                                <i class="fas fa-keyboard"></i> 
                                Yazmaya başlayın, eşleşen markalar görünecek
                            </small>
                        </div>

                        <!-- ADIM 5: Trendyol Kategori Seç -->
                        <div class="mb-4" x-show="selectedTrendyolBrandName" x-transition>
                            <label class="form-label fw-bold">
                                <span class="badge bg-success me-2">5</span> Trendyol Kategorisi
                            </label>
                            <select x-model="selectedTrendyolCategory" 
                                    name="trendyol_category_id"
                                    @change="onTrendyolCategoryChange()"
                                    class="form-select" 
                                    required>
                                <option value="">Trendyol kategorisi seçin...</option>
                                @foreach($trendyolCategories as $category)
                                    <option value="{{ $category['id'] }}" 
                                        data-name="{{ $category['name'] }}"
                                        data-leaf="{{ isset($category['subCategories']) && count($category['subCategories']) > 0 ? 'false' : 'true' }}">
                                        {{ $category['name'] }}
                                        @if(!isset($category['subCategories']) || count($category['subCategories']) == 0)
                                            ✓
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="trendyol_category_name" x-model="selectedTrendyolCategoryName">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> Sadece leaf (son seviye) kategoriler seçilebilir
                            </small>
                        </div>

                        <!-- ADIM 6: Özellik Eşleştirmeleri (Dinamik) -->
                        <div x-show="attributes.length > 0" x-transition>
                            <hr class="my-4">
                            <label class="form-label fw-bold">
                                <span class="badge bg-warning me-2">6</span> Özellik Eşleştirmeleri
                            </label>
                            
                            <template x-if="loadingAttributes">
                                <div class="text-center py-3">
                                    <div class="spinner-border spinner-border-sm" role="status">
                                        <span class="visually-hidden">Yükleniyor...</span>
                                    </div>
                                    <span class="ms-2">Özellikler yükleniyor...</span>
                                </div>
                            </template>

                            <div class="alert alert-info" x-show="!loadingAttributes">
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

                            <template x-for="(attribute, index) in attributes" :key="attribute.attribute.id">
                                <div class="card mb-3" x-show="!loadingAttributes">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="mb-0">
                                                <span x-text="attribute.attribute.name"></span>
                                                <span class="badge bg-info ms-2" x-show="attribute.attribute.varianter">Varyant</span>
                                                <span class="badge bg-secondary ms-2" x-show="!attribute.attribute.varianter">Genel</span>
                                                <span class="badge bg-danger ms-2" x-show="attribute.attribute.required">Zorunlu</span>
                                            </h6>
                                        </div>

                                        <!-- Ürün değerleri -->
                                        <div class="mb-2">
                                            <small class="text-muted">Ürünümüzde:</small>
                                            <div class="d-flex flex-wrap gap-1">
                                                <template x-for="size in productSizes" :key="size.id">
                                                    <span class="badge bg-light text-dark" x-text="size.name"></span>
                                                </template>
                                            </div>
                                        </div>

                                        <!-- Trendyol değer seçimi -->
                                        <select :name="`attributes[${attribute.attribute.id}]`" 
                                                class="form-select"
                                                :required="attribute.attribute.required">
                                            <option value="">Seçiniz...</option>
                                            <template x-for="value in attribute.attributeValues" :key="value.id">
                                                <option :value="value.id" x-text="value.name"></option>
                                            </template>
                                        </select>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Fiyat Bilgileri -->
                        <div class="mt-4" x-show="selectedProduct" x-transition>
                            <hr>
                            <h6 class="fw-bold mb-3">Trendyol Fiyatları (Opsiyonel)</h6>
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
                                            Varsayılan: <strong x-text="productPrice"></strong>
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
                                            Varsayılan: <strong x-text="productSalePrice"></strong>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" 
                                class="btn btn-primary w-100 mt-3"
                                :disabled="!canSubmit">
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
                            <h4 class="text-danger mb-0">{{ $stats['unmapped_products'] }}</h4>
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

@push('scripts')
<script>
function productMapping() {
    return {
        // State
        selectedBrand: '',
        selectedCategory: '',
        selectedProduct: '',
        selectedTrendyolBrandName: '',
        selectedTrendyolCategory: '',
        selectedTrendyolCategoryName: '',
        
        categories: [],
        products: [],
        productSizes: [],
        attributes: [],
        
        productPrice: '-',
        productSalePrice: '-',
        
        loadingCategories: false,
        loadingProducts: false,
        loadingAttributes: false,
        
        // Computed
        get canSubmit() {
            return this.selectedProduct && 
                   this.selectedTrendyolBrandName && 
                   this.selectedTrendyolCategory;
        },
        
        // Methods
        async onBrandChange() {
            if (!this.selectedBrand) {
                this.resetAfterBrand();
                return;
            }
            
            console.log('🏷️ Marka seçildi:', this.selectedBrand);
            this.loadingCategories = true;
            this.resetAfterBrand();
            
            try {
                const response = await fetch(`/admin/trendyol/api/categories-by-brand/${this.selectedBrand}`);
                const data = await response.json();
                
                if (data.success) {
                    this.categories = data.data;
                    console.log('✅ Kategoriler yüklendi:', this.categories.length);
                } else {
                    console.error('❌ API hatası:', data);
                    alert('Kategoriler yüklenirken hata oluştu!');
                }
            } catch (error) {
                console.error('❌ Fetch hatası:', error);
                alert('Kategoriler yüklenirken hata oluştu: ' + error.message);
            } finally {
                this.loadingCategories = false;
            }
        },
        
        async onCategoryChange() {
            if (!this.selectedCategory) {
                this.resetAfterCategory();
                return;
            }
            
            console.log('📁 Kategori seçildi:', this.selectedCategory);
            this.loadingProducts = true;
            this.resetAfterCategory();
            
            try {
                const response = await fetch(`/admin/trendyol/api/products-by-brand-category?brand_id=${this.selectedBrand}&category_id=${this.selectedCategory}`);
                const data = await response.json();
                
                if (data.success) {
                    this.products = data.data;
                    console.log('✅ Ürünler yüklendi:', this.products.length);
                } else {
                    console.error('❌ API hatası:', data);
                    alert('Ürünler yüklenirken hata oluştu!');
                }
            } catch (error) {
                console.error('❌ Fetch hatası:', error);
                alert('Ürünler yüklenirken hata oluştu: ' + error.message);
            } finally {
                this.loadingProducts = false;
            }
        },
        
        onProductChange() {
            if (!this.selectedProduct) {
                this.resetAfterProduct();
                return;
            }
            
            console.log('📦 Ürün seçildi:', this.selectedProduct);
            const product = this.products.find(p => p.id == this.selectedProduct);
            
            if (product) {
                this.productPrice = product.price ? product.price + ' ₺' : '-';
                this.productSalePrice = product.sale_price ? product.sale_price + ' ₺' : '-';
                this.productSizes = product.sizes || [];
            }
        },
        
        async onTrendyolCategoryChange() {
            const select = document.querySelector('select[name="trendyol_category_id"]');
            const selectedOption = select.options[select.selectedIndex];
            this.selectedTrendyolCategoryName = selectedOption.getAttribute('data-name') || '';
            
            if (!this.selectedTrendyolCategory) {
                this.attributes = [];
                return;
            }
            
            console.log('📁 Trendyol kategorisi seçildi:', this.selectedTrendyolCategory);
            this.loadingAttributes = true;
            
            try {
                const response = await fetch(`/admin/trendyol/category-attributes/${this.selectedTrendyolCategory}`);
                const data = await response.json();
                
                if (data.success && data.categoryAttributes) {
                    this.attributes = data.categoryAttributes;
                    console.log('✅ Özellikler yüklendi:', this.attributes.length);
                } else {
                    console.error('❌ API hatası:', data);
                    this.attributes = [];
                }
            } catch (error) {
                console.error('❌ Fetch hatası:', error);
                alert('Özellikler yüklenirken hata oluştu: ' + error.message);
                this.attributes = [];
            } finally {
                this.loadingAttributes = false;
            }
        },
        
        resetAfterBrand() {
            this.selectedCategory = '';
            this.categories = [];
            this.resetAfterCategory();
        },
        
        resetAfterCategory() {
            this.selectedProduct = '';
            this.products = [];
            this.resetAfterProduct();
        },
        
        resetAfterProduct() {
            this.productSizes = [];
            this.productPrice = '-';
            this.productSalePrice = '-';
            this.attributes = [];
        },
        
        validateForm(e) {
            if (!this.canSubmit) {
                e.preventDefault();
                alert('Lütfen tüm zorunlu alanları doldurun!');
                return false;
            }
            return true;
        }
    }
}
</script>
@endpush
@endsection

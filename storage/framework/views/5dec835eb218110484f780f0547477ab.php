

<?php $__env->startSection('title', 'Ürün Düzenle'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="productEditor()" x-init="initializeProduct()">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">
            <i class="bi bi-pencil-square"></i> Ürün Düzenle
        </h2>
        <div class="flex gap-2">
            <a href="<?php echo e(route('admin.products.show', $product)); ?>" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                <i class="bi bi-eye"></i> Detayları Görüntüle
            </a>
            <a href="<?php echo e(route('admin.products.index')); ?>" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                <i class="bi bi-arrow-left"></i> Geri Dön
            </a>
        </div>
    </div>

    <?php if($errors->any()): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <strong>Hata!</strong>
            <ul class="mt-2">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?php echo e(route('admin.products.update', $product)); ?>" method="POST" @submit="prepareFormData">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        
        <!-- Hidden inputs for JSON data -->
        <input type="hidden" name="variants_json" x-model="JSON.stringify(variants)">
        <input type="hidden" name="attributes_json" x-model="JSON.stringify(attributes)">
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Ana Bilgiler - 2/3 genişlik -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Genel Bilgiler -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">
                        <i class="bi bi-info-circle"></i> Genel Bilgiler
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Ürün Adı <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   x-model="productData.name"
                                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Model Kodu <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="model_code" 
                                   x-model="productData.model_code"
                                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Açıklama
                            </label>
                            <textarea name="description" 
                                      x-model="productData.description"
                                      rows="4"
                                      class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Varyantlar -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex justify-between items-center mb-4 border-b pb-2">
                        <h3 class="text-lg font-semibold text-gray-700">
                            <i class="bi bi-layers"></i> Varyantlar
                        </h3>
                        <button type="button" 
                                @click="addVariant"
                                class="px-3 py-1 bg-green-500 text-white text-sm rounded hover:bg-green-600">
                            <i class="bi bi-plus-circle"></i> Varyant Ekle
                        </button>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(variant, index) in variants" :key="index">
                            <div class="border border-gray-200 rounded p-4 bg-gray-50">
                                <div class="flex justify-between items-start mb-3">
                                    <span class="text-sm font-medium text-gray-600">Varyant #<span x-text="index + 1"></span></span>
                                    <button type="button" 
                                            @click="removeVariant(index)"
                                            class="text-red-500 hover:text-red-700">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Renk</label>
                                        <input type="text" 
                                               x-model="variant.color"
                                               class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Beden</label>
                                        <input type="text" 
                                               x-model="variant.size"
                                               class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Barkod <span class="text-red-500">*</span></label>
                                        <input type="text" 
                                               x-model="variant.barcode"
                                               class="w-full border border-gray-300 rounded px-2 py-1 text-sm"
                                               required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Stok Kodu</label>
                                        <input type="text" 
                                               x-model="variant.sku"
                                               class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Fiyat (₺) <span class="text-red-500">*</span></label>
                                        <input type="number" 
                                               step="0.01" 
                                               x-model="variant.price"
                                               class="w-full border border-gray-300 rounded px-2 py-1 text-sm"
                                               required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Stok <span class="text-red-500">*</span></label>
                                        <input type="number" 
                                               x-model="variant.stock"
                                               class="w-full border border-gray-300 rounded px-2 py-1 text-sm"
                                               required>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div x-show="variants.length === 0" class="text-center py-8 text-gray-500">
                            <i class="bi bi-inbox text-4xl"></i>
                            <p class="mt-2">Henüz varyant eklenmedi</p>
                        </div>
                    </div>
                </div>

                <!-- Özellikler -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex justify-between items-center mb-4 border-b pb-2">
                        <h3 class="text-lg font-semibold text-gray-700">
                            <i class="bi bi-tags"></i> Özellikler
                        </h3>
                        <button type="button" 
                                @click="addAttribute"
                                class="px-3 py-1 bg-green-500 text-white text-sm rounded hover:bg-green-600">
                            <i class="bi bi-plus-circle"></i> Özellik Ekle
                        </button>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(attr, index) in attributes" :key="index">
                            <div class="flex gap-2 items-start">
                                <div class="flex-1">
                                    <input type="text" 
                                           x-model="attr.key"
                                           placeholder="Özellik Adı (örn: Malzeme)"
                                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                                </div>
                                <div class="flex-1">
                                    <input type="text" 
                                           x-model="attr.value"
                                           placeholder="Değer (örn: %100 Pamuk)"
                                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                                </div>
                                <button type="button" 
                                        @click="removeAttribute(index)"
                                        class="px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </template>

                        <div x-show="attributes.length === 0" class="text-center py-8 text-gray-500">
                            <i class="bi bi-tags text-4xl"></i>
                            <p class="mt-2">Henüz özellik eklenmedi</p>
                        </div>
                    </div>
                </div>

                <!-- Hazır Doldur Bölümü -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">
                        <i class="bi bi-lightning-charge"></i> Hazır Doldur
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Seçenekler</label>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="option in definedOptions" :key="option.id">
                                    <button type="button"
                                            @click="quickFillOption(option)"
                                            class="px-3 py-1 bg-blue-500 text-white text-sm rounded hover:bg-blue-600">
                                        <span x-text="option.name"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <div class="bg-blue-50 border-l-4 border-blue-500 p-3">
                            <p class="text-sm text-blue-700">
                                <i class="bi bi-info-circle"></i>
                                Yukarıdaki butonlara tıklayarak varyant ve özellik alanlarını otomatik doldurabilirsiniz.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- SEO Bilgileri -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">
                        <i class="bi bi-search"></i> SEO Bilgileri
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Meta Başlık
                                <span class="text-xs text-gray-500">(Maksimum 60 karakter)</span>
                            </label>
                            <input type="text" 
                                   name="meta_title" 
                                   x-model="seo.meta_title"
                                   maxlength="60"
                                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div class="text-xs text-gray-500 mt-1">
                                <span x-text="seo.meta_title.length"></span>/60 karakter
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Meta Anahtar Kelimeler
                                <span class="text-xs text-gray-500">(Enter veya virgül ile ayırın)</span>
                            </label>
                            <div class="border border-gray-300 rounded p-2 min-h-[60px] flex flex-wrap gap-2">
                                <template x-for="(keyword, index) in seo.keywords" :key="index">
                                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded">
                                        <span x-text="keyword"></span>
                                        <button type="button" 
                                                @click="removeKeyword(index)"
                                                class="text-blue-600 hover:text-blue-800">
                                            ×
                                        </button>
                                    </span>
                                </template>
                                <input type="text" 
                                       x-model="seo.currentKeyword"
                                       @keydown.enter.prevent="addKeyword"
                                       @keydown.comma.prevent="addKeyword"
                                       placeholder="Anahtar kelime ekle..."
                                       class="flex-1 min-w-[200px] border-none focus:outline-none">
                            </div>
                            <input type="hidden" name="meta_keywords" x-model="seo.keywords.join(', ')">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Meta Açıklama
                                <span class="text-xs text-gray-500">(Maksimum 160 karakter)</span>
                            </label>
                            <textarea name="meta_description" 
                                      x-model="seo.meta_description"
                                      maxlength="160"
                                      rows="3"
                                      class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                            <div class="text-xs text-gray-500 mt-1">
                                <span x-text="seo.meta_description.length"></span>/160 karakter
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Yan Bilgiler - 1/3 genişlik -->
            <div class="lg:col-span-1 space-y-6">
                
                <!-- Kategori ve Marka -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">
                        <i class="bi bi-folder"></i> Kategori ve Marka
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Kategori <span class="text-red-500">*</span>
                            </label>
                            <select name="category_id" 
                                    x-model="productData.category_id"
                                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                                <option value="">Seçiniz...</option>
                                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($category->id); ?>"><?php echo e($category->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Marka
                            </label>
                            <select name="brand_id" 
                                    x-model="productData.brand_id"
                                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Seçiniz...</option>
                                <?php $__currentLoopData = $brands; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $brand): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($brand->id); ?>"><?php echo e($brand->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Kargo & Vergi -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">
                        <i class="bi bi-truck"></i> Kargo & Vergi
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                KDV Oranı (%) <span class="text-red-500">*</span>
                            </label>
                            <select name="vat_rate" 
                                    x-model="productData.vat_rate"
                                    required
                                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="0">%0</option>
                                <option value="1">%1</option>
                                <option value="10">%10</option>
                                <option value="20">%20</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Desi <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   name="dimensional_weight" 
                                   x-model="productData.dimensional_weight"
                                   step="0.01"
                                   min="0.01"
                                   required
                                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Kargo Şirketi ID
                            </label>
                            <input type="number" 
                                   name="cargo_company_id" 
                                   x-model="productData.cargo_company_id"
                                   min="1"
                                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Durum -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-700 border-b pb-2">
                        <i class="bi bi-toggle-on"></i> Durum
                    </h3>
                    
                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="is_active" 
                                   value="1"
                                   x-model="productData.is_active"
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Aktif</span>
                        </label>
                    </div>
                </div>

                <!-- İşlemler -->
                <div class="bg-white rounded-lg shadow p-6">
                    <button type="submit" 
                            class="w-full bg-blue-500 text-white py-3 rounded font-semibold hover:bg-blue-600 transition">
                        <i class="bi bi-check-circle"></i> Değişiklikleri Kaydet
                    </button>
                </div>

            </div>
        </div>
    </form>

    <!-- Alpine.js Component Script -->
    <script>
        function productEditor() {
            return {
                productData: {
                    name: '',
                    model_code: '',
                    description: '',
                    category_id: '',
                    brand_id: '',
                    is_active: true
                },
                variants: [],
                attributes: [],
                seo: {
                    meta_title: '',
                    meta_description: '',
                    keywords: [],
                    currentKeyword: ''
                },
                definedOptions: <?php echo json_encode($definedOptions, 15, 512) ?>,

                initializeProduct() {
                    const product = <?php echo json_encode($product, 15, 512) ?>;
                    
                    // Temel bilgiler
                    this.productData = {
                        name: product.name || '',
                        model_code: product.model_code || '',
                        description: product.description || '',
                        category_id: product.category_id ? String(product.category_id) : '',
                        brand_id: product.brand_id ? String(product.brand_id) : '',
                        vat_rate: product.vat_rate ? String(product.vat_rate) : '20',
                        dimensional_weight: product.dimensional_weight || 1.0,
                        cargo_company_id: product.cargo_company_id || '',
                        is_active: product.is_active === 1 || product.is_active === true
                    };

                    // SEO bilgileri
                    this.seo = {
                        meta_title: product.meta_title || '',
                        meta_description: product.meta_description || '',
                        keywords: product.meta_keywords ? product.meta_keywords.split(',').map(k => k.trim()).filter(k => k) : [],
                        currentKeyword: ''
                    };

                    // Varyantlar
                    if (product.variants && product.variants.length > 0) {
                        this.variants = product.variants.map(v => ({
                            color: v.color || '',
                            size: v.size || '',
                            barcode: v.barcode || '',
                            sku: v.sku || '',
                            price: v.price || '',
                            stock: v.stock || 0
                        }));
                    }

                    // Özellikler
                    if (product.product_attributes && product.product_attributes.length > 0) {
                        this.attributes = product.product_attributes.map(a => ({
                            key: a.attribute_key || '',
                            value: a.attribute_value || ''
                        }));
                    }
                },

                addVariant() {
                    this.variants.push({
                        color: '',
                        size: '',
                        barcode: '',
                        sku: '',
                        price: '',
                        stock: 0
                    });
                },

                removeVariant(index) {
                    this.variants.splice(index, 1);
                },

                addAttribute() {
                    this.attributes.push({
                        key: '',
                        value: ''
                    });
                },

                removeAttribute(index) {
                    this.attributes.splice(index, 1);
                },

                quickFillOption(option) {
                    // Varyantları temizle ve yenilerini ekle
                    this.variants = [];
                    
                    option.values.forEach(value => {
                        this.variants.push({
                            color: value.color || '',
                            size: value.size || '',
                            barcode: '',
                            sku: '',
                            price: '',
                            stock: 0
                        });
                    });

                    // Özellikleri temizle ve yenilerini ekle
                    this.attributes = [];
                    
                    if (option.attributes && option.attributes.length > 0) {
                        option.attributes.forEach(attr => {
                            this.attributes.push({
                                key: attr.key || '',
                                value: attr.value || ''
                            });
                        });
                    }
                },

                addKeyword() {
                    const keyword = this.seo.currentKeyword.trim();
                    if (keyword && !this.seo.keywords.includes(keyword)) {
                        this.seo.keywords.push(keyword);
                    }
                    this.seo.currentKeyword = '';
                },

                removeKeyword(index) {
                    this.seo.keywords.splice(index, 1);
                },

                prepareFormData(event) {
                    // Form gönderilmeden önce JSON verilerini hazırla
                    const variantsInput = event.target.querySelector('input[name="variants_json"]');
                    const attributesInput = event.target.querySelector('input[name="attributes_json"]');
                    
                    if (variantsInput) {
                        variantsInput.value = JSON.stringify(this.variants);
                    }
                    
                    if (attributesInput) {
                        attributesInput.value = JSON.stringify(this.attributes);
                    }
                }
            };
        }
    </script>
</div>

                               placeholder="Örn: PRD-001"
                               required>
                        <small class="form-text text-muted">Benzersiz ürün kodu olmalıdır</small>
                        <?php $__errorArgs = ['sku'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Açıklama</label>
                        <textarea class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                  id="description" 
                                  name="description" 
                                  rows="5"><?php echo e(old('description', $product->description)); ?></textarea>
                        <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
            </div>

            <!-- Fiyat ve Stok -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-currency-dollar"></i> Fiyat ve Stok Bilgileri</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="price" class="form-label">Fiyat (₺) <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control <?php $__errorArgs = ['price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="price" 
                                   name="price" 
                                   value="<?php echo e(old('price', $product->price)); ?>" 
                                   step="0.01" 
                                   min="0"
                                   required>
                            <?php $__errorArgs = ['price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="discount_price" class="form-label">İndirimli Fiyat (₺)</label>
                            <input type="number" 
                                   class="form-control <?php $__errorArgs = ['discount_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="discount_price" 
                                   name="discount_price" 
                                   value="<?php echo e(old('discount_price', $product->discount_price)); ?>" 
                                   step="0.01" 
                                   min="0">
                            <?php $__errorArgs = ['discount_price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="stock_quantity" class="form-label">Stok Miktarı <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control <?php $__errorArgs = ['stock_quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="stock_quantity" 
                                   name="stock_quantity" 
                                   value="<?php echo e(old('stock_quantity', $product->stock_quantity)); ?>" 
                                   min="0"
                                   required>
                            <?php $__errorArgs = ['stock_quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Görseller -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-images"></i> Ürün Görselleri</h5>
                </div>
                <div class="card-body">
                    <div id="imageInputs">
                        <?php
                            $images = old('images', $product->images ?? []);
                            if (empty($images)) {
                                $images = [''];
                            }
                        ?>
                        
                        <?php $__currentLoopData = $images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="mb-3 image-input-group">
                            <label class="form-label">Görsel URL <?php echo e($index + 1); ?> <?php echo e($index === 0 ? '(Ana Görsel)' : ''); ?></label>
                            <div class="input-group">
                                <input type="url" 
                                       class="form-control" 
                                       name="images[]" 
                                       value="<?php echo e($image); ?>" 
                                       placeholder="https://example.com/image.jpg">
                                <button type="button" class="btn btn-outline-secondary" onclick="previewImage(this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <?php if($index > 0): ?>
                                <button type="button" class="btn btn-outline-danger" onclick="removeImageInput(this)">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                            <?php if($image): ?>
                            <div class="image-preview mt-2">
                                <img src="<?php echo e($image); ?>" alt="Preview" style="max-width: 200px; max-height: 200px;" class="img-thumbnail">
                            </div>
                            <?php else: ?>
                            <div class="image-preview mt-2" style="display: none;">
                                <img src="" alt="Preview" style="max-width: 200px; max-height: 200px;" class="img-thumbnail">
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addImageInput()">
                        <i class="bi bi-plus-circle"></i> Görsel Ekle
                    </button>
                </div>
            </div>
        </div>

        <!-- Sağ Kolon -->
        <div class="col-lg-4">
            <!-- Kategori ve Marka -->
            <div class="card mb-4">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="bi bi-tag"></i> Kategori ve Marka</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select class="form-select <?php $__errorArgs = ['category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                id="category_id" 
                                name="category_id" 
                                required>
                            <option value="">-- Kategori Seçin --</option>
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($category->id); ?>" 
                                    <?php echo e(old('category_id', $product->category_id) == $category->id ? 'selected' : ''); ?>>
                                    <?php echo e($category->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="mb-3">
                        <label for="brand_id" class="form-label">Marka <span class="text-danger">*</span></label>
                        <select class="form-select <?php $__errorArgs = ['brand_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                id="brand_id" 
                                name="brand_id" 
                                required>
                            <option value="">-- Marka Seçin --</option>
                            <?php $__currentLoopData = $brands; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $brand): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($brand->id); ?>" 
                                    <?php echo e(old('brand_id', $product->brand_id) == $brand->id ? 'selected' : ''); ?>>
                                    <?php echo e($brand->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['brand_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
            </div>

            <!-- Durum Ayarları -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-toggles"></i> Durum Ayarları</h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="is_active" 
                               name="is_active" 
                               value="1"
                               <?php echo e(old('is_active', $product->is_active) ? 'checked' : ''); ?>>
                        <label class="form-check-label" for="is_active">
                            <i class="bi bi-check-circle text-success"></i> Aktif
                        </label>
                        <small class="form-text text-muted d-block">
                            Aktif ürünler sitede görünür
                        </small>
                    </div>

                    <div class="form-check form-switch">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="is_featured" 
                               name="is_featured" 
                               value="1"
                               <?php echo e(old('is_featured', $product->is_featured) ? 'checked' : ''); ?>>
                        <label class="form-check-label" for="is_featured">
                            <i class="bi bi-star text-warning"></i> Öne Çıkan
                        </label>
                        <small class="form-text text-muted d-block">
                            Öne çıkan ürünler ana sayfada gösterilir
                        </small>
                    </div>
                </div>
            </div>

            <!-- İstatistikler -->
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> İstatistikler</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Oluşturulma Tarihi:</small>
                        <div><?php echo e($product->created_at->format('d.m.Y H:i')); ?></div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Son Güncelleme:</small>
                        <div><?php echo e($product->updated_at->format('d.m.Y H:i')); ?></div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Satıcı:</small>
                        <div><?php echo e($product->seller->name); ?></div>
                    </div>
                    <?php if($product->productAttributes()->count() > 0): ?>
                    <div class="mb-2">
                        <small class="text-muted">Özellik Sayısı:</small>
                        <div>
                            <span class="badge bg-info"><?php echo e($product->productAttributes()->count()); ?> özellik</span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Kaydet Butonu -->
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle"></i> Değişiklikleri Kaydet
                        </button>
                        <a href="<?php echo e(route('admin.products.show', $product)); ?>" class="btn btn-outline-info">
                            <i class="bi bi-eye"></i> Detayları Görüntüle
                        </a>
                        <a href="<?php echo e(route('admin.products.index')); ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> İptal
                        </a>
                        <hr>
                        <form action="<?php echo e(route('admin.products.destroy', $product)); ?>" method="POST" 
                              onsubmit="return confirm('Bu ürünü silmek istediğinizden emin misiniz?');">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="bi bi-trash"></i> Ürünü Sil
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<?php $__env->startPush('scripts'); ?>
<script>
let imageCount = <?php echo e(count($images)); ?>;

function addImageInput() {
    imageCount++;
    const html = `
        <div class="mb-3 image-input-group">
            <label class="form-label">Görsel URL ${imageCount}</label>
            <div class="input-group">
                <input type="url" 
                       class="form-control" 
                       name="images[]" 
                       placeholder="https://example.com/image.jpg">
                <button type="button" class="btn btn-outline-secondary" onclick="previewImage(this)">
                    <i class="bi bi-eye"></i>
                </button>
                <button type="button" class="btn btn-outline-danger" onclick="removeImageInput(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <div class="image-preview mt-2" style="display: none;">
                <img src="" alt="Preview" style="max-width: 200px; max-height: 200px;" class="img-thumbnail">
            </div>
        </div>
    `;
    document.getElementById('imageInputs').insertAdjacentHTML('beforeend', html);
}

function removeImageInput(btn) {
    btn.closest('.image-input-group').remove();
}

function previewImage(btn) {
    const input = btn.previousElementSibling;
    const previewDiv = btn.closest('.input-group').nextElementSibling;
    const img = previewDiv.querySelector('img');
    
    if (input.value) {
        img.src = input.value;
        previewDiv.style.display = 'block';
    } else {
        previewDiv.style.display = 'none';
    }
}

// Select2 initialization
$(document).ready(function() {
    $('#category_id').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Kategori Seçin --',
        allowClear: true,
        language: 'tr'
    });

    $('#brand_id').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Marka Seçin --',
        allowClear: true,
        language: 'tr'
    });
});

// Fiyat validasyonu
document.getElementById('discount_price').addEventListener('input', function() {
    const price = parseFloat(document.getElementById('price').value) || 0;
    const discountPrice = parseFloat(this.value) || 0;
    
    if (discountPrice >= price && price > 0) {
        this.setCustomValidity('İndirimli fiyat, normal fiyattan düşük olmalıdır!');
    } else {
        this.setCustomValidity('');
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\LENOVO\marketplace-entegrasyonu\resources\views/admin/products/edit.blade.php ENDPATH**/ ?>
@extends('layouts.admin')

@section('title', 'Yeni Ürün Ekle')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<style>
    [x-cloak] { display: none !important; }
    .tag-input {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        padding: 0.5rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        min-height: 42px;
    }
    .tag-item {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.5rem;
        background: #3b82f6;
        color: white;
        border-radius: 0.25rem;
        font-size: 0.875rem;
    }
    .tag-input input {
        border: none;
        outline: none;
        flex: 1;
        min-width: 120px;
    }
    .variant-table {
        border-collapse: collapse;
        width: 100%;
        font-size: 0.875rem;
    }
    .variant-table th,
    .variant-table td {
        border: 1px solid #e5e7eb;
        padding: 0.5rem;
    }
    .variant-table th {
        background: #f9fafb;
        font-weight: 600;
        text-align: left;
    }
    .variant-table input {
        width: 100%;
        padding: 0.375rem;
        border: 1px solid #d1d5db;
        border-radius: 0.25rem;
    }
    .variant-table input:focus {
        outline: none;
        border-color: #3b82f6;
        ring: 2px;
        ring-color: #3b82f6;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 py-6" x-data="productCreator()">
    
    <!-- Header -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Yeni Ürün Ekle</h1>
            <a href="{{ route('admin.products.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Geri Dön
            </a>
        </div>
    </div>

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" @@submit="prepareSubmit">
        @csrf
        
        <!-- Hidden Inputs -->
        <input type="hidden" name="variants_json" x-model="variantsJSON">
        <input type="hidden" name="options_json" x-model="optionsJSON">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- LEFT COLUMN (2/3 width) -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- BASIC INFO CARD -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Temel Bilgiler</h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <!-- Product Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Ürün Adı <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" x-model="product.name" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Örn: Premium Pamuklu T-Shirt">
                            </div>

                            <!-- Model Code -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Model Kodu <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="model_code" x-model="product.model_code" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Örn: TS-2024-001">
                            </div>

                            <!-- Description -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Açıklama
                                </label>
                                <textarea name="description" x-model="product.description" rows="6"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                          placeholder="Ürün açıklamasını buraya yazın..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- MEDIA CARD -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Görseller</h2>
                        </div>
                        <div class="p-6">
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-400 transition">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <div class="mt-4">
                                    <label for="images" class="cursor-pointer">
                                        <span class="text-blue-600 hover:text-blue-500 font-medium">Dosya yükle</span>
                                        <span class="text-gray-500"> veya sürükle-bırak</span>
                                    </label>
                                    <input id="images" name="images[]" type="file" multiple accept="image/*" class="hidden">
                                </div>
                                <p class="text-xs text-gray-500 mt-2">PNG, JPG, GIF (Max. 10MB)</p>
                            </div>
                        </div>
                    </div>

                    <!-- OPTIONS (Variant Configurator) -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                            <h2 class="text-lg font-semibold text-gray-900">Varyantlar</h2>
                            <button type="button" @@click="addOption" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                + Yeni Opsiyon Ekle
                            </button>
                        </div>
                        <div class="p-6 space-y-4">
                            
                            <!-- Option List -->
                            <template x-for="(option, optionIndex) in options" :key="optionIndex">
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-start gap-4">
                                        <!-- Option Name -->
                                        <div class="flex-1">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Opsiyon Adı
                                            </label>
                                            <input type="text" 
                                                   x-model="option.name" 
                                                   @@input="generateVariants"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                                   placeholder="Örn: Renk, Beden, Materyal">
                                        </div>

                                        <!-- Delete Button -->
                                        <button type="button" @@click="removeOption(optionIndex)" 
                                                class="mt-7 text-red-600 hover:text-red-700">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Tag Input for Values -->
                                    <div class="mt-3">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Değerler <span class="text-gray-500 text-xs">(Enter ile ekleyin)</span>
                                        </label>
                                        <div class="tag-input" @@click="$refs['optionInput' + optionIndex]?.focus()">
                                            <template x-for="(value, valueIndex) in option.values" :key="valueIndex">
                                                <span class="tag-item">
                                                    <span x-text="value"></span>
                                                    <button type="button" @@click.stop="removeValue(optionIndex, valueIndex)" class="hover:bg-blue-600">
                                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                        </svg>
                                                    </button>
                                                </span>
                                            </template>
                                            <input type="text" 
                                                   x-ref="'optionInput' + optionIndex"
                                                   @@keydown.enter.prevent="addValue(optionIndex, $event.target.value); $event.target.value = ''"
                                                   placeholder="Değer yazın ve Enter'a basın..."
                                                   class="flex-1 min-w-0">
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Empty State -->
                            <div x-show="options.length === 0" class="text-center py-8 text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                <p class="mt-2">Henüz opsiyon eklenmedi</p>
                                <p class="text-sm">Renk, beden gibi varyant seçenekleri ekleyin</p>
                            </div>
                        </div>
                    </div>

                    <!-- VARIANTS TABLE -->
                    <div class="bg-white shadow rounded-lg" x-show="variants.length > 0" x-cloak>
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">
                                Varyant Detayları 
                                <span class="text-sm font-normal text-gray-500">(<span x-text="variants.length"></span> adet)</span>
                            </h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="variant-table">
                                <thead>
                                    <tr>
                                        <th class="w-48">Varyant</th>
                                        <th class="w-32">Fiyat (₺)</th>
                                        <th class="w-32">İndirimli Fiyat</th>
                                        <th class="w-24">Stok</th>
                                        <th class="w-32">SKU</th>
                                        <th class="w-32">Barkod</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(variant, index) in variants" :key="index">
                                        <tr>
                                            <td>
                                                <span x-text="variant.name" class="font-medium text-gray-900"></span>
                                            </td>
                                            <td>
                                                <input type="number" 
                                                       x-model="variant.price" 
                                                       step="0.01" 
                                                       min="0"
                                                       placeholder="0.00">
                                            </td>
                                            <td>
                                                <input type="number" 
                                                       x-model="variant.discount_price" 
                                                       step="0.01" 
                                                       min="0"
                                                       placeholder="0.00">
                                            </td>
                                            <td>
                                                <input type="number" 
                                                       x-model="variant.stock" 
                                                       min="0"
                                                       placeholder="0">
                                            </td>
                                            <td>
                                                <input type="text" 
                                                       x-model="variant.sku" 
                                                       placeholder="SKU">
                                            </td>
                                            <td>
                                                <input type="text" 
                                                       x-model="variant.barcode" 
                                                       placeholder="Barkod">
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <!-- RIGHT COLUMN (1/3 width) -->
                <div class="space-y-6">
                    
                    <!-- STATUS CARD -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Durum</h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Aktif</span>
                                </label>
                            </div>
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="is_featured" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Öne Çıkan</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- CATEGORY & BRAND -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Organizasyon</h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <!-- Category -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Kategori <span class="text-red-500">*</span>
                                </label>
                                <select name="category_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">-- Seçin --</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Brand -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Marka
                                </label>
                                <select name="brand_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">-- Seçin --</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- BASE PRICE (Optional) -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Fiyatlandırma</h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Temel Fiyat (₺)
                                </label>
                                <input type="number" 
                                       x-model="product.base_price" 
                                       @@input="applyBasePriceToAll"
                                       step="0.01" 
                                       min="0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="0.00">
                                <p class="mt-1 text-xs text-gray-500">Tüm varyantlara uygulanır</p>
                            </div>
                        </div>
                    </div>

                    <!-- SUBMIT BUTTON -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="p-6">
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-md transition">
                                Ürünü Kaydet
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function productCreator() {
    return {
        product: {
            name: '',
            model_code: '',
            description: '',
            base_price: null
        },
        options: [],
        variants: [],

        // Add new option
        addOption() {
            this.options.push({
                name: '',
                values: []
            });
        },

        // Remove option
        removeOption(index) {
            this.options.splice(index, 1);
            this.generateVariants();
        },

        // Add value to option
        addValue(optionIndex, value) {
            if (!value || !value.trim()) return;
            
            const trimmedValue = value.trim();
            if (!this.options[optionIndex].values.includes(trimmedValue)) {
                this.options[optionIndex].values.push(trimmedValue);
                this.generateVariants();
            }
        },

        // Remove value from option
        removeValue(optionIndex, valueIndex) {
            this.options[optionIndex].values.splice(valueIndex, 1);
            this.generateVariants();
        },

        // THE MAGIC: Generate all variant combinations (Cartesian Product)
        generateVariants() {
            // Filter options that have both name and values
            const validOptions = this.options.filter(opt => 
                opt.name && opt.name.trim() && opt.values.length > 0
            );

            if (validOptions.length === 0) {
                this.variants = [];
                return;
            }

            // Cartesian product algorithm
            const combinations = this.cartesianProduct(
                validOptions.map(opt => opt.values)
            );

            // Map combinations to variant objects
            const newVariants = combinations.map(combo => {
                // Create variant name
                const name = Array.isArray(combo) ? combo.join(' / ') : combo;
                
                // Create attributes object
                const attributes = {};
                validOptions.forEach((opt, idx) => {
                    attributes[opt.name] = Array.isArray(combo) ? combo[idx] : combo;
                });

                // Try to find existing variant to preserve user input
                const existing = this.variants.find(v => v.name === name);
                
                return {
                    name: name,
                    attributes: attributes,
                    price: existing?.price || this.product.base_price || '',
                    discount_price: existing?.discount_price || '',
                    stock: existing?.stock || 0,
                    sku: existing?.sku || this.generateSKU(attributes),
                    barcode: existing?.barcode || ''
                };
            });

            this.variants = newVariants;
        },

        // Cartesian Product Helper
        cartesianProduct(arrays) {
            if (arrays.length === 0) return [];
            if (arrays.length === 1) return arrays[0].map(item => [item]);
            
            return arrays.reduce((acc, curr) => {
                return acc.flatMap(a => 
                    curr.map(b => [...(Array.isArray(a) ? a : [a]), b])
                );
            });
        },

        // Generate SKU from attributes
        generateSKU(attributes) {
            const parts = Object.values(attributes).map(val => 
                val.substring(0, 3).toUpperCase()
            );
            return this.product.model_code 
                ? `${this.product.model_code}-${parts.join('-')}`
                : parts.join('-');
        },

        // Apply base price to all variants
        applyBasePriceToAll() {
            if (this.product.base_price) {
                this.variants.forEach(v => {
                    if (!v.price) {
                        v.price = this.product.base_price;
                    }
                });
            }
        },

        // Computed JSON for form submission
        get variantsJSON() {
            return JSON.stringify(this.variants);
        },

        get optionsJSON() {
            return JSON.stringify(this.options);
        },

        // Form submit handler
        prepareSubmit(e) {
            if (this.variants.length === 0) {
                alert('En az 1 varyant oluşturmalısınız!');
                e.preventDefault();
                return false;
            }

            // Validate required fields
            const hasEmptyPrice = this.variants.some(v => !v.price || parseFloat(v.price) <= 0);
            if (hasEmptyPrice) {
                alert('Tüm varyantlar için fiyat girilmelidir!');
                e.preventDefault();
                return false;
            }

            return true;
        }
    }
}
</script>
@endpush

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
        position: relative;
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
    
    .bulk-edit-btn {
        position: absolute;
        top: 2px;
        right: 2px;
        font-size: 0.7rem;
        padding: 2px 6px;
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

                    <!-- PRODUCT SPECIFICATIONS CARD -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                            <h2 class="text-lg font-semibold text-gray-900">Ürün Özellikleri</h2>
                            <button type="button" @click="addAttribute" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                + Özellik Ekle
                            </button>
                        </div>
                        <div class="p-6">
                            <!-- Hidden Input for JSON -->
                            <input type="hidden" name="attributes_json" x-model="attributesJSON">

                            <!-- Attributes List -->
                            <div class="space-y-3">
                                <template x-for="(attr, index) in product_attributes" :key="index">
                                    <div class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                                        <div class="flex-1 grid grid-cols-2 gap-3">
                                            <input type="text" 
                                                   x-model="attr.name" 
                                                   placeholder="Özellik adı (örn: Materyal)"
                                                   class="px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm">
                                            <input type="text" 
                                                   x-model="attr.value" 
                                                   placeholder="Değer (örn: %100 Pamuk)"
                                                   class="px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm">
                                        </div>
                                        <button type="button" 
                                                @click="removeAttribute(index)"
                                                class="flex-shrink-0 text-red-600 hover:text-red-700 p-2 hover:bg-red-50 rounded transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <!-- Empty State -->
                            <div x-show="product_attributes.length === 0" class="text-center py-8 text-gray-500 border-2 border-dashed border-gray-300 rounded-lg">
                                <svg class="mx-auto h-10 w-10 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                <p class="text-sm">Henüz özellik eklenmedi</p>
                                <p class="text-xs text-gray-400 mt-1">Materyal, Desen, Yaka Tipi gibi statik özellikler ekleyin</p>
                            </div>

                            <!-- Info Box -->
                            <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div class="text-xs text-blue-800">
                                        <strong>Not:</strong> Bu bölüm varyant olmayan özellikleri için kullanılır. 
                                        Renk/Beden gibi varyant oluşturan seçenekler için "Varyant Seçenekleri" bölümünü kullanın.
                                    </div>
                                </div>
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

                    <!-- OPTIONS (HYBRID SELECTOR) -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                            <h2 class="text-lg font-semibold text-gray-900">Varyant Seçenekleri</h2>
                            <button type="button" @@click="addOption" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                + Yeni Seçenek Ekle
                            </button>
                        </div>
                        <div class="p-6 space-y-4">
                            
                            <!-- Option List -->
                            <template x-for="(option, optionIndex) in options" :key="optionIndex">
                                <div class="border border-gray-200 rounded-lg p-4">
                                    
                                    <!-- Option Type Selector (Database or Custom) -->
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Seçenek Tipi
                                        </label>
                                        <div class="flex gap-4">
                                            <label class="flex items-center">
                                                <input type="radio" 
                                                       :name="'option-type-' + optionIndex"
                                                       value="database"
                                                       x-model="option.type"
                                                       @@change="generateVariants"
                                                       class="mr-2">
                                                <span>Tanımlı Seçenekler</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="radio" 
                                                       :name="'option-type-' + optionIndex"
                                                       value="custom"
                                                       x-model="option.type"
                                                       @@change="generateVariants"
                                                       class="mr-2">
                                                <span>Özel Seçenek</span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- DATABASE OPTION -->
                                    <template x-if="option.type === 'database'">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Seçenek Seçin
                                            </label>
                                            <select x-model="option.db_option_id" 
                                                    @@change="loadOptionValues(optionIndex)"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                                <option value="">-- Seçiniz --</option>
                                                @foreach($definedOptions as $opt)
                                                    <option value="{{ $opt->id }}" 
                                                            data-name="{{ $opt->name }}"
                                                            data-values='@json($opt->values)'>
                                                        {{ $opt->name }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            <!-- Multi-Select Values -->
                                            <template x-if="option.available_values && option.available_values.length > 0">
                                                <div class="mt-3">
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                                        Değerler (Seçin)
                                                    </label>
                                                    <div class="max-h-48 overflow-y-auto border border-gray-300 rounded-md p-2">
                                                        <template x-for="(val, valIdx) in option.available_values" :key="val.id">
                                                            <label class="flex items-center py-1 px-2 hover:bg-gray-50 cursor-pointer">
                                                                <input type="checkbox" 
                                                                       :value="val.id"
                                                                       @@change="toggleDbValue(optionIndex, val)"
                                                                       :checked="option.selected_db_values.some(v => v.id === val.id)"
                                                                       class="mr-2">
                                                                <span x-text="val.value"></span>
                                                            </label>
                                                        </template>
                                                    </div>
                                                    <p class="text-xs text-gray-500 mt-1">
                                                        <span x-text="option.selected_db_values.length"></span> değer seçildi
                                                    </p>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    <!-- CUSTOM OPTION -->
                                    <template x-if="option.type === 'custom'">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Seçenek Adı
                                            </label>
                                            <input type="text" 
                                                   x-model="option.name" 
                                                   @@input="generateVariants"
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                                   placeholder="Örn: Kumaş, Desen">

                                            <!-- Tag Input for Values -->
                                            <div class="mt-3">
                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                    Değerler <span class="text-gray-500 text-xs">(Enter ile ekleyin)</span>
                                                </label>
                                                <div class="tag-input" @@click="$refs['customInput' + optionIndex]?.focus()">
                                                    <template x-for="(value, valueIndex) in option.custom_values" :key="valueIndex">
                                                        <span class="tag-item">
                                                            <span x-text="value"></span>
                                                            <button type="button" @@click.stop="removeCustomValue(optionIndex, valueIndex)" class="hover:bg-blue-600">
                                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                                </svg>
                                                            </button>
                                                        </span>
                                                    </template>
                                                    <input type="text" 
                                                           x-ref="'customInput' + optionIndex"
                                                           @@keydown.enter.prevent="addCustomValue(optionIndex, $event.target.value); $event.target.value = ''"
                                                           placeholder="Değer yazın ve Enter'a basın..."
                                                           class="flex-1 min-w-0">
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Delete Button -->
                                    <div class="mt-4 flex justify-end">
                                        <button type="button" @@click="removeOption(optionIndex)" 
                                                class="text-red-600 hover:text-red-700 text-sm">
                                            <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Sil
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <!-- Empty State -->
                            <div x-show="options.length === 0" class="text-center py-8 text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                <p class="mt-2">Henüz seçenek eklenmedi</p>
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
                                        <th class="w-32">
                                            Fiyat (₺)
                                            <button type="button" 
                                                    @@click="bulkEditPrice" 
                                                    class="bulk-edit-btn bg-blue-500 text-white rounded hover:bg-blue-600"
                                                    title="Toplu Fiyat Değiştir">
                                                Hepsine Uygula
                                            </button>
                                        </th>
                                        <th class="w-32">İndirimli Fiyat</th>
                                        <th class="w-24">
                                            Stok
                                            <button type="button" 
                                                    @@click="bulkEditStock" 
                                                    class="bulk-edit-btn bg-green-500 text-white rounded hover:bg-green-600"
                                                    title="Toplu Stok Değiştir">
                                                Hepsine Uygula
                                            </button>
                                        </th>
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

                    <!-- SHIPPING & TAX (Trendyol Required) -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">
                                <i class="bi bi-truck"></i> Kargo & Vergi
                            </h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <!-- VAT Rate -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    KDV Oranı (%) <span class="text-red-500">*</span>
                                </label>
                                <select name="vat_rate" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    <option value="0">%0</option>
                                    <option value="1">%1</option>
                                    <option value="10">%10</option>
                                    <option value="20" selected>%20</option>
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Trendyol için gerekli</p>
                            </div>

                            <!-- Dimensional Weight (Desi) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Desi (Hacimsel Ağırlık) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" 
                                       name="dimensional_weight" 
                                       step="0.01" 
                                       min="0.01" 
                                       value="1.00"
                                       required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                <p class="mt-1 text-xs text-gray-500">Minimum: 0.01, Varsayılan: 1.00</p>
                            </div>

                            <!-- Cargo Company ID -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Kargo Şirketi ID
                                </label>
                                <input type="number" 
                                       name="cargo_company_id" 
                                       min="1"
                                       placeholder="Örn: 10 (Trendyol için)"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                <p class="mt-1 text-xs text-gray-500">Opsiyonel - Trendyol kargo şirketi ID</p>
                            </div>
                        </div>
                    </div>

                    <!-- BASE PRICE (Quick Fill Helper) -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Hızlı Doldur</h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Temel Fiyat (₺)
                                </label>
                                <input type="number" 
                                       x-model="quickFill.price" 
                                       step="0.01" 
                                       min="0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="0.00">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Temel Stok
                                </label>
                                <input type="number" 
                                       x-model="quickFill.stock" 
                                       min="0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="0">
                            </div>
                            <button type="button" 
                                    @@click="applyQuickFill"
                                    class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md transition">
                                Boş Alanlara Uygula
                            </button>
                            <p class="text-xs text-gray-500">Sadece boş olan fiyat/stok alanları doldurulur</p>
                        </div>
                    </div>

                    <!-- SEO CARD -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                SEO Ayarları
                            </h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Meta Başlık
                                </label>
                                <input type="text" 
                                       name="meta_title" 
                                       x-model="seo.meta_title"
                                       maxlength="60"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm"
                                       placeholder="Ürün adı - Marka adı">
                                <p class="mt-1 text-xs text-gray-500">
                                    <span x-text="seo.meta_title?.length || 0"></span>/60 karakter
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Anahtar Kelimeler
                                </label>
                                <div class="tag-input" @@click="$refs.keywordInput?.focus()">
                                    <template x-for="(keyword, index) in seo.keywords" :key="index">
                                        <span class="tag-item">
                                            <span x-text="keyword"></span>
                                            <button type="button" @@click="removeKeyword(index)" class="ml-1 hover:text-red-200">×</button>
                                        </span>
                                    </template>
                                    <input type="text"
                                           x-ref="keywordInput"
                                           x-model="seo.currentKeyword"
                                           @@keydown.enter.prevent="addKeyword"
                                           @@keydown.comma.prevent="addKeyword"
                                           class="flex-1 min-w-[120px] border-0 outline-none text-sm"
                                           placeholder="Kelime yazıp Enter'a basın">
                                </div>
                                <input type="hidden" name="meta_keywords" :value="seo.keywords.join(', ')">
                                <p class="mt-1 text-xs text-gray-500">Enter veya virgül ile ekleyin</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Meta Açıklama
                                </label>
                                <textarea name="meta_description" 
                                          x-model="seo.meta_description"
                                          rows="3"
                                          maxlength="160"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm"
                                          placeholder="Ürün hakkında kısa açıklama..."></textarea>
                                <p class="mt-1 text-xs text-gray-500">
                                    <span x-text="seo.meta_description?.length || 0"></span>/160 karakter
                                </p>
                            </div>

                            <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <p class="text-xs text-blue-800">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    SEO alanları boş bırakılırsa otomatik doldurulur
                                </p>
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
            description: ''
        },
        
        options: [],
        variants: [],
        product_attributes: [],
        
        // SEO Fields
        seo: {
            meta_title: '',
            meta_description: '',
            keywords: [],
            currentKeyword: ''
        },
        
        quickFill: {
            price: null,
            stock: null
        },

        // Computed property for attributes JSON
        get attributesJSON() {
            return JSON.stringify(this.product_attributes.filter(attr => attr.name && attr.value));
        },

        // Add attribute
        addAttribute() {
            this.product_attributes.push({
                name: '',
                value: ''
            });
        },

        // Remove attribute
        removeAttribute(index) {
            this.product_attributes.splice(index, 1);
        },

        // Add new option
        addOption() {
            this.options.push({
                type: 'database', // 'database' or 'custom'
                db_option_id: '',
                name: '',
                available_values: [],
                selected_db_values: [], // {id, value}
                custom_values: []
            });
        },

        // Remove option
        removeOption(index) {
            this.options.splice(index, 1);
            this.generateVariants();
        },

        // Load values from database option
        loadOptionValues(optionIndex) {
            const option = this.options[optionIndex];
            const select = event.target;
            const selectedOpt = select.options[select.selectedIndex];
            
            if (selectedOpt && selectedOpt.value) {
                option.name = selectedOpt.dataset.name;
                option.available_values = JSON.parse(selectedOpt.dataset.values);
                option.selected_db_values = [];
            } else {
                option.name = '';
                option.available_values = [];
                option.selected_db_values = [];
            }
            
            this.generateVariants();
        },

        // Toggle database value selection
        toggleDbValue(optionIndex, value) {
            const option = this.options[optionIndex];
            const exists = option.selected_db_values.findIndex(v => v.id === value.id);
            
            if (exists >= 0) {
                option.selected_db_values.splice(exists, 1);
            } else {
                option.selected_db_values.push(value);
            }
            
            this.generateVariants();
        },

        // Add custom value
        addCustomValue(optionIndex, value) {
            if (!value || !value.trim()) return;
            
            const option = this.options[optionIndex];
            const trimmedValue = value.trim();
            
            if (!option.custom_values.includes(trimmedValue)) {
                option.custom_values.push(trimmedValue);
                this.generateVariants();
            }
        },

        // Remove custom value
        removeCustomValue(optionIndex, valueIndex) {
            this.options[optionIndex].custom_values.splice(valueIndex, 1);
            this.generateVariants();
        },

        // THE MAGIC: Generate all variant combinations
        generateVariants() {
            const validOptions = this.options.filter(opt => {
                if (!opt.name || !opt.name.trim()) return false;
                
                if (opt.type === 'database') {
                    return opt.selected_db_values.length > 0;
                } else {
                    return opt.custom_values.length > 0;
                }
            });

            if (validOptions.length === 0) {
                this.variants = [];
                return;
            }

            // Build arrays for cartesian product
            const valueArrays = validOptions.map(opt => {
                if (opt.type === 'database') {
                    return opt.selected_db_values.map(v => ({
                        option_id: opt.db_option_id,
                        option_name: opt.name,
                        value_id: v.id,
                        value: v.value
                    }));
                } else {
                    return opt.custom_values.map(v => ({
                        option_id: null,
                        option_name: opt.name,
                        value_id: null,
                        value: v
                    }));
                }
            });

            const combinations = this.cartesianProduct(valueArrays);

            const newVariants = combinations.map(combo => {
                const comboArray = Array.isArray(combo) ? combo : [combo];
                const name = comboArray.map(c => c.value).join(' / ');
                
                const existing = this.variants.find(v => v.name === name);
                
                return {
                    name: name,
                    option_mapping: comboArray,
                    attributes: comboArray.reduce((acc, c) => {
                        acc[c.option_name] = c.value;
                        return acc;
                    }, {}),
                    price: existing?.price || '',
                    discount_price: existing?.discount_price || '',
                    stock: existing?.stock || 0,
                    sku: existing?.sku || this.generateSKU(comboArray),
                    barcode: existing?.barcode || ''
                };
            });

            this.variants = newVariants;
        },

        // Cartesian Product Helper
        cartesianProduct(arrays) {
            if (arrays.length === 0) return [];
            if (arrays.length === 1) return arrays[0];
            
            return arrays.reduce((acc, curr) => {
                const result = [];
                acc.forEach(a => {
                    curr.forEach(b => {
                        result.push(Array.isArray(a) ? [...a, b] : [a, b]);
                    });
                });
                return result;
            });
        },

        // Generate SKU from attributes
        generateSKU(comboArray) {
            const parts = comboArray.map(c => 
                c.value.substring(0, 3).toUpperCase()
            );
            return this.product.model_code 
                ? `${this.product.model_code}-${parts.join('-')}`
                : parts.join('-');
        },

        // Bulk edit price
        bulkEditPrice() {
            const price = prompt('Tüm varyantlara uygulanacak fiyatı girin:');
            if (price && !isNaN(price)) {
                this.variants.forEach(v => {
                    v.price = parseFloat(price);
                });
            }
        },

        // Bulk edit stock
        bulkEditStock() {
            const stock = prompt('Tüm varyantlara uygulanacak stok miktarını girin:');
            if (stock && !isNaN(stock)) {
                this.variants.forEach(v => {
                    v.stock = parseInt(stock);
                });
            }
        },

        // Apply quick fill
        applyQuickFill() {
            this.variants.forEach(v => {
                if (this.quickFill.price && (!v.price || parseFloat(v.price) === 0)) {
                    v.price = this.quickFill.price;
                }
                if (this.quickFill.stock !== null && (!v.stock || parseInt(v.stock) === 0)) {
                    v.stock = this.quickFill.stock;
                }
            });
            
            alert('Boş alanlar dolduruldu!');
        },

        // SEO Methods
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

        // Computed JSON for form submission
        get variantsJSON() {
            return JSON.stringify(this.variants);
        },

        // Form submit handler
        prepareSubmit(e) {
            if (this.variants.length === 0) {
                alert('En az 1 varyant oluşturmalısınız!');
                e.preventDefault();
                return false;
            }

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

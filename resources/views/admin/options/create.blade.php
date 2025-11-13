@extends('layouts.admin')

@section('title', 'Yeni Opsiyon Ekle')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<style>
    [x-cloak] { display: none !important; }
    
    .tag-input-container {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        padding: 0.75rem;
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        min-height: 3rem;
        background: #ffffff;
        transition: all 0.2s;
    }
    
    .tag-input-container:focus-within {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .tag-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.375rem 0.75rem;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        font-weight: 500;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        animation: slideIn 0.2s ease-out;
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .tag-badge button {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 1rem;
        height: 1rem;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        transition: background 0.2s;
    }
    
    .tag-badge button:hover {
        background: rgba(255, 255, 255, 0.4);
    }
    
    .tag-input-field {
        flex: 1;
        min-width: 200px;
        border: none;
        outline: none;
        font-size: 0.875rem;
        padding: 0.25rem;
    }
    
    .color-preview {
        width: 2rem;
        height: 2rem;
        border-radius: 0.375rem;
        border: 2px solid #e5e7eb;
        cursor: pointer;
        transition: transform 0.2s;
    }
    
    .color-preview:hover {
        transform: scale(1.1);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 py-8" x-data="optionCreator()">
    
    <!-- Header -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Yeni Opsiyon Ekle</h1>
                <p class="mt-1 text-sm text-gray-600">Ürün varyantları için yeni seçenek tanımlayın</p>
            </div>
            <a href="{{ route('admin.options.index') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Geri Dön
            </a>
        </div>
    </div>

    <!-- Main Form -->
    <form action="{{ route('admin.options.store') }}" 
          method="POST" 
          @submit="prepareSubmit" 
          class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        @csrf
        
        <!-- Hidden input for values JSON -->
        <input type="hidden" name="values_json" x-model="valuesJSON">

        <div class="space-y-6">
            
            <!-- Basic Information Card -->
            <div class="bg-white shadow-lg rounded-xl overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-700 border-b border-blue-800">
                    <h2 class="text-xl font-semibold text-white flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Temel Bilgiler
                    </h2>
                </div>
                <div class="p-6 space-y-6">
                    
                    <!-- Option Name -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Opsiyon Adı <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               x-model="option.name"
                               required
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                               placeholder="Örn: Beden, Renk, Materyal, Desen">
                        <p class="mt-2 text-xs text-gray-500">Bu opsiyon tüm ürünlerde kullanılabilir</p>
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Option Type -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Opsiyon Tipi <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Select Type -->
                            <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer transition"
                                   :class="option.type === 'select' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                                <input type="radio" 
                                       name="type" 
                                       value="select" 
                                       x-model="option.type"
                                       class="sr-only">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-2">
                                        <svg class="w-6 h-6" :class="option.type === 'select' ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                        <svg x-show="option.type === 'select'" class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <p class="font-medium text-gray-900">Metin</p>
                                    <p class="text-xs text-gray-500 mt-1">S, M, L, XL gibi değerler</p>
                                </div>
                            </label>

                            <!-- Color Type -->
                            <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer transition"
                                   :class="option.type === 'color' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                                <input type="radio" 
                                       name="type" 
                                       value="color" 
                                       x-model="option.type"
                                       class="sr-only">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-2">
                                        <svg class="w-6 h-6" :class="option.type === 'color' ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                                        </svg>
                                        <svg x-show="option.type === 'color'" class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <p class="font-medium text-gray-900">Renk</p>
                                    <p class="text-xs text-gray-500 mt-1">Hex kodu ile renkler</p>
                                </div>
                            </label>

                            <!-- Image Type -->
                            <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer transition"
                                   :class="option.type === 'image' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                                <input type="radio" 
                                       name="type" 
                                       value="image" 
                                       x-model="option.type"
                                       class="sr-only">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-2">
                                        <svg class="w-6 h-6" :class="option.type === 'image' ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <svg x-show="option.type === 'image'" class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <p class="font-medium text-gray-900">Görsel</p>
                                    <p class="text-xs text-gray-500 mt-1">Resim ile seçim</p>
                                </div>
                            </label>
                        </div>
                        @error('type')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Values Card -->
            <div class="bg-white shadow-lg rounded-xl overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-green-600 to-green-700 border-b border-green-800">
                    <h2 class="text-xl font-semibold text-white flex items-center justify-between">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            Değerler
                        </span>
                        <span class="text-sm font-normal bg-white bg-opacity-20 px-3 py-1 rounded-full" x-text="values.length + ' değer'"></span>
                    </h2>
                </div>
                <div class="p-6">
                    
                    <!-- Tag Input Container -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Opsiyon Değerleri <span class="text-red-500">*</span>
                            <span class="text-xs font-normal text-gray-500 ml-2">(Enter ile ekleyin)</span>
                        </label>
                        
                        <div class="tag-input-container" @click="$refs.valueInput?.focus()">
                            <!-- Existing Tags -->
                            <template x-for="(value, index) in values" :key="index">
                                <div class="tag-badge">
                                    <!-- Color Preview (if color type) -->
                                    <template x-if="option.type === 'color' && value.color_code">
                                        <div class="color-preview" 
                                             :style="'background-color: ' + value.color_code"
                                             :title="value.color_code"></div>
                                    </template>
                                    
                                    <!-- Value Text -->
                                    <span x-text="value.value"></span>
                                    
                                    <!-- Remove Button -->
                                    <button type="button" 
                                            @click="removeValue(index)" 
                                            class="focus:outline-none">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                            
                            <!-- Input Field -->
                            <input type="text" 
                                   x-ref="valueInput"
                                   x-model="currentValue"
                                   @keydown.enter.prevent="addValue"
                                   class="tag-input-field"
                                   placeholder="Değer yazın ve Enter'a basın (Örn: XS, S, M, L)">
                        </div>
                        
                        <!-- Color Picker (Show only for color type) -->
                        <template x-if="option.type === 'color' && currentValue.trim()">
                            <div class="mt-3 p-4 bg-gray-50 border-2 border-gray-200 rounded-lg">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    "<span x-text="currentValue"></span>" için renk seçin:
                                </label>
                                <div class="flex items-center gap-3">
                                    <input type="color" 
                                           x-model="currentColorCode"
                                           class="h-12 w-24 border-2 border-gray-300 rounded-lg cursor-pointer">
                                    <div class="flex-1">
                                        <input type="text" 
                                               x-model="currentColorCode"
                                               class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg font-mono text-sm"
                                               placeholder="#000000">
                                    </div>
                                    <button type="button" 
                                            @click="addValue"
                                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                                        Ekle
                                    </button>
                                </div>
                            </div>
                        </template>
                        
                        <p class="mt-2 text-xs text-gray-500">
                            <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <template x-if="option.type === 'select'">
                                <span>Metin değerler için Enter tuşuna basın</span>
                            </template>
                            <template x-if="option.type === 'color'">
                                <span>Renk adını yazıp hex kodunu seçin, ardından "Ekle" butonuna tıklayın</span>
                            </template>
                            <template x-if="option.type === 'image'">
                                <span>Görsel değerler için değer adı girin (görseller sonra yüklenecek)</span>
                            </template>
                        </p>
                    </div>

                    <!-- Values List Preview -->
                    <template x-if="values.length > 0">
                        <div class="mt-6">
                            <h3 class="text-sm font-semibold text-gray-700 mb-3">
                                Eklenen Değerler (<span x-text="values.length"></span>)
                            </h3>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                <template x-for="(value, index) in values" :key="index">
                                    <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm font-medium text-gray-900" x-text="value.value"></span>
                                            <button type="button" 
                                                    @click="removeValue(index)"
                                                    class="text-red-500 hover:text-red-700">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                </svg>
                                            </button>
                                        </div>
                                        <template x-if="value.color_code">
                                            <div class="flex items-center gap-2">
                                                <div class="w-8 h-8 rounded border-2 border-gray-300" 
                                                     :style="'background-color: ' + value.color_code"></div>
                                                <span class="text-xs text-gray-600 font-mono" x-text="value.color_code"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Empty State -->
                    <template x-if="values.length === 0">
                        <div class="mt-6 text-center py-8 bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">Henüz değer eklenmedi</p>
                            <p class="text-xs text-gray-400">Yukarıdaki alana yazıp Enter'a basın</p>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="bg-white shadow-lg rounded-xl overflow-hidden">
                <div class="p-6">
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold py-4 px-6 rounded-lg shadow-md transition transform hover:scale-[1.02] flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Opsiyonu Kaydet
                    </button>
                    <p class="mt-3 text-center text-xs text-gray-500">
                        Bu opsiyon kaydedildikten sonra ürün oluştururken kullanılabilir
                    </p>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function optionCreator() {
    return {
        option: {
            name: '',
            type: 'select'
        },
        values: [],
        currentValue: '',
        currentColorCode: '#000000',

        // Add value to the list
        addValue() {
            const value = this.currentValue.trim();
            
            if (!value) {
                alert('Lütfen bir değer girin!');
                return;
            }

            // Check for duplicates
            if (this.values.some(v => v.value.toLowerCase() === value.toLowerCase())) {
                alert('Bu değer zaten eklenmiş!');
                return;
            }

            // For color type, require color code
            if (this.option.type === 'color') {
                this.values.push({
                    value: value,
                    color_code: this.currentColorCode
                });
                this.currentColorCode = '#000000'; // Reset to black
            } else {
                this.values.push({
                    value: value,
                    color_code: null
                });
            }

            // Clear input
            this.currentValue = '';
            this.$refs.valueInput.focus();
        },

        // Remove value from list
        removeValue(index) {
            this.values.splice(index, 1);
        },

        // Computed JSON for form submission
        get valuesJSON() {
            return JSON.stringify(this.values);
        },

        // Form validation before submit
        prepareSubmit(e) {
            if (!this.option.name.trim()) {
                alert('Opsiyon adı zorunludur!');
                e.preventDefault();
                return false;
            }

            if (this.values.length === 0) {
                alert('En az 1 değer eklemelisiniz!');
                e.preventDefault();
                return false;
            }

            return true;
        }
    }
}
</script>
@endpush

@extends('layouts.admin')

@section('title', 'Trendyol Özellik Eşleştirme')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    [x-cloak] { display: none !important; }
    
    .option-card {
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .option-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .option-card.active {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .mapping-row {
        transition: background 0.2s;
    }
    
    .mapping-row:hover {
        background: #f9fafb;
    }
    
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .status-mapped {
        background: #dcfce7;
        color: #16a34a;
    }
    
    .status-unmapped {
        background: #fee2e2;
        color: #dc2626;
    }
    
    .select2-container {
        width: 100% !important;
    }
    
    .color-preview {
        width: 1.5rem;
        height: 1.5rem;
        border-radius: 0.25rem;
        border: 2px solid #e5e7eb;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 py-6" x-data="mappingManager()">
    
    <!-- Header -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Trendyol Özellik Eşleştirme</h1>
                <p class="mt-1 text-sm text-gray-600">Yerel değerlerinizi Trendyol API değerleri ile eşleştirin</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Geri Dön
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Toplam Opsiyon</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_options'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-indigo-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Toplam Değer</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_values'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Eşleştirilmiş</p>
                        <p class="text-2xl font-bold text-green-600">{{ $stats['mapped_values'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Eşleşmemiş</p>
                        <p class="text-2xl font-bold text-red-600">{{ $stats['unmapped_values'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- LEFT COLUMN: Options List -->
            <div class="lg:col-span-4">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Yerel Opsiyonlar</h2>
                    </div>
                    <div class="p-4 space-y-3">
                        @foreach($options as $option)
                        <div class="option-card border-2 rounded-lg p-4"
                             :class="selectedOption === {{ $option->id }} ? 'active' : 'border-gray-200'"
                             @click="selectOption({{ $option->id }})">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0">
                                        @if($option->type === 'color')
                                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                                            </svg>
                                        @else
                                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">{{ $option->name }}</h3>
                                        <p class="text-xs text-gray-500">{{ $option->values->count() }} değer</p>
                                    </div>
                                </div>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Mapping Interface -->
            <div class="lg:col-span-8">
                
                <!-- Category Selector -->
                <div class="bg-white shadow rounded-lg mb-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Trendyol Kategorisi Seç</h2>
                    </div>
                    <div class="p-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Kategori <span class="text-xs text-gray-500">(Kategoriye özel eşleştirme için)</span>
                        </label>
                        <select x-model="selectedCategory" @change="loadTrendyolAttributes" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Global Eşleştirme (Tüm Kategoriler)</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->categoryMapping->trendyol_category_id ?? '' }}">
                                    {{ $category->name }} (Trendyol: {{ $category->categoryMapping->trendyol_category_name ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Mapping Table -->
                <div class="bg-white shadow rounded-lg" x-show="selectedOption" x-cloak>
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-900">
                            <span x-text="currentOptionName"></span> - Değer Eşleştirme
                        </h2>
                        <button @click="autoMatch" 
                                x-show="trendyolAttributes.length > 0"
                                class="px-3 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Otomatik Eşleştir
                        </button>
                    </div>

                    <!-- Loading State -->
                    <div x-show="loading" class="p-12 text-center">
                        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-blue-500 border-t-transparent"></div>
                        <p class="mt-4 text-gray-600">Yükleniyor...</p>
                    </div>

                    <!-- Mapping Table -->
                    <div x-show="!loading && localValues.length > 0" x-cloak class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Yerel Değer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">→</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trendyol Değeri</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durum</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">İşlem</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="(value, index) in localValues" :key="value.id">
                                    <tr class="mapping-row">
                                        <!-- Local Value -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <template x-if="value.color_code">
                                                    <div class="color-preview" :style="'background-color: ' + value.color_code"></div>
                                                </template>
                                                <span class="font-medium text-gray-900" x-text="value.value"></span>
                                            </div>
                                        </td>

                                        <!-- Arrow -->
                                        <td class="px-6 py-4">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                            </svg>
                                        </td>

                                        <!-- Trendyol Value Selector -->
                                        <td class="px-6 py-4">
                                            <select :id="'trendyol-select-' + value.id"
                                                    class="trendyol-select w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                                                    @change="saveMapping(value, $event.target.value)">
                                                <option value="">-- Trendyol değeri seçin --</option>
                                                <template x-for="attr in trendyolAttributes" :key="attr.id">
                                                    <optgroup :label="attr.name">
                                                        <template x-for="tv in attr.values" :key="tv.id">
                                                            <option :value="JSON.stringify({attr_id: attr.id, attr_name: attr.name, val_id: tv.id, val_name: tv.name})"
                                                                    :selected="value.trendyol_value_id == tv.id"
                                                                    x-text="tv.name"></option>
                                                        </template>
                                                    </optgroup>
                                                </template>
                                            </select>
                                        </td>

                                        <!-- Status -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="status-badge"
                                                  :class="value.is_mapped ? 'status-mapped' : 'status-unmapped'">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path x-show="value.is_mapped" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    <path x-show="!value.is_mapped" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                </svg>
                                                <span x-text="value.is_mapped ? 'Eşleşmiş' : 'Eşleşmemiş'"></span>
                                            </span>
                                        </td>

                                        <!-- Actions -->
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <button @click="deleteMapping(value)" 
                                                    x-show="value.is_mapped"
                                                    class="text-red-600 hover:text-red-700 text-sm font-medium">
                                                Sil
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div x-show="!loading && localValues.length === 0" x-cloak class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                        <p class="mt-4 text-gray-600">Sol taraftan bir opsiyon seçin</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function mappingManager() {
    return {
        selectedOption: null,
        selectedCategory: '',
        currentOptionName: '',
        localValues: [],
        trendyolAttributes: [],
        loading: false,

        // Select an option
        selectOption(optionId) {
            this.selectedOption = optionId;
            this.loadOptionMappings();
        },

        // Load option mappings
        async loadOptionMappings() {
            this.loading = true;
            
            try {
                const response = await fetch(`/admin/trendyol/mapping/option-mappings?option_id=${this.selectedOption}&category_id=${this.selectedCategory}`);
                const data = await response.json();
                
                if (data.success) {
                    this.currentOptionName = data.option.name;
                    this.localValues = data.values;
                    
                    // Load Trendyol attributes if category selected
                    if (this.selectedCategory) {
                        await this.loadTrendyolAttributes();
                    }
                }
            } catch (error) {
                console.error('Error loading mappings:', error);
                alert('Yükleme hatası!');
            } finally {
                this.loading = false;
            }
        },

        // Load Trendyol attributes for selected category
        async loadTrendyolAttributes() {
            if (!this.selectedCategory) {
                this.trendyolAttributes = [];
                return;
            }

            try {
                const response = await fetch('/admin/trendyol/mapping/attributes', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ category_id: this.selectedCategory })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.trendyolAttributes = data.attributes;
                }
            } catch (error) {
                console.error('Error loading Trendyol attributes:', error);
            }
        },

        // Save mapping
        async saveMapping(localValue, trendyolData) {
            if (!trendyolData) return;

            const parsed = JSON.parse(trendyolData);
            
            try {
                const response = await fetch('/admin/trendyol/mapping/save', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        option_id: this.selectedOption,
                        option_value_id: localValue.id,
                        trendyol_attribute_id: parsed.attr_id,
                        trendyol_attribute_name: parsed.attr_name,
                        trendyol_value_id: parsed.val_id,
                        trendyol_value_name: parsed.val_name,
                        trendyol_category_id: this.selectedCategory || 'global'
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    // Update local value status
                    const index = this.localValues.findIndex(v => v.id === localValue.id);
                    this.localValues[index].is_mapped = true;
                    this.localValues[index].trendyol_value_id = parsed.val_id;
                    this.localValues[index].trendyol_value_name = parsed.val_name;
                    
                    this.showToast('Eşleştirme kaydedildi!', 'success');
                } else {
                    this.showToast('Hata: ' + data.message, 'error');
                }
            } catch (error) {
                console.error('Save error:', error);
                this.showToast('Kayıt başarısız!', 'error');
            }
        },

        // Delete mapping
        async deleteMapping(localValue) {
            if (!confirm('Eşleştirmeyi silmek istediğinizden emin misiniz?')) return;

            try {
                const response = await fetch('/admin/trendyol/mapping/delete', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        option_value_id: localValue.id,
                        trendyol_category_id: this.selectedCategory || 'global'
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    const index = this.localValues.findIndex(v => v.id === localValue.id);
                    this.localValues[index].is_mapped = false;
                    this.localValues[index].trendyol_value_id = null;
                    
                    // Reset select
                    document.getElementById('trendyol-select-' + localValue.id).value = '';
                    
                    this.showToast('Eşleştirme silindi!', 'success');
                }
            } catch (error) {
                console.error('Delete error:', error);
                this.showToast('Silme başarısız!', 'error');
            }
        },

        // Auto-match
        async autoMatch() {
            if (this.trendyolAttributes.length === 0) {
                alert('Önce bir kategori seçin ve Trendyol özelliklerini yükleyin!');
                return;
            }

            // Find best matching Trendyol attribute (usually first one matches the option type)
            const matchingAttr = this.trendyolAttributes[0];
            
            if (!confirm(`"${matchingAttr.name}" özelliği ile otomatik eşleştirme yapılsın mı?`)) return;

            try {
                const response = await fetch('/admin/trendyol/mapping/auto-match', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        option_id: this.selectedOption,
                        trendyol_category_id: this.selectedCategory,
                        trendyol_attribute_id: matchingAttr.id,
                        trendyol_attribute_name: matchingAttr.name,
                        trendyol_values: matchingAttr.values
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    this.showToast(data.message, 'success');
                    this.loadOptionMappings(); // Reload
                }
            } catch (error) {
                console.error('Auto-match error:', error);
                this.showToast('Otomatik eşleştirme başarısız!', 'error');
            }
        },

        // Toast notification
        showToast(message, type) {
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white z-50 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    }
}
</script>
@endpush

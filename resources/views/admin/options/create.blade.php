@extends('layouts.admin')

@section('title', 'Yeni Opsiyon Ekle')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-plus-circle"></i> Yeni Opsiyon Ekle</h2>
    <a href="{{ route('admin.options.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Geri Dön
    </a>
</div>

<form action="{{ route('admin.options.store') }}" method="POST" id="optionForm">
    @csrf

    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Opsiyon Adı <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="Örn: Beden, Renk, Materyal" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Opsiyon Tipi <span class="text-danger">*</span></label>
                        <select class="form-select" name="type" id="optionType" required>
                            <option value="select">Metin (Dropdown)</option>
                            <option value="color">Renk (Renk Paleti)</option>
                            <option value="image">Görsel (Resim)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">Değerler</h5>
        </div>
        <div class="card-body">
            <div id="valuesContainer">
                <!-- Değerler buraya eklenecek -->
            </div>
            <button type="button" class="btn btn-outline-primary" id="addValueBtn">
                <i class="bi bi-plus"></i> Değer Ekle
            </button>
        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-success btn-lg">
            <i class="bi bi-check-circle"></i> Opsiyonu Kaydet
        </button>
    </div>
</form>

@push('scripts')
<script>
$(document).ready(function() {
    let valueIndex = 0;

    function addValueRow() {
        const type = $('#optionType').val();
        const showColorPicker = type === 'color';
        
        const row = `
            <div class="row mb-2 value-row" data-index="${valueIndex}">
                <div class="col-${showColorPicker ? '5' : '10'}">
                    <input type="text" class="form-control" name="values[]" placeholder="Değer (Örn: XS, S, M)" required>
                </div>
                ${showColorPicker ? `
                <div class="col-5">
                    <input type="color" class="form-control form-control-color" name="color_codes[]" value="#000000">
                </div>
                ` : '<input type="hidden" name="color_codes[]" value="">'}
                <div class="col-2">
                    <button type="button" class="btn btn-danger remove-value-btn w-100">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
        
        $('#valuesContainer').append(row);
        valueIndex++;
    }

    $('#addValueBtn').on('click', function() {
        addValueRow();
    });

    $(document).on('click', '.remove-value-btn', function() {
        $(this).closest('.value-row').remove();
    });

    $('#optionType').on('change', function() {
        $('#valuesContainer').html('');
        valueIndex = 0;
    });

    // İlk değer satırını ekle
    addValueRow();
});
</script>
@endpush

@endsection

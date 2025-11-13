@extends('layouts.admin')

@section('title', 'Opsiyon Düzenle')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-pencil"></i> {{ $option->name }} - Düzenle</h2>
    <a href="{{ route('admin.options.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Geri Dön
    </a>
</div>

<form action="{{ route('admin.options.update', $option) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="card mb-3">
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Opsiyon Adı <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" value="{{ old('name', $option->name) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Tip</label>
                <input type="text" class="form-control" value="{{ $option->type }}" disabled>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">Değerler</h5>
        </div>
        <div class="card-body">
            <div id="valuesContainer">
                @foreach($option->values as $index => $value)
                <div class="row mb-2 value-row" data-index="{{ $index }}">
                    <input type="hidden" name="values[{{ $index }}][id]" value="{{ $value->id }}">
                    <div class="col-{{ $option->type === 'color' ? '5' : '10' }}">
                        <input type="text" class="form-control" name="values[{{ $index }}][value]" value="{{ $value->value }}" required>
                    </div>
                    @if($option->type === 'color')
                    <div class="col-5">
                        <input type="color" class="form-control form-control-color" name="values[{{ $index }}][color_code]" value="{{ $value->color_code }}">
                    </div>
                    @endif
                    <div class="col-2">
                        <button type="button" class="btn btn-danger remove-value-btn w-100">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-outline-primary" id="addValueBtn">
                <i class="bi bi-plus"></i> Değer Ekle
            </button>
        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-success btn-lg">
            <i class="bi bi-check-circle"></i> Güncelle
        </button>
        <form action="{{ route('admin.options.destroy', $option) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu opsiyonu silmek istediğinizden emin misiniz?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-lg">
                <i class="bi bi-trash"></i> Sil
            </button>
        </form>
    </div>
</form>

@push('scripts')
<script>
$(document).ready(function() {
    let valueIndex = {{ $option->values->count() }};
    const optionType = '{{ $option->type }}';

    $('#addValueBtn').on('click', function() {
        const showColorPicker = optionType === 'color';
        
        const row = `
            <div class="row mb-2 value-row" data-index="${valueIndex}">
                <div class="col-${showColorPicker ? '5' : '10'}">
                    <input type="text" class="form-control" name="values[${valueIndex}][value]" placeholder="Değer" required>
                </div>
                ${showColorPicker ? `
                <div class="col-5">
                    <input type="color" class="form-control form-control-color" name="values[${valueIndex}][color_code]" value="#000000">
                </div>
                ` : ''}
                <div class="col-2">
                    <button type="button" class="btn btn-danger remove-value-btn w-100">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
        
        $('#valuesContainer').append(row);
        valueIndex++;
    });

    $(document).on('click', '.remove-value-btn', function() {
        $(this).closest('.value-row').remove();
    });
});
</script>
@endpush

@endsection

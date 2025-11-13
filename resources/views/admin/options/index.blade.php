@extends('layouts.admin')

@section('title', 'Opsiyon Düzenle')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-gear"></i> Opsiyon Düzenle</h2>
    <a href="{{ route('admin.options.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Yeni Opsiyon
    </a>
</div>

<div class="row">
    @foreach($options as $option)
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-tag"></i> {{ $option->name }}
                    <span class="badge bg-{{ $option->is_active ? 'success' : 'secondary' }} ms-2">
                        {{ $option->is_active ? 'Aktif' : 'Pasif' }}
                    </span>
                </h5>
                <div>
                    <a href="{{ route('admin.options.edit', $option) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil"></i> Düzenle
                    </a>
                    <form action="{{ route('admin.options.toggle-active', $option) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-{{ $option->is_active ? 'warning' : 'success' }}">
                            <i class="bi bi-{{ $option->is_active ? 'eye-slash' : 'eye' }}"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    @foreach($option->values as $value)
                        @if($option->type === 'color')
                            <div class="position-relative" title="{{ $value->value }}">
                                <div style="width: 40px; height: 40px; background-color: {{ $value->color_code }}; border: 2px solid #dee2e6; border-radius: 4px; {{ $value->is_active ? '' : 'opacity: 0.3;' }}"></div>
                                <small class="d-block text-center" style="font-size: 10px;">{{ $value->value }}</small>
                            </div>
                        @else
                            <span class="badge bg-{{ $value->is_active ? 'primary' : 'secondary' }} fs-6 py-2 px-3">
                                {{ $value->value }}
                            </span>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

@if($options->isEmpty())
<div class="alert alert-info">
    <i class="bi bi-info-circle"></i> Henüz opsiyon eklenmemiş. "Yeni Opsiyon" butonuna tıklayarak başlayın.
</div>
@endif

@endsection

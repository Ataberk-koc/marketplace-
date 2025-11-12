@extends('layouts.admin')

@section('title', 'Satıcı Raporları')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="fas fa-store me-2"></i>Satıcı Performans Raporları</h1>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Geri
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Satıcı</th>
                            <th class="text-center">Ürün Sayısı</th>
                            <th class="text-center">Toplam Satış</th>
                            <th class="text-end">Toplam Gelir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sellers as $seller)
                        <tr>
                            <td>
                                <strong>{{ $seller->name }}</strong><br>
                                <small class="text-muted">{{ $seller->email }}</small>
                            </td>
                            <td class="text-center">{{ $seller->products_count }}</td>
                            <td class="text-center"><strong>{{ $seller->total_sold }}</strong></td>
                            <td class="text-end"><strong>{{ number_format($seller->total_revenue, 2) }} ₺</strong></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Satıcı bulunamadı</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

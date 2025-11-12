@extends('layouts.admin')

@section('title', 'Satış Raporları')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="fas fa-chart-bar me-2"></i>Satış Raporları</h1>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Geri
        </a>
    </div>

    <!-- Filtre Kartı -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.sales') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Başlangıç Tarihi</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Bitiş Tarihi</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Satıcı</label>
                        <select name="seller_id" class="form-select">
                            <option value="">Tüm Satıcılar</option>
                            @foreach($sellers as $seller)
                            <option value="{{ $seller->id }}" {{ request('seller_id') == $seller->id ? 'selected' : '' }}>
                                {{ $seller->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i>Filtrele
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Özet Kartları -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="text-white-50">Toplam Satış</h6>
                    <h2 class="mb-0">{{ number_format($totalSales, 2) }} ₺</h2>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="text-white-50">Toplam Sipariş</h6>
                    <h2 class="mb-0">{{ number_format($totalOrders) }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Satış Tablosu -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Satış Detayları</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Sipariş No</th>
                            <th>Tarih</th>
                            <th>Müşteri</th>
                            <th>Ürün Sayısı</th>
                            <th class="text-end">Tutar</th>
                            <th>Durum</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td><strong>#{{ $order->id }}</strong></td>
                            <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
                            <td>{{ $order->user->name }}</td>
                            <td>{{ $order->items->sum('quantity') }} adet</td>
                            <td class="text-end"><strong>{{ number_format($order->total_amount, 2) }} ₺</strong></td>
                            <td>
                                <span class="badge bg-success">Tamamlandı</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                Satış kaydı bulunamadı
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection

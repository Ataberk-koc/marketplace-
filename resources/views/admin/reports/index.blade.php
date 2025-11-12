@extends('layouts.admin')

@section('title', 'Raporlar')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="fas fa-chart-line me-2"></i>Genel Raporlar</h1>
    </div>

    <!-- İstatistik Kartları -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Toplam Satış</h6>
                            <h3 class="mb-0">{{ number_format($stats['total_sales'], 2) }} ₺</h3>
                        </div>
                        <i class="fas fa-money-bill-wave fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Toplam Sipariş</h6>
                            <h3 class="mb-0">{{ number_format($stats['total_orders']) }}</h3>
                        </div>
                        <i class="fas fa-shopping-cart fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Toplam Ürün</h6>
                            <h3 class="mb-0">{{ number_format($stats['total_products']) }}</h3>
                        </div>
                        <i class="fas fa-box fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Bekleyen Sipariş</h6>
                            <h3 class="mb-0">{{ number_format($stats['pending_orders']) }}</h3>
                        </div>
                        <i class="fas fa-clock fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Satış Grafiği -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Son 30 Gün Satış Grafiği</h5>
                </div>
                <div class="card-body">
                    <canvas id="salesChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- En Çok Satan Ürünler ve Kategori İstatistikleri -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">En Çok Satan Ürünler</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Ürün</th>
                                    <th class="text-end">Satılan</th>
                                    <th class="text-end">Fiyat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topProducts as $product)
                                <tr>
                                    <td>{{ $product->name }}</td>
                                    <td class="text-end">{{ $product->total_sold }}</td>
                                    <td class="text-end">{{ number_format($product->price, 2) }} ₺</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Henüz satış yok</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Kategori Bazlı Satışlar</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Kategori</th>
                                    <th class="text-end">Satılan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categoryStats as $category)
                                <tr>
                                    <td>{{ $category->name }}</td>
                                    <td class="text-end">{{ $category->total_sold ?? 0 }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">Veri yok</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hızlı Linkler -->
    <div class="row mt-4">
        <div class="col-md-4">
            <a href="{{ route('admin.reports.sales') }}" class="text-decoration-none">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <i class="fas fa-file-invoice fa-3x text-primary mb-3"></i>
                        <h5>Satış Raporları</h5>
                        <p class="text-muted">Detaylı satış analizi</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('admin.reports.products') }}" class="text-decoration-none">
                <div class="card border-info">
                    <div class="card-body text-center">
                        <i class="fas fa-boxes fa-3x text-info mb-3"></i>
                        <h5>Ürün Raporları</h5>
                        <p class="text-muted">Ürün stok ve satış durumu</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('admin.reports.sellers') }}" class="text-decoration-none">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <i class="fas fa-store fa-3x text-success mb-3"></i>
                        <h5>Satıcı Raporları</h5>
                        <p class="text-muted">Satıcı performans analizi</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const salesData = @json($salesChart);
const ctx = document.getElementById('salesChart').getContext('2d');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: salesData.map(d => d.date),
        datasets: [{
            label: 'Satış (₺)',
            data: salesData.map(d => d.total),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }, {
            label: 'Sipariş Sayısı',
            data: salesData.map(d => d.count),
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                grid: {
                    drawOnChartArea: false,
                }
            }
        }
    }
});
</script>
@endpush
@endsection

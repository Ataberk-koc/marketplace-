@extends('layouts.admin')

@section('title', 'Satıcı Ödemeleri')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="fas fa-store me-2"></i>Satıcı Ödemeleri</h1>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Ödemeler
        </a>
    </div>

    <!-- Filtre -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Başlangıç Tarihi</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Bitiş Tarihi</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i>Filtrele
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Satıcı Ödemeleri -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Satıcı</th>
                            <th class="text-center">Sipariş Sayısı</th>
                            <th class="text-end">Brüt Satış</th>
                            <th class="text-end">Komisyon (%15)</th>
                            <th class="text-end">Net Ödeme</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sellers as $seller)
                        <tr>
                            <td>
                                <strong>{{ $seller->name }}</strong><br>
                                <small class="text-muted">{{ $seller->email }}</small>
                            </td>
                            <td class="text-center">{{ $seller->total_orders }}</td>
                            <td class="text-end">{{ number_format($seller->total_sales, 2) }} ₺</td>
                            <td class="text-end text-danger">-{{ number_format($seller->commission, 2) }} ₺</td>
                            <td class="text-end">
                                <strong class="text-success">{{ number_format($seller->net_payment, 2) }} ₺</strong>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Ödeme kaydı bulunamadı</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($sellers->count() > 0)
                    <tfoot>
                        <tr class="table-light">
                            <th>TOPLAM</th>
                            <th class="text-center">{{ $sellers->sum('total_orders') }}</th>
                            <th class="text-end">{{ number_format($sellers->sum('total_sales'), 2) }} ₺</th>
                            <th class="text-end text-danger">-{{ number_format($sellers->sum('commission'), 2) }} ₺</th>
                            <th class="text-end">
                                <strong class="text-success">{{ number_format($sellers->sum('net_payment'), 2) }} ₺</strong>
                            </th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

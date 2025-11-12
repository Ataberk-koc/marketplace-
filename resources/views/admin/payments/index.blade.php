@extends('layouts.admin')

@section('title', 'Ödemeler')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="fas fa-credit-card me-2"></i>Ödeme Yönetimi</h1>
        <a href="{{ route('admin.payments.sellers') }}" class="btn btn-info">
            <i class="fas fa-store me-1"></i>Satıcı Ödemeleri
        </a>
    </div>

    <!-- İstatistikler -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="text-white-50">Bekleyen Ödemeler</h6>
                    <h3>{{ number_format($stats['total_pending'], 2) }} ₺</h3>
                    <small>{{ $stats['pending_count'] }} sipariş</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="text-white-50">Ödenen</h6>
                    <h3>{{ number_format($stats['total_paid'], 2) }} ₺</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6 class="text-white-50">İade Edilen</h6>
                    <h3>{{ number_format($stats['total_refunded'], 2) }} ₺</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="text-white-50">Toplam</h6>
                    <h3>{{ number_format($stats['total_pending'] + $stats['total_paid'], 2) }} ₺</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtre -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Ödeme Durumu</label>
                        <select name="payment_status" class="form-select">
                            <option value="">Tümü</option>
                            <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Bekliyor</option>
                            <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Ödendi</option>
                            <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Başarısız</option>
                            <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>İade Edildi</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Başlangıç</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Bitiş</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i>Filtrele
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Ödeme Tablosu -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Sipariş</th>
                            <th>Müşteri</th>
                            <th>Tarih</th>
                            <th class="text-end">Tutar</th>
                            <th>Ödeme Durumu</th>
                            <th>Sipariş Durumu</th>
                            <th class="text-end">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td><strong>#{{ $order->id }}</strong></td>
                            <td>{{ $order->user->name }}</td>
                            <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
                            <td class="text-end"><strong>{{ number_format($order->total, 2) }} ₺</strong></td>
                            <td>
                                @if($order->payment_status == 'pending')
                                <span class="badge bg-warning">Bekliyor</span>
                                @elseif($order->payment_status == 'paid')
                                <span class="badge bg-success">Ödendi</span>
                                @elseif($order->payment_status == 'failed')
                                <span class="badge bg-danger">Başarısız</span>
                                @elseif($order->payment_status == 'refunded')
                                <span class="badge bg-secondary">İade</span>
                                @endif
                            </td>
                            <td>
                                @if($order->status == 'completed')
                                <span class="badge bg-success">Tamamlandı</span>
                                @else
                                <span class="badge bg-info">{{ ucfirst($order->status) }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.payments.show', $order) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> Detay
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Ödeme kaydı bulunamadı</td>
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

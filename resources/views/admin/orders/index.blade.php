@extends('layouts.admin')

@section('title', 'Siparişler')

@section('content')
<div class="mb-4">
    <h1><i class="bi bi-cart-check"></i> Siparişler</h1>
</div>

<!-- Filtreler -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Durum</label>
                <select name="status" class="form-select">
                    <option value="">Tümü</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Beklemede</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>İşleniyor</option>
                    <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Kargoya Verildi</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Tamamlandı</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>İptal Edildi</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Ödeme Durumu</label>
                <select name="payment_status" class="form-select">
                    <option value="">Tümü</option>
                    <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Beklemede</option>
                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Ödendi</option>
                    <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Başarısız</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Başlangıç</label>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Bitiş</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary d-block w-100">
                    <i class="bi bi-search"></i> Filtrele
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="ordersTable" class="table table-hover">
                <thead>
                    <tr>
                        <th>Sipariş No</th>
                        <th>Tarih</th>
                        <th>Müşteri</th>
                        <th>Ürün Sayısı</th>
                        <th>Toplam</th>
                        <th>Durum</th>
                        <th>Ödeme</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td><strong>#{{ $order->order_number }}</strong></td>
                        <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
                        <td>
                            <strong>{{ $order->user->name }}</strong><br>
                            <small class="text-muted">{{ $order->user->email }}</small>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $order->items_count }} ürün</span>
                        </td>
                        <td><strong>{{ number_format($order->total_amount, 2) }} ₺</strong></td>
                        <td>
                            @if($order->status == 'pending')
                                <span class="badge bg-warning text-dark">Beklemede</span>
                            @elseif($order->status == 'processing')
                                <span class="badge bg-info">İşleniyor</span>
                            @elseif($order->status == 'shipped')
                                <span class="badge bg-primary">Kargoya Verildi</span>
                            @elseif($order->status == 'completed')
                                <span class="badge bg-success">Tamamlandı</span>
                            @elseif($order->status == 'cancelled')
                                <span class="badge bg-danger">İptal Edildi</span>
                            @endif
                        </td>
                        <td>
                            @if($order->payment_status == 'paid')
                                <span class="badge bg-success">Ödendi</span>
                            @elseif($order->payment_status == 'pending')
                                <span class="badge bg-warning text-dark">Beklemede</span>
                            @else
                                <span class="badge bg-danger">Başarısız</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Detay
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <p class="text-muted mt-2">Henüz sipariş bulunmuyor</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-center mt-4">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection

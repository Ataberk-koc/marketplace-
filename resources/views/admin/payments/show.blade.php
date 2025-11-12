@extends('layouts.admin')

@section('title', 'Ödeme Detayı')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="fas fa-file-invoice me-2"></i>Ödeme Detayı #{{ $order->id }}</h1>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Geri
        </a>
    </div>

    <div class="row">
        <!-- Sipariş Bilgileri -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Sipariş Bilgileri</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Müşteri:</strong> {{ $order->user->name }}<br>
                            <strong>Email:</strong> {{ $order->user->email }}<br>
                            <strong>Telefon:</strong> {{ $order->user->phone ?? '-' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Sipariş Tarihi:</strong> {{ $order->created_at->format('d.m.Y H:i') }}<br>
                            <strong>Sipariş Durumu:</strong> 
                            <span class="badge bg-success">{{ ucfirst($order->status) }}</span><br>
                            <strong>Ödeme Durumu:</strong>
                            @if($order->payment_status == 'pending')
                            <span class="badge bg-warning">Bekliyor</span>
                            @elseif($order->payment_status == 'paid')
                            <span class="badge bg-success">Ödendi</span>
                            @elseif($order->payment_status == 'refunded')
                            <span class="badge bg-secondary">İade Edildi</span>
                            @endif
                        </div>
                    </div>

                    <h6 class="mt-4 mb-3">Sipariş İçeriği</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Ürün</th>
                                    <th>Satıcı</th>
                                    <th class="text-end">Fiyat</th>
                                    <th class="text-center">Adet</th>
                                    <th class="text-end">Toplam</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td>{{ $item->product->name }}</td>
                                    <td>{{ $item->product->seller->name ?? '-' }}</td>
                                    <td class="text-end">{{ number_format($item->price, 2) }} ₺</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">{{ number_format($item->price * $item->quantity, 2) }} ₺</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">Toplam:</th>
                                    <th class="text-end">{{ number_format($order->total_amount, 2) }} ₺</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Satıcı Bazlı Dağılım -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Satıcı Bazlı Ödeme Dağılımı</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Satıcı</th>
                                    <th class="text-center">Ürün Sayısı</th>
                                    <th class="text-end">Tutar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sellerPayments as $payment)
                                <tr>
                                    <td>
                                        <strong>{{ $payment['seller']->name }}</strong><br>
                                        <small class="text-muted">{{ $payment['seller']->email }}</small>
                                    </td>
                                    <td class="text-center">{{ $payment['items']->count() }}</td>
                                    <td class="text-end"><strong>{{ number_format($payment['total'], 2) }} ₺</strong></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- İşlemler -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Ödeme İşlemleri</h5>
                </div>
                <div class="card-body">
                    @if($order->payment_status == 'pending')
                    <form action="{{ route('admin.payments.approve', $order) }}" method="POST" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-check me-1"></i>Ödemeyi Onayla
                        </button>
                    </form>
                    @endif

                    @if($order->payment_status == 'paid')
                    <button type="button" class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#refundModal">
                        <i class="fas fa-undo me-1"></i>İade İşlemi
                    </button>
                    @endif

                    <hr>

                    <h6 class="mb-3">Ödeme Durumu Değiştir</h6>
                    <form action="{{ route('admin.payments.update-status', $order) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <select name="payment_status" class="form-select" required>
                                <option value="pending" {{ $order->payment_status == 'pending' ? 'selected' : '' }}>Bekliyor</option>
                                <option value="paid" {{ $order->payment_status == 'paid' ? 'selected' : '' }}>Ödendi</option>
                                <option value="failed" {{ $order->payment_status == 'failed' ? 'selected' : '' }}>Başarısız</option>
                                <option value="refunded" {{ $order->payment_status == 'refunded' ? 'selected' : '' }}>İade Edildi</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-1"></i>Güncelle
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- İade Modal -->
<div class="modal fade" id="refundModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.payments.refund', $order) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">İade İşlemi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>Dikkat!</strong> Bu işlem geri alınamaz. Sipariş iptal edilecek ve ödeme iade edilecektir.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">İade Nedeni <span class="text-danger">*</span></label>
                        <textarea name="refund_reason" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-danger">İade Et</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

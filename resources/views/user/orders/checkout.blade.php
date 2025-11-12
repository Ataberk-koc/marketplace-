@extends('layouts.app')

@section('title', 'Ödeme')

@section('content')
<div class="container my-5">
    <h1 class="mb-4"><i class="bi bi-credit-card"></i> Ödeme</h1>

    <form action="{{ route('user.orders.store') }}" method="POST">
        @csrf
        
        <div class="row">
            <!-- Sol Kolon - Adres Bilgileri -->
            <div class="col-md-8">
                <!-- Teslimat Adresi -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-truck"></i> Teslimat Adresi</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="shipping_name" class="form-label">Ad Soyad <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('shipping_name') is-invalid @enderror" 
                                       id="shipping_name" name="shipping_name" value="{{ old('shipping_name', auth()->user()->name) }}" required>
                                @error('shipping_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="shipping_phone" class="form-label">Telefon <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control @error('shipping_phone') is-invalid @enderror" 
                                       id="shipping_phone" name="shipping_phone" value="{{ old('shipping_phone', auth()->user()->phone) }}" required>
                                @error('shipping_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 mb-3">
                                <label for="shipping_address" class="form-label">Adres <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('shipping_address') is-invalid @enderror" 
                                          id="shipping_address" name="shipping_address" rows="3" required>{{ old('shipping_address') }}</textarea>
                                @error('shipping_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="shipping_city" class="form-label">Şehir <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('shipping_city') is-invalid @enderror" 
                                       id="shipping_city" name="shipping_city" value="{{ old('shipping_city') }}" required>
                                @error('shipping_city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="shipping_state" class="form-label">İlçe <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('shipping_state') is-invalid @enderror" 
                                       id="shipping_state" name="shipping_state" value="{{ old('shipping_state') }}" required>
                                @error('shipping_state')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="shipping_zip" class="form-label">Posta Kodu <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('shipping_zip') is-invalid @enderror" 
                                       id="shipping_zip" name="shipping_zip" value="{{ old('shipping_zip') }}" required>
                                @error('shipping_zip')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" id="same_billing" checked>
                            <label class="form-check-label" for="same_billing">
                                Fatura adresi teslimat adresi ile aynı
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Fatura Adresi -->
                <div class="card mb-4" id="billing_section" style="display: none;">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-receipt"></i> Fatura Adresi</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="billing_name" class="form-label">Ad Soyad</label>
                                <input type="text" class="form-control" id="billing_name" name="billing_name" value="{{ old('billing_name') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="billing_phone" class="form-label">Telefon</label>
                                <input type="tel" class="form-control" id="billing_phone" name="billing_phone" value="{{ old('billing_phone') }}">
                            </div>
                            <div class="col-12 mb-3">
                                <label for="billing_address" class="form-label">Adres</label>
                                <textarea class="form-control" id="billing_address" name="billing_address" rows="3">{{ old('billing_address') }}</textarea>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="billing_city" class="form-label">Şehir</label>
                                <input type="text" class="form-control" id="billing_city" name="billing_city" value="{{ old('billing_city') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="billing_state" class="form-label">İlçe</label>
                                <input type="text" class="form-control" id="billing_state" name="billing_state" value="{{ old('billing_state') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="billing_zip" class="form-label">Posta Kodu</label>
                                <input type="text" class="form-control" id="billing_zip" name="billing_zip" value="{{ old('billing_zip') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ödeme Yöntemi -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-credit-card"></i> Ödeme Yöntemi</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="credit_card" value="credit_card" checked>
                            <label class="form-check-label" for="credit_card">
                                <i class="bi bi-credit-card"></i> Kredi Kartı
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="bank_transfer" value="bank_transfer">
                            <label class="form-check-label" for="bank_transfer">
                                <i class="bi bi-bank"></i> Banka Havalesi
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sağ Kolon - Sipariş Özeti -->
            <div class="col-md-4">
                <div class="card sticky-top" style="top: 20px;">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-cart"></i> Sipariş Özeti</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $subtotal = $cart->items->sum(function($item) {
                                return $item->quantity * $item->product->price;
                            });
                            $shipping = 29.90;
                            $tax = $subtotal * 0.20;
                            $total = $subtotal + $shipping + $tax;
                        @endphp

                        <div class="d-flex justify-content-between mb-2">
                            <span>Ara Toplam ({{ $cart->items->count() }} ürün):</span>
                            <strong>{{ number_format($subtotal, 2) }} ₺</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Kargo:</span>
                            <strong>{{ number_format($shipping, 2) }} ₺</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>KDV (20%):</span>
                            <strong>{{ number_format($tax, 2) }} ₺</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Toplam:</strong>
                            <h4 class="text-primary mb-0">{{ number_format($total, 2) }} ₺</h4>
                        </div>

                        <button type="submit" class="btn btn-success btn-lg w-100">
                            <i class="bi bi-check-lg"></i> Siparişi Tamamla
                        </button>

                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <i class="bi bi-shield-check"></i> Güvenli ödeme
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.getElementById('same_billing').addEventListener('change', function() {
    const billingSection = document.getElementById('billing_section');
    if(this.checked) {
        billingSection.style.display = 'none';
    } else {
        billingSection.style.display = 'block';
    }
});
</script>
@endpush
@endsection

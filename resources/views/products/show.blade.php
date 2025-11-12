@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Ana Sayfa</a></li>
            <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Ürünler</a></li>
            <li class="breadcrumb-item active">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Product Images -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    @if($product->main_image)
                        <img src="{{ $product->main_image }}" class="img-fluid rounded" alt="{{ $product->name }}">
                    @else
                        <div class="bg-secondary rounded" style="height: 400px;"></div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Product Details -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h2>{{ $product->name }}</h2>
                    <p class="text-muted">
                        <i class="bi bi-tag"></i> {{ $product->brand->name }} | 
                        <i class="bi bi-grid"></i> {{ $product->category->name }}
                    </p>

                    <hr>

                    <div class="mb-3">
                        @if($product->discount_price)
                            <div class="mb-2">
                                <span class="text-decoration-line-through text-muted h5">
                                    {{ number_format($product->price, 2) }} ₺
                                </span>
                                <span class="badge bg-danger ms-2">
                                    %{{ round((($product->price - $product->discount_price) / $product->price) * 100) }} İndirim
                                </span>
                            </div>
                            <h3 class="text-danger">{{ number_format($product->final_price, 2) }} ₺</h3>
                        @else
                            <h3>{{ number_format($product->price, 2) }} ₺</h3>
                        @endif
                    </div>

                    <div class="mb-3">
                        @if($product->stock > 0)
                            <span class="badge bg-success fs-6">
                                <i class="bi bi-check-circle"></i> Stokta ({{ $product->stock }} adet)
                            </span>
                        @else
                            <span class="badge bg-danger fs-6">
                                <i class="bi bi-x-circle"></i> Stok Yok
                            </span>
                        @endif
                    </div>

                    @if($product->sizes->isNotEmpty())
                    <div class="mb-3">
                        <label class="form-label fw-bold">Beden Seçin:</label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($product->sizes as $size)
                                <button type="button" class="btn btn-outline-primary size-btn" 
                                        data-size-id="{{ $size->id }}"
                                        data-stock="{{ $size->pivot->stock }}">
                                    {{ $size->name }}
                                    @if($size->pivot->stock == 0)
                                        <i class="bi bi-x-circle text-danger"></i>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @auth
                        @if(auth()->user()->isCustomer() && $product->stock > 0)
                        <form action="{{ route('user.cart.add', $product) }}" method="POST" id="addToCartForm">
                            @csrf
                            <input type="hidden" name="size_id" id="selected_size_id">
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Adet:</label>
                                <div class="input-group" style="max-width: 150px;">
                                    <button type="button" class="btn btn-outline-secondary" id="decreaseQty">-</button>
                                    <input type="number" class="form-control text-center" name="quantity" 
                                           id="quantity" value="1" min="1" max="{{ $product->stock }}">
                                    <button type="button" class="btn btn-outline-secondary" id="increaseQty">+</button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success btn-lg w-100">
                                <i class="bi bi-cart-plus"></i> Sepete Ekle
                            </button>
                        </form>
                        @endif
                    @else
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            Satın almak için <a href="{{ route('login') }}">giriş yapın</a>.
                        </div>
                    @endauth
                </div>
            </div>

            <!-- Product Description -->
            @if($product->description)
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Ürün Açıklaması</h5>
                </div>
                <div class="card-body">
                    {!! nl2br(e($product->description)) !!}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        let selectedSizeId = null;
        const hasSizes = {{ $product->sizes->isNotEmpty() ? 'true' : 'false' }};

        // Size selection
        $('.size-btn').click(function() {
            const stock = $(this).data('stock');
            if (stock == 0) return;

            $('.size-btn').removeClass('active');
            $(this).addClass('active');
            selectedSizeId = $(this).data('size-id');
            $('#selected_size_id').val(selectedSizeId);
            
            // Update max quantity
            $('#quantity').attr('max', stock);
            if (parseInt($('#quantity').val()) > stock) {
                $('#quantity').val(stock);
            }
        });

        // Quantity controls
        $('#decreaseQty').click(function() {
            let qty = parseInt($('#quantity').val());
            if (qty > 1) $('#quantity').val(qty - 1);
        });

        $('#increaseQty').click(function() {
            let qty = parseInt($('#quantity').val());
            let max = parseInt($('#quantity').attr('max'));
            if (qty < max) $('#quantity').val(qty + 1);
        });

        // Form submission
        $('#addToCartForm').submit(function(e) {
            if (hasSizes && !selectedSizeId) {
                e.preventDefault();
                alert('Lütfen bir beden seçin!');
                return false;
            }
        });
    });
</script>
@endpush

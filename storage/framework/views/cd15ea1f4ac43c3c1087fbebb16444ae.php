

<?php $__env->startSection('title', 'Ürünler'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-box-seam"></i> Ürünler</h2>
    <div>
        <button class="btn btn-outline-secondary me-2" onclick="window.print()">
            <i class="bi bi-printer"></i> Dışa Aktar
        </button>
        <button class="btn btn-outline-secondary me-2">
            <i class="bi bi-download"></i> İçe Aktar
        </button>
        <div class="btn-group me-2">
            <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                Daha fazla
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#"><i class="bi bi-funnel"></i> Filtre</a></li>
            </ul>
        </div>
        <a href="<?php echo e(route('admin.products.create')); ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Yeni Ürün
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" id="searchInput" placeholder="Tabloda arama yapın">
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px">
                            <input type="checkbox" class="form-check-input" id="selectAll">
                        </th>
                        <th style="width: 80px;">Ürün</th>
                        <th>Ürün Adı / Barkod</th>
                        <th style="width: 120px;">Satış Fiyatı</th>
                        <th style="width: 120px;">İndirimli Fiyat</th>
                        <th style="width: 100px;">Envanter</th>
                        <th style="width: 100px;">Durum</th>
                        <th style="width: 150px;">Satış Kanalları</th>
                        <th style="width: 120px;">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="product-row">
                        <td>
                            <input type="checkbox" class="form-check-input product-checkbox">
                        </td>
                        <td>
                            <?php if(isset($product->images) && is_array($product->images) && count($product->images) > 0): ?>
                                <img src="<?php echo e($product->images[0]); ?>" alt="<?php echo e($product->name); ?>" 
                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                            <?php else: ?>
                                <div class="bg-secondary rounded d-flex align-items-center justify-content-center text-white" 
                                     style="width: 50px; height: 50px; border-radius: 8px;">
                                    <i class="bi bi-image"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div>
                                <strong><?php echo e($product->name); ?></strong>
                                <div class="text-muted small">
                                    <?php echo e($product->model_code ?? 'Model yok'); ?> / <?php echo e($product->sku ?? 'SKU yok'); ?>

                                </div>
                            </div>
                        </td>
                        <td>
                            <strong>₺<?php echo e(number_format($product->price, 2)); ?></strong>
                        </td>
                        <td>
                            <?php if($product->discount_price): ?>
                                <span class="text-danger fw-bold">₺<?php echo e(number_format($product->discount_price, 2)); ?></span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($product->variants && $product->variants->count() > 0): ?>
                                <span class="badge bg-info">
                                    <?php echo e($product->variants->sum('stock_quantity')); ?> adet<br>
                                    <small><?php echo e($product->variants->count()); ?> varyant</small>
                                </span>
                            <?php else: ?>
                                <?php
                                    $stock = $product->stock_quantity ?? 0;
                                ?>
                                <?php if($stock > 10): ?>
                                    <span class="badge bg-success"><?php echo e($stock); ?> adet</span>
                                <?php elseif($stock > 0): ?>
                                    <span class="badge bg-warning"><?php echo e($stock); ?> adet</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Stok yok</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($product->is_active): ?>
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Satışta</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Pasif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <span class="badge bg-light text-dark border">2 Satış Kanalı</span>
                                <button class="btn btn-sm" data-bs-toggle="dropdown">
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="<?php echo e(route('admin.products.edit', $product->id)); ?>" class="btn btn-outline-primary" title="Düzenle">
                                    <i class="bi bi-pencil"></i> Düzenle
                                </a>
                                <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                                    <span class="visually-hidden">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="<?php echo e(route('admin.products.show', $product->id)); ?>">
                                            <i class="bi bi-eye"></i> Görüntüle
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="<?php echo e(route('admin.products.destroy', $product->id)); ?>" method="POST" 
                                              onsubmit="return confirm('Bu ürünü silmek istediğinizden emin misiniz?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="bi bi-trash"></i> Sil
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Collapsible Variant Details Row -->
                    <tr class="collapse-row">
                        <td colspan="9" class="p-0">
                            <div class="collapse" id="product-<?php echo e($product->id); ?>">
                                <div class="d-flex bg-light border-top border-bottom" style="min-height: 400px;">
                                    <div class="bg-dark text-white border-end" style="width: 180px;">
                                        <div class="list-group list-group-flush">
                                            <div class="list-group-item bg-dark text-white border-0 py-2 px-3" style="font-weight: 500; cursor: default;">
                                                Ürünler
                                            </div>
                                            <a href="javascript:void(0)" class="product-menu-item list-group-item list-group-item-action bg-dark text-white border-0 py-2 px-3 active" 
                                               data-product="<?php echo e($product->id); ?>" data-section="details" style="font-size: 14px;">
                                                Ürünler
                                            </a>
                                            <a href="javascript:void(0)" class="product-menu-item list-group-item list-group-item-action bg-dark text-white border-0 py-2 px-3" 
                                               data-product="<?php echo e($product->id); ?>" data-section="purchase" style="font-size: 14px;">
                                                Satın Alma
                                            </a>
                                            <a href="javascript:void(0)" class="product-menu-item list-group-item list-group-item-action bg-dark text-white border-0 py-2 px-3" 
                                               data-product="<?php echo e($product->id); ?>" data-section="transfer" style="font-size: 14px;">
                                                Transferler
                                            </a>
                                            <a href="javascript:void(0)" class="product-menu-item list-group-item list-group-item-action bg-dark text-white border-0 py-2 px-3" 
                                               data-product="<?php echo e($product->id); ?>" data-section="stock" style="font-size: 14px;">
                                                Stok Sayımı
                                            </a>
                                            <a href="javascript:void(0)" class="product-menu-item list-group-item list-group-item-action bg-dark text-white border-0 py-2 px-3" 
                                               data-product="<?php echo e($product->id); ?>" data-section="definitions" style="font-size: 14px;">
                                                Tanımlamalar
                                            </a>
                                            <a href="javascript:void(0)" class="product-menu-item list-group-item list-group-item-action bg-dark text-white border-0 py-2 px-3" 
                                               data-product="<?php echo e($product->id); ?>" data-section="pricelist" style="font-size: 14px;">
                                                Fiyat Listesi
                                            </a>
                                            <a href="javascript:void(0)" class="product-menu-item list-group-item list-group-item-action bg-dark text-white border-0 py-2 px-3" 
                                               data-product="<?php echo e($product->id); ?>" data-section="barcode" style="font-size: 14px;">
                                                Ürün Barkod Etiketi
                                            </a>
                                        </div>
                                    </div>
                                    <div class="flex-fill p-3" id="content-<?php echo e($product->id); ?>">
                                        <!-- Ürünler (Varyant Detayları) -->
                                        <div class="product-section" data-section="details">
                                            <h6 class="mb-3">Varyant Detayları</h6>
                                            <?php if($product->variants->count() > 0): ?>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered">
                                                        <thead>
                                                            <tr class="table-secondary">
                                                                <th>Varyant</th>
                                                                <th>SKU</th>
                                                                <th>Barkod</th>
                                                                <th>Fiyat</th>
                                                                <th>Stok</th>
                                                                <th>Durum</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php $__currentLoopData = $product->variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <tr>
                                                                <td><strong><?php echo e($variant->variant_name ?? 'Varyant'); ?></strong></td>
                                                                <td><code><?php echo e($variant->sku); ?></code></td>
                                                                <td><code><?php echo e($variant->barcode); ?></code></td>
                                                                <td>₺<?php echo e(number_format($variant->price, 2)); ?></td>
                                                                <td>
                                                                    <?php if($variant->stock_quantity > 10): ?>
                                                                        <span class="badge bg-success"><?php echo e($variant->stock_quantity); ?></span>
                                                                    <?php elseif($variant->stock_quantity > 0): ?>
                                                                        <span class="badge bg-warning"><?php echo e($variant->stock_quantity); ?></span>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-danger">0</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <?php if($variant->is_active ?? true): ?>
                                                                        <i class="bi bi-check-circle-fill text-success"></i>
                                                                    <?php else: ?>
                                                                        <i class="bi bi-x-circle-fill text-danger"></i>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php else: ?>
                                                <div class="alert alert-info mb-0">
                                                    <i class="bi bi-info-circle"></i> Bu ürünün varyantı yok
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Satın Alma -->
                                        <div class="product-section" data-section="purchase" style="display:none;">
                                            <h6 class="mb-3">Satın Alma Bilgileri</h6>
                                            <div class="alert alert-info">
                                                <i class="bi bi-info-circle"></i> Bu ürün için satın alma işlemleri burada görüntülenecek
                                            </div>
                                        </div>

                                        <!-- Transferler -->
                                        <div class="product-section" data-section="transfer" style="display:none;">
                                            <h6 class="mb-3">Transfer İşlemleri</h6>
                                            <div class="alert alert-info">
                                                <i class="bi bi-info-circle"></i> Bu ürün için transfer işlemleri burada görüntülenecek
                                            </div>
                                        </div>

                                        <!-- Stok Sayımı -->
                                        <div class="product-section" data-section="stock" style="display:none;">
                                            <h6 class="mb-3">Stok Sayımı</h6>
                                            <?php if($product->variants->count() > 0): ?>
                                                <div class="table-responsive">
                                                    <table class="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>Varyant</th>
                                                                <th>Mevcut Stok</th>
                                                                <th>Sayım Stok</th>
                                                                <th>Fark</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php $__currentLoopData = $product->variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <tr>
                                                                <td><?php echo e($variant->variant_name); ?></td>
                                                                <td><?php echo e($variant->stock_quantity); ?></td>
                                                                <td><input type="number" class="form-control form-control-sm" style="width:80px;"></td>
                                                                <td><span class="text-muted">-</span></td>
                                                            </tr>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Tanımlamalar -->
                                        <div class="product-section" data-section="definitions" style="display:none;">
                                            <h6 class="mb-3">Ürün Tanımlamaları</h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p><strong>Model Kodu:</strong> <?php echo e($product->model_code); ?></p>
                                                    <p><strong>Kategori:</strong> <?php echo e($product->category->name); ?></p>
                                                    <p><strong>Marka:</strong> <?php echo e($product->brand->name); ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong>SKU:</strong> <?php echo e($product->sku); ?></p>
                                                    <p><strong>Durum:</strong> 
                                                        <?php if($product->is_active): ?>
                                                            <span class="badge bg-success">Aktif</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Pasif</span>
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Fiyat Listesi -->
                                        <div class="product-section" data-section="pricelist" style="display:none;">
                                            <h6 class="mb-3">Fiyat Listesi</h6>
                                            <?php if($product->variants->count() > 0): ?>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered">
                                                        <thead>
                                                            <tr class="table-secondary">
                                                                <th>Varyant</th>
                                                                <th>Satış Fiyatı</th>
                                                                <th>İndirimli Fiyat</th>
                                                                <th>Maliyet</th>
                                                                <th>Kar Marjı</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php $__currentLoopData = $product->variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <tr>
                                                                <td><?php echo e($variant->variant_name); ?></td>
                                                                <td>₺<?php echo e(number_format($variant->price, 2)); ?></td>
                                                                <td>
                                                                    <?php if($variant->discount_price): ?>
                                                                        ₺<?php echo e(number_format($variant->discount_price, 2)); ?>

                                                                    <?php else: ?>
                                                                        -
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <?php if($variant->cost): ?>
                                                                        ₺<?php echo e(number_format($variant->cost, 2)); ?>

                                                                    <?php else: ?>
                                                                        -
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <?php if($variant->cost): ?>
                                                                        <span class="badge bg-success">
                                                                            %<?php echo e(number_format((($variant->price - $variant->cost) / $variant->price) * 100, 0)); ?>

                                                                        </span>
                                                                    <?php else: ?>
                                                                        -
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Ürün Barkod Etiketi -->
                                        <div class="product-section" data-section="barcode" style="display:none;">
                                            <h6 class="mb-3">Barkod Etiketleri</h6>
                                            <?php if($product->variants->count() > 0): ?>
                                                <div class="row">
                                                    <?php $__currentLoopData = $product->variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <div class="col-md-4 mb-3">
                                                        <div class="card">
                                                            <div class="card-body text-center">
                                                                <h6><?php echo e($variant->variant_name); ?></h6>
                                                                <svg id="barcode-<?php echo e($variant->id); ?>"></svg>
                                                                <p class="mb-0 mt-2"><code><?php echo e($variant->barcode); ?></code></p>
                                                                <small class="text-muted">₺<?php echo e(number_format($variant->price, 2)); ?></small>
                                                                <div class="mt-2">
                                                                    <button class="btn btn-sm btn-primary" onclick="printBarcode(<?php echo e($variant->id); ?>)">
                                                                        <i class="bi bi-printer"></i> Yazdır
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    
                    <tr class="border-0" style="height: 1px;">
                        <td colspan="9" class="p-0 text-center">
                            <button class="btn btn-link btn-sm text-decoration-none" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#product-<?php echo e($product->id); ?>"
                                    style="margin-top: -10px;">
                                <i class="bi bi-chevron-down"></i>
                            </button>
                        </td>
                    </tr>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-3 mb-0">Henüz ürün eklenmemiş</p>
                                <a href="<?php echo e(route('admin.products.create')); ?>" class="btn btn-primary btn-sm mt-2">
                                    <i class="bi bi-plus-circle"></i> İlk Ürünü Ekle
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if($products->hasPages()): ?>
    <div class="card-footer bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                <?php echo e($products->firstItem()); ?>-<?php echo e($products->lastItem()); ?> arası gösteriliyor (Toplam: <?php echo e($products->total()); ?>)
            </div>
            <div>
                <?php echo e($products->links()); ?>

            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
$(document).ready(function(){
    // Arama
    $('#searchInput').on('keyup', function(){
        var value = $(this).val().toLowerCase();
        $('.product-row').filter(function(){
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    // Tümünü seç
    $('#selectAll').on('change', function(){
        $('.product-checkbox').prop('checked', $(this).is(':checked'));
    });

    // Collapse icon değiştir
    $('[data-bs-toggle="collapse"]').on('click', function(){
        var icon = $(this).find('i');
        icon.toggleClass('bi-chevron-down bi-chevron-up');
    });

    // Sol menü tab sistemi
    $('.product-menu-item').on('click', function(){
        var productId = $(this).data('product');
        var section = $(this).data('section');
        
        // Aktif menüyü değiştir
        $('#content-' + productId).closest('.collapse').find('.product-menu-item').removeClass('active');
        $(this).addClass('active');
        
        // İçeriği değiştir
        $('#content-' + productId + ' .product-section').hide();
        $('#content-' + productId + ' .product-section[data-section="' + section + '"]').show();
    });
});

function printBarcode(variantId) {
    alert('Barkod yazdırma özelliği: Varyant ID ' + variantId);
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\LENOVO\marketplace-entegrasyonu\resources\views/admin/products/index.blade.php ENDPATH**/ ?>
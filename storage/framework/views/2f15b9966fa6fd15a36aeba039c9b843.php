

<?php $__env->startSection('title', 'Trendyol Yönetimi'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="bi bi-shop"></i> Trendyol Entegrasyon Yönetimi</h1>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> <?php echo session('success'); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle"></i> <?php echo session('error'); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- İstatistikler -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Toplam Ürün</h6>
                    <h2 class="mb-0"><?php echo e($stats['total_products']); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6 class="card-title">Bekleyen Gönderim</h6>
                    <h2 class="mb-0"><?php echo e($stats['mapped_products']); ?></h2>
                    <small>Eşleştirilmiş, henüz gönderilmemiş</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Gönderildi</h6>
                    <h2 class="mb-0"><?php echo e($stats['sent_products']); ?></h2>
                    <small>Trendyol'a gönderildi</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">Trendyol Kategori</h6>
                    <h2 class="mb-0"><?php echo e($stats['trendyol_categories']); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Senkronizasyon ve Eşleştirme -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-arrow-repeat"></i> 1. Veri Senkronizasyonu</h5>
                </div>
                <div class="card-body">
                    <p class="mb-3">Trendyol'dan marka ve kategori bilgilerini çekin:</p>
                    <form action="<?php echo e(route('admin.trendyol.sync-brands')); ?>" method="POST" class="d-inline">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-primary mb-2">
                            <i class="bi bi-tags"></i> Markaları Senkronize Et
                        </button>
                    </form>
                    <form action="<?php echo e(route('admin.trendyol.sync-categories')); ?>" method="POST" class="d-inline">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-info mb-2">
                            <i class="bi bi-folder"></i> Kategorileri Senkronize Et
                        </button>
                    </form>
                    
                    <hr>
                    
                    <p class="text-muted mb-2"><small><i class="fas fa-info-circle"></i> Senkronizasyondan sonra manuel eşleştirme yapın:</small></p>
                    <a href="<?php echo e(route('admin.trendyol.brand-mapping')); ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-link"></i> Marka Eşleştir
                    </a>
                    <a href="<?php echo e(route('admin.trendyol.category-mapping')); ?>" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-sitemap"></i> Kategori Eşleştir
                    </a>
                    <a href="<?php echo e(route('admin.trendyol.product-mapping')); ?>" class="btn btn-outline-warning btn-sm">
                        <i class="fas fa-box-open"></i> Ürün Eşleştir (Tek Tablo)
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-upload"></i> 2. Toplu Ürün Gönderimi</h5>
                </div>
                <div class="card-body">
                    <p>Eşleştirilmiş tüm ürünleri Trendyol'a gönder:</p>
                    <div class="mb-3">
                        <span class="badge bg-warning text-dark me-2">
                            <?php echo e(\App\Models\ProductTrendyolMapping::where('status', 'pending')->count()); ?> ürün gönderilmeye hazır
                        </span>
                        <span class="badge bg-success">
                            <?php echo e(\App\Models\ProductTrendyolMapping::where('status', 'sent')->count()); ?> ürün gönderildi
                        </span>
                    </div>
                    <?php if(\App\Models\ProductTrendyolMapping::where('status', 'pending')->count() > 0): ?>
                        <form action="<?php echo e(route('admin.trendyol.bulk-send')); ?>" method="POST" 
                              onsubmit="return confirm('<?php echo e(\App\Models\ProductTrendyolMapping::where('status', 'pending')->count()); ?> ürünü Trendyol\'a göndermek istediğinize emin misiniz?')">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-cloud-upload"></i> Hepsini Gönder
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> Tüm eşleştirilmiş ürünler gönderildi.
                        </div>
                    <?php endif; ?>
                    <a href="<?php echo e(route('admin.trendyol.product-mapping')); ?>" class="btn btn-outline-primary mt-2">
                        <i class="bi bi-link-45deg"></i> Ürün Eşleştir
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stok/Fiyat Güncelleme -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Stok/Fiyat Güncelleme</h5>
                </div>
                <div class="card-body">
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#updateInventoryModal">
                        <i class="bi bi-arrow-repeat"></i> Toplu Güncelle
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-trash"></i> Ürün Silme</h5>
                </div>
                <div class="card-body">
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteProductsModal">
                        <i class="bi bi-x-circle"></i> Toplu Sil
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Kontrolü -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-clock-history"></i> Batch İşlem Kontrolü</h5>
        </div>
        <div class="card-body">
            <div class="input-group">
                <input type="text" class="form-control" id="batchRequestId" placeholder="Batch Request ID girin">
                <button class="btn btn-primary" onclick="checkBatchStatus()">
                    <i class="bi bi-search"></i> Kontrol Et
                </button>
            </div>
            <div id="batchResult" class="mt-3"></div>
        </div>
    </div>
</div>

<!-- Stok/Fiyat Güncelleme Modal -->
<div class="modal fade" id="updateInventoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?php echo e(route('admin.trendyol.bulk-update-inventory')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-header">
                    <h5 class="modal-title">Toplu Stok/Fiyat Güncelleme</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="inventoryUpdates">
                        <div class="row mb-2">
                            <div class="col-md-3">
                                <input type="text" name="updates[0][barcode]" class="form-control" placeholder="Barkod">
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="updates[0][quantity]" class="form-control" placeholder="Stok">
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="updates[0][salePrice]" class="form-control" placeholder="Satış Fiyatı" step="0.01">
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="updates[0][listPrice]" class="form-control" placeholder="Liste Fiyatı" step="0.01">
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary" onclick="addInventoryRow()">
                        <i class="bi bi-plus"></i> Satır Ekle
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-warning">Güncelle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Ürün Silme Modal -->
<div class="modal fade" id="deleteProductsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('admin.trendyol.bulk-delete')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Toplu Ürün Silme</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> 
                        Dikkat! Bu işlem geri alınamaz.
                    </div>
                    <p>Silmek istediğiniz ürünlerin barkodlarını virgülle ayırarak girin:</p>
                    <textarea name="barcodes_text" class="form-control" rows="5" 
                              placeholder="Örnek: BARCODE1,BARCODE2,BARCODE3"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-danger">Sil</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
let inventoryRowCount = 1;

function addInventoryRow() {
    const html = `
        <div class="row mb-2">
            <div class="col-md-3">
                <input type="text" name="updates[${inventoryRowCount}][barcode]" class="form-control" placeholder="Barkod">
            </div>
            <div class="col-md-3">
                <input type="number" name="updates[${inventoryRowCount}][quantity]" class="form-control" placeholder="Stok">
            </div>
            <div class="col-md-3">
                <input type="number" name="updates[${inventoryRowCount}][salePrice]" class="form-control" placeholder="Satış Fiyatı" step="0.01">
            </div>
            <div class="col-md-3">
                <input type="number" name="updates[${inventoryRowCount}][listPrice]" class="form-control" placeholder="Liste Fiyatı" step="0.01">
            </div>
        </div>
    `;
    document.getElementById('inventoryUpdates').insertAdjacentHTML('beforeend', html);
    inventoryRowCount++;
}

function checkBatchStatus() {
    const batchId = document.getElementById('batchRequestId').value;
    
    if (!batchId) {
        alert('Lütfen Batch Request ID girin!');
        return;
    }

    fetch(`/admin/trendyol/batch-status/${batchId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('batchResult').innerHTML = `
                <div class="alert alert-info">
                    <h6>Batch Durumu:</h6>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                </div>
            `;
        })
        .catch(error => {
            document.getElementById('batchResult').innerHTML = `
                <div class="alert alert-danger">
                    Hata: ${error.message}
                </div>
            `;
        });
}

// Form submit işlemlerinde text'i array'e çevir
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const productIdsText = this.querySelector('[name="product_ids_text"]');
        if (productIdsText && productIdsText.value) {
            const ids = productIdsText.value.split(',').map(id => id.trim()).filter(id => id);
            ids.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'product_ids[]';
                input.value = id;
                this.appendChild(input);
            });
        }

        const barcodesText = this.querySelector('[name="barcodes_text"]');
        if (barcodesText && barcodesText.value) {
            const barcodes = barcodesText.value.split(',').map(b => b.trim()).filter(b => b);
            barcodes.forEach(barcode => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'barcodes[]';
                input.value = barcode;
                this.appendChild(input);
            });
        }
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\LENOVO\marketplace-entegrasyonu\resources\views/admin/trendyol/index.blade.php ENDPATH**/ ?>
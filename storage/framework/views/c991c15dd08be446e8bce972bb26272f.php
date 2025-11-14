

<?php $__env->startSection('title', 'Markalar'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-tag"></i> Markalar</h2>
    <div class="btn-group">
        <a href="<?php echo e(route('admin.brands.create')); ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Yeni Marka Ekle
        </a>
        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#syncModal">
            <i class="bi bi-arrow-repeat"></i> Trendyol'dan Senkronize Et
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped datatable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Marka Adı</th>
                    <th>Slug</th>
                    <th>Trendyol Eşleştirme</th>
                    <th>Ürün Sayısı</th>
                    <th>Durum</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $brands; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $brand): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($brand->id); ?></td>
                    <td><?php echo e($brand->name); ?></td>
                    <td><?php echo e($brand->slug); ?></td>
                    <td>
                        <?php if($brand->trendyolMapping): ?>
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> 
                                <?php echo e($brand->trendyolMapping->trendyol_brand_name ?? 'Eşleştirilmiş'); ?>

                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary">
                                <i class="bi bi-x-circle"></i> Eşleştirilmemiş
                            </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge bg-primary"><?php echo e($brand->products_count); ?></span>
                    </td>
                    <td>
                        <?php if($brand->is_active): ?>
                            <span class="badge bg-success">Aktif</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Pasif</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="<?php echo e(route('admin.brands.mapping', $brand)); ?>" class="btn btn-info" title="Trendyol Eşleştir">
                                <i class="bi bi-link-45deg"></i>
                            </a>
                            <a href="<?php echo e(route('admin.brands.edit', $brand)); ?>" class="btn btn-primary" title="Düzenle">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="<?php echo e(route('admin.brands.destroy', $brand)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Markayı silmek istediğinizden emin misiniz?')">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\LENOVO\marketplace-entegrasyonu\resources\views/admin/brands/index.blade.php ENDPATH**/ ?>
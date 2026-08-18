<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>
<div class="container-fluid card">
  <div class="card-header mb-4 pb-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h4 class="mb-0">Product List</h4>

    <a href="<?= base_url('/products/form') ?>" class="btn btn-primary btn-sm"> <i class="fas fa-plus"></i> Add Product</a>
  </div>

  <div class="card-body pt-0 pb-2">
    <!-- Filter Form -->
    <form action="<?= base_url('/products') ?>" method="get" class="row g-2 mb-4 align-items-end">
      <div class="col-lg-3 col-md-6 col-12">
        <label class="form-label text-xs font-weight-bold">Search Product / SKU</label>
        <input
          type="text"
          name="search"
          class="form-control form-control-sm"
          placeholder="Search SKU, product name..."
          value="<?= esc($search ?? '') ?>">
      </div>
      <div class="col-lg-2 col-md-6 col-6">
        <label class="form-label text-xs font-weight-bold">Category</label>
        <select name="category_id" class="form-select form-select-sm">
          <option value="">All Categories</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['category_id'] ?>" <?= ($selectedCategoryId ?? '') == $cat['category_id'] ? 'selected' : '' ?>>
              <?= esc($cat['category_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-lg-2 col-md-6 col-6">
        <label class="form-label text-xs font-weight-bold">Brand</label>
        <select name="brand" class="form-select form-select-sm">
          <option value="">All Brands</option>
          <?php foreach ($brands as $br): ?>
            <option value="<?= esc($br) ?>" <?= ($selectedBrand ?? '') == $br ? 'selected' : '' ?>>
              <?= esc($br) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-lg-2 col-md-6 col-6">
        <label class="form-label text-xs font-weight-bold">Stock Status</label>
        <select name="stock_status" class="form-select form-select-sm">
          <option value="">All Stock</option>
          <option value="empty" <?= ($selectedStockStatus ?? '') == 'empty' ? 'selected' : '' ?>>🔴 Out of Stock (0)</option>
          <option value="low" <?= ($selectedStockStatus ?? '') == 'low' ? 'selected' : '' ?>>🟡 Low Stock (1 - 5)</option>
          <option value="in_stock" <?= ($selectedStockStatus ?? '') == 'in_stock' ? 'selected' : '' ?>>🟢 Safe Stock (> 5)</option>
        </select>
      </div>
      <div class="col-lg-3 col-md-6 col-6 d-flex gap-2 mt-lg-0 mt-3">
        <button type="submit" class="btn btn-sm btn-primary mb-0 d-flex align-items-center justify-content-center gap-1 flex-fill" style="height: 31px;" title="Filter">
          <i class="fa-solid fa-filter"></i> <span>Filter</span>
        </button>
        <a href="<?= base_url('/products') ?>" class="btn btn-sm btn-outline-secondary mb-0 d-flex align-items-center justify-content-center gap-1" style="height: 31px;" title="Reset">
          <i class="fa-solid fa-arrows-rotate"></i> <span>Reset</span>
        </a>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table align-items-center mb-0 table-bordered">
        <thead>
          <tr>
            <th class="text-center">No</th>
            <th>SKU</th>
            <th>Category</th>
            <th>Name</th>
            <th>Brand</th>
            <th class="text-center">Stock</th>
            <th class="text-center">Total Variants</th>
            <th class="sticky-action text-center">Actions</th>
          </tr>
        </thead>
        <tbody id="realtime-tbody">
          <?php $startIndex = ($pager["currentPage"] - 1) * $pager["limit"] + 1; ?>

          <?php if (empty($products)): ?>
            <tr>
              <td colspan="8" class="text-center text-muted py-4">No product data available.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($products as $product): ?>
              <tr>
                <td class="text-center"><?= $startIndex++ ?></td>
                <td><span class="font-weight-bold"><?= esc($product['product_sku']) ?></span></td>
                <td><?= esc($product['category_name']) ?></td>
                <td><?= esc($product['product_name']) ?></td>
                <td><?= esc($product['product_brand'] ?: '-') ?></td>
                <td class="text-center">
                  <?php if ((int)$product['product_stock'] <= 0): ?>
                    <span class="badge bg-gradient-danger">0 (Out of Stock)</span>
                  <?php elseif ((int)$product['product_stock'] <= 5): ?>
                    <span class="badge bg-gradient-warning"><?= $product['product_stock'] ?> (Low Stock)</span>
                  <?php else: ?>
                    <span class="badge bg-gradient-success"><?= $product['product_stock'] ?></span>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <?php if ($product['has_variants']): ?>
                    <span class="badge bg-gradient-info"><?= $product['total_variants'] ?> Variants</span>
                  <?php else: ?>
                    <span class="text-xs text-muted">Non-Variant</span>
                  <?php endif; ?>
                </td>
                <td class="sticky-action text-center">
                  <a href="<?= base_url('/products/form?id=' . $product['product_id']) ?>" class="btn btn-sm btn-warning mb-0" title="Edit Product">
                    <i class="fa-solid fa-pen-to-square"></i>
                  </a>
                  <form action="<?= base_url('/products/delete/' . $product['product_id']) ?>" method="post" style="display:inline-block;" class="confirm-delete mb-0">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-danger mb-0" title="Delete Product">
                      <i class="fa-solid fa-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <nav aria-label="Page navigation example" class="mt-4">
      <ul class="pagination" id="realtime-pagination"></ul>
    </nav>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="text/javascript">
  // PAGINATION
  function handlePagination(pageNumber) {
    const params = new URLSearchParams(window.location.search);
    params.set('page', pageNumber);
    window.location.replace(`<?php echo base_url(); ?>products?${params.toString()}`);
  }

  window.addEventListener('load', function() {
    renderPagination('realtime-pagination', <?= (int)$pager["currentPage"] ?>, <?= (int)$pager["totalPages"] ?>, handlePagination);
  });
</script>
<?= $this->endSection() ?>
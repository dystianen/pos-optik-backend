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
      <div class="col-md-4">
        <label class="form-label text-xs font-weight-bold">Search Product / SKU</label>
        <input
          type="text"
          name="search"
          class="form-control form-control-sm"
          placeholder="Search SKU, product name..."
          value="<?= esc($search ?? '') ?>">
      </div>
      <div class="col-md-3">
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
      <div class="col-md-3">
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
      <div class="col-md-2 d-flex gap-2">
        <button type="submit" class="btn btn-sm btn-primary w-100 d-flex align-items-center justify-content-center gap-1" style="height: 31px;" title="Filter">
          <i class="fa-solid fa-filter"></i> <span>Filter</span>
        </button>
        <a href="<?= base_url('/products') ?>" class="btn btn-sm btn-outline-secondary w-100 mb-0 d-flex align-items-center justify-content-center gap-1" style="height: 31px;" title="Reset">
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
            <th>Stock</th>
            <th>Total Variants</th>
            <th class="sticky-action text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php $startIndex = ($pager["currentPage"] - 1) * $pager["limit"] + 1; ?>

          <?php if (empty($products)): ?>
            <tr>
              <td colspan="8" class="text-center text-muted">No product data available.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($products as $product): ?>
              <tr>
                <td class="text-center"><?= $startIndex++ ?></td>
                <td><?= $product['product_sku'] ?></td>
                <td><?= $product['category_name'] ?></td>
                <td><?= $product['product_name'] ?></td>
                <td><?= $product['product_brand'] ?></td>
                <td><?= $product['product_stock'] ?></td>
                <td><?= $product['total_variants'] ?></td>
                <td class="sticky-action text-center">
                  <a href="<?= base_url('/products/form?id=' . $product['product_id']) ?>" class="btn btn-sm btn-warning">
                    <i class="fa-solid fa-pen-to-square"></i>
                  </a>
                  <form action="<?= base_url('/products/delete/' . $product['product_id']) ?>" method="post" style="display:inline-block;">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin?')">
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
      <ul class="pagination" id="pagination">
      </ul>
    </nav>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="text/javascript">
  var currentURL = window.location.search;
  var urlParams = new URLSearchParams(currentURL);
  var pageParam = urlParams.get('page');

  // PAGINATION
  function handlePagination(pageNumber) {
    const params = new URLSearchParams(window.location.search);
    params.set('page', pageNumber);
    window.location.replace(`<?php echo base_url(); ?>products?${params.toString()}`);
  }

  var paginationContainer = document.getElementById('pagination');
  var totalPages = <?= $pager["totalPages"] ?>;
  if (totalPages > 1) {
    for (var i = 1; i <= totalPages; i++) {
      var pageItem = document.createElement('li');
      pageItem.classList.add('page-item');
      pageItem.classList.add('primary');
      if (i === <?= $pager["currentPage"] ?>) {
        pageItem.classList.add('active');
      }

      var pageLink = document.createElement('a');
      pageLink.classList.add('page-link');
      pageLink.href = 'javascript:void(0);'
      pageLink.textContent = i;

      pageLink.addEventListener('click', function() {
        var pageNumber = parseInt(this.textContent);
        handlePagination(pageNumber);
      });

      pageItem.appendChild(pageLink);
      paginationContainer.appendChild(pageItem);
    }
  }
</script>
<?= $this->endSection() ?>
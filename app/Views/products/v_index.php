<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>
<div class="container-fluid card">
  <div class="card-header mb-4 pb-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h4 class="mb-0">Product List</h4>

    <div class="d-flex align-items-center gap-2">
      <form action="<?= base_url('/products') ?>" method="get" class="d-flex align-items-center">
        <input
          type="text"
          name="search"
          class="form-control form-control-sm me-2"
          placeholder="Search..."
          value="<?= esc($search ?? '') ?>"
          style="min-width: 200px;">
        <button type="submit" class="btn btn-sm btn-secondary">
          <i class="fa-solid fa-magnifying-glass"></i>
        </button>
      </form>

      <a href="<?= base_url('/products/form') ?>" class="btn btn-primary btn-sm">Add Product</a>
    </div>
  </div>


  <div class="card-body px-0 pt-0 pb-2">
    <div class="table-responsive px-4">
      <table class="table align-items-center mb-0">
        <thead>
          <tr>
            <th>No</th>
            <th>Category</th>
            <th>Name</th>
            <th>Brand</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Image URL</th>
            <th>Actions</th>
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
                <td><?= $startIndex++ ?></td>
                <td><?= $product['category_name'] ?></td>
                <td><?= $product['product_name'] ?></td>
                <td><?= $product['product_brand'] ?></td>
                <td><?= $product['product_price'] ?></td>
                <td><?= $product['product_stock'] ?></td>
                <td>
                  <img src="<?= base_url() . esc($product['product_image_url']) ?>" alt="image" width="70" height="70" style="border-radius: 15px">
                </td>
                <td>
                  <a href="<?= base_url('/products/form?id=' . $product['product_id']) ?>" class="btn btn-sm btn-warning">Edit</a>
                  <form action="<?= base_url('/products/delete/' . $product['product_id']) ?>" method="post" style="display:inline-block;">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin?')">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>

      </table>
    </div>

    <nav aria-label="Page navigation example" class="mt-4 mx-4">
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
  if (totalPages >= 1) {
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
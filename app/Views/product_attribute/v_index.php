<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>
<div class="container-fluid card">
  <div class="card-header mb-4 pb-0 d-flex align-items-center justify-content-between">
    <h4>Product Attribute List</h4>
    <a href="<?= base_url('/product-attribute/form') ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Attribute</a>
  </div>

  <div class="card-body pt-0 pb-2">
    <div class="table-responsive">
      <table class="table align-items-center mb-0 table-bordered">
        <thead>
          <tr>
            <th class="text-center">No</th>
            <th>Name</th>
            <th>Type</th>
            <th>Category</th>
            <th class="text-center">Variantable</th>
            <th class="text-center">Required</th>
            <th class="text-center">Filterable</th>
            <th class="text-center">Master Values</th>
            <th class="sticky-action text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php $startIndex = ($pager["currentPage"] - 1) * $pager["limit"] + 1; ?>

          <?php if (empty($attributes)): ?>
            <tr>
              <td colspan="9" class="text-center text-muted">No attribute data available.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($attributes as $attribute): ?>
              <tr>
                <td class="text-center"><?= $startIndex++ ?></td>
                <td><?= $attribute['attribute_name'] ?></td>
                <td>
                  <span class="badge bg-info"><?= ucfirst($attribute['attribute_type']) ?></span>
                </td>
                <td><?= htmlspecialchars($attribute['category_name']) ?></td>
                <td class="text-center">
                  <?= $attribute['is_variantable'] ?
                    '<span class="badge bg-success"><i class="fas fa-check"></i></span>' :
                    '<span class="badge bg-secondary"><i class="fas fa-times"></i></span>'
                  ?>
                </td>
                <td class="text-center">
                  <?= $attribute['is_required'] ?
                    '<span class="badge bg-success"><i class="fas fa-check"></i></span>' :
                    '<span class="badge bg-secondary"><i class="fas fa-times"></i></span>'
                  ?>
                </td>
                <td class="text-center">
                  <?= $attribute['is_filterable'] ?
                    '<span class="badge bg-success"><i class="fas fa-check"></i></span>' :
                    '<span class="badge bg-secondary"><i class="fas fa-times"></i></span>'
                  ?>
                </td>
                <td class="text-center">
                  <?= $attribute['use_master_values'] ?
                    '<span class="badge bg-success"><i class="fas fa-check"></i></span>' :
                    '<span class="badge bg-secondary"><i class="fas fa-times"></i></span>'
                  ?>
                </td>
                <td class="sticky-action text-center">
                  <a href="<?= base_url('/product-attribute/form?id=' . $attribute['attribute_id']) ?>" class="btn btn-sm btn-warning">
                    <i class="fa-solid fa-pen-to-square"></i>
                  </a>
                  <form action="<?= base_url('/product-attribute/delete/' . $attribute['attribute_id']) ?>" method="post" style="display:inline-block;">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="fa-solid fa-trash"></i></button>
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
    window.location.replace(`<?php echo base_url(); ?>product-attribute?page=${pageNumber}`);
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
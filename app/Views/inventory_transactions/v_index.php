<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>

<?php
$referenceBadges = [
  'order'      => 'primary',
  'adjustment' => 'warning',
  'return'     => 'info',
  'transfer'   => 'secondary',
  'initial'    => 'dark',
];
?>

<div class="container-fluid card">
  <div class="card-header mb-4 pb-0 d-flex align-items-center justify-content-between">
    <h4>Inventory Transactions List</h4>

    <div class="d-flex align-items-center gap-2">
      <form action="<?= base_url('/inventory') ?>" method="get" class="d-flex align-items-center">
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

      <a href="<?= base_url('/inventory/form') ?>" class="btn btn-sm btn-primary"> <i class="fas fa-plus"></i> Add Inventory</a>
    </div>
  </div>

  <div class="card-body pt-0 pb-2">
    <div class="table-responsive">
      <table class="table align-items-center mb-0 table-bordered">
        <thead>
          <tr>
            <th class="text-center">No</th>
            <th>Product SKU</th>
            <th>Product</th>
            <th>Variant</th>
            <th>Transaction Type</th>
            <th>Reference Type</th>
            <th>Reference Id</th>
            <th>Quantity</th>
            <th>Description</th>
            <th>Transaction Date</th>
            <th class="sticky-action text-center">Actions</th>
          </tr>
        </thead>
        <tbody id="realtime-tbody">
          <?php $startIndex = ($pager["currentPage"] - 1) * $pager["limit"] + 1; ?>

          <?php if (empty($inventory_transactions)): ?>
            <tr>
              <td colspan="8" class="text-center text-muted">No inventory transactions available.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($inventory_transactions as $inventory_transaction): ?>
              <tr>
                <td class="text-center"><?= $startIndex++ ?></td>
                <td><?= esc($inventory_transaction['product_sku']) ?></td>
                <td><?= esc($inventory_transaction['product_name']) ?></td>
                <td><?= esc($inventory_transaction['variant_name'] ?: '-') ?></td>
                <td>
                  <?php if (strtolower($inventory_transaction['transaction_type']) === 'in') : ?>
                    <span class="badge bg-success">IN</span>
                  <?php else : ?>
                    <span class="badge bg-danger">OUT</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php
                  $refType  = strtolower($inventory_transaction['reference_type'] ?? '');
                  $badgeCls = $referenceBadges[$refType] ?? 'light';
                  ?>
                  <span class="badge bg-<?= $badgeCls ?>">
                    <?= strtoupper(esc($refType ?: 'N/A')) ?>
                  </span>
                </td>
                <td>
                  <?= esc($inventory_transaction['reference_id'] ?: '-') ?>
                </td>
                <td><?= $inventory_transaction['quantity'] ?></td>
                <td><?= esc($inventory_transaction['description'] ?: '-') ?></td>
                <td><?= date('d/m/Y H:i', strtotime($inventory_transaction['transaction_date'])) ?></td>
                <td class="sticky-action text-center">
                  <a href="<?= base_url('/inventory/form?id=' . $inventory_transaction['inventory_transaction_id']) ?>"
                    class="btn btn-sm btn-warning"
                    title="Edit">
                    <i class="fa-solid fa-pen-to-square"></i>
                  </a>

                  <form action="<?= base_url('/inventory/delete/' . $inventory_transaction['inventory_transaction_id']) ?>"
                    method="post"
                    class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit"
                      class="btn btn-sm btn-danger"
                      title="Delete"
                      onclick="return confirm('Are you sure?')">
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
      <ul class="pagination" id="realtime-pagination">
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
    window.location.replace(`<?php echo base_url(); ?>inventory?${params.toString()}`);
  }

  var paginationContainer = document.getElementById('realtime-pagination');
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
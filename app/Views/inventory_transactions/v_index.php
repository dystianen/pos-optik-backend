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

$refLabels = [
  'order'      => 'Order',
  'adjustment' => 'Adjustment',
  'return'     => 'Return',
  'transfer'   => 'Transfer',
  'initial'    => 'Initial Stock',
];
?>

<div class="container-fluid card">
  <div class="card-header mb-4 pb-0 d-flex align-items-center justify-content-between">
    <h4>Inventory Transactions List</h4>

    <a href="<?= base_url('/inventory/form') ?>" class="btn btn-sm btn-primary"> <i class="fas fa-plus"></i> Add Transaction</a>
  </div>

  <div class="card-body pt-0 pb-2">
    <!-- Filter Form -->
    <form action="<?= base_url('/inventory') ?>" method="get" class="row g-2 mb-4 align-items-end">
      <div class="col-lg col-md-6 col-12">
        <label class="form-label text-xs font-weight-bold">Search Product / SKU</label>
        <input
          type="text"
          name="search"
          class="form-control form-control-sm"
          placeholder="Search SKU, product name..."
          value="<?= esc($search ?? '') ?>">
      </div>
      <div class="col-lg col-md-6 col-6">
        <label class="form-label text-xs font-weight-bold">Transaction Type</label>
        <select name="transaction_type" class="form-select form-select-sm">
          <option value="">All</option>
          <option value="in" <?= ($transactionType ?? '') === 'in' ? 'selected' : '' ?>>IN</option>
          <option value="out" <?= ($transactionType ?? '') === 'out' ? 'selected' : '' ?>>OUT</option>
        </select>
      </div>
      <div class="col-lg col-md-6 col-6">
        <label class="form-label text-xs font-weight-bold">Reference Type</label>
        <select name="reference_type" class="form-select form-select-sm">
          <option value="">All</option>
          <option value="order" <?= ($referenceType ?? '') === 'order' ? 'selected' : '' ?>>Order</option>
          <option value="adjustment" <?= ($referenceType ?? '') === 'adjustment' ? 'selected' : '' ?>>Adjustment</option>
          <option value="return" <?= ($referenceType ?? '') === 'return' ? 'selected' : '' ?>>Return</option>
          <option value="transfer" <?= ($referenceType ?? '') === 'transfer' ? 'selected' : '' ?>>Transfer</option>
          <option value="initial" <?= ($referenceType ?? '') === 'initial' ? 'selected' : '' ?>>Initial Stock</option>
        </select>
      </div>
      <div class="col-lg col-md-6 col-6">
        <label class="form-label text-xs font-weight-bold">Start Date</label>
        <input
          type="date"
          name="start_date"
          class="form-control form-control-sm"
          value="<?= esc($startDate ?? '') ?>">
      </div>
      <div class="col-lg col-md-6 col-6">
        <label class="form-label text-xs font-weight-bold">End Date</label>
        <input
          type="date"
          name="end_date"
          class="form-control form-control-sm"
          value="<?= esc($endDate ?? '') ?>">
      </div>
      <div class="col-lg-auto col-md-12 col-12 d-flex gap-2 mt-lg-0 mt-3">
        <button type="submit" class="btn btn-sm btn-primary mb-0 d-flex align-items-center justify-content-center gap-1" style="height: 31px;" title="Filter">
          <i class="fa-solid fa-filter"></i> <span>Filter</span>
        </button>
        <a href="<?= base_url('/inventory') ?>" class="btn btn-sm btn-outline-secondary mb-0 d-flex align-items-center justify-content-center gap-1" style="height: 31px;" title="Reset">
          <i class="fa-solid fa-arrows-rotate"></i> <span>Reset</span>
        </a>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table align-items-center mb-0 table-bordered">
        <thead>
          <tr>
            <th class="text-center">No</th>
            <th>Product SKU</th>
            <th>Product</th>
            <th>Variant</th>
            <th>Type</th>
            <th>Reference</th>
            <th>Quantity</th>
            <th>User</th>
            <th>Description</th>
            <th>Date</th>
            <th class="sticky-action text-center">Actions</th>
          </tr>
        </thead>
        <tbody id="realtime-tbody">
          <?php $startIndex = ($pager["currentPage"] - 1) * $pager["limit"] + 1; ?>

          <?php if (empty($inventory_transactions)): ?>
            <tr>
              <td colspan="11" class="text-center text-muted">No inventory transactions available.</td>
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
                  $refLabel = $refLabels[$refType] ?? strtoupper($refType ?: 'N/A');
                  ?>
                  <span class="badge bg-<?= $badgeCls ?>">
                    <?= esc($refLabel) ?>
                  </span>
                  <?php if (!empty($inventory_transaction['reference_id'])): ?>
                    <div class="text-xs text-muted mt-1">#<?= esc($inventory_transaction['reference_id']) ?></div>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (strtolower($inventory_transaction['transaction_type']) === 'in') : ?>
                    <span class="text-success font-weight-bold">+<?= $inventory_transaction['quantity'] ?></span>
                  <?php else : ?>
                    <span class="text-danger font-weight-bold">-<?= $inventory_transaction['quantity'] ?></span>
                  <?php endif; ?>
                </td>
                <td><?= esc($inventory_transaction['user_name'] ?? 'System') ?></td>
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
                    class="d-inline confirm-delete">
                    <?= csrf_field() ?>
                    <button type="submit"
                      class="btn btn-sm btn-danger"
                      title="Delete">
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
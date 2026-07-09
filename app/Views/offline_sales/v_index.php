<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>

<div class="container-fluid card">
  <div class="card-header mb-4 pb-0 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
    <h4>Offline Sales List</h4>
    <a href="<?= base_url('/offline-sales/create') ?>"
      class="btn btn-primary btn-sm mb-0">
      <i class="fas fa-plus"></i> Add Sales
    </a>
  </div>

  <div class="card-body pt-0 pb-2">
    <!-- Filter Form -->
    <form action="<?= base_url('/offline-sales') ?>" method="get" class="row g-2 mb-4 align-items-end">
      <div class="col-md-4">
        <label class="form-label text-xs font-weight-bold">Search Order</label>
        <input
          type="text"
          name="q"
          class="form-control form-control-sm"
          placeholder="Search Order ID, customer..."
          value="<?= esc($search ?? '') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label text-xs font-weight-bold">Start Date</label>
        <input
          type="date"
          name="start_date"
          class="form-control form-control-sm"
          value="<?= esc($startDate ?? '') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label text-xs font-weight-bold">End Date</label>
        <input
          type="date"
          name="end_date"
          class="form-control form-control-sm"
          value="<?= esc($endDate ?? '') ?>">
      </div>
      <div class="col-md-2 d-flex gap-2">
        <button type="submit" class="btn btn-sm btn-primary w-100 d-flex align-items-center justify-content-center gap-1" style="height: 31px;" title="Filter">
          <i class="fa-solid fa-filter"></i> <span>Filter</span>
        </button>
        <a href="<?= base_url('/offline-sales') ?>" class="btn btn-sm btn-outline-secondary w-100 mb-0 d-flex align-items-center justify-content-center gap-1" style="height: 31px;" title="Reset">
          <i class="fa-solid fa-arrows-rotate"></i> <span>Reset</span>
        </a>
      </div>
    </form>
    <div class="table-responsive">
      <table class="table align-items-center mb-0 table-bordered">
        <thead>
          <tr>
            <th class="text-center">No</th>
            <th>Order ID</th>
            <th>Status</th>
            <th>Order Date</th>
            <th>Customer</th>
            <th>Total Item</th>
            <th>Grand Total</th>
            <th class="sticky-action text-center">Actions</th>
          </tr>
        </thead>
        <tbody id="realtime-tbody">

          <?php
          $startIndex = ($pager['currentPage'] - 1) * $pager['limit'] + 1;
          ?>

          <?php if (empty($orders)): ?>
            <tr>
              <td colspan="8" class="text-center text-muted">
                Tidak ada transaksi
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($orders as $order): ?>
              <tr>
                <td class="text-center"><?= $startIndex++ ?></td>
                <td>
                  <strong>#<?= $order['order_id'] ?></strong>
                </td>
                <td>
                  <span class="badge bg-success">
                    <?= esc($order['status_name']) ?>
                  </span>
                </td>
                <td>
                  <?= date('d M Y H:i', strtotime($order['created_at'])) ?>
                </td>
                <td>
                  <div class="d-flex flex-column">
                    <strong><?= esc($order['customer_name']) ?></strong>
                    <small class="text-muted"><?= esc($order['customer_email']) ?></small>
                  </div>
                </td>
                <td><?= $order['total_items'] ?></td>
                <td>
                  Rp <?= number_format($order['grand_total'], 0, ',', '.') ?>
                </td>
                <td class="sticky-action text-center">
                  <a href="<?= base_url('/offline-sales/' . $order['order_id']) ?>"
                    class="btn btn-sm btn-info">
                    <i class="fa-solid fa-eye"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach ?>
          <?php endif ?>

        </tbody>
      </table>
    </div>

    <!-- PAGINATION -->
    <nav aria-label="Page navigation" class="mt-4">
      <ul class="pagination" id="realtime-pagination"></ul>
    </nav>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  function handlePagination(page) {
    const params = new URLSearchParams(window.location.search);
    params.set('page', page);
    window.location.replace(`<?php echo base_url(); ?>offline-sales?${params.toString()}`);
  }

  const paginationContainer = document.getElementById('realtime-pagination');
  const totalPages = <?= (int) $pager['totalPages'] ?>;
  const currentPage = <?= (int) $pager['currentPage'] ?>;

  if (totalPages > 1) {
    for (let i = 1; i <= totalPages; i++) {
      const li = document.createElement('li');
      li.className = 'page-item' + (i === currentPage ? ' active' : '');

      const a = document.createElement('a');
      a.className = 'page-link';
      a.href = 'javascript:void(0)';
      a.innerText = i;

      a.onclick = () => handlePagination(i);

      li.appendChild(a);
      paginationContainer.appendChild(li);
    }
  }
</script>
<?= $this->endSection() ?>
<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>

<?php
function orderStatusBadge($status)
{
  return match (strtolower($status)) {
    'pending'    => 'badge bg-warning',
    'paid'       => 'badge bg-info',
    'processing' => 'badge bg-primary',
    'shipped'    => 'badge bg-secondary',
    'completed'  => 'badge bg-success',
    'cancelled'  => 'badge bg-danger',
    'expired'    => 'badge bg-dark',
    default      => 'badge bg-light text-dark',
  };
}
?>

<div class="container-fluid card">
  <div class="card-header mb-4 pb-0 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
    <h4>Online Sales List</h4>
  </div>

  <div class="card-body pt-0 pb-2">
    <!-- Filter Form -->
    <form action="<?= base_url('/online-sales') ?>" method="get" class="row g-2 mb-4 align-items-end">
      <div class="col-lg-3 col-md-6 col-12">
        <label class="form-label text-xs font-weight-bold">Search Order</label>
        <input
          type="text"
          name="q"
          class="form-control form-control-sm"
          placeholder="Search Order ID, customer..."
          value="<?= esc($search ?? '') ?>">
      </div>
      <div class="col-lg-2 col-md-6 col-12">
        <label class="form-label text-xs font-weight-bold">Status</label>
        <select name="status_id" class="form-select form-select-sm">
          <option value="">All Statuses</option>
          <?php foreach ($statuses as $st): ?>
            <option value="<?= $st['status_id'] ?>" <?= ($selectedStatusId ?? '') == $st['status_id'] ? 'selected' : '' ?>>
              <?= esc(strtoupper($st['status_name'])) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-lg-2 col-md-4 col-6">
        <label class="form-label text-xs font-weight-bold">Start Date</label>
        <input
          type="date"
          name="start_date"
          class="form-control form-control-sm"
          value="<?= esc($startDate ?? '') ?>">
      </div>
      <div class="col-lg-2 col-md-4 col-6">
        <label class="form-label text-xs font-weight-bold">End Date</label>
        <input
          type="date"
          name="end_date"
          class="form-control form-control-sm"
          value="<?= esc($endDate ?? '') ?>">
      </div>
      <div class="col-lg-3 col-md-12 col-12 d-flex gap-2 mt-lg-0 mt-3">
        <button type="submit" class="btn btn-sm btn-primary mb-0 d-flex align-items-center justify-content-center gap-1" style="height: 31px;" title="Filter">
          <i class="fa-solid fa-filter"></i> <span>Filter</span>
        </button>
        <a href="<?= base_url('/online-sales') ?>" class="btn btn-sm btn-outline-secondary mb-0 d-flex align-items-center justify-content-center gap-1" style="height: 31px;" title="Reset">
          <i class="fa-solid fa-arrows-rotate"></i> <span>Reset</span>
        </a>
      </div>
    </form>
    <div class="table-responsive">
      <table class="table align-items-center mb-0 table-bordered">
        <thead class="thead-light">
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
          <?php foreach ($orders as $order): ?>
            <tr>
              <td class="text-center"><?= $startIndex++ ?></td>
              <td>
                <strong>#<?= $order['order_id'] ?></strong>
              </td>

              <td>
                <span class="<?= orderStatusBadge($order['status_code']) ?>">
                  <?= strtoupper($order['status_name']) ?>
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
                <strong>Rp <?= number_format($order['grand_total']) ?></strong>
              </td>

              <td class="sticky-action text-center">
                <a href="<?= base_url('/online-sales/' . $order['order_id']) ?>"
                  class="btn btn-sm btn-info">
                  <i class="fa-solid fa-eye"></i>
                </a>
              </td>
            </tr>
          <?php endforeach ?>
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
  function handlePagination(page) {
    const params = new URLSearchParams(window.location.search);
    params.set('page', page);
    window.location.replace(`<?php echo base_url(); ?>online-sales?${params.toString()}`);
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
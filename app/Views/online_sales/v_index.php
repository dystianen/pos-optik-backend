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
    default      => 'badge bg-light text-dark',
  };
}
?>

<div class="container-fluid card">
  <div class="card-header mb-4 pb-0 d-flex align-items-center justify-content-between">
    <h4>Online Sales</h4>

    <div class="d-flex align-items-center gap-2">
      <form action="<?= base_url('/online-sales') ?>" method="get" class="d-flex align-items-center">
        <input
          type="text"
          name="q"
          class="form-control form-control-sm me-2"
          placeholder="Search..."
          value="<?= esc($search ?? '') ?>"
          style="min-width: 200px;">
        <button type="submit" class="btn btn-sm btn-secondary">
          <i class="fa-solid fa-magnifying-glass"></i>
        </button>
      </form>
      <a href="<?= base_url('online-sales/export' . (!empty($search) ? '?q=' . $search : '')) ?>"
        class="btn btn-success btn-sm">
        <i class="fas fa-file-excel"></i> Export Excel
      </a>
    </div>
  </div>

  <div class="card-body pt-0 pb-2">
    <div class="table-responsive">
      <table class="table align-items-center mb-0 table-bordered">
        <thead class="thead-light">
          <tr>
            <th class="text-center">No</th>
            <th>Order ID</th>
            <th>Order Date</th>
            <th>Customer</th>
            <th>Total Item</th>
            <th>Grand Total</th>
            <th>Status</th>
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
                <?= date('d M Y', strtotime($order['created_at'])) ?>
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

              <td>
                <span class="<?= orderStatusBadge($order['status_code']) ?>">
                  <?= strtoupper($order['status_name']) ?>
                </span>
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
    window.location.href =
      `<?= base_url('/online-sales') ?>?page=${page}&q=<?= esc($search) ?>`;
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
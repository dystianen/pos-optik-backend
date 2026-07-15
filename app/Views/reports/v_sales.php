<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>

<?php

/**
 * @var string $category
 * @var string $startDate
 * @var string $endDate
 * @var array $summary
 */
?>
<?php $request = service('request'); ?>

<div class="container-fluid p-0">

  <!-- FILTER CARD -->
  <div class="card mb-4 border-0 shadow-sm" style="border-radius: 16px;">
    <div class="card-header pb-2 bg-transparent border-0 d-flex justify-content-between align-items-center">
      <h5 class="mb-0 font-weight-bolder">Sales Report</h5>

      <!-- EXPORT ACTIONS -->
      <div class="d-flex align-items-center gap-2">
        <a href="<?= base_url('/reports/sales/export?category=' . $category . '&status=' . $status . '&start_date=' . $startDate . '&end_date=' . $endDate . '&format=excel') ?>"
          class="btn btn-sm btn-success mb-0 d-flex align-items-center gap-2"
          style="border-radius: 8px;">
          <i class="fa-solid fa-file-excel"></i>
          <span>Excel</span>
        </a>
        <a href="<?= base_url('/reports/sales/export?category=' . $category . '&status=' . $status . '&start_date=' . $startDate . '&end_date=' . $endDate . '&format=pdf') ?>"
          class="btn btn-sm btn-danger mb-0 d-flex align-items-center gap-2"
          style="border-radius: 8px;">
          <i class="fa-solid fa-file-pdf"></i>
          <span>PDF</span>
        </a>
      </div>
    </div>

    <div class="card-body">
      <!-- Form Filters -->
      <form action="<?= base_url('/reports/sales') ?>" method="get" class="row g-3 align-items-end">
        <div class="col-lg-3 col-md-4 col-12">
          <label class="form-label font-weight-bold text-xs text-uppercase">Sales Category</label>
          <select name="category" class="form-select form-select-sm" style="border-radius: 8px;">
            <option value="all" <?= $category === 'all' ? 'selected' : '' ?>>All Sales</option>
            <option value="online" <?= $category === 'online' ? 'selected' : '' ?>>Online Sales</option>
            <option value="offline" <?= $category === 'offline' ? 'selected' : '' ?>>Offline Sales</option>
            <option value="refund" <?= $category === 'refund' ? 'selected' : '' ?>>Refund Sales</option>
            <option value="cancellation" <?= $category === 'cancellation' ? 'selected' : '' ?>>Cancellation Sales</option>
          </select>
        </div>
        <div class="col-lg-2 col-md-4 col-12">
          <label class="form-label font-weight-bold text-xs text-uppercase">Status</label>
          <select name="status" id="status-filter" class="form-select form-select-sm" style="border-radius: 8px;">
            <!-- Will be populated dynamically via JavaScript -->
          </select>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
          <label class="form-label font-weight-bold text-xs text-uppercase">From Date</label>
          <input type="date" name="start_date" class="form-control form-control-sm" style="border-radius: 8px;" value="<?= esc($startDate) ?>">
        </div>
        <div class="col-lg-2 col-md-4 col-6">
          <label class="form-label font-weight-bold text-xs text-uppercase">To Date</label>
          <input type="date" name="end_date" class="form-control form-control-sm" style="border-radius: 8px;" value="<?= esc($endDate) ?>">
        </div>
        <div class="col-lg-2 col-md-6 col-12 d-flex gap-2 mt-lg-0 mt-3">
          <button type="submit" class="btn btn-sm btn-primary mb-0 d-flex align-items-center justify-content-center gap-1 w-100" style="height: 31px; border-radius: 8px;" title="Filter">
            <i class="fa-solid fa-filter"></i> <span>Filter</span>
          </button>
          <a href="<?= base_url('/reports/sales') ?>" class="btn btn-sm btn-outline-secondary mb-0 d-flex align-items-center justify-content-center gap-1 w-100" style="height: 31px; border-radius: 8px;" title="Reset">
            <i class="fa-solid fa-arrows-rotate"></i> <span>Reset</span>
          </a>
        </div>
      </form>
    </div>
  </div>

  <!-- KPI SUMMARY CARDS -->
  <div class="row g-3 mb-4">
    <!-- Net Revenue -->
    <div class="col-xl-3 col-sm-6">
      <div class="card border-0 shadow-sm" style="border-radius: 16px; border-left: 5px solid #2dce89 !important;">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-xs mb-0 text-uppercase font-weight-bold text-muted">Net Income</p>
                <h4 class="font-weight-bolder mb-0 mt-1 text-success" style="font-size: 18px;">
                  Rp <?= number_format($summary['completed_revenue'], 0, ',', '.') ?>
                </h4>
                <small class="text-xs text-muted">
                  <strong><?= number_format($summary['completed_count'], 0, ',', '.') ?></strong> orders (<?= number_format($summary['completed_items'], 0, ',', '.') ?> items)
                </small>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle" style="width: 44px; height: 44px; display: inline-flex; align-items: center; justify-content: center; background-image: linear-gradient(310deg, #2dce89 0%, #2dcc89 100%);">
                <i class="fa-solid fa-money-bill-trend-up text-white d-flex align-items-center justify-content-center" style="font-size: 18px; top: 0px !important;"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Cancelled Sales -->
    <div class="col-xl-3 col-sm-6">
      <div class="card border-0 shadow-sm" style="border-radius: 16px; border-left: 5px solid #f5365c !important;">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-xs mb-0 text-uppercase font-weight-bold text-muted">Cancelled Sales</p>
                <h4 class="font-weight-bolder mb-0 mt-1 text-danger" style="font-size: 18px;">
                  Rp <?= number_format($summary['cancelled_revenue'], 0, ',', '.') ?>
                </h4>
                <small class="text-xs text-muted">
                  <strong><?= number_format($summary['cancelled_count'], 0, ',', '.') ?></strong> orders (<?= number_format($summary['cancelled_items'], 0, ',', '.') ?> items)
                </small>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-danger shadow-danger text-center rounded-circle" style="width: 44px; height: 44px; display: inline-flex; align-items: center; justify-content: center; background-image: linear-gradient(310deg, #f5365c 0%, #f5368c 100%);">
                <i class="fa-solid fa-ban text-white d-flex align-items-center justify-content-center" style="font-size: 18px; top: 0px !important;"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Refunded Sales -->
    <div class="col-xl-3 col-sm-6">
      <div class="card border-0 shadow-sm" style="border-radius: 16px; border-left: 5px solid #fb6340 !important;">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-xs mb-0 text-uppercase font-weight-bold text-muted">Refunded Sales</p>
                <h4 class="font-weight-bolder mb-0 mt-1 text-warning" style="font-size: 18px;">
                  Rp <?= number_format($summary['refunded_revenue'], 0, ',', '.') ?>
                </h4>
                <small class="text-xs text-muted">
                  <strong><?= number_format($summary['refunded_count'], 0, ',', '.') ?></strong> orders (<?= number_format($summary['refunded_items'], 0, ',', '.') ?> items)
                </small>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle" style="width: 44px; height: 44px; display: inline-flex; align-items: center; justify-content: center; background-image: linear-gradient(310deg, #fb6340 0%, #fbb140 100%);">
                <i class="fa-solid fa-rotate-left text-white d-flex align-items-center justify-content-center" style="font-size: 18px; top: 0px !important;"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Total Gross Revenue -->
    <div class="col-xl-3 col-sm-6">
      <div class="card border-0 shadow-sm" style="border-radius: 16px; border-left: 5px solid #11cdef !important;">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-xs mb-0 text-uppercase font-weight-bold text-muted">Gross Revenue (Total)</p>
                <h4 class="font-weight-bolder mb-0 mt-1 text-info" style="font-size: 18px;">
                  Rp <?= number_format($summary['total_revenue'], 0, ',', '.') ?>
                </h4>
                <small class="text-xs text-muted">
                  <strong><?= number_format($summary['total_transactions'], 0, ',', '.') ?></strong> total orders (<?= number_format($summary['total_items'], 0, ',', '.') ?> items)
                </small>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-info shadow-info text-center rounded-circle" style="width: 44px; height: 44px; display: inline-flex; align-items: center; justify-content: center; background-image: linear-gradient(310deg, #11cdef 0%, #1193ef 100%);">
                <i class="fa-solid fa-chart-bar text-white d-flex align-items-center justify-content-center" style="font-size: 18px; top: 0px !important;"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- DETAILS TABLE CARD -->
  <div class="card border-0 shadow-sm" style="border-radius: 16px;">
    <div class="card-header pb-0 bg-transparent border-0">
      <h6 class="mb-0 font-weight-bold">Sales Transaction Details</h6>
      <p class="text-xs text-muted mb-0">List of transactions based on active filter criteria</p>
    </div>

    <div class="card-body pt-3">
      <div class="table-responsive">
        <table class="table align-items-center mb-0 table-hover table-bordered">
          <thead class="bg-light">
            <tr>
              <th>No</th>
              <th>Transaction ID</th>
              <th>Date</th>
              <th>Category</th>
              <th>Customer</th>
              <th>Total Items</th>
              <th>Grand Total</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($orders)): ?>
              <tr>
                <td colspan="8" class="text-center py-4 text-muted font-weight-bold">
                  No transaction data found for this period.
                </td>
              </tr>
            <?php else: ?>
              <?php $startIndex = ($pager["currentPage"] - 1) * $pager["limit"] + 1;
              foreach ($orders as $order): ?>
                <tr>
                  <td class="text-center font-weight-bold"><?= $startIndex++ ?></td>
                  <td class="font-weight-bold text-dark">
                    <strong>#<?= $order['order_id'] ?></strong>
                  </td>
                  <td class="text-muted">
                    <?= date('d M Y H:i', strtotime($order['created_at'])) ?>
                  </td>
                  <td>
                    <?php
                    $badgeClass = match ($order['order_type']) {
                      'online' => 'bg-info',
                      'offline' => 'bg-secondary',
                      'refund' => 'bg-warning text-dark',
                      'cancellation' => 'bg-danger text-white',
                      default => 'bg-light text-dark'
                    };
                    ?>
                    <span class=" badge badge-sm <?= $badgeClass ?>" style="text-transform: uppercase;">
                      <?= $order['order_type'] ?>
                    </span>
                  </td>
                  <td>
                    <div class="d-flex flex-column">
                      <strong><?= esc($order['customer_name']) ?></strong>
                      <small class="text-muted"><?= esc($order['customer_email']) ?></small>
                    </div>
                  </td>
                  <td class="text-center font-weight-bold">
                    <?= $order['total_items'] ?>
                  </td>
                  <td class="text-end font-weight-bold text-dark">
                    Rp <?= number_format($order['grand_total'], 0, ',', '.') ?>
                  </td>
                  <td class="text-center">
                    <?php
                    $statusColor = match (strtolower($order['status_name'] ?? '')) {
                      'approved', 'refunded', 'completed' => 'bg-success',
                      'pending', 'requested', 'processing', 'return_approved', 'return_shipped', 'return_received' => 'bg-warning text-dark',
                      'rejected', 'cancelled', 'request_rejected', 'return_rejected', 'payment expired', 'expired' => 'bg-danger',
                      default => 'bg-light text-dark'
                    };
                    ?>
                    <span class="badge badge-sm <?= $statusColor ?>" style="font-size: 10px;">
                      <?= strtoupper(esc((string) ($order['status_name'] ?? 'Completed'))) ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination mb-0" id="realtime-pagination"></ul>
      </nav>
    </div>
  </div>
</div>

<?= $this->section('scripts') ?>
<script type="text/javascript">
  // PAGINATION
  function handlePagination(page) {
    const params = new URLSearchParams(window.location.search);
    params.set('page', page);
    window.location.replace(`<?php echo base_url(); ?>reports/sales?${params.toString()}`);
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

  // DYNAMIC STATUS OPTION POPULATION BASED ON SELECTED CATEGORY
  const statusOptions = {
    all: [
      { value: 'all', text: 'All Statuses' },
      { value: 'pending', text: 'Pending Payment' },
      { value: 'waiting_confirmation', text: 'Waiting Payment Confirmation' },
      { value: 'processing', text: 'Order Processing' },
      { value: 'shipped', text: 'Shipped to Courier' },
      { value: 'completed', text: 'Order Completed' },
      { value: 'cancelled', text: 'Order Cancelled' },
      { value: 'refunded', text: 'Order Refunded' },
      { value: 'partially_refunded', text: 'Partially Refunded' },
      { value: 'rejected', text: 'Payment Rejected' },
      { value: 'expired', text: 'Payment Expired' }
    ],
    online: [
      { value: 'all', text: 'All Statuses' },
      { value: 'pending', text: 'Pending Payment' },
      { value: 'waiting_confirmation', text: 'Waiting Payment Confirmation' },
      { value: 'processing', text: 'Order Processing' },
      { value: 'shipped', text: 'Shipped to Courier' },
      { value: 'completed', text: 'Order Completed' },
      { value: 'cancelled', text: 'Order Cancelled' },
      { value: 'refunded', text: 'Order Refunded' },
      { value: 'partially_refunded', text: 'Partially Refunded' },
      { value: 'rejected', text: 'Payment Rejected' },
      { value: 'expired', text: 'Payment Expired' }
    ],
    offline: [
      { value: 'all', text: 'All Statuses' },
      { value: 'pending', text: 'Pending Payment' },
      { value: 'waiting_confirmation', text: 'Waiting Payment Confirmation' },
      { value: 'processing', text: 'Order Processing' },
      { value: 'shipped', text: 'Shipped to Courier' },
      { value: 'completed', text: 'Order Completed' },
      { value: 'cancelled', text: 'Order Cancelled' },
      { value: 'refunded', text: 'Order Refunded' },
      { value: 'partially_refunded', text: 'Partially Refunded' },
      { value: 'rejected', text: 'Payment Rejected' },
      { value: 'expired', text: 'Payment Expired' }
    ],
    refund: [
      { value: 'all', text: 'All Statuses' },
      { value: 'requested', text: 'Requested' },
      { value: 'request_rejected', text: 'Request Rejected' },
      { value: 'return_approved', text: 'Return Approved' },
      { value: 'return_shipped', text: 'Return Shipped' },
      { value: 'return_received', text: 'Return Received' },
      { value: 'return_rejected', text: 'Return Rejected' },
      { value: 'approved', text: 'Approved' },
      { value: 'refunded', text: 'Refunded' },
      { value: 'expired', text: 'Expired' }
    ],
    cancellation: [
      { value: 'all', text: 'All Statuses' },
      { value: 'pending', text: 'Pending' },
      { value: 'processing', text: 'Processing' },
      { value: 'approved', text: 'Approved' },
      { value: 'rejected', text: 'Rejected' }
    ]
  };

  const categorySelect = document.querySelector('select[name="category"]');
  const statusSelect = document.getElementById('status-filter');
  const currentStatus = <?= json_encode($status) ?>;

  function updateStatusOptions() {
    const category = categorySelect.value;
    const options = statusOptions[category] || statusOptions['all'];
    
    // Clear current options
    statusSelect.innerHTML = '';
    
    // Populate new options
    options.forEach(opt => {
      const option = document.createElement('option');
      option.value = opt.value;
      option.text = opt.text;
      if (opt.value === currentStatus) {
        option.selected = true;
      }
      statusSelect.appendChild(option);
    });
  }

  // Update on category change
  categorySelect.addEventListener('change', updateStatusOptions);

  // Initialize on load
  updateStatusOptions();
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
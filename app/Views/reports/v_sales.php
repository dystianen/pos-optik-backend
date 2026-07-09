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
        <a href="<?= base_url('/reports/sales/export?category=' . $category . '&start_date=' . $startDate . '&end_date=' . $endDate . '&format=excel') ?>"
          class="btn btn-sm btn-success mb-0 d-flex align-items-center gap-2"
          style="border-radius: 8px;">
          <i class="fa-solid fa-file-excel"></i>
          <span>Excel</span>
        </a>
        <a href="<?= base_url('/reports/sales/export?category=' . $category . '&start_date=' . $startDate . '&end_date=' . $endDate . '&format=pdf') ?>"
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
        <div class="col-md-4">
          <label class="form-label font-weight-bold text-xs text-uppercase">Sales Category</label>
          <select name="category" class="form-select form-select-sm" style="border-radius: 8px;">
            <option value="all" <?= $category === 'all' ? 'selected' : '' ?>>All Sales</option>
            <option value="online" <?= $category === 'online' ? 'selected' : '' ?>>Online Sales</option>
            <option value="offline" <?= $category === 'offline' ? 'selected' : '' ?>>Offline Sales</option>
            <option value="refund" <?= $category === 'refund' ? 'selected' : '' ?>>Refund Sales</option>
            <option value="cancellation" <?= $category === 'cancellation' ? 'selected' : '' ?>>Cancellation Sales</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label font-weight-bold text-xs text-uppercase">From Date</label>
          <input type="date" name="start_date" class="form-control form-control-sm" style="border-radius: 8px;" value="<?= esc($startDate) ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label font-weight-bold text-xs text-uppercase">To Date</label>
          <input type="date" name="end_date" class="form-control form-control-sm" style="border-radius: 8px;" value="<?= esc($endDate) ?>">
        </div>
        <div class="col-md-2 d-flex gap-2">
          <button type="submit" class="btn btn-sm btn-primary w-100 d-flex align-items-center justify-content-center gap-1" style="height: 31px; border-radius: 8px;" title="Filter">
            <i class="fa-solid fa-filter"></i> <span>Filter</span>
          </button>
          <a href="<?= base_url('/reports/sales') ?>" class="btn btn-sm btn-outline-secondary w-100 mb-0 d-flex align-items-center justify-content-center gap-1" style="height: 31px; border-radius: 8px;" title="Reset">
            <i class="fa-solid fa-arrows-rotate"></i> <span>Reset</span>
          </a>
        </div>
      </form>
    </div>
  </div>

  <!-- KPI SUMMARY CARDS -->
  <div class="row g-3 mb-4">
    <!-- Total Revenue -->
    <div class="col-xl-3 col-sm-6">
      <div class="card border-0 shadow-sm" style="border-radius: 16px; border-left: 5px solid #2fb8aa !important;">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-sm mb-0 text-uppercase font-weight-bold text-muted">
                  <?= $category === 'refund' ? 'Total Refund' : ($category === 'cancellation' ? 'Total Cancelled' : 'Total Revenue') ?>
                </p>
                <h4 class="font-weight-bolder mb-0 mt-1" style="font-size: 20px;">
                  Rp <?= number_format($summary['total_revenue'], 0, ',', '.') ?>
                </h4>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle" style="width: 48px; height: 48px; display: inline-flex; align-items: center; justify-content: center; background-image: linear-gradient(310deg, #2fb8aa 0%, #209b8e 100%);">
                <i class="fa-solid fa-money-bill-wave text-white d-flex align-items-center justify-content-center" style="font-size: 20px; top: 0px !important;"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Total Transactions -->
    <div class="col-xl-3 col-sm-6">
      <div class="card border-0 shadow-sm" style="border-radius: 16px; border-left: 5px solid #2dce89 !important;">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-sm mb-0 text-uppercase font-weight-bold text-muted">
                  <?= $category === 'refund' ? 'Total Returns' : ($category === 'cancellation' ? 'Total Cancellations' : 'Total Transactions') ?>
                </p>
                <h4 class="font-weight-bolder mb-0 mt-1" style="font-size: 20px;">
                  <?= number_format($summary['total_transactions'], 0, ',', '.') ?>
                </h4>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle" style="width: 48px; height: 48px; display: inline-flex; align-items: center; justify-content: center; background-image: linear-gradient(310deg, #2dce89 0%, #2dcecc 100%);">
                <i class="fa-solid fa-cart-shopping text-white d-flex align-items-center justify-content-center" style="font-size: 20px; top: 0px !important;"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Total Items Sold -->
    <div class="col-xl-3 col-sm-6">
      <div class="card border-0 shadow-sm" style="border-radius: 16px; border-left: 5px solid #11cdef !important;">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-sm mb-0 text-uppercase font-weight-bold text-muted">
                  <?= $category === 'refund' ? 'Refunded Items' : ($category === 'cancellation' ? 'Cancelled Items' : 'Items Sold') ?>
                </p>
                <h4 class="font-weight-bolder mb-0 mt-1" style="font-size: 20px;">
                  <?= number_format($summary['total_items'], 0, ',', '.') ?>
                </h4>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-info shadow-info text-center rounded-circle" style="width: 48px; height: 48px; display: inline-flex; align-items: center; justify-content: center; background-image: linear-gradient(310deg, #11cdef 0%, #1193ef 100%);">
                <i class="fa-solid fa-box text-white d-flex align-items-center justify-content-center" style="font-size: 20px; top: 0px !important;"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Average Order Value -->
    <div class="col-xl-3 col-sm-6">
      <div class="card border-0 shadow-sm" style="border-radius: 16px; border-left: 5px solid #fb6340 !important;">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-sm mb-0 text-uppercase font-weight-bold text-muted">AVG Transaction</p>
                <h4 class="font-weight-bolder mb-0 mt-1" style="font-size: 20px;">
                  Rp <?= number_format($summary['average_value'], 0, ',', '.') ?>
                </h4>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle" style="width: 48px; height: 48px; display: inline-flex; align-items: center; justify-content: center; background-image: linear-gradient(310deg, #fb6340 0%, #fbb140 100%);">
                <i class="fa-solid fa-calculator text-white d-flex align-items-center justify-content-center" style="font-size: 20px; top: 0px !important;"></i>
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
              <?php $no = 1;
              foreach ($orders as $order): ?>
                <tr>
                  <td class="text-center font-weight-bold"><?= $no++ ?></td>
                  <td class=font-weight-bold text-dark">
                    <strong>#<?= $order['order_id'] ?></strong>
                  </td>
                  <td class=text-muted">
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
    </div>
  </div>
</div>

<?= $this->endSection() ?>
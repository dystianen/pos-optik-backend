<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>

<?php
/**
 * @var string $transactionType
 * @var string $startDate
 * @var string $endDate
 * @var array $summary
 * @var array $transactions
 */
?>
<?php $request = service('request'); ?>

<div class="container-fluid p-0">
  <div class="card mb-4 border-0 shadow-sm" style="border-radius: 16px;">
    <div class="card-header pb-2 bg-transparent border-0 d-flex justify-content-between align-items-center">
      <h5 class="mb-0 font-weight-bolder">Inventory Report</h5>

      <div class="d-flex align-items-center gap-2">
        <a href="<?= base_url('/reports/inventory/export?transaction_type=' . $transactionType . '&start_date=' . $startDate . '&end_date=' . $endDate . '&format=excel') ?>"
          class="btn btn-sm btn-success mb-0 d-flex align-items-center gap-2"
          style="border-radius: 8px;">
          <i class="fa-solid fa-file-excel"></i>
          <span>Excel</span>
        </a>
        <a href="<?= base_url('/reports/inventory/export?transaction_type=' . $transactionType . '&start_date=' . $startDate . '&end_date=' . $endDate . '&format=pdf') ?>"
          class="btn btn-sm btn-danger mb-0 d-flex align-items-center gap-2"
          style="border-radius: 8px;">
          <i class="fa-solid fa-file-pdf"></i>
          <span>PDF</span>
        </a>
      </div>
    </div>

    <div class="card-body">
      <form action="<?= base_url('/reports/inventory') ?>" method="get" class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label font-weight-bold text-xs text-uppercase">Transaction Type</label>
          <select name="transaction_type" class="form-select form-select-sm" style="border-radius: 8px;">
            <option value="all" <?= $transactionType === 'all' ? 'selected' : '' ?>>All Transactions</option>
            <option value="in" <?= $transactionType === 'in' ? 'selected' : '' ?>>IN</option>
            <option value="out" <?= $transactionType === 'out' ? 'selected' : '' ?>>OUT</option>
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
        <div class="col-md-3 d-flex gap-2">
          <button type="submit" class="btn btn-sm btn-secondary mb-0 w-100" style="border-radius: 8px; height: 38px;">
            <i class="fa-solid fa-magnifying-glass me-1"></i> Filter
          </button>
          <a href="<?= base_url('/reports/inventory') ?>" class="btn btn-sm btn-outline-secondary mb-0 w-100 d-flex align-items-center justify-content-center" style="border-radius: 8px; height: 38px;">
            Reset
          </a>
        </div>
      </form>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
      <div class="card border-0 shadow-sm" style="border-radius: 16px; border-left: 5px solid #2fb8aa !important;">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-sm mb-0 text-uppercase font-weight-bold text-muted">Total Transactions</p>
                <h4 class="font-weight-bolder mb-0 mt-1" style="font-size: 20px;">
                  <?= number_format($summary['total_transactions'], 0, ',', '.') ?>
                </h4>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle" style="width: 48px; height: 48px; display: inline-flex; align-items: center; justify-content: center; background-image: linear-gradient(310deg, #2fb8aa 0%, #209b8e 100%);">
                <i class="fa-solid fa-list-check text-white d-flex align-items-center justify-content-center" style="font-size: 20px; top: 0px !important;"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-sm-6">
      <div class="card border-0 shadow-sm" style="border-radius: 16px; border-left: 5px solid #2dce89 !important;">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-sm mb-0 text-uppercase font-weight-bold text-muted">Total IN Quantity</p>
                <h4 class="font-weight-bolder mb-0 mt-1" style="font-size: 20px;">
                  <?= number_format($summary['total_in'], 0, ',', '.') ?>
                </h4>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle" style="width: 48px; height: 48px; display: inline-flex; align-items: center; justify-content: center; background-image: linear-gradient(310deg, #2dce89 0%, #2dcecc 100%);">
                <i class="fa-solid fa-download text-white d-flex align-items-center justify-content-center" style="font-size: 20px; top: 0px !important;"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-sm-6">
      <div class="card border-0 shadow-sm" style="border-radius: 16px; border-left: 5px solid #11cdef !important;">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-sm mb-0 text-uppercase font-weight-bold text-muted">Total OUT Quantity</p>
                <h4 class="font-weight-bolder mb-0 mt-1" style="font-size: 20px;">
                  <?= number_format($summary['total_out'], 0, ',', '.') ?>
                </h4>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-info shadow-info text-center rounded-circle" style="width: 48px; height: 48px; display: inline-flex; align-items: center; justify-content: center; background-image: linear-gradient(310deg, #11cdef 0%, #1193ef 100%);">
                <i class="fa-solid fa-arrow-up-from-bracket text-white d-flex align-items-center justify-content-center" style="font-size: 20px; top: 0px !important;"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-sm-6">
      <div class="card border-0 shadow-sm" style="border-radius: 16px; border-left: 5px solid #fb6340 !important;">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-sm mb-0 text-uppercase font-weight-bold text-muted">Net Quantity</p>
                <h4 class="font-weight-bolder mb-0 mt-1" style="font-size: 20px;">
                  <?= number_format($summary['net_quantity'], 0, ',', '.') ?>
                </h4>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle" style="width: 48px; height: 48px; display: inline-flex; align-items: center; justify-content: center; background-image: linear-gradient(310deg, #fb6340 0%, #fbb140 100%);">
                <i class="fa-solid fa-scale-balanced text-white d-flex align-items-center justify-content-center" style="font-size: 20px; top: 0px !important;"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card border-0 shadow-sm" style="border-radius: 16px;">
    <div class="card-header pb-0 bg-transparent border-0">
      <h6 class="mb-0 font-weight-bold">Inventory Transaction Details</h6>
      <p class="text-xs text-muted mb-0">List of inventory transactions based on active filter criteria</p>
    </div>

    <div class="card-body pt-3">
      <div class="table-responsive">
        <table class="table align-items-center mb-0 table-hover table-bordered">
          <thead class="bg-light">
            <tr>
              <th>No</th>
              <th>Date</th>
              <th>Type</th>
              <th>Reference</th>
              <th>Product</th>
              <th>Variant</th>
              <th>User</th>
              <th>Quantity</th>
              <th>Description</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($transactions)): ?>
              <tr>
                <td colspan="10" class="text-center py-4 text-muted font-weight-bold">
                  No inventory transaction data found for this period.
                </td>
              </tr>
            <?php else: ?>
              <?php $no = 1;
              foreach ($transactions as $transaction): ?>
                <tr>
                  <td class="text-center font-weight-bold"><?= $no++ ?></td>
                  <td class="text-muted"><?= date('d M Y H:i', strtotime($transaction['transaction_date'])) ?></td>
                  <td>
                    <?php if (strtolower($transaction['transaction_type']) === 'in'): ?>
                      <span class="badge bg-success">IN</span>
                    <?php else: ?>
                      <span class="badge bg-danger">OUT</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <div class="text-muted small"><?= esc($transaction['reference_id'] ?? '-') ?></div>
                  </td>
                  <td><?= esc($transaction['product_name'] ?? '-') ?></td>
                  <td><?= esc($transaction['variant_name'] ?? '-') ?></td>
                  <td><?= esc($transaction['user_name'] ?? 'System') ?></td>
                  <td class="text-center font-weight-bold"><?= esc($transaction['quantity']) ?></td>
                  <td><?= esc($transaction['description'] ?? '-') ?></td>
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
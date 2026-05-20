<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>

<?php
function cancellationStatusBadge($status)
{
  return match (strtolower($status)) {
    'requested'    => 'badge bg-warning',
    'approved'   => 'badge bg-success',
    'rejected'   => 'badge bg-danger',
    default      => 'badge bg-light text-dark',
  };
}
?>

<div class="container-fluid card">
  <div class="card-header mb-4 pb-0 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
    <h4>Cancellation Sales</h4>

    <div class="d-flex flex-wrap align-items-center gap-2">
      <form action="<?= base_url('/cancellation-sales') ?>" method="get" class="d-flex flex-wrap align-items-center gap-2 mb-0">
        <input type="text" name="q" class="form-control form-control-sm" placeholder="Search..."
          value="<?= esc($search ?? '') ?>" style="min-width: 150px; width: auto;">
        <input type="date" name="start_date" class="form-control form-control-sm" placeholder="Start Date"
          value="<?= esc($startDate ?? '') ?>" style="width: auto;">
        <input type="date" name="end_date" class="form-control form-control-sm" placeholder="End Date"
          value="<?= esc($endDate ?? '') ?>" style="width: auto;">
        <button type="submit" class="btn btn-sm btn-secondary mb-0"><i class="fa-solid fa-magnifying-glass"></i> Filter</button>
        <?php if (!empty($search) || !empty($startDate) || !empty($endDate)): ?>
          <a href="<?= base_url('/cancellation-sales') ?>" class="btn btn-sm btn-outline-danger mb-0">Clear</a>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <div class="card-body pt-0 pb-2">
    <div class="table-responsive">
      <table class="table align-items-center mb-0 table-bordered">
        <thead class="thead-light">
          <tr>
            <th class="text-center">No</th>
            <th>Order ID</th>
            <th>Request Date</th>
            <th>Customer</th>
            <th class="text-end">Order Amount</th>
            <th>Status</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody id="realtime-tbody">
          <?php if (empty($cancellations)): ?>
            <tr>
              <td colspan="6" class="text-center text-muted">No cancellation requests available.</td>
            </tr>
          <?php else: ?>
            <?php $no = 1;
            foreach ($cancellations as $r): ?>
              <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td>
                  <strong>#<?= $r['order_id'] ?></strong>
                </td>
                <td><?= date('d M Y H:i', strtotime($r['created_at'])) ?></td>
                <td>
                  <div class="d-flex flex-column">
                    <strong><?= esc($r['customer_name'] ?? '-') ?></strong>
                    <small class="text-muted"><?= esc($r['customer_email'] ?? '') ?></small>
                  </div>
                </td>
                <td class="text-end">Rp <?= number_format($r['grand_total'] ?? 0) ?></td>
                <td><span class="<?= cancellationStatusBadge($r['status']) ?>"><?= strtoupper($r['status']) ?></span></td>
                <td class="text-center">
                  <a href="<?= base_url('/cancellation-sales/' . $r['order_cancellation_id']) ?>" class="btn btn-sm btn-info"><i
                      class="fa-solid fa-eye"></i></a>
                </td>
              </tr>
            <?php endforeach ?>
          <?php endif ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
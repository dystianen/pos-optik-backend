<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>

<?php
function refundStatusBadge($status)
{
  return match (strtolower($status)) {
    'pending'    => 'badge bg-warning',
    'processing' => 'badge bg-primary',
    'approved'   => 'badge bg-success',
    'rejected'   => 'badge bg-danger',
    default      => 'badge bg-light text-dark',
  };
}
?>

<div class="container-fluid card">
  <div class="card-header mb-4 pb-0 d-flex align-items-center justify-content-between">
    <h4>Refund Requests</h4>

    <div class="d-flex align-items-center gap-2">
      <form action="<?= base_url('/refund-sales') ?>" method="get" class="d-flex align-items-center">
        <input type="text" name="q" class="form-control form-control-sm me-2" placeholder="Search..."
          value="<?= esc($search ?? '') ?>" style="min-width:200px">
        <button type="submit" class="btn btn-sm btn-secondary"><i class="fa-solid fa-magnifying-glass"></i></button>
      </form>
    </div>
  </div>

  <div class="card-body pt-0 pb-2">
    <div class="table-responsive">
      <table class="table align-items-center mb-0 table-bordered">
        <thead class="thead-light">
          <tr>
            <th class="text-center">No</th>
            <th>Order / Customer</th>
            <th>Date</th>
            <th>Refund Type</th>
            <th class="text-end">Amount</th>
            <th>Status</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody id="realtime-tbody">
          <?php if (empty($refunds)): ?>
            <tr>
              <td colspan="7" class="text-center text-muted">No refund requests available.</td>
            </tr>
          <?php else: ?>
            <?php $no = 1;
            foreach ($refunds as $r): ?>
              <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td>
                  <div class="d-flex flex-column">
                    <strong><?= esc($r['order_id']) ?></strong>
                    <small class="text-muted"><?= esc($r['customer_name'] ?? '-') ?><br><?= esc($r['customer_email'] ?? '') ?></small>
                  </div>
                </td>
                <td><?= date('d M Y H:i', strtotime($r['order_date'] ?? now())) ?></td>
                <td>
                  <span class="badge <?= ($r['refund_type'] === 'partial') ? 'bg-info' : 'bg-secondary' ?>">
                    <?= ucfirst($r['refund_type'] ?? 'full') ?>
                  </span>
                </td>
                <td class="text-end">Rp <?= number_format($r['refund_amount'] ?? 0) ?></td>
                <td><span class="<?= refundStatusBadge($r['status']) ?>"><?= strtoupper($r['status']) ?></span></td>
                <td class="text-center">
                  <a href="<?= base_url('/refund-sales/' . $r['order_refund_id']) ?>" class="btn btn-sm btn-info"><i
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
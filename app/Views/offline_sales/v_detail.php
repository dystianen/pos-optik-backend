<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>

<?php
function badgeStatus($status)
{
  return match (strtolower($status)) {
    'pending'    => 'badge bg-warning',
    'processing' => 'badge bg-primary',
    'shipped'    => 'badge bg-secondary',
    'completed'  => 'badge bg-success',
    'cancelled'  => 'badge bg-danger',
    default      => 'badge bg-light text-dark'
  };
}
?>


<div class="container-fluid card py-3">
  <div class="card mb-4">
    <div class="card-body d-flex justify-content-between align-items-center">
      <div>
        <h4 class="mb-0">Order #<?= $order['order_id'] ?></h4>
        <small class="text-muted">
          <?= date('d M Y H:i', strtotime($order['order_date'])) ?>
        </small>
      </div>

      <span class="<?= badgeStatus($order['status_code']) ?>">
        <?= strtoupper($order['status_name']) ?>
      </span>
    </div>
  </div>

  <?php
  $hasPayment = !empty($payment);
  $colClass = $hasPayment ? 'col-lg-4 col-md-6 mb-3' : 'col-md-6 mb-3';
  ?>

  <div class="row mb-4">
    <div class="<?= $colClass ?>">
      <div class="card h-100">
        <div class="card-body">
          <strong>Customer Information</strong>
          <div class="mt-2">
            <p class="mb-1"><strong><?= esc($order['customer_name']) ?></strong></p>
            <p class="mb-1"><?= esc($order['customer_email']) ?></p>
          </div>
        </div>
      </div>
    </div>

    <?php if ($hasPayment): ?>
      <div class="<?= $colClass ?>">
        <div class="card h-100">
          <div class="card-body">
            <strong>Payment Information</strong>
            <dl class="row mt-2 mb-0">
              <dt class="col-5">Metode</dt>
              <dd class="col-7 text-end"><?= esc($payment['method_name']) ?></dd>

              <dt class="col-5">Total Bayar</dt>
              <dd class="col-7 text-end">Rp <?= number_format($payment['amount']) ?></dd>

              <?php $change = max(0, $payment['amount'] - $order['grand_total']); ?>
              <?php if ($change > 0): ?>
                <dt class="col-5">Kembalian</dt>
                <dd class="col-7 text-end">Rp <?= number_format($change) ?></dd>
              <?php endif ?>

              <?php if ($payment['proof']): ?>
                <dt class="col-5">Bukti</dt>
                <dd class="col-7 text-end">
                  <a href="<?= esc($payment['proof']) ?>" target="_blank">Lihat Bukti</a>
                </dd>
              <?php endif ?>

              <?php if ($payment['paid_at']): ?>
                <dt class="col-5">Dibayar Pada</dt>
                <dd class="col-7 text-end"><?= esc($payment['paid_at']) ?></dd>
              <?php endif ?>
            </dl>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <div class="<?= $colClass ?>">
      <div class="card h-100">
        <div class="card-body">
          <strong>Order Summary</strong>
          <table class="table table-sm mt-2 mb-0">
            <tr>
              <td>Subtotal</td>
              <td class="text-end">Rp <?= number_format($order['grand_total'] - $order['shipping_cost']) ?></td>
            </tr>
            <tr>
              <td>Shipping</td>
              <td class="text-end">Rp <?= number_format($order['shipping_cost']) ?></td>
            </tr>
            <tr class="fw-bold">
              <td>Total</td>
              <td class="text-end">Rp <?= number_format($order['grand_total']) ?></td>
            </tr>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <strong>Order Items</strong>
      <table class="table mt-2 mb-0">
        <thead class="thead-light">
          <tr>
            <th>#</th>
            <th>SKU</th>
            <th>Product</th>
            <th class="text-center">Qty</th>
            <th class="text-end">Price</th>
            <th class="text-end">Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <?php $no = 1;
          foreach ($items as $item): ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= esc($item['product_sku']) ?></td>
              <td>
                <div class="fw-bold text-dark"><?= esc($item['product_name']) ?></div>
                <?php if (
                  $item['right_sph'] !== null || $item['right_cyl'] !== null || $item['right_axis'] !== null || $item['pd_right'] !== null ||
                  $item['left_sph'] !== null || $item['left_cyl'] !== null || $item['left_axis'] !== null || $item['pd_left'] !== null
                ): ?>
                  <div class="mt-2 p-3 bg-light border rounded-3" style="max-width: 550px;">
                    <div class="d-flex align-items-center mb-2">
                      <span class="badge bg-primary me-2"><i class="fas fa-glasses"></i> Prescription</span>
                      <small class="text-muted font-weight-bold">Lenses Specification</small>
                    </div>
                    <table class="table table-bordered table-sm text-center mb-0 bg-white" style="font-size: 0.775rem;">
                      <thead>
                        <tr class="table-light">
                          <th class="text-start" style="font-size: 0.72rem;">Eye / Mata</th>
                          <th style="font-size: 0.72rem; width: 18%;">SPH</th>
                          <th style="font-size: 0.72rem; width: 18%;">CYL</th>
                          <th style="font-size: 0.72rem; width: 18%;">AXIS</th>
                          <th style="font-size: 0.72rem; width: 18%;">PD</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td class="text-start fw-bold text-dark"><span class="badge bg-warning text-dark me-1" style="font-size: 0.65rem;">OD</span> Right / Kanan</td>
                          <td class="font-weight-bold text-secondary"><?= $item['right_sph'] !== null ? esc($item['right_sph']) : '-' ?></td>
                          <td class="font-weight-bold text-secondary"><?= $item['right_cyl'] !== null ? esc($item['right_cyl']) : '-' ?></td>
                          <td class="font-weight-bold text-secondary"><?= $item['right_axis'] !== null ? esc($item['right_axis']) . '°' : '-' ?></td>
                          <td class="font-weight-bold text-secondary"><?= $item['pd_right'] !== null ? esc($item['pd_right']) . ' mm' : '-' ?></td>
                        </tr>
                        <tr>
                          <td class="text-start fw-bold text-dark"><span class="badge bg-info text-white me-1" style="font-size: 0.65rem;">OS</span> Left / Kiri</td>
                          <td class="font-weight-bold text-secondary"><?= $item['left_sph'] !== null ? esc($item['left_sph']) : '-' ?></td>
                          <td class="font-weight-bold text-secondary"><?= $item['left_cyl'] !== null ? esc($item['left_cyl']) : '-' ?></td>
                          <td class="font-weight-bold text-secondary"><?= $item['left_axis'] !== null ? esc($item['left_axis']) . '°' : '-' ?></td>
                          <td class="font-weight-bold text-secondary"><?= $item['pd_left'] !== null ? esc($item['pd_left']) . ' mm' : '-' ?></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                <?php endif; ?>
              </td>
              <td class="text-center"><?= $item['qty'] ?></td>
              <td class="text-end">Rp <?= number_format($item['price']) ?></td>
              <td class="text-end">Rp <?= number_format($item['price'] * $item['qty']) ?></td>
            </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
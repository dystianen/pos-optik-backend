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

  <div class="row mb-4">
    <div class="col-md-6">
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

    <div class="col-md-6">
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


  <?php if (!empty($payment)): ?>
    <div class="row mb-4">
      <div class="col-md-6">
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
    </div>
  <?php endif; ?>

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
              <td><?= esc($item['product_name']) ?></td>
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
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
    'expired'    => 'badge bg-dark',
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


  <div class="card mb-4">
    <div class="card-body">
      <strong>Order Items</strong>
      <table class="table mt-2 mb-0">
        <thead class="thead-light">
          <tr>
            <th>#</th>
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

  <div class="row mb-4">
    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-header fw-bold">
          Shipping
        </div>
        <div class="card-body">
          <dl class="row mb-0 small">
            <dt class="col-5 text-muted">Method</dt>
            <dd class="col-7 fw-semibold"><?= $order['shipping_method'] ?></dd>

            <dt class="col-5 text-muted">Estimated</dt>
            <dd class="col-7"><?= $order['estimated_days'] ?> days</dd>

            <dt class="col-5 text-muted">Courier</dt>
            <dd class="col-7"><?= $order['courier'] ?></dd>

            <dt class="col-5 text-muted">Tracking</dt>
            <dd class="col-7 fw-semibold"><?= $order['tracking_number'] ?: '-' ?></dd>

            <dt class="col-12 text-muted mt-2">Address</dt>
            <dd class="col-12 mb-0"><?= $shippingAddress['address'] ?? '-' ?></dd>
          </dl>
        </div>
      </div>
    </div>

    <?php if ($payment): ?>
      <div class="col-md-4">
        <div class="card h-100">
          <div class="card-header fw-bold">
            Payment
          </div>
          <div class="card-body">
            <div class="text-center mb-3">
              <img
                src="<?= esc($payment['proof']) ?>"
                class="img-thumbnail"
                style="max-height:120px; cursor: pointer; transition: transform 0.2s;"
                onmouseover="this.style.transform='scale(1.05)'"
                onmouseout="this.style.transform='scale(1)'"
                alt="payment proof"
                data-bs-toggle="modal"
                data-bs-target="#imageZoomModal"
                onclick="document.getElementById('zoomedImage').src = this.src">
            </div>

            <dl class="row mb-0 small">
              <dt class="col-5 text-muted">Amount</dt>
              <dd class="col-7 fw-semibold">
                Rp <?= number_format($payment['amount']) ?>
              </dd>

              <dt class="col-5 text-muted">Method</dt>
              <dd class="col-7"><?= $payment['method_name'] ?></dd>

              <dt class="col-5 text-muted">Paid At</dt>
              <dd class="col-7"><?= $payment['paid_at'] ?></dd>
            </dl>
          </div>
        </div>
      </div>
    <?php endif ?>

    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-header fw-bold">
          Refund Account
        </div>
        <div class="card-body">
          <?php if ($refundAccount): ?>
            <dl class="row mb-0 small">
              <dt class="col-5 text-muted">Account Name</dt>
              <dd class="col-7 fw-semibold"><?= $refundAccount['account_name'] ?></dd>

              <dt class="col-5 text-muted">Bank</dt>
              <dd class="col-7"><?= $refundAccount['bank_name'] ?></dd>

              <dt class="col-5 text-muted">Account No</dt>
              <dd class="col-7"><?= $refundAccount['account_number'] ?></dd>
            </dl>
          <?php else: ?>
            <p class="text-muted mb-0">No refund account</p>
          <?php endif ?>
        </div>
      </div>
    </div>

  </div>

  <?php if (in_array($order['status_code'], ['pending', 'waiting_confirmation', 'processing'])): ?>
    <div class="card">
      <div class="card-body">
        <h5 class="mb-3">Admin Actions</h5>

        <!-- PENDING ACTIONS (EXPIRED) -->
        <?php if ($order['status_code'] === 'pending'): ?>
          <div class="mb-4">
            <p class="mb-2 fw-semibold">Pending Payment Actions</p>
            <p class="text-muted small">Customer has not uploaded payment proof yet. If they exceed the payment deadline, you can mark this order as Expired to release/restore the inventory stock.</p>
            <form method="post" action="<?= base_url('/api/online-sales/' . $order['order_id'] . '/expire') ?>">
              <?= csrf_field() ?>
              <button type="submit"
                class="btn btn-warning text-white"
                onclick="return confirm('Apakah Anda yakin ingin membatalkan order ini karena waktu pembayaran kadaluwarsa (Expired)? Tindakan ini akan mengembalikan stok barang.')">
                <i class="fa fa-clock-o me-2"></i>Mark as Expired (Restore Stock)
              </button>
            </form>
          </div>
        <?php endif ?>

        <!-- PAYMENT ACTIONS -->
        <?php if (in_array($order['status_code'], ['waiting_confirmation'])): ?>
          <div class="mb-4">
            <p class="mb-2 fw-semibold">Payment Verification</p>
            <div class="d-flex gap-2 flex-wrap">
              <form method="post" action="<?= base_url('/api/online-sales/' . $order['order_id'] . '/approve') ?>">
                <?= csrf_field() ?>
                <button type="submit"
                  class="btn btn-success"
                  onclick="return confirm('Approve payment for this order?')">
                  Approve Payment
                </button>
              </form>

              <form method="post" action="<?= base_url('/api/online-sales/' . $order['order_id'] . '/reject') ?>">
                <?= csrf_field() ?>
                <button type="submit"
                  class="btn btn-danger"
                  onclick="return confirm('Reject payment for this order?')">
                  Reject Payment
                </button>
              </form>
            </div>
          </div>
        <?php endif ?>

        <?php if (in_array($order['status_code'], ['processing'])): ?>
          <div class="card mt-3">
            <div class="card-body">
              <h5 class="mb-3">Shipping Information</h5>

              <form method="post" action="<?= base_url('/api/online-sales/' . $order['order_id'] . '/ship') ?>">
                <?= csrf_field() ?>

                <div class="row g-3">
                  <div class="col-md-4">
                    <label class="form-label">Courier</label>
                    <select name="courier" class="form-select" required>
                      <option value="">-- Select Courier --</option>
                      <option value="JNE">JNE</option>
                      <option value="J&T">J&T</option>
                      <option value="SiCepat">SiCepat</option>
                      <option value="AnterAja">AnterAja</option>
                      <option value="POS Indonesia">POS Indonesia</option>
                    </select>
                  </div>

                  <div class="col-md-5">
                    <label class="form-label">Tracking Number</label>
                    <input type="text"
                      name="tracking_number"
                      class="form-control"
                      placeholder="Input resi pengiriman"
                      required>
                  </div>

                  <div class="col-md-auto align-self-end">
                    <button class="btn btn-primary"
                      onclick="return confirm('Confirm shipment & save tracking number?')">
                      Submit Shipment
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        <?php endif ?>
      </div>
    </div>
  <?php endif ?>
</div>

<!-- Image Zoom Modal -->
<div class="modal fade" id="imageZoomModal" tabindex="-1" aria-labelledby="imageZoomModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg border-0" style="border-radius: 16px;">
      <div class="modal-header bg-light d-flex justify-content-between align-items-center" style="border-top-left-radius: 16px; border-top-right-radius: 16px; padding: 12px 20px;">
        <h6 class="modal-title font-weight-bold mb-0" id="imageZoomModalLabel">Image Preview</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 1.25rem; color: #888;">&times;</button>
      </div>
      <div class="modal-body text-center p-3 bg-white" style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
        <img id="zoomedImage" src="" class="img-fluid rounded shadow-sm" style="max-height: 70vh; object-fit: contain; background: #f8f9fa; padding: 10px; width: 100%;">
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>

<?php
function badgeStatus($status)
{
  return match (strtolower($status)) {
    'requested'         => 'badge bg-info',
    'request_rejected'  => 'badge bg-danger',
    'return_approved'   => 'badge bg-primary',
    'return_shipped'    => 'badge bg-secondary',
    'return_received'   => 'badge bg-warning',
    'return_rejected'   => 'badge bg-danger',
    'approved'          => 'badge bg-success',
    'refunded'          => 'badge bg-success',
    'expired'           => 'badge bg-dark',
    'pending'           => 'badge bg-warning',
    'processing'        => 'badge bg-primary',
    'shipped'           => 'badge bg-secondary',
    'completed'         => 'badge bg-success',
    'cancelled'         => 'badge bg-danger',
    default             => 'badge bg-light text-dark'
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
            <dd class="col-7 fw-semibold text-right"><?= $order['shipping_method'] ?></dd>

            <dt class="col-5 text-muted">Estimated</dt>
            <dd class="col-7 text-right"><?= $order['estimated_days'] ?> days</dd>

            <dt class="col-5 text-muted">Courier</dt>
            <dd class="col-7 text-right"><?= $order['courier'] ?: '-' ?></dd>

            <dt class="col-5 text-muted">Tracking</dt>
            <dd class="col-7 fw-semibold text-right"><?= $order['tracking_number'] ?: '-' ?></dd>

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
              <dd class="col-7 fw-semibold text-right">
                Rp <?= number_format($payment['amount']) ?>
              </dd>

              <dt class="col-5 text-muted">Method</dt>
              <dd class="col-7 text-right"><?= $payment['method_name'] ?></dd>

              <dt class="col-5 text-muted">Paid At</dt>
              <dd class="col-7 text-right"><?= $payment['paid_at'] ?></dd>
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
          <?php if (!empty($refund['account_name'])): ?>
            <dl class="row mb-0 small">
              <dt class="col-5 text-muted">Account Name</dt>
              <dd class="col-7 fw-semibold text-right"><?= esc($refund['account_name'] ?? '-') ?></dd>

              <dt class="col-5 text-muted">Bank</dt>
              <dd class="col-7 text-right"><?= esc($refund['bank_name'] ?? '-') ?></dd>

              <dt class="col-5 text-muted">Account No</dt>
              <dd class="col-7 text-right d-flex justify-content-end align-items-center gap-2">
                <span><?= esc($refund['account_number'] ?? '-') ?></span>
                <?php if (!empty($refund['account_number'])): ?>
                  <button class="btn btn-sm btn-link p-0 mb-0 text-primary" onclick="copyToClipboard('<?= esc($refund['account_number']) ?>', this)">
                    <i class="fa fa-copy"></i>
                  </button>
                <?php endif ?>
              </dd>
            </dl>
          <?php else: ?>
            <p class="text-muted mb-0">No refund account</p>
          <?php endif ?>
        </div>
      </div>
    </div>

  </div>

  <div class="card mb-4">
    <div class="card-body">
      <strong>Refund Type & Items</strong>
      <div class="mt-3">
        <p class="mb-2"><strong>Type:</strong> <?= ucfirst($refund['refund_type'] ?? 'full') ?> Refund</p>

        <?php if ($refund['refund_type'] === 'partial' && !empty($refundItems)): ?>
          <div class="table-responsive">
            <table class="table table-sm mt-2 mb-0">
              <thead class="thead-light">
                <tr>
                  <th>#</th>
                  <th>Product</th>
                  <th class="text-center">Qty Refunded</th>
                  <th class="text-end">Price</th>
                  <th class="text-end">Subtotal Refund</th>
                </tr>
              </thead>
              <tbody>
                <?php $no = 1;
                foreach ($refundItems as $item): ?>
                  <tr>
                    <td><?= $no++ ?></td>
                    <td>
                      <div class="d-flex flex-column">
                        <strong><?= esc($item['product_name']) ?></strong>
                        <?php if (!empty($item['variant_name'])): ?>
                          <small class="text-muted"><?= esc($item['variant_name']) ?></small>
                        <?php endif ?>
                      </div>
                    </td>
                    <td class="text-center"><?= $item['qty_refunded'] ?></td>
                    <td class="text-end">Rp <?= number_format($item['price_per_item']) ?></td>
                    <td class="text-end">Rp <?= number_format($item['subtotal_refunded']) ?></td>
                  </tr>
                <?php endforeach ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <p class="small text-muted">Full refund untuk semua items dalam order</p>
        <?php endif ?>
      </div>
    </div>
  </div>


  <div class="card-body">
    <h5 class="mb-3">Refund Details & Admin Actions</h5>

    <div class="row mb-3">
      <div class="col-md-4">
        <div class="card">
          <div class="card-body small">
            <p class="mb-1 text-muted">Refund ID</p>
            <p class="fw-semibold"><?= esc($refund['order_refund_id']) ?></p>

            <p class="mb-1 text-muted">Amount</p>
            <p class="fw-semibold">Rp <?= number_format($refund['refund_amount'] ?? 0) ?></p>

            <p class="mb-1 text-muted">Reason</p>
            <p class="small text-muted"><?= esc($refund['reason'] ?? '-') ?></p>

            <p class="mb-1 text-muted">Status</p>
            <p><span class="<?= badgeStatus($refund['status']) ?>"><?= strtoupper($refund['status']) ?></span></p>

            <?php if (!empty($refund['evidence_url'])): ?>
              <p class="mb-1 text-muted">Evidence</p>
              <?php
              $ext = pathinfo($refund['evidence_url'], PATHINFO_EXTENSION);
              $isVideo = in_array(strtolower($ext), ['mp4', 'webm', 'ogg', 'mov']);
              ?>

              <?php if ($isVideo): ?>
                <video src="<?= esc($refund['evidence_url']) ?>" controls class="img-fluid rounded" style="max-height: 300px;"></video>
              <?php else: ?>
                <img
                  src="<?= esc($refund['evidence_url']) ?>"
                  class="img-thumbnail"
                  style="max-height: 200px; cursor: pointer; transition: transform 0.2s;"
                  onmouseover="this.style.transform='scale(1.05)'"
                  onmouseout="this.style.transform='scale(1)'"
                  alt="Evidence"
                  data-bs-toggle="modal"
                  data-bs-target="#imageZoomModal"
                  onclick="document.getElementById('zoomedImage').src = this.src">
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="col-md-8">
        <div class="card">
          <div class="card-body">
            <h6>Admin Note</h6>
            <p class="small text-muted"><?= esc($refund['admin_note'] ?? '-') ?></p>

            <?php if (!empty($refund['processed_by'])): ?>
              <p class="small text-muted">Processed by: <?= esc($refund['admin_name'] ?? $refund['admin_email'] ?? '-') ?></p>
            <?php endif ?>

            <?php if (!empty($refund['return_courier'])): ?>
              <hr>
              <h6>Return Shipping Info</h6>
              <dl class="row mb-0 small">
                <dt class="col-5 text-muted">Courier</dt>
                <dd class="col-7 fw-semibold"><?= esc($refund['return_courier']) ?></dd>

                <dt class="col-5 text-muted">Tracking No</dt>
                <dd class="col-7 fw-semibold"><?= esc($refund['return_tracking_number']) ?></dd>

                <dt class="col-5 text-muted">Shipped At</dt>
                <dd class="col-7"><?= !empty($refund['return_shipped_at']) ? date('d M Y H:i', strtotime($refund['return_shipped_at'])) : '-' ?></dd>
              </dl>
            <?php endif ?>
          </div>
        </div>
      </div>
    </div>

    <?php if (in_array($refund['status'], ['requested', 'return_approved', 'return_shipped', 'return_received', 'approved'])): ?>
      <div class="row g-3">
        <div class="col-md-6">
          <div class="card border-success">
            <div class="card-body">
              <?php if ($refund['status'] === 'requested'): ?>
                <h6 class="text-success">Approve Return Request</h6>
                <p class="small text-muted">Approve the request and wait for customer to ship return items.</p>
                <div class="mb-2">
                  <label class="form-label">Adjusted Amount (optional)</label>
                  <input id="approve_amount" type="number" class="form-control" placeholder="Leave empty to keep requested amount" value="<?= $refund['refund_amount'] ?>">
                </div>
                <div class="mb-2">
                  <label class="form-label">Admin Note (optional)</label>
                  <textarea id="approve_note" class="form-control" rows="2"></textarea>
                </div>
                <button id="btnApprove" class="btn btn-success w-100">Approve Request</button>

              <?php elseif ($refund['status'] === 'return_shipped'): ?>
                <h6 class="text-success">Mark as Received</h6>
                <p class="small text-muted">The items have been received at our warehouse.</p>
                <div class="mb-2">
                  <label class="form-label">Admin Note (optional)</label>
                  <textarea id="receive_note" class="form-control" rows="2"></textarea>
                </div>
                <button id="btnReceive" class="btn btn-primary w-100">Confirm Received</button>

              <?php elseif ($refund['status'] === 'return_received'): ?>
                <h6 class="text-success">Final Approve Refund</h6>
                <p class="small text-muted">Final approval of the refund amount to customer.</p>
                <div class="mb-2">
                  <label class="form-label">Admin Note (optional)</label>
                  <textarea id="final_approve_note" class="form-control" rows="2"></textarea>
                </div>
                <button id="btnFinalApprove" class="btn btn-success w-100">Final Approve</button>

              <?php elseif ($refund['status'] === 'approved'): ?>
                <h6 class="text-success">Mark as Refunded</h6>
                <p class="small text-muted">Only click this after you have successfully transferred the funds to the customer.</p>
                <div class="mb-2">
                  <label class="form-label">Admin Note (optional)</label>
                  <textarea id="refund_note" class="form-control" rows="2"></textarea>
                </div>
                <button id="btnRefund" class="btn btn-success w-100">Confirm Funds Sent</button>

              <?php else: ?>
                <p class="small text-muted">Waiting for customer action.</p>
              <?php endif ?>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <?php if (in_array($refund['status'], ['requested', 'return_received'])): ?>
            <div class="card border-danger">
              <div class="card-body">
                <h6>Reject Refund</h6>
                <div class="mb-2">
                  <label class="form-label">Admin Note (required)</label>
                  <textarea id="reject_note" class="form-control" rows="2" required></textarea>
                </div>
                <button id="btnReject" class="btn btn-danger w-100">Reject Refund</button>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php else: ?>
      <div class="alert alert-secondary">No actions available for this status: <strong><?= strtoupper($refund['status']) ?></strong></div>
    <?php endif ?>
  </div>
</div>
</div>

<script>
  function copyToClipboard(text, btn) {
    navigator.clipboard.writeText(text).then(function() {
      const icon = btn.querySelector('i');
      const originalClass = icon.className;
      icon.className = 'fa fa-check text-success';
      
      if (typeof Swal !== 'undefined') {
        const Toast = Swal.mixin({
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 1500,
          timerProgressBar: true
        });
        Toast.fire({
          icon: 'success',
          title: 'Account number copied!'
        });
      }
      
      setTimeout(function() {
        icon.className = originalClass;
      }, 1500);
    }).catch(function(err) {
      console.error('Failed to copy: ', err);
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    const refundId = '<?= esc($refund['order_refund_id']) ?>';
    console.log('Refund Detail Loaded. ID:', refundId);

    async function postJson(url, body) {
      console.log('Attempting POST to:', url, 'Body:', body);
      try {
        const res = await fetch(url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(body)
        });
        const data = await res.json();
        console.log('Response:', data);
        return data;
      } catch (err) {
        console.error('Fetch error:', err);
        return {
          success: false,
          message: 'Network error or server failed: ' + err.message
        };
      }
    }

    document.getElementById('btnApprove')?.addEventListener('click', async () => {
      const confirm = await Swal.fire({
        title: 'Approve refund?',
        text: 'Are you sure you want to approve this refund request?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        confirmButtonText: 'Yes, Approve'
      });

      if (!confirm.isConfirmed) return;

      const adjusted = document.getElementById('approve_amount').value || null;
      const note = document.getElementById('approve_note').value || null;
      const url = `<?= base_url('api/admin/refund') ?>/${refundId}/approve`;

      const payload = {};
      if (adjusted) payload.adjusted_amount = parseFloat(adjusted);
      if (note) payload.admin_note = note;

      Swal.fire({
        title: 'Approving...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
      });

      const resp = await postJson(url, payload);

      if (resp.success !== false) {
        Swal.fire({
          icon: 'success',
          title: 'Approved',
          text: resp.message || 'Refund approved successfully',
          timer: 1500,
          showConfirmButton: false
        }).then(() => location.reload());
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Failed',
          text: resp.message || 'Failed to approve refund'
        });
      }
    });

    document.getElementById('btnReject')?.addEventListener('click', async () => {
      const note = document.getElementById('reject_note').value || '';
      if (!note) {
        Swal.fire({
          icon: 'warning',
          title: 'Note required',
          text: 'Admin note is required for rejection'
        });
        return;
      }

      const confirm = await Swal.fire({
        title: 'Reject refund?',
        text: 'Are you sure you want to reject this refund request?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Yes, Reject'
      });

      if (!confirm.isConfirmed) return;

      const url = `<?= base_url('api/admin/refund') ?>/${refundId}/reject`;

      Swal.fire({
        title: 'Rejecting...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
      });

      const resp = await postJson(url, {
        admin_note: note
      });

      if (resp.success !== false) {
        Swal.fire({
          icon: 'success',
          title: 'Rejected',
          text: resp.message || 'Refund rejected successfully',
          timer: 1500,
          showConfirmButton: false
        }).then(() => location.reload());
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Failed',
          text: resp.message || 'Failed to reject refund'
        });
      }
    });

    document.getElementById('btnReceive')?.addEventListener('click', async () => {
      const confirm = await Swal.fire({
        title: 'Mark as Received?',
        text: 'Confirm that items have been received at warehouse',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Received'
      });

      if (!confirm.isConfirmed) return;

      const note = document.getElementById('receive_note').value || null;
      const url = `<?= base_url('api/admin/refund') ?>/${refundId}/receive`;

      Swal.fire({
        title: 'Processing...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
      });

      const resp = await postJson(url, {
        admin_note: note
      });
      if (resp.success !== false) {
        Swal.fire({
          icon: 'success',
          title: 'Received',
          text: resp.message,
          timer: 1500,
          showConfirmButton: false
        }).then(() => location.reload());
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Failed',
          text: resp.message
        });
      }
    });

    document.getElementById('btnFinalApprove')?.addEventListener('click', async () => {
      const confirm = await Swal.fire({
        title: 'Final Approve Refund?',
        text: 'This will approve the refund and update order status to REFUNDED',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        confirmButtonText: 'Yes, Final Approve'
      });

      if (!confirm.isConfirmed) return;

      const note = document.getElementById('final_approve_note').value || null;
      const url = `<?= base_url('api/admin/refund') ?>/${refundId}/final-approve`;

      Swal.fire({
        title: 'Processing...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
      });

      const resp = await postJson(url, {
        admin_note: note
      });
      if (resp.success !== false) {
        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: resp.message,
          timer: 1500,
          showConfirmButton: false
        }).then(() => location.reload());
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Failed',
          text: resp.message
        });
      }
    });

    document.getElementById('btnRefund')?.addEventListener('click', async () => {
      const confirm = await Swal.fire({
        title: 'Confirm Funds Sent?',
        text: 'This will mark the refund as completed and update order status to REFUNDED',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        confirmButtonText: 'Yes, Mark as Refunded'
      });

      if (!confirm.isConfirmed) return;

      const note = document.getElementById('refund_note').value || null;
      const url = `<?= base_url('api/admin/refund') ?>/${refundId}/refund`;

      Swal.fire({
        title: 'Processing...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
      });

      const resp = await postJson(url, {
        admin_note: note
      });
      if (resp.success !== false) {
        Swal.fire({
          icon: 'success',
          title: 'Refunded',
          text: resp.message,
          timer: 1500,
          showConfirmButton: false
        }).then(() => location.reload());
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Failed',
          text: resp.message
        });
      }
    });
  });
</script>

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
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

<div class="container-fluid card py-3" id="realtime-detail-container">
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
    <?php if ($payment): ?>
      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-header fw-bold text-dark">
            Payment Info
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

            <dl class="row mb-0 small text-dark">
              <dt class="col-5 text-muted">Amount</dt>
              <dd class="col-7 fw-semibold text-end">
                Rp <?= number_format($payment['amount']) ?>
              </dd>

              <dt class="col-5 text-muted">Method</dt>
              <dd class="col-7 text-end"><?= $payment['method_name'] ?></dd>

              <dt class="col-5 text-muted">Paid At</dt>
              <dd class="col-7 text-end"><?= $payment['paid_at'] ?></dd>
            </dl>
          </div>
        </div>
      </div>
    <?php endif ?>

    <div class="col-md-<?= $payment ? '6' : '12' ?>">
      <div class="card h-100">
        <div class="card-header fw-bold text-dark">
          Refund Account Info
        </div>
        <div class="card-body">
          <?php if ($refundAccount): ?>
            <dl class="row mb-0 small text-dark">
              <dt class="col-5 text-muted">Account Name</dt>
              <dd class="col-7 fw-semibold text-end"><?= esc($refundAccount['account_name'] ?? '-') ?></dd>

              <dt class="col-5 text-muted">Bank</dt>
              <dd class="col-7 text-end"><?= esc($refundAccount['bank_name'] ?? '-') ?></dd>

              <dt class="col-5 text-muted">Account No</dt>
              <dd class="col-7 text-end d-flex justify-content-end align-items-center gap-2">
                <span><?= esc($refundAccount['account_number'] ?? '-') ?></span>
                <?php if (!empty($refundAccount['account_number'])): ?>
                  <button class="btn btn-sm btn-link p-0 mb-0 text-primary" onclick="copyToClipboard('<?= esc($refundAccount['account_number']) ?>', this)">
                    <i class="fa fa-copy"></i>
                  </button>
                <?php endif ?>
              </dd>
            </dl>
          <?php else: ?>
            <p class="text-muted mb-0">No refund account configured by user</p>
          <?php endif ?>
        </div>
      </div>
    </div>
  </div>

  <div class="card-body">
    <h5 class="mb-3">Cancellation Details & Admin Actions</h5>

    <div class="row mb-3">
      <div class="col-md-4">
        <div class="card">
          <div class="card-header fw-bold">Request Details</div>
          <div class="card-body small">
            <p class="mb-1 text-muted">Cancellation ID</p>
            <p class="fw-semibold"><?= esc($cancellation['order_cancellation_id']) ?></p>

            <p class="mb-1 text-muted">Reason</p>
            <p class="small text-muted"><?= esc($cancellation['reason'] ?? '-') ?></p>

             <p class="mb-1 text-muted">Additional Note</p>
            <p class="small text-muted"><?= esc($cancellation['additional_note'] ?? '-') ?></p>

            <p class="mb-1 text-muted">Status</p>
            <p><span class="badge <?= ($cancellation['status'] === 'requested') ? 'bg-warning' : (($cancellation['status'] === 'approved') ? 'bg-success' : 'bg-danger') ?>"><?= strtoupper($cancellation['status']) ?></span></p>
          </div>
        </div>
      </div>

       <div class="col-md-8">
        <div class="card">
           <div class="card-header fw-bold">Processing Info</div>
          <div class="card-body">
             <?php if (!empty($cancellation['processed_by'])): ?>
              <p class="small text-muted">Processed by: <?= esc($cancellation['admin_name'] ?? '-') ?></p>
              <p class="small text-muted">Processed at: <?= esc($cancellation['processed_at'] ?? '-') ?></p>
            <?php else: ?>
                <p class="text-muted">Not processed yet</p>
            <?php endif ?>
          </div>
        </div>
      </div>
    </div>

    <?php if ($cancellation['status'] === 'requested'): ?>
      <div class="row g-3">
        <div class="col-md-6">
          <div class="card">
            <div class="card-body">
              <h6>Approve Cancellation</h6>
              <p class="small text-muted">Approving will cancel the order and mark this request as approved.</p>
              <button id="btnApprove" class="btn btn-success">Approve Cancellation</button>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card">
            <div class="card-body">
              <h6>Reject Cancellation</h6>
               <p class="small text-muted">Rejecting will keep the order status as is.</p>
              <div class="mb-2">
                <label class="form-label">Admin Note (required)</label>
                <textarea id="reject_note" class="form-control" rows="2" required></textarea>
              </div>
              <button id="btnReject" class="btn btn-danger">Reject Cancellation</button>
            </div>
          </div>
        </div>
      </div>
    <?php else: ?>
      <div class="alert alert-secondary">No actions available for this request</div>
    <?php endif ?>
  </div>
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

<?= $this->section('scripts') ?>
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

  const cancellationId = '<?= esc($cancellation['order_cancellation_id']) ?>';

  async function postJson(url, body) {
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(body)
    });
    return res.json();
  }

  document.getElementById('btnApprove')?.addEventListener('click', () => {
    Swal.fire({
      title: 'Approve Cancellation?',
      text: 'This will cancel the order and cannot be undone.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#2dce89',
      cancelButtonColor: '#8392ab',
      confirmButtonText: 'Yes, Approve',
      cancelButtonText: 'Cancel'
    }).then(async (result) => {
      if (result.isConfirmed) {
        const url = `<?= base_url('/api/admin/cancel/') ?>${cancellationId}/approve`;
        try {
          const resp = await postJson(url, {});
          if (resp.status !== 200 && resp.status !== 201 && !resp.success) {
            Swal.fire('Error', resp.message || 'Error occurred', 'error');
          } else {
            Swal.fire('Success', resp.message || 'Approved successfully', 'success').then(() => {
              location.reload();
            });
          }
        } catch (err) {
          Swal.fire('Error', 'System error occurred: ' + err.message, 'error');
        }
      }
    });
  });

  document.getElementById('btnReject')?.addEventListener('click', () => {
    const note = document.getElementById('reject_note').value || '';
    if (!note) {
      Swal.fire('Warning', 'Admin note is required for rejection', 'warning');
      return;
    }

    Swal.fire({
      title: 'Reject Cancellation?',
      text: 'Are you sure you want to reject this cancellation request?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#f5365c',
      cancelButtonColor: '#8392ab',
      confirmButtonText: 'Yes, Reject',
      cancelButtonText: 'Cancel'
    }).then(async (result) => {
      if (result.isConfirmed) {
        const url = `<?= base_url('/api/admin/cancel/') ?>${cancellationId}/reject`;
        try {
          const resp = await postJson(url, {
            admin_note: note
          });
          if (resp.status !== 200 && resp.status !== 201 && !resp.success) {
            Swal.fire('Error', resp.message || 'Error occurred', 'error');
          } else {
            Swal.fire('Success', resp.message || 'Rejected successfully', 'success').then(() => {
              location.reload();
            });
          }
        } catch (err) {
          Swal.fire('Error', 'System error occurred: ' + err.message, 'error');
        }
      }
    });
  });
</script>
<?= $this->endSection() ?>

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
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
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

<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>
<div class="container-fluid card py-4">
  <div class="card-header pb-0">
    <h4><?= isset($order) ? 'Order Detail' : 'Add Order' ?></h4>
  </div>
  <div class="card-body">
    <form action="<?= site_url('/orders/save') ?>" method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= isset($order) ? $order['order_id'] : '' ?>">

      <div class="mb-3">
        <label class="form-label">Customer</label>
        <input
          type="text"
          class="form-control"
          value="<?= isset($order) ? esc($order['customer_name']) : '' ?>"
          disabled>
      </div>

      <div class="mb-3">
        <label class="form-label">Email</label>
        <input
          type="text"
          class="form-control"
          value="<?= isset($order) ? esc($order['customer_email']) : '' ?>"
          disabled>
      </div>

      <div class="mb-3">
        <label class="form-label">Phone</label>
        <input
          type="text"
          class="form-control"
          value="<?= isset($order) ? esc($order['customer_phone']) : '' ?>"
          disabled>
      </div>

      <div class="mb-3">
        <label class="form-label">Order Date</label>
        <input
          type="text"
          class="form-control"
          value="<?= isset($order) ? esc($order['order_date']) : '' ?>"
          disabled>
      </div>

      <div class="mb-3">
        <label class="form-label">Address</label>
        <textarea
          class="form-control"
          disabled><?= isset($order) ? esc($order['address']) : '' ?></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label">Total Price</label>
        <input
          type="text"
          class="form-control"
          value="<?= isset($order) ? esc($order['total_price']) : '' ?>"
          disabled>
      </div>

      <div class="mb-3">
        <label class="form-label">Proof of Payment</label> <br>
        <img src="<?= base_url() . esc($order['proof_of_payment']) ?>" alt="image" width="300" height="400" style="border-radius: 15px">
      </div>

      <div class="mb-3">
        <label class="form-label">Status</label>
        <select class="form-control" name="status" required>
          <option value="pending" disabled <?= isset($order) && $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
          <option value="waiting_confirmation" disabled <?= isset($order) && $order['status'] === 'waiting_confirmation' ? 'selected' : '' ?>>Waiting Confirmation</option>
          <option value="paid" <?= isset($order) && $order['status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
          <option value="shipped" <?= isset($order) && $order['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
          <option value="cancelled" <?= isset($order) && $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>
      </div>

      <a href="<?= base_url('/orders') ?>" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary">Update</button>
    </form>
  </div>

  <?php if (isset($orderItems) && count($orderItems) > 0): ?>
    <div class="card-body mt-4">
      <h5>Order Items</h5>
      <table class="table table-responsive table-bordered">
        <thead>
          <tr>
            <th>No</th>
            <th>Product Name</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <?php $no = 1;
          foreach ($orderItems as $item): ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= esc($item['product_name']) ?></td>
              <td><?= esc($item['quantity']) ?></td>
              <td><?= number_format($item['price'], 0, ',', '.') ?></td>
              <td><?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></td>
            </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>
  <?php endif ?>
</div>
<?= $this->endSection() ?>
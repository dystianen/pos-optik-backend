<?= $this->extend('layouts/l_dashboard') ?>
<?= $this->section('content') ?>
<div class="container-fluid card py-4">
  <div class="card-header pb-0">
    <h4><?= isset($product) ? 'Edit Inventory Transaction' : 'Create Inventory Transaction' ?></h4>
  </div>

  <div class="card-body">
    <form action="<?= site_url('inventory/save') ?>" method="post">
      <input type="hidden" name="id" value="<?= isset($transaction) ? $transaction['inventory_transaction_id'] : '' ?>">

      <div class="row">
        <div class="col-12 col-md-6 mb-3">
          <label for="product_id">Product</label>
          <select class="form-control" name="product_id" id="product_id" required>
            <?php foreach ($products as $product) : ?>
              <option value="<?= $product['product_id'] ?>"
                <?= isset($transaction) && $transaction['product_id'] == $product['product_id'] ? 'selected' : '' ?>>
                <?= $product['product_name'] ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label for="transaction_type">Transaction Type</label>
          <select class="form-control" name="transaction_type" id="transaction_type" required>
            <option value="in" <?= isset($transaction) && $transaction['transaction_type'] == 'in' ? 'selected' : '' ?>>IN</option>
            <option value="out" <?= isset($transaction) && $transaction['transaction_type'] == 'out' ? 'selected' : '' ?>>OUT</option>
          </select>
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label for="quantity">Quantity</label>
          <input class="form-control" type="number" name="quantity" id="quantity" required min="1" placeholder="10"
            value="<?= isset($transaction) ? $transaction['quantity'] : '' ?>">
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label for="description">Description</label>
          <textarea class="form-control" name="description" id="description"><?= isset($transaction) ? $transaction['description'] : '' ?></textarea>
        </div>

        <div class="mt-4">
          <a href="<?= base_url('/inventory') ?>" class="btn btn-secondary">Cancel</a>
          <button type="submit" class="btn btn-primary"><?= isset($transaction) ? 'Update' : 'Save' ?></button>
        </div>
      </div>
    </form>
  </div>
</div>
<?= $this->endSection() ?>
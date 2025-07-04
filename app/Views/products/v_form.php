<?= $this->extend('layouts/l_dashboard') ?>
<?= $this->section('content') ?>
<div class="container-fluid card">
  <div class="card-header pb-0">
    <h4><?= isset($product) ? 'Edit Product' : 'Create Product' ?></h4>
  </div>

  <div class="card-body">
    <form action="<?= site_url('products/save') ?>" method="post" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= isset($product) ? $product['product_id'] : '' ?>">

      <div class="row">
        <div class="col-12 col-md-6 mb-3">
          <label for="category_id" class="form-label">Category</label>
          <select class="form-control" name="category_id" required>
            <option value="" disabled <?= !isset($product) ? 'selected' : '' ?>>Select category</option>
            <?php foreach ($categories as $category): ?>
              <option value="<?= $category['category_id']; ?>"
                <?= (old('category_id', $product['category_id'] ?? '') == $category['category_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($category['category_name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label for="product_name" class="form-label">Name</label>
          <input type="text" class="form-control" name="product_name" placeholder="cth: Adidas Ultra Boost"
            value="<?= old('product_name', $product['product_name'] ?? '') ?>" required>
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label for="product_price" class="form-label">Price</label>
          <input type="number" step="0.01" class="form-control" name="product_price" placeholder="cth: 1.500.000"
            value="<?= old('product_price', $product['product_price'] ?? '') ?>" required>
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label for="product_stock" class="form-label">Stock</label>
          <input type="number" class="form-control" name="product_stock" placeholder="cth: 25"
            value="<?= old('product_stock', $product['product_stock'] ?? '') ?>" required>
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label for="product_brand" class="form-label">Brand</label>
          <input type="text" class="form-control" name="product_brand" placeholder="cth: Adidas"
            value="<?= old('product_brand', $product['product_brand'] ?? '') ?>" required>
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label for="model" class="form-label">Model</label>
          <input type="text" name="model" class="form-control" placeholder="cth: Ultra Boost 21"
            value="<?= old('model', $product['model'] ?? '') ?>">
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label for="duration" class="form-label">Duration</label>
          <select name="duration" class="form-select">
            <?php
            $durations = ['Daily', 'Weekly', 'Monthly', 'Yearly'];
            $selectedDuration = old('duration', $product['duration'] ?? '');
            ?>
            <?php foreach ($durations as $duration): ?>
              <option value="<?= $duration ?>" <?= $selectedDuration === $duration ? 'selected' : '' ?>><?= $duration ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label for="material" class="form-label">Material</label>
          <input type="text" name="material" class="form-control" placeholder="cth: Policarbonate"
            value="<?= old('material', $product['material'] ?? '') ?>">
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label for="base_curve" class="form-label">Base Curve</label>
          <input type="text" name="base_curve" class="form-control" placeholder="cth: 8.6 mm"
            value="<?= old('base_curve', $product['base_curve'] ?? '') ?>">
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label for="diameter" class="form-label">Diameter</label>
          <input type="text" name="diameter" class="form-control" placeholder="cth: 14.2 mm"
            value="<?= old('diameter', $product['diameter'] ?? '') ?>">
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label for="power_range" class="form-label">Power Range</label>
          <input type="text" name="power_range" class="form-control" placeholder="cth: -1.00 to -6.00"
            value="<?= old('power_range', $product['power_range'] ?? '') ?>">
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label for="water_content" class="form-label">Water Content</label>
          <input type="text" name="water_content" class="form-control" placeholder="cth: 38%"
            value="<?= old('water_content', $product['water_content'] ?? '') ?>">
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label for="uv_protection" class="form-label">UV Protection</label>
          <select name="uv_protection" class="form-select">
            <option value="true" <?= old('uv_protection', $product['uv_protection'] ?? '') === 'true' ? 'selected' : '' ?>>Yes</option>
            <option value="false" <?= old('uv_protection', $product['uv_protection'] ?? '') === 'false' ? 'selected' : '' ?>>No</option>
          </select>
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label for="color" class="form-label">Color</label>
          <input type="text" name="color" class="form-control" placeholder="cth: Blue / Brown"
            value="<?= old('color', $product['color'] ?? '') ?>">
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label for="coating" class="form-label">Coating</label>
          <input type="text" name="coating" class="form-control" placeholder="cth: Anti-UV"
            value="<?= old('coating', $product['coating'] ?? '') ?>">
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label for="product_image_url" class="form-label">Image</label>
          <input
            type="file"
            class="form-control"
            name="product_image_url"
            accept=".jpg,.png" />
          <?php if (isset($product['product_image_url'])): ?>
            <small class="form-text text-muted">Current: <?= $product['product_image_url'] ?></small>
          <?php endif; ?>
        </div>
      </div>

      <div class="mt-4">
        <a href="<?= base_url('/products') ?>" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary"><?= isset($product) ? 'Update' : 'Save' ?></button>
      </div>
    </form>
  </div>
</div>
<?= $this->endSection() ?>
<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>
<div class="container-fluid card py-4">
  <div class="card-header pb-0">
    <h4><?= isset($category) ? 'Edit' : 'Add' ?> Product Category</h4>
  </div>
  <div class="card-body">
    <form action="<?= site_url('/product-category/save') ?>" method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= isset($category) ? $category['category_id'] : '' ?>">

      <div class="mb-3">
        <label for="category_name" class="form-label">Category Name</label>
        <input
          type="text"
          name="category_name"
          class="form-control"
          value="<?= isset($category) ? esc($category['category_name']) : '' ?>"
          required>
      </div>
      <div class="mb-3">
        <label for="category_description" class="form-label">Description</label>
        <textarea
          name="category_description"
          class="form-control"><?= isset($category) ? esc($category['category_description']) : '' ?></textarea>
      </div>
      <a href="<?= base_url('/product-category') ?>" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary"><?= isset($category) ? 'Update' : 'Save' ?></button>
    </form>
  </div>
</div>
<?= $this->endSection() ?>
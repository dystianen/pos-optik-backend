<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>
<div class="container-fluid card">
  <div class="card-header pb-0">
    <h4><?= isset($category) ? 'Edit Product Category' : 'Add Product Category' ?></h4>
  </div>
  <div class="card-body">
    <!-- Error Messages Display -->
    <?php if (session()->getFlashdata('failed')): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <h5 class="alert-heading"><i class="fas fa-exclamation-circle"></i> Validation Error</h5>
        <div><?= session()->getFlashdata('failed') ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <!-- Success Messages Display -->
    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <h5 class="alert-heading"><i class="fas fa-check-circle"></i> Success</h5>
        <div><?= session()->getFlashdata('success') ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <form action="<?= site_url('/product-category/save') ?>" method="post" novalidate>
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= isset($category) ? htmlspecialchars($category['category_id']) : '' ?>">

      <!-- Category Name -->
      <div class="mb-3">
        <label for="category_name" class="form-label">Category Name <span class="text-danger">*</span></label>
        <input
          type="text"
          name="category_name"
          id="category_name"
          class="form-control"
          placeholder="e.g., Eyeglasses, Sunglasses, Contact Lenses"
          value="<?= old('category_name', isset($category) ? htmlspecialchars($category['category_name']) : '') ?>"
          required>
        <small class="form-text text-muted d-block mt-1">Enter category name (max 50 characters)</small>
      </div>

      <!-- Description -->
      <div class="mb-3">
        <label for="category_description" class="form-label">Description</label>
        <textarea
          name="category_description"
          id="category_description"
          class="form-control"
          rows="4"
          placeholder="Describe this product category..."><?= old('category_description', isset($category) ? htmlspecialchars($category['category_description']) : '') ?></textarea>
        <small class="form-text text-muted d-block mt-1">Optional: Description of the category (max 500 characters)</small>
      </div>

      <div class="mt-4">
        <a href="<?= base_url('/product-category') ?>" class="btn btn-secondary">
          <i class="fas fa-times"></i> Cancel
        </a>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save"></i> <?= isset($category) ? 'Update Category' : 'Create Category' ?>
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
      }
      form.classList.add('was-validated');
    });
  });
</script>
<?= $this->endSection() ?>
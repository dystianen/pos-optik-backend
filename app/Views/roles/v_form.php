<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>
<div class="container-fluid card">
  <div class="card-header pb-0">
    <h4><?= isset($role) ? 'Edit Role' : 'Add Role' ?></h4>
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

    <form action="<?= site_url('/roles/save') ?>" method="post" novalidate>
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= isset($role) ? htmlspecialchars($role['role_id']) : '' ?>">

      <!-- Role Name -->
      <div class="mb-3">
        <label for="role_name" class="form-label">Role Name <span class="text-danger">*</span></label>
        <input
          type="text"
          name="role_name"
          id="role_name"
          class="form-control"
          placeholder="e.g., Administrator, Manager, Cashier"
          value="<?= old('role_name', isset($role) ? htmlspecialchars($role['role_name']) : '') ?>"
          required>
        <small class="form-text text-muted d-block mt-1">Enter role name (max 100 characters)</small>
      </div>

      <!-- Role Description -->
      <div class="mb-3">
        <label for="role_description" class="form-label">Description</label>
        <textarea
          name="role_description"
          id="role_description"
          class="form-control"
          rows="4"
          placeholder="Describe the purpose and responsibilities of this role..."><?= old('role_description', isset($role) ? htmlspecialchars($role['role_description']) : '') ?></textarea>
        <small class="form-text text-muted d-block mt-1">Optional: Description of role responsibilities (max 500 characters)</small>
      </div>

      <div class="mt-4">
        <a href="<?= base_url('/roles') ?>" class="btn btn-secondary">
          <i class="fas fa-times"></i> Cancel
        </a>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save"></i> <?= isset($role) ? 'Update Role' : 'Create Role' ?>
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
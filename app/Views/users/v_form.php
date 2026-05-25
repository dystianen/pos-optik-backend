<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>
<div class="container-fluid card">
  <div class="card-header pb-0">
    <h4><?= isset($user) ? 'Edit User' : 'Add User' ?></h4>
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

    <form action="<?= site_url('/users/save') ?>" method="post" novalidate>
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= isset($user) ? $user['user_id'] : '' ?>">

      <!-- Full Name -->
      <div class="mb-3">
        <label for="user_name" class="form-label">Full Name <span class="text-danger">*</span></label>
        <input
          type="text"
          name="user_name"
          id="user_name"
          class="form-control"
          placeholder="e.g., John Doe"
          value="<?= old('user_name', isset($user) ? esc($user['user_name']) : '') ?>"
          required>
        <small class="form-text text-muted d-block mt-1">Enter the user's full name (max 100 characters)</small>
      </div>

      <!-- Email -->
      <div class="mb-3">
        <label for="user_email" class="form-label">Email Address <span class="text-danger">*</span></label>
        <input
          type="email"
          name="user_email"
          id="user_email"
          class="form-control"
          placeholder="user@example.com"
          value="<?= old('user_email', isset($user) ? esc($user['user_email']) : '') ?>"
          required>
        <small class="form-text text-muted d-block mt-1">Use a valid email address for user account</small>
      </div>

      <!-- Password -->
      <div class="mb-3">
        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
        <input
          type="password"
          name="password"
          id="password"
          class="form-control"
          placeholder="<?= isset($user) ? 'Leave blank to keep current password' : 'Enter a strong password' ?>"
          <?= !isset($user) ? 'required' : '' ?>>
        <small class="form-text text-muted d-block mt-1">
          <?= isset($user) ? 'Leave blank to keep the current password' : 'Password is required for new accounts' ?>
        </small>
      </div>

      <!-- Role -->
      <div class="mb-3">
        <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
        <select class="form-control" name="role_id" id="role_id" required>
          <option value="" disabled <?= !isset($user) ? 'selected' : '' ?>>-- Select a role --</option>
          <?php foreach ($roles as $role): ?>
            <option value="<?= htmlspecialchars($role['role_id']); ?>"
              <?= old('role_id', isset($user) ? $user['role_id'] : '') === $role['role_id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($role['role_name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <small class="form-text text-muted d-block mt-1">Select the user's role/permission level</small>
      </div>

      <div class="mt-4">
        <a href="<?= base_url('/users') ?>" class="btn btn-secondary">
          <i class="fas fa-times"></i> Cancel
        </a>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save"></i> <?= isset($user) ? 'Update User' : 'Create User' ?>
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
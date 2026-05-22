<?= $this->extend('layouts/l_dashboard') ?>
<?= $this->section('content') ?>
<div class="container-fluid card py-4">
  <div class="card-header pb-0">
    <h4><?= isset($customer) ? 'Edit Customer' : 'Add Customer' ?></h4>
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

    <form action="<?= site_url('customers/save') ?>" method="post" novalidate>
      <input type="hidden" name="id" value="<?= isset($customer) ? $customer['customer_id'] : '' ?>">

      <div class="row">
        <!-- Customer Name -->
        <div class="col-md-6 mb-3">
          <label for="customer_name" class="form-label">Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control <?= old('customer_name') === false && session('failed') ? 'is-invalid' : '' ?>"
            name="customer_name" id="customer_name" placeholder="e.g., Rudi Amanah" required
            value="<?= old('customer_name', isset($customer) ? $customer['customer_name'] : '') ?>">
          <small class="form-text text-muted d-block mt-1">Enter customer's full name (max 100 characters)</small>
        </div>

        <!-- Customer Email -->
        <div class="col-md-6 mb-3">
          <label for="customer_email" class="form-label">Email <span class="text-danger">*</span></label>
          <input type="email" class="form-control <?= old('customer_email') === false && session('failed') ? 'is-invalid' : '' ?>"
            name="customer_email" id="customer_email" placeholder="your@email.com" required
            value="<?= old('customer_email', isset($customer) ? $customer['customer_email'] : '') ?>">
          <small class="form-text text-muted d-block mt-1">Use a valid email address (e.g., customer@example.com)</small>
        </div>

        <!-- Password -->
        <?php if (!isset($customer)): ?>
          <div class="col-md-6 mb-3">
            <label for="customer_password" class="form-label">Password <span class="text-danger">*</span></label>
            <input type="password" class="form-control <?= old('customer_password') === false && session('failed') ? 'is-invalid' : '' ?>"
              name="customer_password" id="customer_password" placeholder="Enter a strong password" required>
            <small class="form-text text-muted d-block mt-1">Password is required for new customer accounts</small>
          </div>
        <?php else: ?>
          <div class="col-md-6 mb-3">
            <label for="customer_password" class="form-label">Password</label>
            <input type="password" class="form-control"
              name="customer_password" id="customer_password" placeholder="Leave blank to keep current password">
            <small class="form-text text-muted d-block mt-1">Leave blank to keep the current password</small>
          </div>
        <?php endif; ?>

        <!-- Phone -->
        <div class="col-md-6 mb-3">
          <label for="customer_phone" class="form-label">Phone</label>
          <input type="text" class="form-control <?= old('customer_phone') === false && session('failed') ? 'is-invalid' : '' ?>"
            name="customer_phone" id="customer_phone" placeholder="e.g., +62 813-3948-3847"
            value="<?= old('customer_phone', isset($customer) ? $customer['customer_phone'] : '') ?>">
          <small class="form-text text-muted d-block mt-1">Optional: Enter customer's phone number (max 20 characters)</small>
        </div>

        <!-- Date of Birth -->
        <div class="col-md-6 mb-3">
          <label for="customer_dob" class="form-label">Date of Birth</label>
          <input type="date" class="form-control <?= old('customer_dob') === false && session('failed') ? 'is-invalid' : '' ?>"
            name="customer_dob" id="customer_dob"
            value="<?= old('customer_dob', isset($customer) ? $customer['customer_dob'] : '') ?>">
          <small class="form-text text-muted d-block mt-1">Optional: Select date in YYYY-MM-DD format</small>
        </div>

        <!-- Gender -->
        <div class="col-md-6 mb-3">
          <label for="customer_gender" class="form-label">Gender</label>
          <select class="form-control <?= old('customer_gender') === false && session('failed') ? 'is-invalid' : '' ?>"
            name="customer_gender" id="customer_gender">
            <option value="">-- Select Gender --</option>
            <option value="male" <?= old('customer_gender', isset($customer) ? $customer['customer_gender'] : '') === 'male' ? 'selected' : '' ?>>Male</option>
            <option value="female" <?= old('customer_gender', isset($customer) ? $customer['customer_gender'] : '') === 'female' ? 'selected' : '' ?>>Female</option>
            <option value="other" <?= old('customer_gender', isset($customer) ? $customer['customer_gender'] : '') === 'other' ? 'selected' : '' ?>>Other</option>
          </select>
          <small class="form-text text-muted d-block mt-1">Optional: Select customer's gender</small>
        </div>

        <div class="col-12 mt-4">
          <a href="<?= base_url('/customers') ?>" class="btn btn-secondary">
            <i class="fas fa-times"></i> Cancel
          </a>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> <?= isset($customer) ? 'Update Customer' : 'Create Customer' ?>
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
  // Client-side validation feedback
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
<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>
<div class="container-fluid card">
  <div class="card-header pb-0">
    <h4><?= isset($coupon) ? 'Edit Coupon' : 'Add New Coupon' ?></h4>
  </div>
  <div class="card-body">
    <!-- Error Messages Display -->
    <?php if (session()->getFlashdata('failed')): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <h5 class="alert-heading text-white"><i class="fas fa-exclamation-circle"></i> Validation Error</h5>
        <div class="text-white"><?= session()->getFlashdata('failed') ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <form action="<?= site_url('/coupons/save') ?>" method="post" novalidate>
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= isset($coupon) ? htmlspecialchars($coupon['coupon_id']) : '' ?>">

      <div class="row">
        <!-- Coupon Code -->
        <div class="col-md-6 mb-3">
          <label for="code" class="form-label">Coupon Code <span class="text-danger">*</span></label>
          <input
            type="text"
            name="code"
            id="code"
            class="form-control"
            placeholder="e.g. PROMO10"
            style="text-transform: uppercase;"
            value="<?= old('code', isset($coupon) ? htmlspecialchars($coupon['code']) : '') ?>"
            required>
          <div class="invalid-feedback">Please enter a valid coupon code.</div>
          <small class="form-text text-muted">Coupon code must be unique (max 50 characters).</small>
        </div>

        <!-- Discount Type -->
        <div class="col-md-6 mb-3">
          <label for="discount_type" class="form-label">Discount Type <span class="text-danger">*</span></label>
          <select name="discount_type" id="discount_type" class="form-select" required>
            <option value="percentage" <?= old('discount_type', isset($coupon) ? $coupon['discount_type'] : 'percentage') === 'percentage' ? 'selected' : '' ?>>Percentage (%)</option>
            <option value="fixed" <?= old('discount_type', isset($coupon) ? $coupon['discount_type'] : 'percentage') === 'fixed' ? 'selected' : '' ?>>Fixed Amount (Rp)</option>
            <option value="free_shipping" <?= old('discount_type', isset($coupon) ? $coupon['discount_type'] : 'percentage') === 'free_shipping' ? 'selected' : '' ?>>Free Shipping</option>
          </select>
          <div class="invalid-feedback">Please select a discount type.</div>
        </div>
      </div>

      <!-- Description -->
      <div class="mb-3">
        <label for="description" class="form-label">Coupon Description</label>
        <textarea
          name="description"
          id="description"
          class="form-control"
          rows="3"
          placeholder="Explain the description or terms of this coupon..."><?= old('description', isset($coupon) ? htmlspecialchars($coupon['description'] ?? '') : '') ?></textarea>
        <small class="form-text text-muted">Optional: Description to be shown to customers.</small>
      </div>

      <div class="row">
        <!-- Discount Value -->
        <div class="col-md-4 mb-3">
          <label for="discount_value" class="form-label">Discount Value <span class="text-danger">*</span></label>
          <input
            type="number"
            step="0.01"
            name="discount_value"
            id="discount_value"
            class="form-control"
            placeholder="e.g. 10 for 10%, or 50000 for Rp 50,000"
            value="<?= old('discount_value', isset($coupon) ? $coupon['discount_value'] : '') ?>"
            required>
          <div class="invalid-feedback">Please enter a valid discount value.</div>
        </div>

        <!-- Min Order Amount -->
        <div class="col-md-4 mb-3">
          <label for="min_order_amount" class="form-label">Min. Spend (Rp)</label>
          <input
            type="number"
            step="0.01"
            name="min_order_amount"
            id="min_order_amount"
            class="form-control"
            placeholder="Minimum order amount required"
            value="<?= old('min_order_amount', isset($coupon) ? $coupon['min_order_amount'] : '') ?>">
          <small class="form-text text-muted">Leave blank if no minimum order amount is required.</small>
        </div>

        <!-- Max Discount -->
        <div class="col-md-4 mb-3" id="max_discount_container">
          <label for="max_discount" class="form-label">Max. Discount / Cap (Rp)</label>
          <input
            type="number"
            step="0.01"
            name="max_discount"
            id="max_discount"
            class="form-control"
            placeholder="Maximum discount cap value"
            value="<?= old('max_discount', isset($coupon) ? $coupon['max_discount'] : '') ?>">
          <small class="form-text text-muted">Only applies to percentage type. Leave blank if uncapped.</small>
        </div>
      </div>

      <div class="row">
        <!-- Start Date -->
        <div class="col-md-6 mb-3">
          <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
          <input
            type="datetime-local"
            name="start_date"
            id="start_date"
            class="form-control"
            value="<?= old('start_date', isset($coupon) && $coupon['start_date'] ? date('Y-m-d\TH:i', strtotime($coupon['start_date'])) : date('Y-m-d\T00:00')) ?>"
            required>
          <div class="invalid-feedback">Please choose a valid start date.</div>
        </div>

        <!-- End Date -->
        <div class="col-md-6 mb-3">
          <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
          <input
            type="datetime-local"
            name="end_date"
            id="end_date"
            class="form-control"
            value="<?= old('end_date', isset($coupon) && $coupon['end_date'] ? date('Y-m-d\TH:i', strtotime($coupon['end_date'])) : date('Y-m-d\T23:59', strtotime('+1 month'))) ?>"
            required>
          <div class="invalid-feedback">Please choose a valid end date.</div>
        </div>
      </div>

      <div class="row">
        <!-- Usage Limit -->
        <div class="col-md-6 mb-3">
          <label for="usage_limit" class="form-label">Global Usage Limit</label>
          <input
            type="number"
            name="usage_limit"
            id="usage_limit"
            class="form-control"
            placeholder="How many times this coupon can be used globally"
            value="<?= old('usage_limit', isset($coupon) ? $coupon['usage_limit'] : '') ?>">
          <small class="form-text text-muted">Leave blank if unlimited usage.</small>
        </div>

        <!-- Per User Limit -->
        <div class="col-md-6 mb-3">
          <label for="per_user_limit" class="form-label">Per-Customer Usage Limit</label>
          <input
            type="number"
            name="per_user_limit"
            id="per_user_limit"
            class="form-control"
            placeholder="How many times this coupon can be used per-customer"
            value="<?= old('per_user_limit', isset($coupon) ? $coupon['per_user_limit'] : '') ?>">
          <small class="form-text text-muted">Leave blank if unlimited per-customer.</small>
        </div>
      </div>

      <!-- Switches for Boolean States -->
      <div class="row mt-2 mb-4">
        <div class="col-md-6 mb-2">
          <div class="form-check form-switch">
            <input
              type="checkbox"
              name="is_active"
              id="is_active"
              class="form-check-input"
              value="1"
              <?= old('is_active', isset($coupon) ? $coupon['is_active'] : '1') == '1' ? 'checked' : '' ?>>
            <label class="form-check-label" for="is_active">
              <strong>Coupon Active</strong>
            </label>
            <small class="d-block text-muted">Enable so the coupon can be searched and used by customers.</small>
          </div>
        </div>

        <div class="col-md-6 mb-2">
          <div class="form-check form-switch">
            <input
              type="checkbox"
              name="first_order_only"
              id="first_order_only"
              class="form-check-input"
              value="1"
              <?= old('first_order_only', isset($coupon) ? $coupon['first_order_only'] : '0') == '1' ? 'checked' : '' ?>>
            <label class="form-check-label" for="first_order_only">
              <strong>First Transaction Only (New User)</strong>
            </label>
            <small class="d-block text-muted">Only can be used by customers with zero previous successful or active transactions.</small>
          </div>
        </div>
      </div>

      <div class="mt-4">
        <a href="<?= base_url('/coupons') ?>" class="btn btn-secondary">
          <i class="fas fa-times"></i> Cancel
        </a>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save"></i> <?= isset($coupon) ? 'Save Changes' : 'Create Coupon' ?>
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

    const discountTypeSelect = document.getElementById('discount_type');
    const maxDiscountContainer = document.getElementById('max_discount_container');

    function toggleMaxDiscount() {
      const val = discountTypeSelect.value;
      const discountValueInput = document.getElementById('discount_value');
      const discountValueGroup = discountValueInput.closest('.col-md-4');
      const maxDiscountInput = document.getElementById('max_discount');

      if (val === 'fixed') {
        maxDiscountContainer.style.opacity = '0.5';
        maxDiscountInput.value = '';
        maxDiscountInput.disabled = true;
        discountValueInput.disabled = false;
        discountValueGroup.style.opacity = '1';
      } else if (val === 'free_shipping') {
        maxDiscountContainer.style.opacity = '0.5';
        maxDiscountInput.value = '';
        maxDiscountInput.disabled = true;
        discountValueInput.value = '0';
        discountValueInput.disabled = true;
        discountValueGroup.style.opacity = '0.5';
      } else {
        maxDiscountContainer.style.opacity = '1';
        maxDiscountInput.disabled = false;
        discountValueInput.disabled = false;
        discountValueGroup.style.opacity = '1';
      }
    }

    discountTypeSelect.addEventListener('change', toggleMaxDiscount);
    toggleMaxDiscount(); // Initial trigger
  });
</script>
<?= $this->endSection() ?>

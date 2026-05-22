<?= $this->extend('layouts/l_dashboard') ?>
<?= $this->section('content') ?>
<div class="container-fluid card">
  <div class="card-header pb-0">
    <h4><?= isset($eyeExamination) ? 'Edit Eye Examination' : 'Create Eye Examination' ?></h4>
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

    <form action="<?= site_url('eye-examinations/save') ?>" method="post" novalidate>
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= isset($eyeExamination) ? htmlspecialchars($eyeExamination['eye_examination_id']) : '' ?>">

      <div class="row">
        <!-- Customer Selection -->
        <div class="col-12 mb-3">
          <label for="customer_id" class="form-label">Customer <span class="text-danger">*</span></label>
          <select class="form-control" name="customer_id" id="customer_id" required>
            <option value="" disabled <?= !isset($eyeExamination) ? 'selected' : '' ?>>-- Select a customer --</option>
            <?php foreach ($customers as $customer): ?>
              <option value="<?= htmlspecialchars($customer['customer_id']); ?>"
                <?= (old('customer_id', $eyeExamination['customer_id'] ?? '') == $customer['customer_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($customer['customer_name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <small class="form-text text-muted d-block mt-1">Select the customer for this eye examination</small>
        </div>

        <!-- Left Eye Section -->
        <div class="col-12">
          <h5 class="mb-3"><i class="fas fa-eye"></i> Left Eye</h5>
        </div>

        <div class="col-12 col-md-4 mb-3">
          <label for="left_eye_sphere" class="form-label">Sphere</label>
          <input type="number" step="0.01" class="form-control" name="left_eye_sphere" id="left_eye_sphere" placeholder="e.g., 0.00"
            value="<?= old('left_eye_sphere', isset($eyeExamination) ? $eyeExamination['left_eye_sphere'] : '') ?>">
          <small class="form-text text-muted d-block mt-1">Spherical correction value</small>
        </div>

        <div class="col-12 col-md-4 mb-3">
          <label for="left_eye_cylinder" class="form-label">Cylinder</label>
          <input type="number" step="0.01" class="form-control" name="left_eye_cylinder" id="left_eye_cylinder" placeholder="e.g., 0.00"
            value="<?= old('left_eye_cylinder', isset($eyeExamination) ? $eyeExamination['left_eye_cylinder'] : '') ?>">
          <small class="form-text text-muted d-block mt-1">Cylindrical correction value</small>
        </div>

        <div class="col-12 col-md-4 mb-3">
          <label for="left_eye_axis" class="form-label">Axis</label>
          <input type="number" class="form-control" name="left_eye_axis" id="left_eye_axis" placeholder="e.g., 0" min="0" max="180"
            value="<?= old('left_eye_axis', isset($eyeExamination) ? $eyeExamination['left_eye_axis'] : '') ?>">
          <small class="form-text text-muted d-block mt-1">Axis angle (0-180 degrees)</small>
        </div>

        <!-- Right Eye Section -->
        <div class="col-12">
          <h5 class="mb-3"><i class="fas fa-eye"></i> Right Eye</h5>
        </div>

        <div class="col-12 col-md-4 mb-3">
          <label for="right_eye_sphere" class="form-label">Sphere</label>
          <input type="number" step="0.01" class="form-control" name="right_eye_sphere" id="right_eye_sphere" placeholder="e.g., 0.00"
            value="<?= old('right_eye_sphere', isset($eyeExamination) ? $eyeExamination['right_eye_sphere'] : '') ?>">
          <small class="form-text text-muted d-block mt-1">Spherical correction value</small>
        </div>

        <div class="col-12 col-md-4 mb-3">
          <label for="right_eye_cylinder" class="form-label">Cylinder</label>
          <input type="number" step="0.01" class="form-control" name="right_eye_cylinder" id="right_eye_cylinder" placeholder="e.g., 0.00"
            value="<?= old('right_eye_cylinder', isset($eyeExamination) ? $eyeExamination['right_eye_cylinder'] : '') ?>">
          <small class="form-text text-muted d-block mt-1">Cylindrical correction value</small>
        </div>

        <div class="col-12 col-md-4 mb-3">
          <label for="right_eye_axis" class="form-label">Axis</label>
          <input type="number" class="form-control" name="right_eye_axis" id="right_eye_axis" placeholder="e.g., 0" min="0" max="180"
            value="<?= old('right_eye_axis', isset($eyeExamination) ? $eyeExamination['right_eye_axis'] : '') ?>">
          <small class="form-text text-muted d-block mt-1">Axis angle (0-180 degrees)</small>
        </div>

        <!-- Clinical Notes -->
        <div class="col-12">
          <h5 class="mb-3"><i class="fas fa-notes-medical"></i> Clinical Notes</h5>
        </div>

        <div class="col-12 mb-3">
          <label for="symptoms" class="form-label">Symptoms</label>
          <textarea class="form-control" name="symptoms" id="symptoms" rows="3" placeholder="e.g., Red eyes, itching, watery eyes"><?= old('symptoms', isset($eyeExamination) ? htmlspecialchars($eyeExamination['symptoms']) : '') ?></textarea>
          <small class="form-text text-muted d-block mt-1">Optional: Describe any symptoms reported by the customer (max 500 characters)</small>
        </div>

        <div class="col-12 mb-3">
          <label for="diagnosis" class="form-label">Diagnosis</label>
          <textarea class="form-control" name="diagnosis" id="diagnosis" rows="3" placeholder="e.g., Myopia, Hyperopia, Astigmatism"><?= old('diagnosis', isset($eyeExamination) ? htmlspecialchars($eyeExamination['diagnosis']) : '') ?></textarea>
          <small class="form-text text-muted d-block mt-1">Optional: Clinical diagnosis (max 500 characters)</small>
        </div>
      </div>

      <div class="mt-4">
        <a href="<?= base_url('eye-examinations') ?>" class="btn btn-secondary">
          <i class="fas fa-times"></i> Cancel
        </a>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save"></i> <?= isset($eyeExamination) ? 'Update Examination' : 'Save Examination' ?>
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
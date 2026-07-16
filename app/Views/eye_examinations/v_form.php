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

<!-- Select2 CSS & Theme -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
  /* Styling to integrate Select2 with Argon Dashboard & Bootstrap 5 nicely */
  .select2-container--bootstrap-5 {
    z-index: 1050;
  }
  .select2-container--bootstrap-5 .select2-selection {
    border-color: #d2d6da !important;
    font-size: 0.875rem !important;
    border-radius: 0.5rem !important;
    height: 40px !important;
    display: flex !important;
    align-items: center !important;
  }
  .select2-container--bootstrap-5 .select2-selection--single {
    padding: 0.5rem 0.75rem !important;
  }
  .select2-container--bootstrap-5 .select2-selection__rendered {
    color: #495057 !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
  }
  .select2-container--bootstrap-5 .select2-selection__placeholder {
    color: #adb5bd !important;
  }
  .select2-container--bootstrap-5 .select2-selection__arrow {
    top: 50% !important;
    transform: translateY(-50%) !important;
    right: 10px !important;
  }
  .group {
    display: flex;
    flex-wrap: nowrap !important;
    gap: .5rem !important;
  }
</style>

    <form action="<?= site_url('eye-examinations/save') ?>" method="post" novalidate>
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= isset($eyeExamination) ? htmlspecialchars($eyeExamination['eye_examination_id']) : '' ?>">

      <div class="row">
        <!-- Customer Selection -->
        <div class="col-12 mb-3">
          <label for="customerSelect" class="form-label">Customer <span class="text-danger">*</span></label>
          <div class="d-flex gap-2">
            <div class="flex-grow-1">
              <select class="form-select" name="customer_id" id="customerSelect" required>
                <option value="" disabled <?= !isset($eyeExamination) ? 'selected' : '' ?>>-- Select a customer --</option>
                <?php foreach ($customers as $customer): ?>
                  <option value="<?= htmlspecialchars($customer['customer_id']); ?>"
                    <?= (old('customer_id', $eyeExamination['customer_id'] ?? '') == $customer['customer_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($customer['customer_name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="invalid-feedback">Please select a customer.</div>
            </div>
            <a href="<?= site_url('customers/form') ?>" class="btn btn-outline-primary btn-sm mb-0" id="btnAddNewCustomer" title="Tambah Customer Baru" style="display: flex; align-items: center; justify-content: center; gap: 4px; height: 40px;">
              <i class="fa fa-plus"></i> Add
            </a>
          </div>
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
          <textarea class="form-control" name="symptoms" id="symptoms" rows="3" placeholder="e.g., Red eyes, itching, watery eyes"
            ><?= old('symptoms', isset($eyeExamination) ? htmlspecialchars($eyeExamination['symptoms']) : '') ?></textarea>
          <small class="form-text text-muted d-block mt-1">Optional: Describe any symptoms reported by the customer (max 500 characters)</small>
        </div>

        <div class="col-12 mb-3">
          <label for="diagnosis" class="form-label">Diagnosis</label>
          <textarea class="form-control" name="diagnosis" id="diagnosis" rows="3" placeholder="e.g., Myopia, Hyperopia, Astigmatism"
            ><?= old('diagnosis', isset($eyeExamination) ? htmlspecialchars($eyeExamination['diagnosis']) : '') ?></textarea>
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
  $(document).ready(function() {
    // Initialize Select2 on customer dropdown
    $('#customerSelect').select2({
      theme: 'bootstrap-5',
      placeholder: '-- Select a customer --',
      width: '100%'
    });

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
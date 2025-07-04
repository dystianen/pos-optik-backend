<?= $this->extend('layouts/l_dashboard') ?>
<?= $this->section('content') ?>
<div class="container-fluid card py-4">
  <div class="card-header pb-0">
    <h4><?= isset($eyeExamination) ? 'Edit Eye Examinations' : 'Create Eye Examinations' ?></h4>
  </div>

  <div class="card-body">
    <form action="<?= site_url('eye-examinations/save') ?>" method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= isset($eyeExamination) ? $eyeExamination['eye_examination_id'] : '' ?>">

      <div class="row">
        <div class="col-12 col-md-6 mb-3">
          <label for="customer_id" class="form-label">Customer</label>
          <select class="form-control" name="customer_id" required>
            <option value="" disabled <?= !isset($eyeExamination) ? 'selected' : '' ?>>Select category</option>
            <?php foreach ($customers as $customer): ?>
              <option value="<?= $customer['customer_id']; ?>"
                <?= (old('customer_id', $eyeExamination['customer_id'] ?? '') == $customer['customer_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($customer['customer_name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label for="left_eye_axis">Left Eye Axis</label>
          <input type="number" class="form-control" name="left_eye_axis" id="left_eye_axis" placeholder="e.g., 0"
            value="<?= isset($eyeExamination) ? $eyeExamination['left_eye_axis'] : '' ?>">
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label for="left_eye_sphere">Left Eye Sphere</label>
          <input type="number" class="form-control" name="left_eye_sphere" id="left_eye_sphere" placeholder="e.g., 0"
            value="<?= isset($eyeExamination) ? $eyeExamination['left_eye_sphere'] : '' ?>">
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label for="left_eye_cylinder">Left Eye Cylinder</label>
          <input type="number" class="form-control" name="left_eye_cylinder" id="left_eye_cylinder" placeholder="e.g., 0"
            value="<?= isset($eyeExamination) ? $eyeExamination['left_eye_cylinder'] : '' ?>">
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label for="right_eye_axis">Right Eye Axis</label>
          <input type="number" class="form-control" name="right_eye_axis" id="right_eye_axis" placeholder="e.g., 0"
            value="<?= isset($eyeExamination) ? $eyeExamination['right_eye_axis'] : '' ?>">
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label for="right_eye_sphere">Right Eye Sphere</label>
          <input type="number" class="form-control" name="right_eye_sphere" id="right_eye_sphere" placeholder="e.g., 0"
            value="<?= isset($eyeExamination) ? $eyeExamination['right_eye_sphere'] : '' ?>">
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label for="right_eye_cylinder">Right Eye Cylinder</label>
          <input type="number" class="form-control" name="right_eye_cylinder" id="right_eye_cylinder" placeholder="e.g., 0"
            value="<?= isset($eyeExamination) ? $eyeExamination['right_eye_cylinder'] : '' ?>">
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label for="symptoms">Symptoms</label>
          <input class="form-control" name="symptoms" id="symptoms" placeholder="e.g., Teks"
            value="<?= isset($eyeExamination) ? $eyeExamination['symptoms'] : '' ?>">
        </div>

        <div class="col-12 col-md-6 mb-3">
          <label for="diagnosis">Diagnosis</label>
          <input class="form-control" name="diagnosis" id="diagnosis" placeholder="e.g., 0"
            value="<?= isset($eyeExamination) ? $eyeExamination['diagnosis'] : '' ?>">
        </div>
      </div>

      <div class="mt-4">
        <a href="<?= base_url('eye-examinations') ?>" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary"><?= isset($eyeExamination) ? 'Update' : 'Save' ?></button>
      </div>
    </form>
  </div>
</div>
<?= $this->endSection() ?>
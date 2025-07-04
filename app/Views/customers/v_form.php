<?= $this->extend('layouts/l_dashboard') ?>
<?= $this->section('content') ?>
<div class="container-fluid card py-4">
  <div class="card-header pb-0">
    <h4><?= isset($customer) ? 'Edit Customer' : 'Add Customer' ?></h4>
  </div>

  <div class="card-body">
    <form action="<?= site_url('customers/save') ?>" method="post">
      <input type="hidden" name="id" value="<?= isset($customer) ? $customer['customer_id'] : '' ?>">

      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="customer_name">Name</label>
          <input type="text" class="form-control" name="customer_name" id="customer_name" placeholder="e.g., Rudi Amanah" required
            value="<?= isset($customer) ? $customer['customer_name'] : '' ?>">
        </div>

        <div class="col-md-6 mb-3">
          <label for="customer_email">Email</label>
          <input type="email" class="form-control" name="customer_email" id="customer_email" placeholder="your@email.com" required
            value="<?= isset($customer) ? $customer['customer_email'] : '' ?>">
        </div>

        <?php if (!isset($customer)): ?>
          <div class="col-md-6 mb-3">
            <label for="customer_password">Password <?= isset($customer) ? '(Leave blank to keep current)' : '' ?></label>
            <input type="password" class="form-control" name="customer_password" id="customer_password" placeholder="******"
              <?= isset($customer) ? '' : 'required' ?>>
          </div>
        <?php endif; ?>

        <div class="col-md-6 mb-3">
          <label for="customer_phone">Phone</label>
          <input type="text" class="form-control" name="customer_phone" id="customer_phone" placeholder="e.g., +62 813-3948-3847"
            value="<?= isset($customer) ? $customer['customer_phone'] : '' ?>">
        </div>

        <div class="col-md-6 mb-3">
          <label for="customer_dob">Date of Birth</label>
          <input type="date" class="form-control" name="customer_dob" id="customer_dob"
            value="<?= isset($customer) ? $customer['customer_dob'] : '' ?>">
        </div>

        <div class="col-md-6 mb-3">
          <label for="customer_gender">Gender</label>
          <select class="form-control" name="customer_gender" id="customer_gender">
            <option value="Male" <?= isset($customer) && $customer['customer_gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
            <option value="Female" <?= isset($customer) && $customer['customer_gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
            <option value="Other" <?= isset($customer) && $customer['customer_gender'] == 'Other' ? 'selected' : '' ?>>Other</option>
          </select>
        </div>

        <div class="col-md-6 mb-3">
          <label for="customer_occupation">Occupation</label>
          <input type="text" class="form-control" name="customer_occupation" id="customer_occupation" placeholder="e.g., Researcher"
            value="<?= isset($customer) ? $customer['customer_occupation'] : '' ?>">
        </div>

        <hr style="border: 1px solid grey" class="my-2" />
        <div class="mb-3 text-center">
          <label for="customer_preferences">Preferences</label>
        </div>

        <div class="col-md-6 mb-3">
          <label for="color">Color Preferences</label>
          <input type="text" class="form-control" name="color" id="color" placeholder="e.g., Hijau Neon"
            value="<?= isset($customer) ? $customer['color'] : '' ?>">
        </div>

        <div class="col-md-6 mb-3">
          <label for="material">Material Preferences</label>
          <input type="text" class="form-control" name="material" id="material" placeholder="e.g., Titanium"
            value="<?= isset($customer) ? $customer['material'] : '' ?>">
        </div>

        <div class="col-md-6 mb-3">
          <label for="frame_style">Frame Style</label>
          <input type="text" class="form-control" name="frame_style" id="frame_style" placeholder="e.g., Half-rim"
            value="<?= isset($customer) ? $customer['frame_style'] : '' ?>">
        </div>

        <hr style="border: 1px solid grey" class="my-2" />
        <div class="mb-3 text-center">
          <label for="customer_preferences">Eye History</label>
        </div>

        <div class="col-md-6 mb-3">
          <label for="condition">Condition</label>
          <input class="form-control" name="condition" id="condition" placeholder="e.g., Hipermetropi" value="<?= isset($customer) ? $customer['condition'] : '' ?>" />
        </div>

        <div class="col-md-6 mb-3">
          <label for="last_checkup">Last Checkup</label>
          <input type="date" class="form-control" name="last_checkup" id="last_checkup"
            value="<?= isset($customer) ? $customer['last_checkup'] : '' ?>">
        </div>

        <label class="font-weight-bolder mt-3">Left Eye</label>
        <div class="col-md-4 mb-3">
          <label for="left_axis">Axis</label>
          <input type="number" class="form-control" name="left_axis" id="left_axis" placeholder="e.g., 0"
            value="<?= isset($customer) ? $customer['left_axis'] : '' ?>">
        </div>

        <div class="col-md-4 mb-3">
          <label for="left_sphere">Sphere</label>
          <input type="number" class="form-control" name="left_sphere" id="left_sphere" placeholder="e.g., 0"
            value="<?= isset($customer) ? $customer['left_sphere'] : '' ?>">
        </div>

        <div class="col-md-4 mb-3">
          <label for="left_cylinder">Cylinder</label>
          <input type="number" class="form-control" name="left_cylinder" id="left_cylinder" placeholder="e.g., 0"
            value="<?= isset($customer) ? $customer['left_cylinder'] : '' ?>">
        </div>

        <label for="customer_preferences" class="font-weight-bolder mt-3">Right Eye</label>
        <div class="col-md-4 mb-3">
          <label for="right_axis">Axis</label>
          <input type="number" class="form-control" name="right_axis" id="right_axis" placeholder="e.g., 0"
            value="<?= isset($customer) ? $customer['right_axis'] : '' ?>">
        </div>

        <div class="col-md-4 mb-3">
          <label for="right_sphere">Sphere</label>
          <input type="number" class="form-control" name="right_sphere" id="right_sphere" placeholder="e.g., 0"
            value="<?= isset($customer) ? $customer['right_sphere'] : '' ?>">
        </div>

        <div class="col-md-4 mb-3">
          <label for="right_cylinder">Cylinder</label>
          <input type="number" class="form-control" name="right_cylinder" id="right_cylinder" placeholder="e.g., 0"
            value="<?= isset($customer) ? $customer['right_cylinder'] : '' ?>">
        </div>

        <div class="col-12 mt-4">
          <a href="<?= base_url('/customers') ?>" class="btn btn-secondary">Cancel</a>
          <button type="submit" class="btn btn-primary"><?= isset($customer) ? 'Update' : 'Save' ?></button>
        </div>
      </div>
    </form>
  </div>
</div>
<?= $this->endSection() ?>
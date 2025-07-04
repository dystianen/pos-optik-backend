<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>
<div class="container-fluid card">
  <div class="card-header pb-0">
    <h4><?= isset($category) ? 'Edit' : 'Add' ?> Role</h4>
  </div>
  <div class="card-body">
    <form action="<?= site_url('/roles/save') ?>" method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= isset($role) ? $role['role_id'] : '' ?>">

      <div class="mb-3">
        <label for="role_name" class="form-label">Name</label>
        <input
          type="text"
          name="role_name"
          class="form-control"
          value="<?= isset($role) ? esc($role['role_name']) : '' ?>"
          required>
      </div>

      <div class="mb-3">
        <label for="role_description" class="form-label">Description</label>
        <input
          type="text"
          name="role_description"
          class="form-control"
          value="<?= isset($role) ? esc($role['role_description']) : '' ?>"
          required>
      </div>

      <a href="<?= base_url('/roles') ?>" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary"><?= isset($role) ? 'Update' : 'Save' ?></button>
    </form>
  </div>
</div>
<?= $this->endSection() ?>
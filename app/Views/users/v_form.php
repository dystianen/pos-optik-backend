<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>
<div class="container-fluid card">
  <div class="card-header pb-0">
    <h4><?= isset($role) ? 'Edit' : 'Add' ?> User</h4>
  </div>
  <div class="card-body">
    <form action="<?= site_url('/users/save') ?>" method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= isset($user) ? $user['user_id'] : '' ?>">

      <div class="mb-3">
        <label for="user_name" class="form-label">Name</label>
        <input
          type="text"
          name="user_name"
          class="form-control"
          value="<?= isset($user) ? esc($user['user_name']) : '' ?>"
          required>
      </div>

      <div class="mb-3">
        <label for="user_email" class="form-label">Email</label>
        <input
          type="text"
          name="user_email"
          class="form-control"
          value="<?= isset($user) ? esc($user['user_email']) : '' ?>"
          required>
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input
          type="password"
          name="password"
          class="form-control"
          value="<?= isset($user) ? esc($user['password']) : '' ?>"
          required>
      </div>

      <div class="mb-3">
        <label for="role_id" class="form-label">Role</label>
        <select class="form-control" name="role_id" required>
          <option value="" disabled <?= !isset($user) ? 'selected' : '' ?>>Select role</option>
          <?php foreach ($roles as $role): ?>
            <option value="<?= $role['role_id']; ?>"
              <?= (old('role_id', $user['role_id'] ?? '') == $role['role_id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($role['role_name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <a href="<?= base_url('/users') ?>" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary"><?= isset($user) ? 'Update' : 'Save' ?></button>
    </form>
  </div>
</div>
<?= $this->endSection() ?>
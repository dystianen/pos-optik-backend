<?= $this->extend('layouts/l_auth.php') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-center min-vh-90">
  <div class="col-lg-4 col-md-6 col-12">
    <div class="card shadow-lg border-0">
      <div class="card-body p-5">

        <h2 class="text-center mb-2">OPTIKERS<span class="text-primary">.</span></h2>
        <p class="text-center text-muted mb-4">
          Log in with your admin account
        </p>

        <form action="<?= base_url('signin/store') ?>" method="post" novalidate>

          <div class="form-group mb-3">
            <label>Email</label>
            <input name="email" type="email" class="form-control form-control-lg" placeholder="Email" required>
            <div class="invalid-feedback">Please enter a valid email address.</div>
          </div>

          <div class="form-group mb-4">
            <label>Password</label>
            <input name="password" type="password" class="form-control form-control-lg" placeholder="Password" required>
            <div class="invalid-feedback">Please enter your password.</div>
          </div>

          <button type="submit" class="btn btn-primary btn-lg w-100 shadow">
            Log in
          </button>

        </form>

      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (form) {
      form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
          e.preventDefault();
          e.stopPropagation();
        }
        form.classList.add('was-validated');
      });
    }
  });
</script>

<?= $this->endSection() ?>
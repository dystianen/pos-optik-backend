<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>
<div class="container-fluid py-4">
  <div class="row justify-content-center">
    <div class="col-lg-6 col-md-8 text-center">
      <div class="card shadow border-0 mt-5 py-5 px-4 bg-white border-radius-xl">
        <div class="card-body">
          <div class="icon-shape bg-light-danger text-danger rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
            <i class="fa-solid fa-shield-halved" style="font-size: 48px; color: #f5365c;"></i>
          </div>
          <h2 class="font-weight-bolder text-dark mb-2">Access Denied</h2>
          <p class="text-lead text-muted mb-4">
            You do not have the required permissions to access this page. If you believe this is an error, please contact your administrator.
          </p>
          <div class="d-flex justify-content-center gap-3">
            <a href="<?= base_url('dashboard') ?>" class="btn bg-gradient-primary btn-md px-4 border-radius-md">
              <i class="fa-solid fa-house me-2"></i> Go to Dashboard
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

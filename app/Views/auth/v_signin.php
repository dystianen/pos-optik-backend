<?= $this->extend('layouts/l_auth.php') ?>
<?= $this->section('content') ?>
<div id="auth">
  <div class="row">
    <div class="col-lg-5 col-12" style="align-self: center">
      <div id="auth-left">
        <h1>OPTIKERS <span color="#7048E8">.</span></h1>

        <?php if (session()->getFlashData('failed')) : ?>
          <div class="alert alert-danger" role="alert">
            <?php echo session("failed") ?>
          </div>
        <?php endif; ?>

        <?php if (session()->getFlashData('success')) : ?>
          <div class="alert success alert-success" role="alert">
            <?php echo session("success") ?>
          </div>
        <?php endif; ?>

        <p class="auth-subtitle mb-5">Log in with your data that the admin entered.</p>

        <form action="<?php echo base_url(); ?>signin/store" method="post">
          <div class="form-group position-relative has-icon-left mb-4">
            <input name="email" type="text" class="form-control form-control-xl" placeholder="Email">
            <div class="form-control-icon">
              <i class="bi bi-person"></i>
            </div>
          </div>
          <div class="form-group position-relative has-icon-left mb-4">
            <input name="password" type="password" class="form-control form-control-xl" placeholder="Password">
            <div class="form-control-icon">
              <i class="bi bi-shield-lock"></i>
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-block btn-lg shadow-lg mt-2 w-100">Log in</button>
          </button>
        </form>
      </div>
    </div>
    <div class="col-lg-7 d-none d-lg-block">
      <img src="/assets/img/optik.jpeg" style="width: 100%; height: 90vh; border-radius: 10px">
    </div>
  </div>
</div>
<?= $this->endSection() ?>
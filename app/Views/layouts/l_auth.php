<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="<?= base_url('assets'); ?>/img/apple-icon.png">
  <link rel="icon" type="image/png" href="<?= base_url('assets'); ?>/img/favicon.png">
  <title>
    Optikers
  </title>
  <!--     Fonts and icons     -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
  <!-- Nucleo Icons -->
  <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-svg.css" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <!-- CSS Files -->
  <link id="pagestyle" href="<?= base_url('assets'); ?>/css/argon-dashboard.css?v=2.1.0" rel="stylesheet" />
  <link id="pagestyle" href="<?= base_url('assets'); ?>/css/auth.css?v=2.1.0" rel="stylesheet" />
  <!-- JQUERY -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</head>

<body class="g-sidenav-show bg-gray-100">
  <div class="position-fixed top-5 start-50 translate-middle p-3" style="z-index: 1100">
    <?php if (session()->getFlashData('failed')) : ?>
      <div class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-body">
          <?= session("failed") ?>
        </div>
      </div>
    <?php endif; ?>

    <?php if (session()->getFlashData('success')) : ?>
      <div class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-body">
          <?= session("success") ?>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <main class="main-content position-relative border-radius-lg d-flex flex-column justify-content-between min-vh-100">
    <div class="mx-4">
      <!-- Start Content -->
      <div class="container-fluid py-4">
        <?= $this->renderSection('content') ?>
      </div>
      <!-- End Content -->
    </div>
  </main>

  <!--   Core JS Files   -->
  <script src="<?= base_url('assets'); ?>/js/core/popper.min.js"></script>
  <script src="<?= base_url('assets'); ?>/js/core/bootstrap.min.js"></script>
  <script src="<?= base_url('assets'); ?>/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="<?= base_url('assets'); ?>/js/plugins/smooth-scrollbar.min.js"></script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }

    const toastElList = [].slice.call(document.querySelectorAll('.toast'))
    toastElList.map(function(toastEl) {
      const toast = new bootstrap.Toast(toastEl, {
        delay: 3000
      });
      toast.show();
    });

    function showDropdown() {
      const dropdowns = document.getElementsByClassName('dropdown-menu');
      if (dropdowns.length > 0) {
        const dropdown = dropdowns[0];
        dropdown.classList.remove('d-none'); // menghapus class d-none
      }
    }
  </script>

  </script>
  <!-- Github buttons -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="<?= base_url('assets'); ?>/assets/js/argon-dashboard.min.js?v=2.1.0"></script>

  <?= $this->renderSection('scripts') ?>
</body>

</html>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="<?= base_url(); ?>/favicon.ico">
  <link rel="icon" href="<?= base_url(); ?>/favicon.ico">
  <title>
    Optikers
  </title>
  <!--     Fonts and icons     -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
  <!-- Nucleo Icons -->
  <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-svg.css" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/218d5eb4ba.js" crossorigin="anonymous"></script>
  <!-- <link rel="stylesheet" href="path/to/font-awesome/css/font-awesome.min.css"> -->
  <!-- CSS Files -->
  <link id="pagestyle" href="<?= base_url('assets'); ?>/css/argon-dashboard.css?v=2.1.0" rel="stylesheet" />
  <link id="pagestyle" href="<?= base_url('assets'); ?>/css/custom.css?v=2.1.3" rel="stylesheet" />
  <!-- JQUERY -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
    integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
</head>

<body class="g-sidenav-show bg-gray-100">
  <?php
  $currentURI = uri_string();
  $segments = explode('/', $currentURI);

  // Mapping khusus untuk label
  $breadcrumbLabels = [
    'dashboard' => 'Dashboard',
    'products' => 'Products',
    'product-category' => 'Product Category',
    'inventory' => 'Inventory',
    'customers' => 'Customers',
    'customers/form' => 'Customer Form',
    'eye-examinations' => 'Eye Examinations',
    'coupons' => 'Coupons',
  ];

  // Build breadcrumbs dengan URL
  $breadcrumbTrail = [];
  $path = '';
  foreach ($segments as $segment) {
    $path .= ($path === '' ? '' : '/') . $segment;
    $label = $breadcrumbLabels[$segment] ?? ucwords(str_replace('-', ' ', $segment));
    $breadcrumbTrail[] = [
      'label' => $label,
      'url' => base_url($path),
    ];
  }

  $currentPage = end($breadcrumbTrail)['label'];

  $salesMenus = ['online-sales', 'offline-sales', 'refund-sales', 'cancellation-sales'];
  $isSalesActive = in_array($segments[0], $salesMenus);
  ?>

  <div class="position-fixed top-5 start-50 translate-middle p-3" style="z-index: 1100">
    <?php if (session()->getFlashData('failed')): ?>
      <div class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-body">
          <?= session("failed") ?>
        </div>
      </div>
    <?php endif; ?>

    <?php if (session()->getFlashData('success')): ?>
      <div class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive"
        aria-atomic="true">
        <div class="toast-body">
          <?= session("success") ?>
        </div>
      </div>
    <?php endif; ?>
  </div>  

  <div class="min-height-400 bg-dark position-fixed w-100"></div>

  <aside
    class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4 "
    id="sidenav-main">
    <div class="sidenav-header">
      <a class="navbar-brand m-0 d-flex gap-2" href="<?= base_url('dashboard') ?>" target="_blank">
        <h5 class="ms-1 font-weight-bolder" style="font-size: 28px;">OPTIKERS<span style="color: #7048E8">.</span></h5>
      </a>
    </div>
    <hr class="horizontal dark mt-0">

    <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
      <ul class="navbar-nav">
        <?php $roleName = session('role_name'); ?>

        <!-- Menu untuk semua role -->
        <li class="nav-item">
          <a class="nav-link <?= $currentURI === 'dashboard' ? 'active' : '' ?>" href="/dashboard">
            <div class="me-2 d-flex align-items-center justify-content-center">
              <i class="fas fa-tv"></i>
            </div>
            <span class="nav-link-text ms-1">Dashboard</span>
          </a>
        </li>

        <!-- Admin (1) dan Cashier (3) -->
        <?php if (in_array($roleName, ['admin', 'cashier'])): ?>
          <li class="nav-item">
            <a class="nav-link d-flex justify-content-between align-items-center <?= $isSalesActive ? '' : 'collapsed' ?>"
              data-bs-toggle="collapse" href="#salesMenu" role="button"
              aria-expanded="<?= $isSalesActive ? 'true' : 'false' ?>" aria-controls="salesMenu">
              <div class="d-flex align-items-center">
                <div class="me-2 d-flex align-items-center justify-content-center">
                  <i class="fa-solid fa-chart-line"></i>
                </div>
                <span class="nav-link-text ms-1">Sales</span>
              </div>
            </a>

            <div class="collapse <?= $isSalesActive ? 'show' : '' ?>" id="salesMenu">
              <ul class="navbar-nav ms-4 flex-column">

                <!-- Online Sales -->
                <li class="nav-item">
                  <a class="nav-link <?= $segments[0] === 'online-sales' ? 'active' : '' ?>" href="/online-sales">
                    <i class="fa-solid fa-bag-shopping me-1"></i>
                    <span class="nav-link-text">Online Sales</span>
                  </a>
                </li>

                <!-- Offline / Offline Sales -->
                <li class="nav-item">
                  <a class="nav-link <?= $segments[0] === 'offline-sales' ? 'active' : '' ?>" href="/offline-sales">
                    <i class="fa-solid fa-store me-1"></i>
                    <span class="nav-link-text">Offline Sales</span>
                  </a>
                </li>

                <!-- Refund Sales -->
                <li class="nav-item">
                  <a class="nav-link <?= $segments[0] === 'refund-sales' ? 'active' : '' ?>" href="/refund-sales">
                    <i class="fa-solid fa-rotate-left me-1"></i>
                    <span class="nav-link-text">Refund Sales</span>
                  </a>
                </li>

                <!-- Cancelled Sales -->
                <li class="nav-item">
                  <a class="nav-link <?= $segments[0] === 'cancellation-sales' ? 'active' : '' ?>"
                    href="/cancellation-sales">
                    <i class="fa-solid fa-ban me-1"></i>
                    <span class="nav-link-text">Cancellation Sales</span>
                  </a>
                </li>
              </ul>
            </div>
          </li>
        <?php endif; ?>

        <!-- Admin (1) dan Cashier (3) - Reports -->
        <?php if (in_array($roleName, ['admin', 'cashier'])): ?>
          <?php
          $isReportsActive = $segments[0] === 'reports';
          $isReportsSalesActive = $currentURI === 'reports' || (isset($segments[1]) && $segments[1] === 'sales');
          $isReportsInventoryActive = isset($segments[1]) && $segments[1] === 'inventory';
          ?>
          <li class="nav-item">
            <a class="nav-link <?= $isReportsActive ? 'active' : '' ?>" data-bs-toggle="collapse" href="#reportsMenu"
              role="button" aria-expanded="<?= $isReportsActive ? 'true' : 'false' ?>" aria-controls="reportsMenu">
              <div class="me-2 d-flex align-items-center justify-content-center">
                <i class="fa-solid fa-file-invoice-dollar"></i>
              </div>
              <span class="nav-link-text ms-1">Reports</span>
            </a>

            <div class="collapse <?= $isReportsActive ? 'show' : '' ?>" id="reportsMenu">
              <ul class="navbar-nav ms-4 flex-column">
                <li class="nav-item">
                  <a class="nav-link <?= $isReportsSalesActive ? 'active' : '' ?>" href="/reports/sales">
                    <i class="fa-solid fa-chart-line me-1"></i>
                    <span class="nav-link-text">Sales Report</span>
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link <?= $isReportsInventoryActive ? 'active' : '' ?>" href="/reports/inventory">
                    <i class="fa-solid fa-boxes-packing me-1"></i>
                    <span class="nav-link-text">Inventory Report</span>
                  </a>
                </li>
              </ul>
            </div>
          </li>
        <?php endif; ?>

        <!-- Admin (1) dan Optometrist (2) -->
        <?php if (in_array($roleName, ['admin', 'optometrist'])): ?>
          <li class="nav-item">
            <a class="nav-link <?= $currentURI === 'eye-examinations' ? 'active' : '' ?>" href="/eye-examinations">
              <div class="me-2 d-flex align-items-center justify-content-center">
                <i class="fas fa-glasses"></i>
              </div>
              <span class="nav-link-text ms-1">Eye Examinations</span>
            </a>
          </li>
        <?php endif; ?>

        <hr class="horizontal dark">

        <!-- Admin (1) dan Inventory (4) -->
        <?php if (in_array($roleName, ['admin', 'inventory'])): ?>
          <li class="nav-item">
            <a class="nav-link <?= $currentURI === 'products' ? 'active' : '' ?>" href="/products">
              <div class="me-2 d-flex align-items-center justify-content-center">
                <i class="fas fa-shopping-basket"></i>
              </div>
              <span class="nav-link-text ms-1">Products</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $currentURI === 'inventory' ? 'active' : '' ?>" href="/inventory">
              <div class="me-2 d-flex align-items-center justify-content-center">
                <i class="fas fa-truck-loading"></i>
              </div>
              <span class="nav-link-text ms-1">Inventory</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $currentURI === 'product-category' ? 'active' : '' ?>" href="/product-category">
              <div class="me-2 d-flex align-items-center justify-content-center">
                <i class="fas fa-filter"></i>
              </div>
              <span class="nav-link-text ms-1">Product Category</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $currentURI === 'product-attribute' ? 'active' : '' ?>" href="/product-attribute">
              <div class="me-2 d-flex align-items-center justify-content-center">
                <i class="fa-solid fa-bacteria"></i>
              </div>
              <span class="nav-link-text ms-1">Product Attribute</span>
            </a>
          </li>

          <hr class="horizontal dark">
        <?php endif; ?>

        <!-- Admin (1) -->
        <?php if (in_array($roleName, ['admin'])): ?>
          <li class="nav-item">
            <a class="nav-link <?= $currentURI === 'customers' ? 'active' : '' ?>" href="/customers">
              <div class="me-2 d-flex align-items-center justify-content-center">
                <i class="fas fa-users"></i>
              </div>
              <span class="nav-link-text ms-1">Customers</span>
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link <?= $currentURI === 'users' ? 'active' : '' ?>" href="/users">
              <div class="me-2 d-flex align-items-center justify-content-center">
                <i class="fas fa-user-shield"></i>
              </div>
              <span class="nav-link-text ms-1">Users</span>
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link <?= $currentURI === 'roles' ? 'active' : '' ?>" href="/roles">
              <div class="me-2 d-flex align-items-center justify-content-center">
                <i class="fa-solid fa-user-lock"></i>
              </div>
              <span class="nav-link-text ms-1">Roles</span>
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link <?= $segments[0] === 'coupons' ? 'active' : '' ?>" href="/coupons">
              <div class="me-2 d-flex align-items-center justify-content-center">
                <i class="fa-solid fa-ticket"></i>
              </div>
              <span class="nav-link-text ms-1">Coupons</span>
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link <?= $currentURI === 'dashboard/recommendation-debug' ? 'active' : '' ?>"
              href="/dashboard/recommendation-debug">
              <div class="me-2 d-flex align-items-center justify-content-center">
                <i class="fa-solid fa-square-poll-vertical"></i>
              </div>
              <span class="nav-link-text ms-1">Recs Debugger</span>
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </aside>

  <main class="main-content position-relative border-radius-lg d-flex flex-column justify-content-between min-vh-100">
    <div class="mx-4">
      <!-- Navbar -->
      <nav class="fixed navbar navbar-main navbar-expand-lg px-0 shadow-none border-radius-xl " id="navbarBlur"
        data-scroll="false">
        <div class="container-fluid py-1">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
              <li class="breadcrumb-item text-sm">
                <a class="opacity-5 text-white" href="<?= base_url('dashboard') ?>">Pages</a>
              </li>

              <?php foreach (array_slice($breadcrumbTrail, 0, -1) as $item): ?>
                <li class="breadcrumb-item text-sm text-white">
                  <a href="<?= esc($item['url']) ?>" class="text-white opacity-8"><?= esc($item['label']) ?></a>
                </li>
              <?php endforeach; ?>

              <li class="breadcrumb-item text-sm text-white active" aria-current="page">
                <?= esc($currentPage) ?>
              </li>
            </ol>
            <h6 class="font-weight-bolder text-white mb-0"><?= esc($currentPage) ?></h6>
          </nav>

          <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
            <div class="ms-md-auto" />
            <ul class="navbar-nav justify-content-end gap-4">
              <li class="nav-item position-relative">
                <a href="#" class="nav-link text-white" onclick="toggleNotif(event)">
                  <i class="fa fa-bell"></i>
                  <span class="badge bg-danger badge-notification" style="display:none;">0</span>
                </a>

                <div id="notifMenu" class="notif-dropdown">
                  <div class="notif-header">
                    <h5>Notifications</h5>
                  </div>

                  <div id="notifList"></div>

                  <div class="notif-footer">
                    <a href="/notifications">Lihat semua notifikasi</a>
                  </div>
                </div>
              </li>

              <li class="nav-item dropdown d-flex align-items-center">
                <a class="nav-link text-white font-weight-bold px-0 dropdown-toggle" onclick="showDropdown()" href="#"
                  id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fa fa-user me-sm-1"></i>
                  <span class="d-sm-inline d-none"><?= session()->get('full_name') ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end d-none" id="userDropdown" aria-labelledby="navbarDropdown">
                  <li><a class="dropdown-item" href="<?= base_url('logout') ?>">Logout</a></li>
                </ul>
              </li>

              <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
                <a href="javascript:;" class="nav-link text-white p-0" id="iconNavbarSidenav">
                  <div class="sidenav-toggler-inner">
                    <i class="sidenav-toggler-line bg-white"></i>
                    <i class="sidenav-toggler-line bg-white"></i>
                    <i class="sidenav-toggler-line bg-white"></i>
                  </div>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </nav>
      <!-- End Navbar -->

      <!-- Start Content -->
      <div class="container-fluid py-4">
        <?= $this->renderSection('content') ?>
      </div>
      <!-- End Content -->
    </div>
    <footer class="footer mx-5 my-3">
      <div class="container-fluid">
        <div class="row align-items-center justify-content-lg-between">
          <div class="col-lg-6 mb-lg-0 mb-4">
            <div class="copyright text-center text-sm text-muted text-lg-start">
              ©
              <script>
                document.write(new Date().getFullYear())
              </script>,
              made with <i class="fa fa-heart"></i> by
              <a href="https://dystianen.vercel.app" class="font-weight-bold" target="_blank">Devyus</a>.
            </div>
          </div>
          <div class="col-lg-6">
            <ul class="nav nav-footer justify-content-center justify-content-lg-end">
              <li class="nav-item">
                <a href="https://dystianen.vercel.app/about" class="nav-link text-muted" target="_blank">About</a>
              </li>
              <li class="nav-item">
                <a href="https://dystianen.vercel.app/portfolio" class="nav-link pe-0 text-muted"
                  target="_blank">Portfolio</a>
              </li>
              <li class="nav-item">
                <a href="https://dystianen.vercel.app/certificate" class="nav-link pe-0 text-muted"
                  target="_blank">Certificate</a>
              </li>
            </ul>
          </div>
        </div>
    </footer>
  </main>

  <style>
    .notif-dropdown {
      position: absolute;
      top: 120%;
      right: 0;
      width: 320px;
      background: #fff;
      border-radius: 14px;
      box-shadow: 0 12px 35px rgba(0, 0, 0, .18);
      z-index: 9999;

      display: flex;
      flex-direction: column;

      /* hidden state */
      opacity: 0;
      transform: translateY(-10px);
      visibility: hidden;
      pointer-events: none;

      transition:
        opacity .25s cubic-bezier(.4, 0, .2, 1),
        transform .25s cubic-bezier(.4, 0, .2, 1);
    }

    .notif-header {
      padding: 10px;
      border-bottom: 1px solid #eee;
    }

    .notif-dropdown.show {
      opacity: 1;
      transform: translateY(0);
      visibility: visible;
      pointer-events: auto;
    }

    .badge-notification {
      position: absolute;
      top: -2px;
      right: -4px;
      font-size: 10px;
      padding: 4px 6px;
    }

    #notifList {
      max-height: 320px;
      overflow-y: auto;
    }

    /* item */
    .notif-item {
      display: flex;
      gap: 12px;
      padding: 12px 14px;
      text-decoration: none;
      color: #344767;
      transition: background .2s ease;
      border-radius: 14px;
    }

    .notif-item:hover {
      background: #f5f7fa;
    }

    .notif-icon {
      width: 36px;
      height: 36px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(0, 0, 0, .05);
      font-size: 14px;
      flex-shrink: 0;
    }

    .notif-content {
      display: flex;
      flex-direction: column;
    }

    .notif-title {
      font-size: 14px;
      line-height: 1.3;
    }

    .notif-time {
      font-size: 12px;
      color: #8392ab;
      margin-top: 2px;
    }

    /* footer */
    .notif-footer {
      padding: 10px;
      text-align: center;
      border-top: 1px solid #eee;
    }

    .notif-footer a {
      font-size: 13px;
      color: #7048e8;
      text-decoration: none;
      font-weight: 600;
    }
  </style>

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
    toastElList.map(function (toastEl) {
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

    function toggleNotif(e) {
      e.preventDefault();
      e.stopPropagation();

      const menu = document.getElementById('notifMenu');
      menu.classList.toggle('show');
    }

    // klik di luar → close
    document.addEventListener('click', function () {
      const menu = document.getElementById('notifMenu');
      menu.classList.remove('show');
    });

    // klik di dalam dropdown jangan close
    document.getElementById('notifMenu').addEventListener('click', function (e) {
      e.stopPropagation();
    });

    const notifMenu = document.getElementById('notifMenu');
    const notifBadge = document.querySelector('.badge-notification');

    function loadNotifications() {
      fetch('/notifications/unread')
        .then(res => res.json())
        .then(res => {

          if (!res.status) return;

          /* badge */
          if (res.count > 0) {
            notifBadge.textContent = res.count;
            notifBadge.style.display = 'inline-flex';
          } else {
            notifBadge.style.display = 'none';
          }

          const notifList = document.getElementById('notifList');
          notifList.innerHTML = '';

          /* empty state */
          if (res.data.length === 0) {
            notifList.innerHTML = `
          <div class="notif-item">
            <div class="notif-content">
              <div class="notif-title text-muted">
                Tidak ada notifikasi
              </div>
            </div>
          </div>
        `;
            return;
          }

          /* render notif */
          res.data.forEach(notif => {
            const item = document.createElement('a');
            item.href = getNotifLink(notif);
            item.className = 'notif-item';
            item.onclick = function () {
              readNotif(notif.notification_id);
            };

            item.innerHTML = `
          <div class="notif-icon ${getNotifColor(notif.type)}">
            <i class="${getNotifIcon(notif.type)}"></i>
          </div>
          <div class="notif-content">
            <div class="notif-title">${notif.message}</div>
            <div class="notif-time">
              <i class="fa fa-clock"></i> ${timeAgo(notif.created_at)}
            </div>
          </div>
        `;

            notifList.appendChild(item);
          });
        })
        .catch(err => console.error('Notif error:', err));
    }


    function readNotif(id) {
      fetch('/notifications/read/' + id, {
        method: 'POST'
      });
    }

    /* helper */
    function getNotifIcon(type) {
      switch (type) {
        case 'new_order':
          return 'fa fa-shopping-cart';
        case 'stock':
          return 'fa fa-box';
        default:
          return 'fa fa-bell';
      }
    }

    function getNotifColor(type) {
      switch (type) {
        case 'order':
          return 'text-success';
        case 'stock':
          return 'text-warning';
        default:
          return 'text-primary';
      }
    }

    function getNotifLink(notif) {
      if (notif.type === 'new_order') {
        return `/online-sales/${notif.related_id}`;
      } else if (['low_stock', 'stock_empty'].includes(notif.type)) {
        return `/products/form?id=${notif.related_id}`;
      } else if (notif.type === 'cancel_order') {
        return `/cancellation-sales/${notif.related_id}`;
      } else if (notif.type === 'refund_order') {
        return `/refund-sales/${notif.related_id}`;
      }
      return '#';
    }

    function timeAgo(date) {
      const seconds = Math.floor((new Date() - new Date(date)) / 1000);
      const units = [{
        l: 'tahun',
        s: 31536000
      },
      {
        l: 'bulan',
        s: 2592000
      },
      {
        l: 'hari',
        s: 86400
      },
      {
        l: 'jam',
        s: 3600
      },
      {
        l: 'menit',
        s: 60
      }
      ];

      for (let u of units) {
        const v = Math.floor(seconds / u.s);
        if (v > 0) return `${v} ${u.l} lalu`;
      }
      return 'baru saja';
    }

    /* initial + realtime */
    loadNotifications();

    // ==========================================
    // REAL-TIME LIST UPDATES (PUSHER)
    // ==========================================
    const pusherKey = '<?= env('pusher.key') ?>';
    if (pusherKey) {
      const pusher = new Pusher(pusherKey, {
        cluster: 'ap1'
      });

      const channel = pusher.subscribe('pos-channel');
      channel.bind_global(function (eventName, data) {
        console.log('Global Event:', eventName, data);

        // Map events to potential updates
        const updateEvents = [
          'dashboard-update',
          'order-online-new',
          'pos-order-new',
          'order-approved',
          'order-rejected',
          'order-shipped',
          'order-status-update',
          'cancellation-requested',
          'refund-requested',
          'stock-update'
        ];

        if (updateEvents.includes(eventName)) {
          // Refresh notifications
          loadNotifications();

          // If we are on dashboard, trigger its refresh function
          if (typeof refreshDashboardData === 'function') {
            refreshDashboardData();
          }

          // Trigger global table refresh for list views
          refreshRealtimeTable();

          // Trigger global detail refresh for detail views (without full reload!)
          refreshRealtimeDetail();
        }
      });

      // Export pusher for specialized views (like dashboard)
      window.pusher = pusher;
    }

    /**
     * Re-fetches the current page via AJAX and replaces table content
     * This keeps filters/pagination while showing new data
     */
    function refreshRealtimeTable() {
      const tbody = document.getElementById('realtime-tbody');
      if (!tbody) return;

      console.log('Refreshing table data...');
      const currentUrl = window.location.href;

      fetch(currentUrl)
        .then(res => res.text())
        .then(html => {
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');

          // Swap Tbody
          const newTbody = doc.getElementById('realtime-tbody');
          if (newTbody) {
            tbody.innerHTML = newTbody.innerHTML;
          }

          // Swap Pagination
          const oldPagination = document.getElementById('realtime-pagination');
          const newPagination = doc.getElementById('realtime-pagination');
          if (oldPagination && newPagination) {
            oldPagination.innerHTML = newPagination.innerHTML;
          }
        })
        .catch(err => console.error('Refresh table error:', err));
    }

    /**
     * Re-fetches the current page via AJAX and replaces the detail container content
     * This updates warning alerts, locked buttons, and details without full page reload.
     */
    function refreshRealtimeDetail() {
      const container = document.getElementById('realtime-detail-container');
      if (!container) return;

      console.log('Refreshing detail content...');
      const currentUrl = window.location.href;

      fetch(currentUrl)
        .then(res => res.text())
        .then(html => {
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');

          const newContainer = doc.getElementById('realtime-detail-container');
          if (newContainer) {
            container.innerHTML = newContainer.innerHTML;
          }
        })
        .catch(err => console.error('Refresh detail error:', err));
    }

    $(document).on('submit', 'form.confirm-delete', function (e) {
      e.preventDefault();
      const form = this;
      Swal.fire({
        title: 'Are you sure?',
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f5365c',
        cancelButtonColor: '#8392ab',
        confirmButtonText: 'Yes, Delete!',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          form.submit();
        }
      });
    });
  </script>
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <script src="<?= base_url('assets'); ?>/js/argon-dashboard.js?v=2.1.3"></script>

  <?= $this->renderSection('scripts') ?>
</body>

</html>
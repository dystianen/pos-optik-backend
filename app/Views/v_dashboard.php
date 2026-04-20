<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>
<div class="container-fluid">
  <div class="row">
    <?php
    $cards = [
      [
        'title' => 'Total Revenue',
        'value' => 'Rp ' . number_format($totalRevenue, 0, ',', '.'),
        'icon'  => 'ni ni-money-coins',
        'color' => 'success'
      ],
      [
        'title' => 'Orders Today',
        'value' => $totalOrdersToday,
        'icon'  => 'ni ni-cart',
        'color' => 'info'
      ],
      [
        'title' => 'Online Sales',
        'value' => 'Rp ' . number_format($onlineSales, 0, ',', '.'),
        'icon'  => 'ni ni-world',
        'color' => 'primary'
      ],
      [
        'title' => 'In Store Sales',
        'value' => 'Rp ' . number_format($posSales, 0, ',', '.'),
        'icon'  => 'ni ni-shop',
        'color' => 'warning'
      ],
      [
        'title' => 'Customers',
        'value' => $totalCustomers,
        'icon'  => 'ni ni-single-02',
        'color' => 'dark'
      ],
      [
        'title' => 'Low Stock Items',
        'value' => $lowStockCount,
        'icon'  => 'ni ni-bell-55',
        'color' => 'danger'
      ],
    ];

    foreach ($cards as $index => $card): ?>
      <div class="col-xl-4 col-lg-4 col-md-6 mb-4">
        <div class="card">
          <div class="card-body p-3">
            <div class="row">
              <div class="col-8">
                <div class="numbers">
                  <p class="text-sm mb-0 text-uppercase font-weight-bold">
                    <?= $card['title'] ?>
                  </p>
                  <h5 class="font-weight-bolder mb-0 mt-2" id="val-<?= strtolower(str_replace(' ', '-', $card['title'])) ?>">
                    <?= $card['value'] ?>
                  </h5>
                </div>
              </div>
              <div class="col-4 text-end">
                <div class="icon icon-shape bg-gradient-<?= $card['color'] ?> shadow text-center rounded-circle">
                  <i class="<?= $card['icon'] ?> text-lg opacity-10" aria-hidden="true"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach ?>
  </div>

  <div class="row mt-4">
    <div class="col-lg-12">
      <div class="card z-index-2 h-100">
        <div class="card-header pb-0 pt-3 bg-transparent">
          <h6 class="text-capitalize">Monthly Revenue</h6>
        </div>
        <div class="card-body p-3">
          <div class="chart">
            <canvas id="monthlyChart" class="chart-canvas" height="180"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row mt-4">
    <div class="col-lg-6 mb-4">
      <div class="card h-100">
        <div class="card-header pb-0">
          <h6>Top 5 Products</h6>
        </div>
        <div class="card-body p-3">
          <ul class="list-group" id="val-top-products">
            <?php foreach ($topProducts as $p): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <?= $p['product_name'] ?>
                <span class="badge bg-gradient-primary"><?= $p['sold'] ?> pcs</span>
              </li>
            <?php endforeach ?>
          </ul>
        </div>
      </div>
    </div>

    <div class="col-lg-6 mb-4">
      <div class="card h-100">
        <div class="card-header pb-0">
          <h6>Order Status</h6>
        </div>
        <div class="card-body p-3">
          <ul class="list-group" id="val-order-statuses">
            <?php foreach ($orderStatuses as $s): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <?= strtoupper($s['status']) ?>
                <span class="badge bg-gradient-primary"><?= $s['total'] ?></span>
              </li>
            <?php endforeach ?>
          </ul>
        </div>
      </div>
    </div>

  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // Initialize Chart
  let monthlyChart = new Chart(document.getElementById('monthlyChart'), {
    type: 'line',
    data: {
      labels: <?= $months ?>,
      datasets: [{
        label: 'Revenue (Rp)',
        data: <?= $revenues ?>,
        borderColor: 'rgba(94,114,228,1)',
        backgroundColor: 'rgba(94,114,228,0.15)',
        fill: true,
        tension: 0.4
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          display: false
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: value => 'Rp ' + value.toLocaleString('id-ID')
          }
        }
      }
    }
  });


  function refreshDashboardData() {
    fetch('/dashboard/api-stats')
      .then(response => response.json())
      .then(data => {
        // Update Card Values
        document.getElementById('val-total-revenue').innerText = 'Rp ' + data.totalRevenue.toLocaleString('id-ID');
        document.getElementById('val-orders-today').innerText = data.totalOrdersToday;
        document.getElementById('val-online-sales').innerText = 'Rp ' + data.onlineSales.toLocaleString('id-ID');
        document.getElementById('val-in-store-sales').innerText = 'Rp ' + data.posSales.toLocaleString('id-ID');
        document.getElementById('val-customers').innerText = data.totalCustomers;
        document.getElementById('val-low-stock-items').innerText = data.lowStockCount;

        // Update Chart
        monthlyChart.data.labels = JSON.parse(data.months);
        monthlyChart.data.datasets[0].data = JSON.parse(data.revenues);
        monthlyChart.update();

        // Update Top Products List
        const productList = document.getElementById('val-top-products');
        productList.innerHTML = '';
        data.topProducts.forEach(p => {
          productList.innerHTML += `
            <li class="list-group-item d-flex justify-content-between align-items-center">
              ${p.product_name}
              <span class="badge bg-gradient-primary">${p.sold} pcs</span>
            </li>
          `;
        });

        // Update Order Status List
        const statusList = document.getElementById('val-order-statuses');
        statusList.innerHTML = '';
        data.orderStatuses.forEach(s => {
          statusList.innerHTML += `
            <li class="list-group-item d-flex justify-content-between align-items-center">
              ${s.status.toUpperCase()}
              <span class="badge bg-gradient-primary">${s.total}</span>
            </li>
          `;
        });
      })
      .catch(error => console.error('Error fetching dashboard stats:', error));
  }
</script>
<?= $this->endSection() ?>
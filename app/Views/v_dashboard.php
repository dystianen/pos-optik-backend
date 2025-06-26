<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>
<div class="container-fluid py-4">
  <div class="row">
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
      <div class="card">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Products</p>
                <h5 class="font-weight-bolder">
                  <?= $totalProducts ?>
                </h5>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                <i class="ni ni-money-coins text-lg opacity-10" aria-hidden="true"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
      <div class="card">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Customers</p>
                <h5 class="font-weight-bolder">
                  <?= $totalCustomers ?>
                </h5>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-danger shadow-danger text-center rounded-circle">
                <i class="ni ni-world text-lg opacity-10" aria-hidden="true"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
      <div class="card">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Selling (Unit)</p>
                <h5 class="font-weight-bolder">
                  <?= $totalSellingUnits ?>
                </h5>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-info shadow-info text-center rounded-circle">
                <i class="ni ni-basket text-lg opacity-10" aria-hidden="true"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
      <div class="card">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Selling (Rp)</p>
                <h5 class="font-weight-bolder">
                  Rp <?= number_format($totalSellingRupiah, 0, ',', '.') ?>
                </h5>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                <i class="ni ni-paper-diploma text-lg opacity-10" aria-hidden="true"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row mt-4">
    <div class="col-lg-7 mb-lg-0 mb-4">
      <div class="card z-index-2 h-100">
        <div class="card-header pb-0 pt-3 bg-transparent">
          <h6 class="text-capitalize">Monthly Selling (Unit & Rp)</h6>
        </div>
        <div class="card-body p-3">
          <div class="chart">
            <canvas id="chart-line" class="chart-canvas" height="180"></canvas>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="card card-carousel overflow-hidden h-100 p-0">
        <div id="carouselExampleCaptions" class="carousel slide h-100" data-bs-ride="carousel">
          <div class="carousel-inner border-radius-lg h-100">
            <!-- Slide 1 -->
            <div class="carousel-item h-100 active" style="background-image: url('../assets/img/carousel-1.jpg'); background-size: cover;">
              <div class="carousel-caption d-none d-md-block bottom-0 text-start start-0 ms-5">
                <div class="icon icon-shape icon-sm bg-white text-center border-radius-md mb-3">
                  <i class="ni ni-shop text-dark opacity-10"></i>
                </div>
                <h5 class="text-white mb-1">Welcome to OPTIKERS</h5>
                <p>Manage sales, inventory, and customer orders with ease and efficiency.</p>
              </div>
            </div>
            <!-- Slide 2 -->
            <div class="carousel-item h-100" style="background-image: url('../assets/img/carousel-2.jpg'); background-size: cover;">
              <div class="carousel-caption d-none d-md-block bottom-0 text-start start-0 ms-5">
                <div class="icon icon-shape icon-sm bg-white text-center border-radius-md mb-3">
                  <i class="ni ni-credit-card text-dark opacity-10"></i>
                </div>
                <h5 class="text-white mb-1">Fast and Secure Payments</h5>
                <p>Seamless payment processing with multiple integrated payment options.</p>
              </div>
            </div>
            <!-- Slide 3 -->
            <div class="carousel-item h-100" style="background-image: url('../assets/img/carousel-3.jpg'); background-size: cover;">
              <div class="carousel-caption d-none d-md-block bottom-0 text-start start-0 ms-5">
                <div class="icon icon-shape icon-sm bg-white text-center border-radius-md mb-3">
                  <i class="ni ni-chart-bar-32 text-dark opacity-10"></i>
                </div>
                <h5 class="text-white mb-1">Sales Analytics</h5>
                <p>Track your optical store's performance in real-time and make informed business decisions.</p>
              </div>
            </div>
          </div>
          <!-- Carousel Controls -->
          <button class="carousel-control-prev w-5 me-3" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next w-5 me-3" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const months = <?= $months ?>;
  const unitTotals = <?= $unitTotals ?>;
  const rupiahTotals = <?= $rupiahTotals ?>;

  const ctx = document.getElementById('chart-line').getContext('2d');
  const chart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: months,
      datasets: [{
          label: 'Unit Terjual (pcs)',
          data: unitTotals,
          borderColor: 'rgba(54, 162, 235, 1)',
          backgroundColor: 'rgba(54, 162, 235, 0.2)',
          tension: 0.4,
          fill: true,
          yAxisID: 'y'
        },
        {
          label: 'Penjualan (Rp)',
          data: rupiahTotals,
          borderColor: 'rgba(255, 99, 132, 1)',
          backgroundColor: 'rgba(255, 99, 132, 0.2)',
          tension: 0.4,
          fill: true,
          yAxisID: 'y1'
        }
      ]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          display: true
        },
        tooltip: {
          mode: 'index',
          intersect: false,
          callbacks: {
            label: function(context) {
              if (context.dataset.label.includes("Rp")) {
                return context.dataset.label + ': Rp ' + context.formattedValue.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
              }
              return context.dataset.label + ': ' + context.formattedValue;
            }
          }
        }
      },
      interaction: {
        mode: 'nearest',
        axis: 'x',
        intersect: false
      },
      scales: {
        y: {
          beginAtZero: true,
          position: 'left',
          title: {
            display: true,
            text: 'Unit'
          }
        },
        y1: {
          beginAtZero: true,
          position: 'right',
          title: {
            display: true,
            text: 'Rupiah'
          },
          grid: {
            drawOnChartArea: false
          }
        }
      }
    }
  });
</script>
<?= $this->endSection() ?>
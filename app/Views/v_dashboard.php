<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>
<div class="container-fluid">
  <div class="row">
    <div class="col-xl-4 col-lg-4 col-md-6 mb-4">
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
    <div class="col-xl-4 col-lg-4 col-md-6 mb-4">
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
    <div class="col-xl-4 col-lg-4 col-md-6 mb-4">
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
    <div class="col-xl-4 col-lg-4 col-md-6 mb-4">
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
    <div class="col-xl-4 col-lg-4 col-md-6 mb-4">
      <div class="card">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-sm mb-0 text-uppercase font-weight-bold">Incoming Stock</p>
                <h5 class="font-weight-bolder"><?= $totalIn ?></h5>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                <i class="ni ni-fat-add text-lg opacity-10" aria-hidden="true"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-4 col-lg-4 col-md-6 mb-4">
      <div class="card">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-sm mb-0 text-uppercase font-weight-bold">Outgoing Stock</p>
                <h5 class="font-weight-bolder"><?= $totalOut ?></h5>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                <i class="ni ni-fat-remove text-lg opacity-10" aria-hidden="true"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row mt-4">
    <div class="col-lg-6 mb-lg-0">
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
    <div class="col-lg-6">
      <div class="card z-index-2 h-100">
        <div class="card-header pb-0 pt-3 bg-transparent">
          <h6 class="text-capitalize">Incoming vs Outgoing Stock</h6>
        </div>
        <div class="card-body p-3">
          <div class="chart">
            <canvas id="chart-bar-inout" class="chart-canvas" height="180"></canvas>
          </div>
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

  const monthsIO = <?= $monthsIO ?>;
  const inQuantities = <?= $inQuantities ?>;
  const outQuantities = <?= $outQuantities ?>;

  const ctxInOut = document.getElementById('chart-bar-inout').getContext('2d');
  new Chart(ctxInOut, {
    type: 'bar',
    data: {
      labels: monthsIO,
      datasets: [{
          label: 'Incoming Stock',
          data: inQuantities,
          backgroundColor: 'rgba(75, 192, 192, 0.6)'
        },
        {
          label: 'Outgoing Stock',
          data: outQuantities,
          backgroundColor: 'rgba(255, 99, 132, 0.6)'
        }
      ]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'top'
        },
        tooltip: {
          callbacks: {
            label: ctx => `${ctx.dataset.label}: ${ctx.parsed.y} pcs`
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: 'Total Stock'
          }
        }
      }
    }
  });
</script>
<?= $this->endSection() ?>
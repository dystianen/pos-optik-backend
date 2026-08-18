<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>
<div class="container-fluid">
  <?php 
  $roleName = session('role_name');
  $isStaffWithInventoryAccess = strtolower($roleName ?? '') === 'admin';
  ?>
  <div class="row">
    <?php
    $cards = [
      [
        'title' => 'Total Revenue',
        'value' => 'Rp ' . number_format($totalRevenue, 0, ',', '.'),
        'icon'  => 'ni ni-money-coins',
        'color' => 'success',
        'link'  => null,
        'sub'   => null
      ],
      [
        'title' => 'Orders Today',
        'value' => $totalOrdersToday,
        'icon'  => 'ni ni-cart',
        'color' => 'info',
        'link'  => null,
        'sub'   => null
      ],
      [
        'title' => 'Online Sales',
        'value' => 'Rp ' . number_format($onlineSales, 0, ',', '.'),
        'icon'  => 'ni ni-world',
        'color' => 'primary',
        'link'  => null,
        'sub'   => null
      ],
      [
        'title' => 'In Store Sales',
        'value' => 'Rp ' . number_format($posSales, 0, ',', '.'),
        'icon'  => 'ni ni-shop',
        'color' => 'warning',
        'link'  => null,
        'sub'   => null
      ],
      [
        'title' => 'Customers',
        'value' => $totalCustomers,
        'icon'  => 'ni ni-single-02',
        'color' => 'dark',
        'link'  => null,
        'sub'   => null
      ],
      [
        'title' => 'Stock Alerts',
        'value' => ($stockAlertCount ?? $lowStockCount) . ' Items',
        'icon'  => 'ni ni-bell-55',
        'color' => 'danger',
        'link'  => $isStaffWithInventoryAccess ? base_url('/products?stock_status=low') : null,
        'sub'   => '<span class="text-danger font-weight-bold">' . ($emptyStockCount ?? 0) . ' Out of Stock</span> | <span class="text-warning font-weight-bold">' . ($lowStockCount ?? 0) . ' Low Stock</span>'
      ],
    ];

    foreach ($cards as $index => $card): ?>
      <div class="col-xl-4 col-lg-4 col-md-6 mb-4">
        <div class="card h-100 <?= !empty($card['link']) ? 'card-hover' : '' ?>">
          <div class="card-body p-3">
            <div class="row align-items-center">
              <div class="col-8">
                <div class="numbers">
                  <p class="text-sm mb-0 text-uppercase font-weight-bold">
                    <?php if (!empty($card['link'])): ?>
                      <a href="<?= $card['link'] ?>" class="text-dark text-decoration-none">
                        <?= $card['title'] ?> <i class="fas fa-external-link-alt text-xs text-muted"></i>
                      </a>
                    <?php else: ?>
                      <?= $card['title'] ?>
                    <?php endif; ?>
                  </p>
                  <h5 class="font-weight-bolder mb-0 mt-2 d-flex align-items-baseline gap-2 flex-wrap" id="val-<?= strtolower(str_replace([' ', '&'], ['-', ''], $card['title'])) ?>">
                    <span><?= $card['value'] ?></span>
                    <?php if (!empty($card['sub'])): ?>
                      <span class="text-xs font-weight-normal" id="sub-<?= strtolower(str_replace([' ', '&'], ['-', ''], $card['title'])) ?>">
                        (<?= $card['sub'] ?>)
                      </span>
                    <?php endif; ?>
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

  <div class="row mb-4">
    <div class="col-lg-12">
      <div class="card z-index-2 h-100">
        <div class="card-header pb-0 pt-3 bg-transparent">
          <h6 class="text-capitalize">Monthly Revenue</h6>
        </div>
        <div class="card-body p-3">
          <div class="chart">
            <canvas id="monthlyChart" class="chart-canvas" height="80"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Stock Alert Section -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card border-0 shadow-sm">
        <div class="card-header pb-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div>
            <h6 class="mb-0 d-flex align-items-center gap-2">
              <i class="ni ni-bell-55 text-danger"></i> Stock Alerts (Low & Out of Stock)
              <span class="badge bg-gradient-danger text-xxs" id="badge-empty-count"><?= ($emptyStockCount ?? 0) ?> Out of Stock</span>
              <span class="badge bg-gradient-warning text-xxs" id="badge-low-count"><?= ($lowStockCount ?? 0) ?> Low Stock</span>
            </h6>
            <p class="text-xs text-muted mb-0">List of products and variants requiring urgent restock (stock &le; 5 pcs)</p>
          </div>
          <?php if ($isStaffWithInventoryAccess): ?>
          <div class="d-flex gap-2">
            <a href="<?= base_url('/products?stock_status=empty') ?>" class="btn btn-xs btn-outline-danger mb-0">
              <i class="fas fa-filter me-1"></i> Out of Stock (0)
            </a>
            <a href="<?= base_url('/products?stock_status=low') ?>" class="btn btn-xs btn-outline-warning mb-0">
              <i class="fas fa-filter me-1"></i> Low Stock (1-5)
            </a>
            <a href="<?= base_url('/products') ?>" class="btn btn-xs btn-primary mb-0">
              Manage All Products &rarr;
            </a>
          </div>
          <?php endif; ?>
        </div>
        <div class="card-body px-0 pt-3 pb-2">
          <div class="table-responsive p-0">
            <table class="table align-items-center mb-0">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">No</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">SKU</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Product</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Category / Brand</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Variant</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Current Stock</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Status</th>
                  <?php if ($isStaffWithInventoryAccess): ?>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Action</th>
                  <?php endif; ?>
                </tr>
              </thead>
              <tbody id="val-stock-alerts-tbody">
                <?php if (empty($stockAlerts)): ?>
                  <tr>
                    <td colspan="<?= $isStaffWithInventoryAccess ? 8 : 7 ?>" class="text-center text-muted py-4">
                      <i class="fas fa-check-circle text-success me-1"></i> All product stocks are in safe condition (&gt; 5 pcs).
                    </td>
                  </tr>
                <?php else: ?>
                  <?php $no = 1; foreach ($stockAlerts as $item): ?>
                    <tr>
                      <td class="ps-3 text-xs"><?= $no++ ?></td>
                      <td>
                        <span class="text-xs font-weight-bold"><?= esc($item['product_sku']) ?></span>
                      </td>
                      <td>
                        <h6 class="mb-0 text-sm"><?= esc($item['product_name']) ?></h6>
                      </td>
                      <td>
                        <p class="text-xs font-weight-bold mb-0"><?= esc($item['category_name'] ?? '-') ?></p>
                        <p class="text-xxs text-secondary mb-0"><?= esc($item['product_brand'] ?: '-') ?></p>
                      </td>
                      <td>
                        <?php if (!empty($item['variant_name'])): ?>
                          <span class="badge bg-gradient-info text-xxs"><?= esc($item['variant_name']) ?></span>
                        <?php else: ?>
                          <span class="text-xs text-muted">Non-Variant</span>
                        <?php endif; ?>
                      </td>
                      <td class="align-middle text-center">
                        <span class="text-sm font-weight-bolder <?= (int)$item['current_stock'] <= 0 ? 'text-danger' : 'text-warning' ?>">
                          <?= (int)$item['current_stock'] ?>
                        </span>
                      </td>
                      <td class="align-middle text-center text-sm">
                        <?php if ((int)$item['current_stock'] <= 0): ?>
                          <span class="badge badge-sm bg-gradient-danger">🔴 Out of Stock (0)</span>
                        <?php else: ?>
                          <span class="badge badge-sm bg-gradient-warning">🟡 Low Stock (<?= $item['current_stock'] ?>)</span>
                        <?php endif; ?>
                      </td>
                      <?php if ($isStaffWithInventoryAccess): ?>
                      <td class="align-middle text-center">
                        <a href="<?= base_url('/products/form?id=' . $item['product_id']) ?>" class="btn btn-xs btn-outline-primary mb-0" title="Edit Product">
                          <i class="fas fa-pen me-1"></i> Edit
                        </a>
                        <a href="<?= base_url('/inventory/form') ?>" class="btn btn-xs btn-outline-success mb-0" title="Restock via Inventory">
                          <i class="fas fa-plus me-1"></i> Restock
                        </a>
                      </td>
                      <?php endif; ?>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
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
        if (document.getElementById('val-total-revenue')) document.getElementById('val-total-revenue').innerText = 'Rp ' + data.totalRevenue.toLocaleString('id-ID');
        if (document.getElementById('val-orders-today')) document.getElementById('val-orders-today').innerText = data.totalOrdersToday;
        if (document.getElementById('val-online-sales')) document.getElementById('val-online-sales').innerText = 'Rp ' + data.onlineSales.toLocaleString('id-ID');
        if (document.getElementById('val-in-store-sales')) document.getElementById('val-in-store-sales').innerText = 'Rp ' + data.posSales.toLocaleString('id-ID');
        if (document.getElementById('val-customers')) document.getElementById('val-customers').innerText = data.totalCustomers;
        
        const totalAlerts = data.stockAlertCount !== undefined ? data.stockAlertCount : data.lowStockCount;
        const valStockAlerts = document.getElementById('val-stock-alerts');
        if (valStockAlerts) {
          valStockAlerts.innerHTML = `<span>${totalAlerts} Items</span> <span class="text-xs font-weight-normal" id="sub-stock-alerts">(<span class="text-danger font-weight-bold">${data.emptyStockCount || 0} Out of Stock</span> | <span class="text-warning font-weight-bold">${data.lowStockCount || 0} Low Stock</span>)</span>`;
        }

        // Badges in Stock Alert Section
        if (document.getElementById('badge-empty-count')) document.getElementById('badge-empty-count').innerText = `${data.emptyStockCount || 0} Out of Stock`;
        if (document.getElementById('badge-low-count')) document.getElementById('badge-low-count').innerText = `${data.lowStockCount || 0} Low Stock`;

        // Update Chart
        monthlyChart.data.labels = JSON.parse(data.months);
        monthlyChart.data.datasets[0].data = JSON.parse(data.revenues);
        monthlyChart.update();

        // Update Stock Alerts Table
        const alertsTbody = document.getElementById('val-stock-alerts-tbody');
        const canManage = <?= $isStaffWithInventoryAccess ? 'true' : 'false' ?>;
        if (alertsTbody && data.stockAlerts) {
          if (data.stockAlerts.length === 0) {
            alertsTbody.innerHTML = `
              <tr>
                <td colspan="${canManage ? 8 : 7}" class="text-center text-muted py-4">
                  <i class="fas fa-check-circle text-success me-1"></i> All product stocks are in safe condition (> 5 pcs).
                </td>
              </tr>
            `;
          } else {
            let tbodyHtml = '';
            data.stockAlerts.forEach((item, idx) => {
              const isZero = parseInt(item.current_stock) <= 0;
              const variantBadge = item.variant_name 
                ? `<span class="badge bg-gradient-info text-xxs">${item.variant_name}</span>`
                : `<span class="text-xs text-muted">Non-Variant</span>`;
              const statusBadge = isZero
                ? `<span class="badge badge-sm bg-gradient-danger">🔴 Out of Stock (0)</span>`
                : `<span class="badge badge-sm bg-gradient-warning">🟡 Low Stock (${item.current_stock})</span>`;
              const stockColor = isZero ? 'text-danger' : 'text-warning';

              const actionTd = canManage ? `
                <td class="align-middle text-center">
                  <a href="/products/form?id=${item.product_id}" class="btn btn-xs btn-outline-primary mb-0" title="Edit Product">
                    <i class="fas fa-pen me-1"></i> Edit
                  </a>
                  <a href="/inventory/form" class="btn btn-xs btn-outline-success mb-0" title="Restock via Inventory">
                    <i class="fas fa-plus me-1"></i> Restock
                  </a>
                </td>
              ` : '';

              tbodyHtml += `
                <tr>
                  <td class="ps-3 text-xs">${idx + 1}</td>
                  <td><span class="text-xs font-weight-bold">${item.product_sku || '-'}</span></td>
                  <td><h6 class="mb-0 text-sm">${item.product_name}</h6></td>
                  <td>
                    <p class="text-xs font-weight-bold mb-0">${item.category_name || '-'}</p>
                    <p class="text-xxs text-secondary mb-0">${item.product_brand || '-'}</p>
                  </td>
                  <td>${variantBadge}</td>
                  <td class="align-middle text-center">
                    <span class="text-sm font-weight-bolder ${stockColor}">${item.current_stock}</span>
                  </td>
                  <td class="align-middle text-center text-sm">${statusBadge}</td>
                  ${actionTd}
                </tr>
              `;
            });
            alertsTbody.innerHTML = tbodyHtml;
          }
        }

        // Update Top Products List
        const productList = document.getElementById('val-top-products');
        if (productList && data.topProducts) {
          productList.innerHTML = '';
          data.topProducts.forEach(p => {
            productList.innerHTML += `
              <li class="list-group-item d-flex justify-content-between align-items-center">
                ${p.product_name}
                <span class="badge bg-gradient-primary">${p.sold} pcs</span>
              </li>
            `;
          });
        }

        // Update Order Status List
        const statusList = document.getElementById('val-order-statuses');
        if (statusList && data.orderStatuses) {
          statusList.innerHTML = '';
          data.orderStatuses.forEach(s => {
            statusList.innerHTML += `
              <li class="list-group-item d-flex justify-content-between align-items-center">
                ${s.status.toUpperCase()}
                <span class="badge bg-gradient-primary">${s.total}</span>
              </li>
            `;
          });
        }
      })
      .catch(error => console.error('Error fetching dashboard stats:', error));
  }
</script>
<?= $this->endSection() ?>
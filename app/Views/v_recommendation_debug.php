<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>

<!-- DataTables CSS CDN -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<style>
  .progress-preference {
    height: 10px;
    border-radius: 5px;
  }
  .badge-status {
    font-size: 0.85em;
    padding: 0.5em 0.85em;
  }
  .card-vector {
    max-height: 250px;
    overflow-y: auto;
  }
  .math-formula {
    background-color: #f8f9fa;
    border-left: 4px solid #5e72e4;
    padding: 10px;
    font-family: monospace;
    white-space: pre-wrap;
  }
  .nav-tabs .nav-link.active {
    background-color: #5e72e4 !important;
    color: white !important;
    border-radius: 0.5rem;
  }
  .nav-tabs .nav-link {
    color: #5e72e4;
    margin-right: 5px;
    border: 1px solid transparent;
  }
  
  /* Override DataTables Pagination to match Argon circular style */
  .dataTables_paginate .pagination .page-item .page-link {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: 50% !important;
    width: 36px !important;
    height: 36px !important;
    padding: 0 !important;
    margin: 0 3px !important;
    border: 1px solid #e9ecef !important;
    font-size: 0.875rem !important;
    color: #8392ab !important;
    background-color: transparent !important;
  }
  .dataTables_paginate .pagination .page-item.active .page-link {
    background: #5e72e4 !important;
    border-color: #5e72e4 !important;
    color: white !important;
    box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08) !important;
  }
  .dataTables_paginate .pagination .page-item.disabled .page-link {
    opacity: 0.5 !important;
    cursor: not-allowed !important;
  }
</style>

<div class="container-fluid py-4">
  <!-- Tabs Navigation -->
  <div class="row mb-4">
    <div class="col-12">
      <ul class="nav nav-tabs border-0" id="debugTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active btn btn-outline-primary border-0" id="single-tab" data-bs-toggle="tab" data-bs-target="#single-debug" type="button" role="tab" aria-controls="single-debug" aria-selected="true">
            <i class="fa-solid fa-bug me-2"></i>Personalized Recommendation Debug
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link btn btn-outline-primary border-0" id="compare-tab" data-bs-toggle="tab" data-bs-target="#compare-debug" type="button" role="tab" aria-controls="compare-debug" aria-selected="false">
            <i class="fa-solid fa-users-between-lines me-2"></i>Compare Customer Personal
          </button>
        </li>
      </ul>
    </div>
  </div>

  <!-- Tabs Content -->
  <div class="tab-content" id="debugTabsContent">
    
    <!-- TAB 1: Single Customer Debug -->
    <div class="tab-pane fade show active" id="single-debug" role="tabpanel" aria-labelledby="single-tab">
      
      <!-- Input Selectors Card -->
      <div class="card mb-4 shadow">
        <div class="card-header pb-0">
          <h5 class="mb-0 text-primary"><i class="fa-solid fa-sliders me-2"></i>Debugger Configuration</h5>
          <p class="text-sm mb-0">Select a Base Product and a Customer to analyze personalized content-based filtering recommendations.</p>
        </div>
        <div class="card-body">
          <form id="debugForm" class="row align-items-end g-3">
            <div class="col-md-5">
              <label for="base_product_id" class="form-label font-weight-bold">Base Product (Produk Yang Sedang Dilihat)</label>
              <select class="form-control select2" id="base_product_id" required>
                <option value="">-- Pilih Produk --</option>
                <?php foreach ($products as $p): ?>
                  <option value="<?= esc($p['product_id']) ?>">
                    <?= esc($p['product_name']) ?> (<?= esc($p['product_brand']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-5">
              <label for="customer_id" class="form-label font-weight-bold">Customer (Simulasi Login)</label>
              <select class="form-control select2" id="customer_id">
                <option value="">-- Guest (Tanpa Personalisasi) --</option>
                <?php foreach ($customers as $c): ?>
                  <option value="<?= esc($c['customer_id']) ?>">
                    <?= esc($c['customer_name']) ?> (ID: <?= esc(substr($c['customer_id'], 0, 8)) ?>...)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-primary w-100 mb-0" id="btnAnalyze">
                <i class="fa-solid fa-magnifying-glass-chart me-2"></i>Analyze
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Analysis Results Area (Initially Hidden) -->
      <div id="analysisResults" style="display: none;">
        
        <!-- STEP 1 & STEP 2 & STEP 10 Cards -->
        <div class="row">
          <!-- Customer Info Card -->
          <div class="col-lg-4 col-md-6 mb-4">
            <div class="card shadow h-100">
              <div class="card-body p-4">
                <h6 class="text-uppercase text-muted font-weight-bold text-xs"><i class="fa-solid fa-user me-2 text-info"></i>Step 1 · Customer Information</h6>
                <hr class="horizontal dark my-2">
                <div class="row mt-3">
                  <div class="col-12">
                    <p class="mb-1 text-sm text-secondary">Customer ID: <span class="font-weight-bold text-dark float-end" id="lbl-cust-id">-</span></p>
                    <p class="mb-1 text-sm text-secondary">Nama Customer: <span class="font-weight-bold text-dark float-end" id="lbl-cust-name">-</span></p>
                    <p class="mb-1 text-sm text-secondary">Apakah Login: <span class="font-weight-bold float-end" id="lbl-cust-login">-</span></p>
                    <p class="mb-1 text-sm text-secondary">Total Orders: <span class="font-weight-bold text-dark float-end" id="lbl-cust-orders">-</span></p>
                    <p class="mb-1 text-sm text-secondary">Completed Orders: <span class="font-weight-bold text-success float-end" id="lbl-cust-completed">-</span></p>
                    <p class="mb-1 text-sm text-secondary">Marketplace Orders (Online): <span class="font-weight-bold text-dark float-end" id="lbl-cust-online">-</span></p>
                    <p class="mb-1 text-sm text-secondary">POS Orders (Offline): <span class="font-weight-bold text-dark float-end" id="lbl-cust-offline">-</span></p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Base Product Card -->
          <div class="col-lg-4 col-md-6 mb-4">
            <div class="card shadow h-100">
              <div class="card-body p-4">
                <h6 class="text-uppercase text-muted font-weight-bold text-xs"><i class="fa-solid fa-glasses me-2 text-primary"></i>Step 2 · Base Product Details</h6>
                <hr class="horizontal dark my-2">
                <div class="row mt-3">
                  <div class="col-12">
                    <p class="mb-1 text-sm text-secondary">Nama Produk: <span class="font-weight-bold text-dark float-end text-truncate" style="max-width: 180px;" id="lbl-prod-name">-</span></p>
                    <p class="mb-1 text-sm text-secondary">Brand: <span class="font-weight-bold text-dark float-end" id="lbl-prod-brand">-</span></p>
                    <p class="mb-1 text-sm text-secondary">Harga: <span class="font-weight-bold text-dark float-end" id="lbl-prod-price">-</span></p>
                    <p class="mb-1 text-sm text-secondary">Kategori: <span class="font-weight-bold text-dark float-end" id="lbl-prod-category">-</span></p>
                  </div>
                  <div class="col-12 mt-3">
                    <h6 class="text-xs font-weight-bold text-secondary mb-1">Product Specifications:</h6>
                    <div id="lbl-prod-specs" class="d-flex flex-wrap gap-1"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Statistics Card -->
          <div class="col-lg-4 col-md-12 mb-4">
            <div class="card shadow h-100">
              <div class="card-body p-4">
                <h6 class="text-uppercase text-muted font-weight-bold text-xs"><i class="fa-solid fa-chart-pie me-2 text-warning"></i>Step 10 · Recommendation Statistics</h6>
                <hr class="horizontal dark my-2">
                <div class="row mt-3">
                  <div class="col-6">
                    <div class="border-end border-light pe-2">
                      <p class="text-xs text-muted mb-0">Total Candidates</p>
                      <h4 class="font-weight-bold mb-0" id="stat-candidates">0</h4>
                      <p class="text-xs text-muted mt-2 mb-0">Similarity > 0: <span class="font-weight-bold text-success" id="stat-sim-pos">0</span></p>
                      <p class="text-xs text-muted mb-0">Similarity = 0: <span class="font-weight-bold text-danger" id="stat-sim-zero">0</span></p>
                    </div>
                  </div>
                  <div class="col-6 ps-3">
                    <p class="text-xs text-muted mb-0">Passed / Filtered</p>
                    <h4 class="font-weight-bold mb-0"><span class="text-success" id="stat-passed">0</span> / <span class="text-danger" id="stat-filtered">0</span></h4>
                    <p class="text-xs text-muted mt-2 mb-0">Avg CBF: <span class="font-weight-bold" id="stat-avg-cbf">0</span></p>
                    <p class="text-xs text-muted mb-0">Avg Final: <span class="font-weight-bold text-primary" id="stat-avg-final">0</span></p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- History & User Preference Visualizers -->
        <div class="row">
          <!-- Purchase History Card -->
          <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
              <div class="card-header pb-0 bg-transparent">
                <h6 class="text-uppercase text-muted font-weight-bold text-xs"><i class="fa-solid fa-clock-rotate-left me-2 text-danger"></i>Purchase History</h6>
              </div>
              <div class="card-body">
                <div class="table-responsive" style="max-height: 250px;">
                  <table class="table align-items-center mb-0" id="tblPurchaseHistory">
                    <thead>
                      <tr>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Tanggal</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Order ID</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Produk</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Jenis</th>
                      </tr>
                    </thead>
                    <tbody></tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- User Preference Visualizer -->
          <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
              <div class="card-header pb-0 bg-transparent">
                <h6 class="text-uppercase text-muted font-weight-bold text-xs"><i class="fa-solid fa-ranking-stars me-2 text-success"></i>User Preference Analysis</h6>
              </div>
              <div class="card-body">
                <div id="preferenceVisualizer" style="max-height: 250px; overflow-y: auto;">
                  <p class="text-muted text-sm text-center my-4">Belum ada riwayat pembelian untuk customer ini.</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Vectors display -->
        <div class="row">
          <!-- Base & User Profiles Vector Details -->
          <div class="col-md-4 mb-4">
            <div class="card shadow card-vector h-100">
              <div class="card-body p-3">
                <h6 class="text-uppercase text-muted font-weight-bold text-xs"><i class="fa-solid fa-code me-2 text-dark"></i>Step 2 · Base Vector</h6>
                <hr class="horizontal dark my-2">
                <div id="vectorBaseContainer" class="text-sm"></div>
              </div>
            </div>
          </div>
          <div class="col-md-4 mb-4">
            <div class="card shadow card-vector h-100">
              <div class="card-body p-3">
                <h6 class="text-uppercase text-muted font-weight-bold text-xs"><i class="fa-solid fa-code me-2 text-primary"></i>Step 3 · User Profile Vector</h6>
                <hr class="horizontal dark my-2">
                <div id="vectorUserContainer" class="text-sm"></div>
              </div>
            </div>
          </div>
          <div class="col-md-4 mb-4">
            <div class="card shadow card-vector h-100">
              <div class="card-body p-3">
                <h6 class="text-uppercase text-muted font-weight-bold text-xs"><i class="fa-solid fa-code me-2 text-warning"></i>Step 3 · POS Vector (Offline Only)</h6>
                <hr class="horizontal dark my-2">
                <div id="vectorPosContainer" class="text-sm"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Candidate Comparison Table -->
        <div class="row">
          <div class="col-12 mb-4">
            <div class="card shadow">
              <div class="card-header pb-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                  <h5 class="mb-0 text-primary"><i class="fa-solid fa-list-ol me-2"></i>Candidate Comparison & Recommendation Table</h5>
                  <p class="text-sm mb-0">Lists all candidate products evaluated, similarity components, and scoring details.</p>
                </div>
                <div class="d-flex gap-2">
                  <button class="btn btn-sm btn-info" id="btnExportJSON"><i class="fa-solid fa-file-code me-2"></i>Export JSON</button>
                  <button class="btn btn-sm btn-success" id="btnExportExcel"><i class="fa-solid fa-file-excel me-2"></i>Export Excel</button>
                  <button class="btn btn-sm btn-dark" id="btnExportCSV"><i class="fa-solid fa-file-csv me-2"></i>Export CSV</button>
                </div>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-bordered align-items-center mb-0 w-100" id="tblCandidates">
                    <thead>
                      <tr>
                        <th class="text-center text-xs font-weight-bold text-uppercase">Rank Pre</th>
                        <th class="text-center text-xs font-weight-bold text-uppercase">Rank Post</th>
                        <th class="text-xs font-weight-bold text-uppercase">Product</th>
                        <th class="text-center text-xs font-weight-bold text-uppercase">Match Specs</th>
                        <th class="text-end text-xs font-weight-bold text-uppercase">CBF (Base)</th>
                        <th class="text-end text-xs font-weight-bold text-uppercase">User</th>
                        <th class="text-end text-xs font-weight-bold text-uppercase">POS</th>
                        <th class="text-end text-xs font-weight-bold text-uppercase">Final Score</th>
                        <th class="text-center text-xs font-weight-bold text-uppercase">Status</th>
                        <th class="text-center text-xs font-weight-bold text-uppercase no-sort">Action</th>
                      </tr>
                    </thead>
                    <tbody></tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

    </div>

    <!-- TAB 2: Compare Customer -->
    <div class="tab-pane fade" id="compare-debug" role="tabpanel" aria-labelledby="compare-tab">
      
      <!-- Compare Selector Card -->
      <div class="card mb-4 shadow">
        <div class="card-header pb-0">
          <h5 class="mb-0 text-primary"><i class="fa-solid fa-users-between-lines me-2"></i>Compare Personas</h5>
          <p class="text-sm mb-0">Evaluate personalization differences by comparing the recommendation scores for the same base product side-by-side between two customers.</p>
        </div>
        <div class="card-body">
          <form id="compareForm" class="row align-items-end g-3">
            <div class="col-md-4">
              <label for="compare_product_id" class="form-label font-weight-bold">Product (Produk Yang Sedang Dilihat)</label>
              <select class="form-control select2" id="compare_product_id" required>
                <option value="">-- Pilih Produk --</option>
                <?php foreach ($products as $p): ?>
                  <option value="<?= esc($p['product_id']) ?>">
                    <?= esc($p['product_name']) ?> (<?= esc($p['product_brand']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label for="customer_a" class="form-label font-weight-bold">Customer A</label>
              <select class="form-control select2" id="customer_a" required>
                <option value="">-- Pilih Customer A --</option>
                <?php foreach ($customers as $c): ?>
                  <option value="<?= esc($c['customer_id']) ?>">
                    <?= esc($c['customer_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label for="customer_b" class="form-label font-weight-bold">Customer B</label>
              <select class="form-control select2" id="customer_b" required>
                <option value="">-- Pilih Customer B --</option>
                <?php foreach ($customers as $c): ?>
                  <option value="<?= esc($c['customer_id']) ?>">
                    <?= esc($c['customer_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-primary w-100 mb-0" id="btnCompare">
                <i class="fa-solid fa-code-compare me-2"></i>Compare
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Compare Results Area (Initially Hidden) -->
      <div id="compareResults" style="display: none;">
        
        <!-- Explanation Block -->
        <div class="row">
          <div class="col-12 mb-4">
            <div class="card bg-gradient-light border-0 shadow">
              <div class="card-body p-4">
                <h5 class="text-primary font-weight-bold mb-3"><i class="fa-solid fa-circle-info me-2"></i>Explanation Profile & Personalization Analysis</h5>
                <div class="p-3 bg-white rounded shadow-sm text-dark" id="compareExplanation"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Side-by-Side Table -->
        <div class="row">
          <div class="col-12 mb-4">
            <div class="card shadow">
              <div class="card-header pb-0">
                <h5 class="mb-0 text-primary"><i class="fa-solid fa-table-columns me-2"></i>Side-by-Side Candidates Comparison</h5>
                <p class="text-sm mb-0">Compares the scores generated for Customer A and Customer B for each candidate product evaluated.</p>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-bordered align-items-center mb-0 w-100" id="tblCompareResult">
                    <thead>
                      <tr>
                        <th rowspan="2" class="text-xs font-weight-bold text-uppercase align-middle">Candidate Product</th>
                        <th colspan="4" class="text-center text-xs font-weight-bold text-uppercase bg-light" id="compare-hdr-cust-a">Customer A</th>
                        <th colspan="4" class="text-center text-xs font-weight-bold text-uppercase bg-light" id="compare-hdr-cust-b">Customer B</th>
                      </tr>
                      <tr>
                        <th class="text-end text-xxs font-weight-bold text-uppercase">CBF</th>
                        <th class="text-end text-xxs font-weight-bold text-uppercase">User</th>
                        <th class="text-end text-xxs font-weight-bold text-uppercase">POS</th>
                        <th class="text-end text-xxs font-weight-bold text-uppercase">Final</th>
                        <th class="text-end text-xxs font-weight-bold text-uppercase">CBF</th>
                        <th class="text-end text-xxs font-weight-bold text-uppercase">User</th>
                        <th class="text-end text-xxs font-weight-bold text-uppercase">POS</th>
                        <th class="text-end text-xxs font-weight-bold text-uppercase">Final</th>
                      </tr>
                    </thead>
                    <tbody></tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

    </div>

  </div>
</div>

<!-- Modal Perhitungan Matematika -->
<div class="modal fade" id="mathModal" tabindex="-1" aria-labelledby="mathModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="mathModalLabel"><i class="fa-solid fa-square-root-variable text-primary me-2"></i>Mathematical Logic Breakdown</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <h6 class="font-weight-bold text-secondary text-sm">Product Evaluated: <span class="text-dark" id="math-prod-name">-</span></h6>
        
        <div class="mb-4 mt-3">
          <h6 class="text-primary text-sm font-weight-bold mb-2">1. CBF Similarity (Base Product vs Candidate)</h6>
          <div class="math-formula" id="math-cbf-calc"></div>
        </div>
        
        <div class="mb-4">
          <h6 class="text-primary text-sm font-weight-bold mb-2">2. User Score Similarity (User Preference Vector vs Candidate)</h6>
          <div class="math-formula" id="math-user-calc"></div>
        </div>

        <div class="mb-4">
          <h6 class="text-primary text-sm font-weight-bold mb-2">3. POS Score Similarity (POS Preference Vector vs Candidate)</h6>
          <div class="math-formula" id="math-pos-calc"></div>
        </div>

        <div class="mb-4">
          <h6 class="text-primary text-sm font-weight-bold mb-2">4. Final Weighted Combined Score</h6>
          <div class="math-formula" id="math-final-calc"></div>
        </div>

        <div class="mb-0">
          <h6 class="text-primary text-sm font-weight-bold mb-2">5. Attributes Match Status & Decision Reason</h6>
          <div class="p-3 bg-light rounded text-dark text-sm">
            <p class="mb-1"><strong>Status:</strong> <span id="math-status-badge"></span></p>
            <p class="mb-0"><strong>Reason:</strong> <span id="math-reason-text"></span></p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- DataTables JS & SheetJS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<script>
  let cachedCandidates = [];
  let baseProductData = {};
  let customerData = {};
  let weightsData = {};
  let singleDataTable = null;

  $(document).ready(function() {
    
    // TAB 1 ANALYZE SUBMIT
    $('#debugForm').on('submit', function(e) {
      e.preventDefault();
      
      const productId = $('#base_product_id').val();
      const customerId = $('#customer_id').val();
      
      if (!productId) return;
      
      $('#btnAnalyze').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Analyzing...');
      
      $.ajax({
        url: `/api/products/recommendations/${productId}`,
        type: 'GET',
        data: {
          debug: 1,
          customer_id: customerId
        },
        dataType: 'json',
        success: function(res) {
          const data = res.data ? res.data : res;
          renderDebugResults(data);
          $('#analysisResults').slideDown();
        },
        error: function(err) {
          Swal.fire('Error', 'Failed to retrieve recommendation logs. Make sure the backend supports debug=1.', 'error');
        },
        complete: function() {
          $('#btnAnalyze').prop('disabled', false).html('<i class="fa-solid fa-magnifying-glass-chart me-2"></i>Analyze');
        }
      });
    });

    // TAB 2 COMPARE SUBMIT
    $('#compareForm').on('submit', function(e) {
      e.preventDefault();
      
      const productId = $('#compare_product_id').val();
      const customerA = $('#customer_a').val();
      const customerB = $('#customer_b').val();
      
      if (!productId || !customerA || !customerB) return;
      if (customerA === customerB) {
        Swal.fire('Warning', 'Customer A and Customer B must be different.', 'warning');
        return;
      }
      
      $('#btnCompare').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Comparing...');
      
      $.ajax({
        url: `/api/products/recommendations/${productId}/compare`,
        type: 'GET',
        data: {
          customer_a: customerA,
          customer_b: customerB
        },
        dataType: 'json',
        success: function(res) {
          const data = res.data ? res.data : res;
          renderCompareResults(data);
          $('#compareResults').slideDown();
        },
        error: function(err) {
          Swal.fire('Error', 'Failed to retrieve comparison analytics.', 'error');
        },
        complete: function() {
          $('#btnCompare').prop('disabled', false).html('<i class="fa-solid fa-code-compare me-2"></i>Compare');
        }
      });
    });

    // EXPORTS
    $('#btnExportJSON').on('click', function() {
      const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(cachedCandidates, null, 2));
      const downloadAnchor = document.createElement('a');
      downloadAnchor.setAttribute("href", dataStr);
      downloadAnchor.setAttribute("download", `recommendation_debug_candidates_${Date.now()}.json`);
      document.body.appendChild(downloadAnchor);
      downloadAnchor.click();
      downloadAnchor.remove();
    });

    $('#btnExportExcel').on('click', function() {
      const formatted = formatExportData(cachedCandidates);
      const ws = XLSX.utils.json_to_sheet(formatted);
      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, "Recommendation Debug");
      XLSX.writeFile(wb, `recommendation_debug_${Date.now()}.xlsx`);
    });

    $('#btnExportCSV').on('click', function() {
      const formatted = formatExportData(cachedCandidates);
      const ws = XLSX.utils.json_to_sheet(formatted);
      const csvOutput = XLSX.utils.sheet_to_csv(ws);
      const blob = new Blob([csvOutput], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const downloadAnchor = document.createElement('a');
      downloadAnchor.setAttribute("href", url);
      downloadAnchor.setAttribute("download", `recommendation_debug_${Date.now()}.csv`);
      document.body.appendChild(downloadAnchor);
      downloadAnchor.click();
      downloadAnchor.remove();
    });

  });

  // FORMAT EXPORT DATA FOR EXCEL/CSV
  function formatExportData(data) {
    return data.map(item => ({
      "Rank Before": item.rank_before,
      "Rank After": item.rank_after,
      "Product ID": item.product_id,
      "Product Name": item.product_name,
      "Brand": item.product_brand,
      "Price": item.product_price,
      "Stock": item.product_stock,
      "Match Specs Count": `${item.match_count}/${item.total_base_attrs}`,
      "CBF Score": item.cbf_score,
      "User Score": item.user_score,
      "POS Score": item.pos_score,
      "Final Score": item.final_score,
      "Status": item.status,
      "Reason": item.reason
    }));
  }

  // RENDER COMPARE RESULTS
  function renderCompareResults(res) {
    $('#compare-hdr-cust-a').text(`Customer A: ${res.customer_a.customer_name}`);
    $('#compare-hdr-cust-b').text(`Customer B: ${res.customer_b.customer_name}`);
    $('#compareExplanation').html(res.explanation);

    const tbody = $('#tblCompareResult tbody');
    tbody.empty();
    
    res.comparison.forEach(item => {
      const row = `
        <tr>
          <td class="text-sm font-weight-bold text-dark">
            ${item.product_name}<br>
            <span class="text-xs text-muted">${item.product_brand} | Rp ${Number(item.product_price).toLocaleString('id-ID')}</span>
          </td>
          <td class="text-end text-sm">${Number(item.customer_a.cbf).toFixed(4)}</td>
          <td class="text-end text-sm">${Number(item.customer_a.user).toFixed(4)}</td>
          <td class="text-end text-sm">${Number(item.customer_a.pos).toFixed(4)}</td>
          <td class="text-end text-sm font-weight-bold text-primary">${Number(item.customer_a.final).toFixed(4)}</td>
          <td class="text-end text-sm">${Number(item.customer_b.cbf).toFixed(4)}</td>
          <td class="text-end text-sm">${Number(item.customer_b.user).toFixed(4)}</td>
          <td class="text-end text-sm">${Number(item.customer_b.pos).toFixed(4)}</td>
          <td class="text-end text-sm font-weight-bold text-warning">${Number(item.customer_b.final).toFixed(4)}</td>
        </tr>
      `;
      tbody.append(row);
    });
  }

  // RENDER TAB 1 RESULTS
  function renderDebugResults(res) {
    cachedCandidates = res.candidates;
    baseProductData = res.base_product;
    customerData = res.customer_info;
    weightsData = res.weights;

    // 1. Customer Card
    $('#lbl-cust-id').text(customerData.customer_id ? customerData.customer_id.substring(0, 8) + '...' : '-');
    $('#lbl-cust-name').text(customerData.customer_name);
    $('#lbl-cust-login').text(customerData.is_logged_in);
    if (customerData.is_logged_in === 'Ya') {
      $('#lbl-cust-login').removeClass('text-danger').addClass('text-success font-weight-bold');
    } else {
      $('#lbl-cust-login').removeClass('text-success').addClass('text-danger font-weight-bold');
    }
    $('#lbl-cust-orders').text(customerData.total_orders);
    $('#lbl-cust-completed').text(customerData.completed_orders);
    $('#lbl-cust-online').text(customerData.marketplace_orders);
    $('#lbl-cust-offline').text(customerData.pos_orders);

    // 2. Base Product Card
    $('#lbl-prod-name').text(baseProductData.product_name);
    $('#lbl-prod-brand').text(baseProductData.product_brand);
    $('#lbl-prod-price').text(`Rp ${baseProductData.product_price.toLocaleString('id-ID')}`);
    $('#lbl-prod-category').text(baseProductData.category_name);

    let specBadges = '';
    baseProductData.attributes.forEach(attr => {
      specBadges += `<span class="badge bg-gradient-secondary text-xxs mb-1 me-1">${attr.attribute_name}: ${attr.value}</span>`;
    });
    $('#lbl-prod-specs').html(specBadges);

    // 3. Stats Card
    $('#stat-candidates').text(res.statistics.total_candidates);
    $('#stat-sim-pos').text(res.statistics.similarity_greater_than_zero);
    $('#stat-sim-zero').text(res.statistics.similarity_equal_to_zero);
    $('#stat-passed').text(res.statistics.passed_count);
    $('#stat-filtered').text(res.statistics.filtered_count);
    $('#stat-avg-cbf').text(res.statistics.avg_cbf_score.toFixed(4));
    $('#stat-avg-final').text(res.statistics.avg_final_score.toFixed(4));

    // 4. Purchase History Table
    const tbodyHistory = $('#tblPurchaseHistory tbody');
    tbodyHistory.empty();
    if (customerData.purchase_history.length === 0) {
      tbodyHistory.append('<tr><td colspan="4" class="text-center text-xs text-muted py-3">Belum ada riwayat pembelian.</td></tr>');
    } else {
      customerData.purchase_history.forEach(item => {
        const orderDate = new Date(item.created_at).toLocaleDateString('id-ID');
        const badgeType = item.order_type === 'offline' 
          ? '<span class="badge bg-gradient-warning text-xxs">POS</span>' 
          : '<span class="badge bg-gradient-info text-xxs">Marketplace</span>';
        
        const row = `
          <tr>
            <td class="text-xs text-secondary">${orderDate}</td>
            <td class="text-xs font-weight-bold">${item.order_id.substring(0, 8)}...</td>
            <td class="text-xs font-weight-bold text-dark text-truncate" style="max-width: 140px;">${item.product_name}</td>
            <td>${badgeType}</td>
          </tr>
        `;
        tbodyHistory.append(row);
      });
    }

    // 5. User Preference Visualizer (Bar Chart representation)
    const prefContainer = $('#preferenceVisualizer');
    prefContainer.empty();
    const userVector = res.user_profile.user_vector;
    const userVectorKeys = Object.keys(userVector);

    if (userVectorKeys.length === 0) {
      prefContainer.append('<p class="text-muted text-sm text-center my-4">Belum ada riwayat pembelian untuk customer ini.</p>');
    } else {
      // Find max freq for percentage calculation
      const maxFreq = Math.max(...Object.values(userVector));
      userVectorKeys.forEach(key => {
        const freq = userVector[key];
        const percent = (freq / maxFreq) * 100;
        const parts = key.split('::', 2);
        const name = parts[0] ?? '';
        const val = parts[1] ?? '';
        
        const bar = `
          <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="text-xs font-weight-bold text-dark text-capitalize">${name}: ${val}</span>
              <span class="text-xs font-weight-bold text-primary">${freq} kali</span>
            </div>
            <div class="progress progress-preference">
              <div class="progress-bar bg-gradient-primary" role="progressbar" style="width: ${percent}%" aria-valuenow="${freq}" aria-valuemin="0" aria-valuemax="${maxFreq}"></div>
            </div>
          </div>
        `;
        prefContainer.append(bar);
      });
    }

    // 6. Vector displays
    renderVectorList($('#vectorBaseContainer'), res.base_product.base_vector);
    renderVectorList($('#vectorUserContainer'), res.user_profile.user_vector);
    renderVectorList($('#vectorPosContainer'), res.user_profile.pos_vector);

    // 7. Candidates Table
    if (singleDataTable) {
      singleDataTable.destroy();
    }

    const tbodyCandidates = $('#tblCandidates tbody');
    tbodyCandidates.empty();

    res.candidates.forEach(c => {
      let statusBadge = '';
      if (c.status === 'Recommended') {
        statusBadge = '<span class="badge bg-success badge-status"><i class="fa-solid fa-circle-check me-1"></i>Recommended</span>';
      } else if (c.status === 'Filtered') {
        statusBadge = '<span class="badge bg-danger badge-status"><i class="fa-solid fa-filter me-1"></i>Filtered</span>';
      } else if (c.status === 'Low Score') {
        statusBadge = '<span class="badge bg-warning badge-status text-dark"><i class="fa-solid fa-chart-line-down me-1"></i>Low Score</span>';
      } else {
        statusBadge = '<span class="badge bg-secondary badge-status"><i class="fa-solid fa-ban me-1"></i>No Similarity</span>';
      }

      // Progress bar for score
      const scorePercent = c.final_score * 100;
      const scoreProgress = `
        <div class="d-flex align-items-center justify-content-end">
          <span class="me-2 font-weight-bold text-xs">${Number(c.final_score).toFixed(4)}</span>
          <div class="progress" style="width: 50px; height: 6px; margin-bottom: 0;">
            <div class="progress-bar bg-gradient-primary" role="progressbar" style="width: ${scorePercent}%" aria-valuenow="${scorePercent}" aria-valuemin="0" aria-valuemax="100"></div>
          </div>
        </div>
      `;

      const matchRatio = `${c.match_count}/${c.total_base_attrs}`;

      const row = `
        <tr>
          <td class="text-center font-weight-bold text-sm">${c.rank_before}</td>
          <td class="text-center font-weight-bold text-sm">${c.rank_after}</td>
          <td>
            <div class="d-flex px-2 py-1">
              ${c.product_image_url ? `<div><img src="${c.product_image_url}" class="avatar avatar-sm me-3" alt="product"></div>` : ''}
              <div class="d-flex flex-column justify-content-center">
                <h6 class="mb-0 text-sm">${c.product_name}</h6>
                <p class="text-xs text-secondary mb-0">${c.product_brand} | Rp ${Number(c.product_price).toLocaleString('id-ID')}</p>
              </div>
            </div>
          </td>
          <td class="text-center text-sm font-weight-bold">${matchRatio}</td>
          <td class="text-end text-sm">${Number(c.cbf_score).toFixed(4)}</td>
          <td class="text-end text-sm">${Number(c.user_score).toFixed(4)}</td>
          <td class="text-end text-sm">${Number(c.pos_score).toFixed(4)}</td>
          <td class="text-end">${scoreProgress}</td>
          <td class="text-center">${statusBadge}</td>
          <td class="text-center">
            <button class="btn btn-xs btn-outline-primary mb-0" onclick="showMathDetails('${c.product_id}')">
              <i class="fa-solid fa-square-root-variable me-1"></i>Math Details
            </button>
          </td>
        </tr>
      `;
      tbodyCandidates.append(row);
    });

    singleDataTable = $('#tblCandidates').DataTable({
      pageLength: 10,
      order: [[0, 'asc']],
      columnDefs: [
        { targets: 'no-sort', orderable: false }
      ],
      language: {
        paginate: {
          previous: '<i class="fa-solid fa-angle-left"></i>',
          next: '<i class="fa-solid fa-angle-right"></i>'
        }
      }
    });
  }

  // RENDER VECTOR LIST
  function renderVectorList(container, vec) {
    container.empty();
    const keys = Object.keys(vec);
    if (keys.length === 0) {
      container.append('<p class="text-muted text-xs text-center my-3">Vektor Kosong</p>');
      return;
    }
    let html = '<ul class="list-group list-group-flush">';
    keys.forEach(k => {
      const val = vec[k];
      html += `
        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-1 bg-transparent">
          <span class="text-xs text-secondary text-truncate" style="max-width: 190px;" title="${k}">${k}</span>
          <span class="badge bg-gradient-dark text-xxs font-weight-bold">${val}</span>
        </li>
      `;
    });
    html += '</ul>';
    container.append(html);
  }

  // SHOW MATH MODAL DETAILS
  function showMathDetails(productId) {
    const candidate = cachedCandidates.find(c => c.product_id === productId);
    if (!candidate) return;

    $('#math-prod-name').text(candidate.product_name);
    
    // 1. CBF Similarity Details
    $('#math-cbf-calc').html(candidate.cbf_details.calculation);
    
    // 2. User Details
    if (candidate.user_details.calculation) {
      $('#math-user-calc').html(candidate.user_details.calculation);
    } else {
      $('#math-user-calc').html('User Vector is empty, user score set to 0.0');
    }

    // 3. POS Details
    if (candidate.pos_details.calculation) {
      $('#math-pos-calc').html(candidate.pos_details.calculation);
    } else {
      $('#math-pos-calc').html('POS Vector is empty, POS score set to 0.0');
    }

    // 4. Final Weighted combined calculation
    const wBase = weightsData.cbf_weight;
    const wUser = weightsData.user_weight;
    const wPos = weightsData.pos_weight;

    const finalFormula = `Weights:
CBF Weight = ${wBase * 100}%
User Weight = ${wUser * 100}%
POS Weight = ${wPos * 100}%

Final Score:
(${wBase} × CBF) + (${wUser} × USER) + (${wPos} × POS)
= (${wBase} × ${Number(candidate.cbf_score).toFixed(4)}) + (${wUser} × ${Number(candidate.user_score).toFixed(4)}) + (${wPos} × ${Number(candidate.pos_score).toFixed(4)})
= ${Number(candidate.final_score).toFixed(6)}`;

    $('#math-final-calc').html(finalFormula);

    // 5. Badges & Decision
    let badgeClass = 'bg-secondary';
    if (candidate.status === 'Recommended') badgeClass = 'bg-success';
    else if (candidate.status === 'Filtered') badgeClass = 'bg-danger';
    else if (candidate.status === 'Low Score') badgeClass = 'bg-warning text-dark';

    $('#math-status-badge')
      .removeClass()
      .addClass(`badge ${badgeClass}`)
      .text(candidate.status);
    $('#math-reason-text').text(candidate.reason);

    // Show modal
    const mathModal = new bootstrap.Modal(document.getElementById('mathModal'));
    mathModal.show();
  }
</script>
<?= $this->endSection() ?>

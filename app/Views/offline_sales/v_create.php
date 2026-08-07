<?= $this->extend('layouts/l_dashboard') ?>
<?= $this->section('content') ?>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
  :root {
    --brand: #5e72e4;
    --brand-light: #eef0fd;
    --brand-dark: #4a5bd0;
    --success: #2dce89;
    --danger: #f5365c;
    --card-radius: 16px;
    --input-radius: 10px;
    --shadow-sm: 0 2px 8px rgba(0,0,0,.06);
    --shadow-md: 0 4px 20px rgba(0,0,0,.10);
    --transition: .18s ease;
  }
  .page-header-os { display:flex; align-items:center; gap:14px; margin-bottom:28px; }
  .page-header-os .icon-wrap { width:48px; height:48px; border-radius:14px; background:var(--brand); display:flex; align-items:center; justify-content:center; color:#fff; font-size:20px; flex-shrink:0; box-shadow:0 4px 14px rgba(94,114,228,.4); }
  .page-header-os h4 { margin:0; font-size:1.35rem; font-weight:700; color:#1e1e2d; }
  .page-header-os p  { margin:0; font-size:.825rem; color:#8a94a6; }
  .os-section { background:#fff; border-radius:var(--card-radius); box-shadow:var(--shadow-sm); border:1px solid #f0f1f7; margin-bottom:20px; overflow:visible; }
  .os-section-header { display:flex; align-items:center; gap:10px; padding:18px 24px 14px; border-bottom:1px solid #f4f5fb; }
  .os-section-header .num { width:30px; height:30px; border-radius:50%; background:var(--brand); color:#fff; font-size:.78rem; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
  .os-section-header h6 { margin:0; font-size:.95rem; font-weight:700; color:#1e1e2d; }
  .os-section-body { padding:20px 24px; }
  .select2-container--bootstrap-5 .select2-selection { border-color:#e4e7f0 !important; border-radius:var(--input-radius) !important; height:44px !important; display:flex !important; align-items:center !important; font-size:.875rem !important; box-shadow:none !important; transition:border-color var(--transition),box-shadow var(--transition) !important; }
  .select2-container--bootstrap-5.select2-container--focus .select2-selection { border-color:var(--brand) !important; box-shadow:0 0 0 3px rgba(94,114,228,.12) !important; }
  .select2-container--bootstrap-5 .select2-selection--single { padding:0 .75rem !important; }
  .select2-container--bootstrap-5 .select2-selection__placeholder { color:#adb5bd !important; }
  .select2-container--bootstrap-5 .select2-selection__arrow { top:50% !important; transform:translateY(-50%) !important; right:10px !important; }
  .form-control, .form-select { border-color:#e4e7f0; border-radius:var(--input-radius); font-size:.875rem; height:44px; transition:border-color var(--transition),box-shadow var(--transition); }
  .form-control:focus, .form-select:focus { border-color:var(--brand); box-shadow:0 0 0 3px rgba(94,114,228,.12); }
  .form-control[readonly] { background:#f8f9fe; color:#6b7490; }
  .form-label { font-size:.8rem; font-weight:600; color:#5a6178; margin-bottom:6px; text-transform:uppercase; letter-spacing:.03em; }
  .product-card { border:1.5px solid #e8ebf5; border-radius:var(--card-radius); padding:20px; position:relative; transition:border-color var(--transition),box-shadow var(--transition); background:#fff; margin-bottom:16px; }
  .product-card:hover { border-color:var(--brand); box-shadow:var(--shadow-md); }
  .product-card-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; padding-bottom:12px; border-bottom:1px solid #f0f2fb; }
  .product-card-num { font-size:.75rem; font-weight:700; color:var(--brand); background:var(--brand-light); padding:4px 10px; border-radius:20px; }
  .btn-remove-card { width:32px; height:32px; padding:0; border-radius:50%; border:1.5px solid #f0f2fb; background:#fff; color:#adb5bd; display:flex; align-items:center; justify-content:center; transition:all var(--transition); font-size:14px; cursor:pointer; }
  .btn-remove-card:hover { background:#fff0f3; border-color:var(--danger); color:var(--danger); }
  .product-info-grid { display:grid; grid-template-columns:2fr 2fr 1fr 1.2fr 0.8fr 1.2fr; gap:12px; align-items:start; margin-bottom:16px; }
  @media(max-width:1100px){ .product-info-grid { grid-template-columns:1fr 1fr; } }
  .rx-toggle-row { display:flex; align-items:center; gap:12px; padding:12px 16px; background:#f8f9fe; border-radius:10px; cursor:pointer; border:1.5px solid transparent; transition:all var(--transition); user-select:none; margin-bottom:0; }
  .rx-toggle-row:hover { border-color:var(--brand); background:var(--brand-light); }
  .rx-toggle-row.active { border-color:var(--brand); background:var(--brand-light); }
  .rx-toggle-icon { width:36px; height:36px; border-radius:10px; background:#fff; display:flex; align-items:center; justify-content:center; font-size:16px; box-shadow:var(--shadow-sm); flex-shrink:0; }
  .rx-toggle-text { flex:1; }
  .rx-toggle-text strong { font-size:.85rem; color:#1e1e2d; display:block; }
  .rx-toggle-text span { font-size:.78rem; color:#8a94a6; }
  .rx-toggle-switch { position:relative; width:44px; height:24px; flex-shrink:0; }
  .rx-toggle-switch input { opacity:0; width:0; height:0; }
  .rx-slider { position:absolute; inset:0; background:#d4d8e8; border-radius:24px; transition:background var(--transition); cursor:pointer; }
  .rx-slider::before { content:''; position:absolute; width:18px; height:18px; border-radius:50%; background:#fff; top:3px; left:3px; transition:transform var(--transition); box-shadow:0 1px 4px rgba(0,0,0,.15); }
  .rx-toggle-switch input:checked + .rx-slider { background:var(--brand); }
  .rx-toggle-switch input:checked + .rx-slider::before { transform:translateX(20px); }
  .rx-panel { margin-top:14px; border:1.5px solid var(--brand); border-radius:12px; overflow:hidden; display:none; animation:rxSlideIn .2s ease; }
  .rx-panel.show { display:block; }
  @keyframes rxSlideIn { from{opacity:0;transform:translateY(-8px)} to{opacity:1;transform:translateY(0)} }
  .rx-panel-header { background:var(--brand); color:#fff; padding:10px 20px; font-size:.8rem; font-weight:700; letter-spacing:.04em; text-transform:uppercase; display:flex; align-items:center; gap:8px; }
  .rx-eye-grid { display:grid; grid-template-columns:1fr 1fr; }
  .rx-eye-col { padding:16px 20px; }
  .rx-eye-col:first-child { border-right:1px solid #e8ebf5; }
  .rx-eye-label { display:flex; align-items:center; gap:8px; margin-bottom:14px; }
  .badge-eye { padding:4px 10px; border-radius:20px; font-size:.72rem; font-weight:700; }
  .badge-eye.od { background:#fff3cd; color:#856404; }
  .badge-eye.os { background:#d1ecf1; color:#0c5460; }
  .rx-eye-label small { font-size:.72rem; color:#8a94a6; }
  .rx-fields { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
  .rx-field label { font-size:.7rem; font-weight:600; color:#8a94a6; text-transform:uppercase; letter-spacing:.04em; margin-bottom:4px; display:block; }
  .rx-field .form-control { height:38px; font-size:.85rem; border-color:#e8ebf5; border-radius:8px; text-align:center; font-weight:600; color:#1e1e2d; }
  .rx-field .form-control:focus { border-color:var(--brand); box-shadow:0 0 0 3px rgba(94,114,228,.12); }
  .subtotal-badge { background:var(--brand-light); color:var(--brand); font-size:.8rem; font-weight:700; padding:4px 12px; border-radius:20px; }
  .btn-add-product { display:flex; align-items:center; justify-content:center; gap:8px; width:100%; padding:14px; border-radius:var(--card-radius); border:2px dashed #c8cee8; background:transparent; color:#8a94a6; font-size:.875rem; font-weight:600; transition:all var(--transition); cursor:pointer; }
  .btn-add-product:hover { border-color:var(--brand); color:var(--brand); background:var(--brand-light); }
  .grand-total-bar { background:linear-gradient(135deg,var(--brand),#8460e8); border-radius:var(--card-radius); padding:20px 28px; display:flex; align-items:center; justify-content:space-between; color:#fff; margin-bottom:20px; box-shadow:0 8px 24px rgba(94,114,228,.3); }
  .grand-total-bar .label { font-size:.85rem; opacity:.85; }
  .grand-total-bar .amount { font-size:1.8rem; font-weight:800; }
  .action-bar { display:flex; gap:12px; justify-content:flex-end; }
  .btn-save { padding:12px 32px; background:linear-gradient(135deg,var(--brand),#8460e8); border:none; border-radius:10px; color:#fff; font-weight:700; font-size:.9rem; box-shadow:0 4px 14px rgba(94,114,228,.35); transition:all var(--transition); cursor:pointer; }
  .btn-save:hover { transform:translateY(-1px); box-shadow:0 6px 20px rgba(94,114,228,.45); color:#fff; }
  .btn-cancel { padding:12px 24px; background:#fff; border:1.5px solid #e4e7f0; border-radius:10px; color:#6b7490; font-weight:600; transition:all var(--transition); text-decoration:none; display:inline-flex; align-items:center; }
  .btn-cancel:hover { border-color:#c0c8e4; background:#f8f9fe; color:#1e1e2d; }
</style>

<div class="container-fluid px-0">

  <div class="page-header-os">
    <div class="icon-wrap"><i class="fas fa-shopping-cart"></i></div>
    <div>
      <h4 class="text-white">New Offline Sale</h4>
      <p class="text-white">Fill in the form below to record a new transaction</p>
    </div>
  </div>

  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger border-0 rounded-3 mb-4">
      <i class="fas fa-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
    </div>
  <?php endif; ?>

  <form id="transactionForm" action="<?= site_url('offline-sales/store') ?>" method="post" enctype="multipart/form-data" novalidate>
    <?= csrf_field() ?>

    <!-- STEP 1: CUSTOMER -->
    <div class="os-section">
      <div class="os-section-header">
        <div class="num">1</div>
        <h6>Customer Information</h6>
      </div>
      <div class="os-section-body">
        <div class="row align-items-end g-3">
          <div class="col-md-8 col-lg-6">
            <label class="form-label">Customer <span class="text-danger">*</span></label>
            <select name="customer_id" id="customerSelect" class="form-select" required>
              <option value="">-- Select Customer --</option>
              <?php foreach ($customers as $customer): ?>
                <option value="<?= $customer['customer_id'] ?>"><?= esc($customer['customer_name']) ?></option>
              <?php endforeach ?>
            </select>
            <div class="invalid-feedback">Please select a customer.</div>
          </div>
          <div class="col-auto">
            <a href="<?= site_url('customers/form?from=offline-sales') ?>" class="btn btn-outline-primary btn-sm mb-0 d-flex align-items-center gap-2" style="height:44px;border-radius:10px;padding:0 18px;">
              <i class="fas fa-user-plus"></i> Add New Customer
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- STEP 2: PRODUCTS -->
    <div class="os-section">
      <div class="os-section-header">
        <div class="num">2</div>
        <h6>Purchased Products</h6>
        <span class="badge bg-secondary ms-auto" id="itemCountBadge" style="border-radius:20px;">1 item</span>
      </div>
      <div class="os-section-body">

        <div id="productCardsContainer">
          <!-- FIRST CARD (index 0) -->
          <div class="product-card" data-index="0">
            <div class="product-card-header">
              <span class="product-card-num">Item #1</span>
              <div class="d-flex align-items-center gap-3">
                <span class="subtotal-badge">Rp 0</span>
                <button type="button" class="btn-remove-card" title="Remove item"><i class="fas fa-times"></i></button>
              </div>
            </div>

            <div class="product-info-grid">
              <div>
                <label class="form-label">Product <span class="text-danger">*</span></label>
                <select name="items[0][product_id]" class="form-select product-select" required>
                  <option value="">-- Select Product --</option>
                  <?php foreach ($products as $p): ?>
                    <option value="<?= $p['product_id'] ?>" data-price="<?= $p['product_price'] ?>" data-stock="<?= $p['product_stock'] ?>" <?= ((int)$p['product_stock'] <= 0) ? 'disabled' : '' ?>>
                      <?= esc($p['product_name']) ?><?= ((int)$p['product_stock'] <= 0) ? ' (Out of Stock)' : '' ?>
                    </option>
                  <?php endforeach ?>
                </select>
                <div class="invalid-feedback">Please select a product.</div>
              </div>
              <div>
                <label class="form-label">Variant</label>
                <select name="items[0][variant_id]" class="form-select variant-select" disabled>
                  <option value="">-- No Variant --</option>
                </select>
              </div>
              <div>
                <label class="form-label">Stock</label>
                <input type="text" class="form-control stock-display text-center fw-bold" readonly value="-">
              </div>
              <div>
                <label class="form-label">Unit Price</label>
                <input type="number" name="items[0][price]" class="form-control price" readonly placeholder="0">
              </div>
              <div>
                <label class="form-label">Qty</label>
                <input type="number" name="items[0][qty]" class="form-control qty text-center fw-bold" value="1" min="1">
              </div>
              <div>
                <label class="form-label">Subtotal</label>
                <input type="text" class="form-control subtotal fw-bold" readonly placeholder="Rp 0">
              </div>
            </div>

            <input type="hidden" class="subtotal-raw" value="0">

            <div>
              <label class="form-label mb-2">Prescription / Kacamata</label>
              <label class="rx-toggle-row" for="rxToggle_0">
                <div class="rx-toggle-icon">👁️</div>
                <div class="rx-toggle-text">
                  <strong>Include Prescription Data</strong>
                  <span>SPH, CYL, Axis, PD — for eyeglasses / lenses</span>
                </div>
                <div class="rx-toggle-switch">
                  <input type="checkbox" id="rxToggle_0" class="rx-checkbox" name="items[0][prescription][type]" value="manual">
                  <span class="rx-slider"></span>
                </div>
              </label>
              <div class="rx-panel" id="rxPanel_0">
                <div class="rx-panel-header"><i class="fas fa-eye me-1"></i> Prescription Data</div>
                <div class="rx-eye-grid">
                  <div class="rx-eye-col">
                    <div class="rx-eye-label"><span class="badge-eye od">OD</span><small>Right Eye / Mata Kanan</small></div>
                    <div class="rx-fields">
                      <div class="rx-field"><label>SPH</label><input type="text" name="items[0][prescription][right][sph]" class="form-control" placeholder="-"></div>
                      <div class="rx-field"><label>CYL</label><input type="text" name="items[0][prescription][right][cyl]" class="form-control" placeholder="-"></div>
                      <div class="rx-field"><label>Axis</label><input type="text" name="items[0][prescription][right][axis]" class="form-control" placeholder="0°"></div>
                      <div class="rx-field"><label>PD</label><input type="text" name="items[0][prescription][right][pd]" class="form-control" placeholder="0 mm"></div>
                    </div>
                  </div>
                  <div class="rx-eye-col">
                    <div class="rx-eye-label"><span class="badge-eye os">OS</span><small>Left Eye / Mata Kiri</small></div>
                    <div class="rx-fields">
                      <div class="rx-field"><label>SPH</label><input type="text" name="items[0][prescription][left][sph]" class="form-control" placeholder="-"></div>
                      <div class="rx-field"><label>CYL</label><input type="text" name="items[0][prescription][left][cyl]" class="form-control" placeholder="-"></div>
                      <div class="rx-field"><label>Axis</label><input type="text" name="items[0][prescription][left][axis]" class="form-control" placeholder="0°"></div>
                      <div class="rx-field"><label>PD</label><input type="text" name="items[0][prescription][left][pd]" class="form-control" placeholder="0 mm"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- end first card -->
        </div>

        <button type="button" class="btn-add-product" id="addRow">
          <i class="fas fa-plus-circle" style="font-size:16px;"></i> Add Another Product
        </button>

      </div>
    </div>

    <!-- STEP 3: PAYMENT -->
    <div class="os-section">
      <div class="os-section-header">
        <div class="num">3</div>
        <h6>Payment</h6>
      </div>
      <div class="os-section-body">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Payment Method <span class="text-danger">*</span></label>
            <select id="paymentMethod" name="payment_method_id" class="form-select" required>
              <option value="">-- Select Payment Method --</option>
              <?php foreach ($paymentMethods as $method): ?>
                <option value="<?= esc($method['payment_method_id']) ?>" data-method-type="<?= esc($method['method_type']) ?>"><?= esc($method['method_name']) ?></option>
              <?php endforeach ?>
            </select>
            <div class="invalid-feedback">Please select a payment method.</div>
          </div>
          <div class="col-md-4 cash-only d-none">
            <label class="form-label">Cash Received</label>
            <input type="number" name="cash_received" id="cashReceived" class="form-control" min="0" step="0.01" placeholder="0">
          </div>
          <div class="col-md-4 noncash-only d-none">
            <label class="form-label">Upload Payment Proof</label>
            <input type="file" name="payment_proof" id="paymentProof" class="form-control" accept="image/*" style="height:auto;padding:8px;">
          </div>
          <div class="col-md-4">
            <label class="form-label">Total Payment</label>
            <input type="text" id="paymentTotal" class="form-control fw-bold" readonly value="Rp 0">
          </div>
          <div class="col-md-4 cash-only d-none">
            <label class="form-label">Change</label>
            <input type="text" id="paymentChange" class="form-control fw-bold text-success" readonly value="Rp 0">
          </div>
        </div>
      </div>
    </div>

    <!-- GRAND TOTAL + ACTIONS -->
    <div class="grand-total-bar">
      <div>
        <div class="label">Grand Total</div>
        <div class="amount" id="grandTotal">Rp 0</div>
      </div>
      <i class="fas fa-receipt" style="font-size:32px;opacity:.6;"></i>
    </div>

    <div class="action-bar">
      <a href="<?= site_url('offline-sales') ?>" class="btn-cancel">
        <i class="fas fa-arrow-left me-2"></i>Cancel
      </a>
      <button type="submit" class="btn-save">
        <i class="fas fa-save me-2"></i>Save Transaction
      </button>
    </div>

  </form>
</div>

<script>
  // ⚡ Cache product options HTML BEFORE Select2 touches the DOM
  // Using json_encode so special chars in product names (backticks, ${..}) are safe
  const PRODUCT_OPTIONS_CACHE = (function() {
    const tmp = document.createElement('div');
    tmp.innerHTML = <?= json_encode(
      implode('', array_map(function($p) {
        $disabled = ((int)$p['product_stock'] <= 0) ? ' disabled' : '';
        $outTxt   = ((int)$p['product_stock'] <= 0) ? ' (Out of Stock)' : '';
        return "<option value=\"{$p['product_id']}\" data-price=\"{$p['product_price']}\" data-stock=\"{$p['product_stock']}\"{$disabled}>"
             . esc($p['product_name']) . $outTxt . "</option>";
      }, $products))
    ) ?>;
    return tmp.innerHTML;
  })();

  let itemIndex = 1;

  function formatCurrency(v) { return 'Rp ' + Number(v||0).toLocaleString('id-ID'); }
  function parseCurrency(s)  { return Number(String(s).replace(/[^0-9]/g,''))||0; }

  function updateSubtotal(card) {
    const price = Number(card.querySelector('.price').value||0);
    const qty   = Number(card.querySelector('.qty').value||1);
    const sub   = price * qty;
    card.querySelector('.subtotal').value = formatCurrency(sub);
    card.querySelector('.subtotal-raw').value = sub;
    card.querySelector('.subtotal-badge').textContent = formatCurrency(sub);
    updateTotal();
  }

  function updateTotal() {
    let total = 0;
    document.querySelectorAll('.subtotal-raw').forEach(el => total += Number(el.value||0));
    document.getElementById('grandTotal').textContent = formatCurrency(total);
    document.getElementById('paymentTotal').value = formatCurrency(total);
    updatePaymentSummary(total);
  }

  function updatePaymentSummary(grandTotal=0) {
    const selected    = document.getElementById('paymentMethod').selectedOptions[0];
    const methodType  = selected?.dataset?.methodType;
    const cashOnly    = document.querySelectorAll('.cash-only');
    const noncashOnly = document.querySelectorAll('.noncash-only');
    const cashInput   = document.getElementById('cashReceived');
    const changeInput = document.getElementById('paymentChange');
    if (methodType === 'cash') {
      cashOnly.forEach(el=>el.classList.remove('d-none'));
      noncashOnly.forEach(el=>el.classList.add('d-none'));
      const change = Number(cashInput.value||0) - grandTotal;
      changeInput.value = formatCurrency(change>0?change:0);
    } else if (methodType) {
      cashOnly.forEach(el=>el.classList.add('d-none'));
      noncashOnly.forEach(el=>el.classList.remove('d-none'));
    } else {
      cashOnly.forEach(el=>el.classList.add('d-none'));
      noncashOnly.forEach(el=>el.classList.add('d-none'));
    }
  }

  function loadVariants(productId, card) {
    const variantSelect = card.querySelector('.variant-select');
    const priceInput    = card.querySelector('.price');
    const stockInput    = card.querySelector('.stock-display');
    const qtyInput      = card.querySelector('.qty');
    if (!productId) {
      variantSelect.innerHTML = '<option value="">-- No Variant --</option>';
      variantSelect.disabled = true;
      priceInput.value = ''; stockInput.value = '-'; qtyInput.max = '';
      updateSubtotal(card); return;
    }
    const selOpt = card.querySelector('.product-select').selectedOptions[0];
    const productPrice = selOpt?.dataset.price||0;
    const productStock = selOpt?.dataset.stock||0;
    fetch('<?= base_url('api/variants?productId=') ?>'+productId)
      .then(r=>r.json()).then(({data})=>{
        variantSelect.innerHTML = '<option value="">-- No Variant --</option>';
        if (!data||data.length===0) {
          variantSelect.disabled=true; priceInput.value=productPrice;
          stockInput.value=productStock; qtyInput.max=productStock;
        } else {
          data.forEach(v=>{
            const opt=document.createElement('option');
            opt.value=v.variant_id;
            opt.textContent=v.variant_name+(Number(v.stock)<=0?' (Out of Stock)':`— Stock: ${v.stock}`);
            opt.dataset.price=v.price; opt.dataset.stock=v.stock;
            if(Number(v.stock)<=0) opt.disabled=true;
            variantSelect.appendChild(opt);
          });
          variantSelect.disabled=false; priceInput.value=productPrice;
          stockInput.value='-'; qtyInput.max='';
        }
        updateSubtotal(card);
      });
  }

  function updateItemBadge() {
    const c=document.querySelectorAll('.product-card').length;
    document.getElementById('itemCountBadge').textContent=c+(c===1?' item':' items');
  }

  function buildCardHTML(idx) {
    return `
    <div class="product-card" data-index="${idx}">
      <div class="product-card-header">
        <span class="product-card-num">Item #${idx+1}</span>
        <div class="d-flex align-items-center gap-3">
          <span class="subtotal-badge">Rp 0</span>
          <button type="button" class="btn-remove-card"><i class="fas fa-times"></i></button>
        </div>
      </div>
      <div class="product-info-grid">
        <div>
          <label class="form-label">Product <span class="text-danger">*</span></label>
          <select name="items[${idx}][product_id]" class="form-select product-select" required>
            <option value="">-- Select Product --</option>
            ${PRODUCT_OPTIONS_CACHE}
          </select>
        </div>
        <div>
          <label class="form-label">Variant</label>
          <select name="items[${idx}][variant_id]" class="form-select variant-select" disabled>
            <option value="">-- No Variant --</option>
          </select>
        </div>
        <div>
          <label class="form-label">Stock</label>
          <input type="text" class="form-control stock-display text-center fw-bold" readonly value="-">
        </div>
        <div>
          <label class="form-label">Unit Price</label>
          <input type="number" name="items[${idx}][price]" class="form-control price" readonly placeholder="0">
        </div>
        <div>
          <label class="form-label">Qty</label>
          <input type="number" name="items[${idx}][qty]" class="form-control qty text-center fw-bold" value="1" min="1">
        </div>
        <div>
          <label class="form-label">Subtotal</label>
          <input type="text" class="form-control subtotal fw-bold" readonly placeholder="Rp 0">
        </div>
      </div>
      <input type="hidden" class="subtotal-raw" value="0">
      <div>
        <label class="form-label mb-2">Prescription / Kacamata</label>
        <label class="rx-toggle-row" for="rxToggle_${idx}">
          <div class="rx-toggle-icon">👁️</div>
          <div class="rx-toggle-text">
            <strong>Include Prescription Data</strong>
            <span>SPH, CYL, Axis, PD — for eyeglasses / lenses</span>
          </div>
          <div class="rx-toggle-switch">
            <input type="checkbox" id="rxToggle_${idx}" class="rx-checkbox" name="items[${idx}][prescription][type]" value="manual">
            <span class="rx-slider"></span>
          </div>
        </label>
        <div class="rx-panel" id="rxPanel_${idx}">
          <div class="rx-panel-header"><i class="fas fa-eye me-1"></i> Prescription Data</div>
          <div class="rx-eye-grid">
            <div class="rx-eye-col">
              <div class="rx-eye-label"><span class="badge-eye od">OD</span><small>Right Eye / Mata Kanan</small></div>
              <div class="rx-fields">
                <div class="rx-field"><label>SPH</label><input type="text" name="items[${idx}][prescription][right][sph]" class="form-control" placeholder="-"></div>
                <div class="rx-field"><label>CYL</label><input type="text" name="items[${idx}][prescription][right][cyl]" class="form-control" placeholder="-"></div>
                <div class="rx-field"><label>Axis</label><input type="text" name="items[${idx}][prescription][right][axis]" class="form-control" placeholder="0°"></div>
                <div class="rx-field"><label>PD</label><input type="text" name="items[${idx}][prescription][right][pd]" class="form-control" placeholder="0 mm"></div>
              </div>
            </div>
            <div class="rx-eye-col">
              <div class="rx-eye-label"><span class="badge-eye os">OS</span><small>Left Eye / Mata Kiri</small></div>
              <div class="rx-fields">
                <div class="rx-field"><label>SPH</label><input type="text" name="items[${idx}][prescription][left][sph]" class="form-control" placeholder="-"></div>
                <div class="rx-field"><label>CYL</label><input type="text" name="items[${idx}][prescription][left][cyl]" class="form-control" placeholder="-"></div>
                <div class="rx-field"><label>Axis</label><input type="text" name="items[${idx}][prescription][left][axis]" class="form-control" placeholder="0°"></div>
                <div class="rx-field"><label>PD</label><input type="text" name="items[${idx}][prescription][left][pd]" class="form-control" placeholder="0 mm"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>`;
  }

  function initSelect2OnCard(card) {
    $(card).find('.product-select').select2({
      theme:'bootstrap-5', placeholder:'-- Select Product --', width:'100%'
    });
  }

  $(document).ready(function() {
    $('#customerSelect').select2({theme:'bootstrap-5',placeholder:'-- Select Customer --',width:'100%'});
    initSelect2OnCard(document.querySelector('.product-card'));

    document.getElementById('addRow').addEventListener('click', function() {
      const container = document.getElementById('productCardsContainer');
      const div = document.createElement('div');
      div.innerHTML = buildCardHTML(itemIndex);
      const newCard = div.firstElementChild;
      container.appendChild(newCard);
      initSelect2OnCard(newCard);
      itemIndex++;
      updateItemBadge();
    });

    document.addEventListener('click', function(e) {
      const btn = e.target.closest('.btn-remove-card');
      if (!btn) return;
      if (document.querySelectorAll('.product-card').length <= 1) return;
      btn.closest('.product-card').remove();
      updateTotal(); updateItemBadge();
    });

    $(document).on('change', '.product-select', function() {
      loadVariants(this.value, this.closest('.product-card'));
    });

    $(document).on('change', '.variant-select', function() {
      const card = this.closest('.product-card');
      const selOpt = card.querySelector('.product-select').selectedOptions[0];
      const productPrice = selOpt?.dataset.price||0;
      const selected = this.selectedOptions[0];
      if (!selected||!selected.value) {
        card.querySelector('.price').value = productPrice;
        card.querySelector('.stock-display').value = '-';
        card.querySelector('.qty').max = '';
      } else {
        card.querySelector('.price').value = selected.dataset.price||productPrice;
        card.querySelector('.stock-display').value = selected.dataset.stock||0;
        card.querySelector('.qty').max = selected.dataset.stock||0;
      }
      updateSubtotal(card);
    });

    $(document).on('input change', '.qty', function() {
      updateSubtotal(this.closest('.product-card'));
    });

    $(document).on('change', '.rx-checkbox', function() {
      const card = this.closest('.product-card');
      const panel = card.querySelector('.rx-panel');
      const toggleRow = this.closest('.rx-toggle-row');
      if (this.checked) {
        panel.classList.add('show');
        toggleRow.classList.add('active');
      } else {
        panel.classList.remove('show');
        toggleRow.classList.remove('active');
        panel.querySelectorAll('input[type="text"]').forEach(i=>i.value='');
      }
    });

    document.getElementById('paymentMethod').addEventListener('change', function() {
      updatePaymentSummary(parseCurrency(document.getElementById('grandTotal').textContent));
    });
    document.getElementById('cashReceived').addEventListener('input', function() {
      updatePaymentSummary(parseCurrency(document.getElementById('grandTotal').textContent));
    });

    $('#transactionForm').on('submit', function(e) {
      e.preventDefault();
      if (!this.checkValidity()) { this.classList.add('was-validated'); return; }
      const btnSubmit = $(this).find('button[type="submit"]');
      const origHTML  = btnSubmit.html();
      btnSubmit.prop('disabled',true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');
      fetch(this.action,{method:'POST',body:new FormData(this),headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(r=>r.json())
        .then(data=>{
          if (data.status) {
            Swal.fire({title:'Transaction Saved!',text:'Would you like to print the receipt?',icon:'success',showCancelButton:true,confirmButtonColor:'#5e72e4',cancelButtonColor:'#8392ab',confirmButtonText:'🖨️ Print Receipt',cancelButtonText:'Skip',allowOutsideClick:false})
              .then(result=>{
                if(result.isConfirmed) window.open('<?= site_url('offline-sales/print/') ?>'+data.order_id,'_blank');
                window.location.href='<?= site_url('offline-sales') ?>';
              });
          } else {
            btnSubmit.prop('disabled',false).html(origHTML);
            Swal.fire({icon:'error',title:'Failed',text:data.message||'An error occurred.',confirmButtonColor:'#f5365c'});
          }
        })
        .catch(err=>{
          btnSubmit.prop('disabled',false).html(origHTML);
          Swal.fire({icon:'error',title:'Error',text:err.message,confirmButtonColor:'#f5365c'});
        });
    });

    updateTotal();
    updateItemBadge();
  });
</script>

<?= $this->endSection() ?>

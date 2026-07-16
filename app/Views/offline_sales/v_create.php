<?= $this->extend('layouts/l_dashboard') ?>
<?= $this->section('content') ?>

<!-- Select2 CSS & Theme -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
  /* Styling to integrate Select2 with Argon Dashboard & Bootstrap 5 nicely */
  .select2-container--bootstrap-5 {
    z-index: 1050;
  }
  .select2-container--bootstrap-5 .select2-selection {
    border-color: #d2d6da !important;
    font-size: 0.875rem !important;
    border-radius: 0.5rem !important;
    height: 40px !important;
    display: flex !important;
    align-items: center !important;
  }
  .select2-container--bootstrap-5 .select2-selection--single {
    padding: 0.5rem 0.75rem !important;
  }
  .select2-container--bootstrap-5 .select2-selection__rendered {
    color: #495057 !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
  }
  .select2-container--bootstrap-5 .select2-selection__placeholder {
    color: #adb5bd !important;
  }
  .select2-container--bootstrap-5 .select2-selection__arrow {
    top: 50% !important;
    transform: translateY(-50%) !important;
    right: 10px !important;
  }
  .group {
    display: flex;
    flex-wrap: nowrap !important;
    gap: .5rem !important;
  }
</style>

<div class="container-fluid card">
  <div class="card-header pb-0">
    <h4>Offline Sales</h4>
  </div>

  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger">
      <?= session()->getFlashdata('error') ?>
    </div>
  <?php endif; ?>

  <div class="card-body">
    <form id="transactionForm" action="<?= site_url('offline-sales/store') ?>" method="post" enctype="multipart/form-data" novalidate>
      <?= csrf_field() ?>

      <!-- CUSTOMER -->
      <div class="mb-3">
        <label class="form-label">Customer <span class="text-danger">*</span></label>
        <div class="d-flex gap-2">
          <div class="flex-grow-1">
            <select name="customer_id" id="customerSelect" class="form-select" required>
              <option value="">-- Select Customer --</option>
              <?php foreach ($customers as $customer): ?>
                <option value="<?= $customer['customer_id'] ?>">
                  <?= $customer['customer_name'] ?>
                </option>
              <?php endforeach ?>
            </select>
            <div class="invalid-feedback">Please select a customer first.</div>
          </div>
          <a href="<?= site_url('customers/form') ?>" class="btn btn-outline-primary btn-sm mb-0" id="btnAddNewCustomer" title="Add New Customer" style="display: flex; align-items: center; justify-content: center; gap: 4px;">
            <i class="fa fa-plus"></i> Add
          </a>
        </div>
      </div>

      <!-- ITEMS -->
      <h5 class="mt-4">Purchased Products</h5>

      <div class="table-responsive">
        <table class="table table-bordered align-middle" id="itemsTable" style="min-width: 1400px;">
          <thead>
            <tr>
              <th style="width:320px;">Product</th>
              <th style="width:320px;">Variant</th>
              <th style="width:120px;">Stock</th>
              <th style="min-width:120px;">Price</th>
              <th style="width:120px;">Qty</th>
              <th style="min-width:120px;">Subtotal</th>
              <th style="min-width:180px;">Prescription</th>
              <th style="min-width:50px;"></th>
            </tr>
          </thead>
          <tbody>

            <tr>
              <!-- PRODUCT -->
              <td>
                <select name="items[0][product_id]"
                  class="form-select product-select"
                  required>
                  <option value="">-- Select Product --</option>
                  <?php foreach ($products as $p): ?>
                    <option value="<?= $p['product_id'] ?>"
                      data-price="<?= $p['product_price'] ?>"
                      data-stock="<?= $p['product_stock'] ?>"
                      <?= ((int)$p['product_stock'] <= 0) ? 'disabled' : '' ?>>
                      <?= $p['product_name'] ?> <?= ((int)$p['product_stock'] <= 0) ? '(Out of Stock)' : '(Stock: ' . $p['product_stock'] . ')' ?>
                    </option>
                  <?php endforeach ?>
                </select>
                <div class="invalid-feedback">Please select a product.</div>
              </td>

              <!-- VARIANT -->
              <td>
                <select name="items[0][variant_id]"
                  class="form-select variant-select"
                  disabled>
                  <option value="">-- Select Variant --</option>
                </select>
              </td>

              <!-- STOK -->
              <td>
                <input type="text" class="form-control stock-display" readonly value="-">
              </td>

              <!-- PRICE -->
              <td>
                <input type="number" name="items[0][price]"
                  class="form-control price" readonly>
              </td>

              <!-- QTY -->
              <td>
                <input type="number" name="items[0][qty]"
                  class="form-control qty" value="1" min="1">
              </td>

              <!-- SUBTOTAL -->
              <td>
                <input type="text" class="form-control subtotal" readonly>
              </td>

              <td>
                <div class="d-flex flex-column gap-2">

                  <!-- TOGGLE -->
                  <select name="items[0][prescription][type]"
                    class="form-select rx-type">
                    <option value="none">No Prescription</option>
                    <option value="manual">Manual Input</option>
                  </select>

                  <!-- RX FORM -->
                  <div class="rx-form d-none">

                    <small class="fw-bold">OD (Right Eye)</small>
                    <div class="d-flex gap-2 flex-column mb-2">
                      <input type="text" name="items[0][prescription][right][sph]" class="form-control form-control-sm" placeholder="SPH">
                      <input type="text" name="items[0][prescription][right][cyl]" class="form-control form-control-sm" placeholder="CYL">
                      <input type="text" name="items[0][prescription][right][axis]" class="form-control form-control-sm" placeholder="Axis">
                      <input type="text" name="items[0][prescription][right][pd]" class="form-control form-control-sm" placeholder="PD">
                    </div>

                    <small class="fw-bold">OS (Left Eye)</small>
                    <div class="d-flex gap-2 flex-column">
                      <input type="text" name="items[0][prescription][left][sph]" class="form-control form-control-sm" placeholder="SPH">
                      <input type="text" name="items[0][prescription][left][cyl]" class="form-control form-control-sm" placeholder="CYL">
                      <input type="text" name="items[0][prescription][left][axis]" class="form-control form-control-sm" placeholder="Axis">
                      <input type="text" name="items[0][prescription][left][pd]" class="form-control form-control-sm" placeholder="PD">
                    </div>
                  </div>
                </div>
              </td>

              <td>
                <button type="button" class="btn btn-danger btn-sm remove-row">✕</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="card mb-4">
        <div class="card-body">
          <h5>Payment</h5>
          <div class="row g-3 mt-2">
            <div class="col-md-4">
              <label class="form-label">Payment Method <span class="text-danger">*</span></label>
              <select id="paymentMethod" name="payment_method_id" class="form-select" required>
                <option value="">-- Select Payment Method --</option>
                <?php foreach ($paymentMethods as $method): ?>
                  <option value="<?= esc($method['payment_method_id']) ?>"
                    data-method-type="<?= esc($method['method_type']) ?>">
                    <?= esc($method['method_name']) ?>
                  </option>
                <?php endforeach ?>
              </select>
              <div class="invalid-feedback">Please select a payment method.</div>
            </div>

            <div class="col-md-4 cash-only d-none">
              <label class="form-label">Customer Cash Amount</label>
              <input type="number" name="cash_received" id="cashReceived"
                class="form-control" min="0" step="0.01" placeholder="0">
            </div>

            <div class="col-md-4 noncash-only d-none">
              <label class="form-label">Upload Payment Proof</label>
              <input type="file" name="payment_proof" id="paymentProof"
                class="form-control" accept="image/*">
            </div>

            <div class="col-md-4">
              <label class="form-label">Total Payment</label>
              <input type="text" id="paymentTotal" class="form-control" readonly value="Rp 0">
            </div>

            <div class="col-md-4 cash-only d-none">
              <label class="form-label">Change</label>
              <input type="text" id="paymentChange" class="form-control" readonly value="Rp 0">
            </div>
          </div>
        </div>
      </div>

      <button type="button" class="btn btn-secondary btn-sm" id="addRow">
        + Add Product
      </button>

      <!-- TOTAL -->
      <div class="mt-4 text-end">
        <h4>Total: <span id="grandTotal">0</span></h4>
      </div>

      <div class="mt-4">
        <a href="<?= site_url('offline-sales') ?>" class="btn btn-secondary">
          Cancel
        </a>
        <button type="submit" class="btn btn-primary">
          Save
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  let index = 1;

  $(document).ready(function() {
    // Initialize Select2 on customer dropdown
    $('#customerSelect').select2({
      theme: 'bootstrap-5',
      placeholder: '-- Select Customer --',
      width: '100%'
    });

    // Initialize Select2 on product dropdowns
    $('.product-select').select2({
      theme: 'bootstrap-5',
      placeholder: '-- Select Product --',
      width: '100%'
    });

    // Handle form submission via AJAX
    $('#transactionForm').on('submit', function(e) {
      e.preventDefault();

      const form = this;
      
      // Basic HTML5 validation check
      if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
      }

      const btnSubmit = $(form).find('button[type="submit"]');
      const originalText = btnSubmit.html();
      
      // Disable submit button and show loading spinner
      btnSubmit.prop('disabled', true);
      btnSubmit.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');

      const formData = new FormData(form);

      fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(res => res.json())
      .then(data => {
        if (data.status) {
          // Show Swal prompt to print receipt or not
          Swal.fire({
            title: 'Success',
            text: 'Transaction saved successfully. Would you like to print the transaction receipt?',
            icon: 'success',
            showCancelButton: true,
            confirmButtonColor: '#7048E8',
            cancelButtonColor: '#8392ab',
            confirmButtonText: '🖨️ Yes, Print Receipt',
            cancelButtonText: 'No',
            allowOutsideClick: false,
            allowEscapeKey: false
          }).then((result) => {
            if (result.isConfirmed) {
              // Open print window in a new tab
              window.open('<?= site_url('offline-sales/print/') ?>' + data.order_id, '_blank');
            }
            // Redirect to offline sales index page
            window.location.href = '<?= site_url('offline-sales') ?>';
          });
        } else {
          // Re-enable submit button
          btnSubmit.prop('disabled', false);
          btnSubmit.html(originalText);

          // Show error alert
          Swal.fire({
            icon: 'error',
            title: 'Failed to Save Transaction',
            text: data.message || 'An error occurred while saving the transaction.',
            confirmButtonColor: '#f5365c'
          });
        }
      })
      .catch(err => {
        // Re-enable submit button
        btnSubmit.prop('disabled', false);
        btnSubmit.html(originalText);

        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'System error occurred: ' + err.message,
          confirmButtonColor: '#f5365c'
        });
      });
    });
  });

  function loadVariants(productId, row) {
    const variantSelect = row.querySelector('.variant-select');
    const priceInput = row.querySelector('.price');
    const stockInput = row.querySelector('.stock-display');
    const qtyInput = row.querySelector('.qty');

    if (!productId) {
      variantSelect.innerHTML = '<option value="">-- Select Variant --</option>';
      variantSelect.disabled = true;
      priceInput.value = '';
      stockInput.value = '-';
      qtyInput.max = '';
      updateSubtotal(row);
      return;
    }

    const selectedProductOpt = row.querySelector('.product-select').selectedOptions[0];
    const productPrice = selectedProductOpt?.dataset.price || 0;
    const productStock = selectedProductOpt?.dataset.stock || 0;

    fetch('<?= base_url('api/variants?productId=') ?>' + productId)
      .then(res => res.json())
      .then(({
        data
      }) => {

        variantSelect.innerHTML = '<option value="">-- Select Variant --</option>';

        // NO VARIANT
        if (!data || data.length === 0) {
          variantSelect.disabled = true;
          priceInput.value = productPrice;
          stockInput.value = productStock;
          qtyInput.max = productStock;
          updateSubtotal(row);
          return;
        }

        // HAS VARIANT
        data.forEach(v => {
          const opt = document.createElement('option');
          opt.value = v.variant_id;
          opt.textContent = `${v.variant_name} (Stock: ${v.stock})`;
          opt.dataset.price = v.price;
          opt.dataset.stock = v.stock;
          if (Number(v.stock) <= 0) {
            opt.disabled = true;
            opt.textContent = `${v.variant_name} (Out of Stock)`;
          }
          variantSelect.appendChild(opt);
        });

        variantSelect.disabled = false;

        // Default set to empty/placeholder until variant is chosen
        priceInput.value = productPrice;
        stockInput.value = '-';
        qtyInput.max = '';
        updateSubtotal(row);
      });
  }

  $(document).on('change', '.variant-select', function() {
    const row = this.closest('tr');
    const priceInput = row.querySelector('.price');
    const stockInput = row.querySelector('.stock-display');
    const qtyInput = row.querySelector('.qty');

    const selectedProductOpt = row.querySelector('.product-select').options[row.querySelector('.product-select').selectedIndex];
    const productPrice = selectedProductOpt?.dataset.price || 0;

    const selectedOption = this.options[this.selectedIndex];

    // ⬅️ IF variant is cleared
    if (!selectedOption || !selectedOption.value) {
      priceInput.value = productPrice;
      stockInput.value = '-';
      qtyInput.max = '';
      updateSubtotal(row);
      return;
    }

    // ⬅️ IF variant is selected
    priceInput.value = selectedOption.dataset.price || productPrice;
    stockInput.value = selectedOption.dataset.stock || 0;
    qtyInput.max = selectedOption.dataset.stock || 0;
    updateSubtotal(row);
  });

  function formatCurrency(value) {
    return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
  }

  function updateSubtotal(row) {
    const price = Number(row.querySelector('.price').value || 0);
    const qty = Number(row.querySelector('.qty').value || 1);
    row.querySelector('.subtotal').value = price * qty;
    updateTotal();
  }

  function updateTotal() {
    let total = 0;
    document.querySelectorAll('.subtotal').forEach(el => {
      total += Number(el.value || 0);
    });
    document.getElementById('grandTotal').innerText = formatCurrency(total);
    document.getElementById('paymentTotal').value = formatCurrency(total);
    updatePaymentSummary(total);
  }

  function updatePaymentSummary(grandTotal = 0) {
    const paymentMethod = document.getElementById('paymentMethod');
    const selected = paymentMethod.selectedOptions[0];
    const cashInput = document.getElementById('cashReceived');
    const changeInput = document.getElementById('paymentChange');
    const cashOnly = document.querySelectorAll('.cash-only');
    const noncashOnly = document.querySelectorAll('.noncash-only');

    const methodType = selected?.dataset?.methodType;

    if (methodType === 'cash') {
      cashOnly.forEach(el => el.classList.remove('d-none'));
      noncashOnly.forEach(el => el.classList.add('d-none'));
      const cashValue = Number(cashInput.value || 0);
      const change = cashValue - grandTotal;
      changeInput.value = formatCurrency(change > 0 ? change : 0);
    } else if (methodType) {
      cashOnly.forEach(el => el.classList.add('d-none'));
      noncashOnly.forEach(el => el.classList.remove('d-none'));
      changeInput.value = formatCurrency(0);
    } else {
      cashOnly.forEach(el => el.classList.add('d-none'));
      noncashOnly.forEach(el => el.classList.add('d-none'));
      changeInput.value = formatCurrency(0);
    }
  }

  /* EVENT HANDLER */
  $(document).on('change', '.product-select', function() {
    const row = this.closest('tr');
    const selectedProductOpt = this.options[this.selectedIndex];
    const price = selectedProductOpt?.dataset.price || 0;
    row.querySelector('.price').value = price;
    row.querySelector('.qty').value = 1;
    loadVariants(this.value, row);
    updateSubtotal(row);
  });

  $(document).on('change', '.qty', function() {
    const row = this.closest('tr');
    updateSubtotal(row);
  });

  document.getElementById('paymentMethod').addEventListener('change', function() {
    updatePaymentSummary(Number(document.getElementById('grandTotal').innerText.replace(/[^0-9]/g, '')) || 0);
  });

  document.getElementById('cashReceived').addEventListener('input', function() {
    updatePaymentSummary(Number(document.getElementById('grandTotal').innerText.replace(/[^0-9]/g, '')) || 0);
  });

  updateTotal();

  /* ADD ROW */
  document.getElementById('addRow').addEventListener('click', function() {
    const tbody = document.querySelector('#itemsTable tbody');

    // Temporarily destroy select2 on first row product-select before cloning
    const firstSelect = $(tbody.rows[0]).find('.product-select');
    if (firstSelect.data('select2')) {
      firstSelect.select2('destroy');
    }

    const newRow = tbody.rows[0].cloneNode(true);

    // Re-initialize select2 on first row
    firstSelect.select2({
      theme: 'bootstrap-5',
      placeholder: '-- Select Product --',
      width: '100%'
    });

    newRow.querySelectorAll('input, select').forEach(el => {
      if (el.name) {
        el.name = el.name.replace(/\[\d+\]/, `[${index}]`);
      }

      // ❗ PENTING
      if (el.tagName === 'INPUT') {
        el.value = '';
      }

      // ⛔ JANGAN reset SELECT
    });

    // Reset stock and helper UI for the new row
    newRow.querySelector('.stock-display').value = '-';
    newRow.querySelector('.qty').max = '';

    // default tetap none
    const rxType = newRow.querySelector('.rx-type');
    rxType.value = 'none';

    const rxForm = newRow.querySelector('.rx-form');
    rxForm.classList.add('d-none');

    newRow.querySelector('.variant-select').disabled = true;

    tbody.appendChild(newRow);

    // Initialize select2 on the new row product select
    $(newRow).find('.product-select').select2({
      theme: 'bootstrap-5',
      placeholder: '-- Select Product --',
      width: '100%'
    });

    index++;
  });



  /* REMOVE ROW */
  document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-row')) {
      const tbody = document.querySelector('#itemsTable tbody');
      if (tbody.rows.length > 1) {
        e.target.closest('tr').remove();
        updateTotal();
      }
    }
  });

  document.addEventListener('change', function(e) {

    // RX TYPE CHANGE
    if (e.target.classList.contains('rx-type')) {
      const row = e.target.closest('tr');
      const rxForm = row.querySelector('.rx-form');

      if (e.target.value === 'manual') {
        rxForm.classList.remove('d-none');
      } else {
        rxForm.classList.add('d-none');

        // reset field
        rxForm.querySelectorAll('input').forEach(i => i.value = '');
      }
    }

  });
</script>


<?= $this->endSection() ?>
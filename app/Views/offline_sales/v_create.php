<?= $this->extend('layouts/l_dashboard') ?>
<?= $this->section('content') ?>

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
    <form action="<?= site_url('offline-sales/store') ?>" method="post" enctype="multipart/form-data">
      <?= csrf_field() ?>

      <!-- CUSTOMER -->
      <div class="mb-3">
        <label class="form-label">Customer</label>
        <select name="customer_id" class="form-select" required>
          <option value="">-- Pilih Customer --</option>
          <?php foreach ($customers as $customer): ?>
            <option value="<?= $customer['customer_id'] ?>">
              <?= $customer['customer_name'] ?>
            </option>
          <?php endforeach ?>
        </select>
      </div>

      <!-- ITEMS -->
      <h5 class="mt-4">Produk Dibeli</h5>

      <table class="table table-bordered" id="itemsTable">
        <thead>
          <tr>
            <th>Produk</th>
            <th>Variant</th>
            <th width="120">Harga</th>
            <th width="80">Qty</th>
            <th width="120">Subtotal</th>
            <th width="180">Prescription</th>
            <th width="50"></th>
          </tr>
        </thead>
        <tbody>

          <tr>
            <!-- PRODUCT -->
            <td>
              <select name="items[0][product_id]"
                class="form-select product-select"
                required>
                <option value="">-- Pilih Produk --</option>
                <?php foreach ($products as $p): ?>
                  <option value="<?= $p['product_id'] ?>"
                    data-price="<?= $p['product_price'] ?>">
                    <?= $p['product_name'] ?>
                  </option>
                <?php endforeach ?>
              </select>
            </td>

            <!-- VARIANT -->
            <td>
              <select name="items[0][variant_id]"
                class="form-select variant-select"
                disabled>
                <option value="">-- Pilih Variant --</option>
              </select>
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
                  <option value="none">Tanpa Resep</option>
                  <option value="manual">Input Manual</option>
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

      <div class="card mb-4">
        <div class="card-body">
          <h5>Pembayaran</h5>
          <div class="row g-3 mt-2">
            <div class="col-md-4">
              <label class="form-label">Metode Pembayaran</label>
              <select id="paymentMethod" name="payment_method_id" class="form-select" required>
                <option value="">-- Pilih Metode Pembayaran --</option>
                <?php foreach ($paymentMethods as $method): ?>
                  <option value="<?= esc($method['payment_method_id']) ?>"
                    data-method-type="<?= esc($method['method_type']) ?>">
                    <?= esc($method['method_name']) ?>
                  </option>
                <?php endforeach ?>
              </select>
            </div>

            <div class="col-md-4 cash-only d-none">
              <label class="form-label">Nominal Uang Customer</label>
              <input type="number" name="cash_received" id="cashReceived"
                class="form-control" min="0" step="0.01" placeholder="0">
            </div>

            <div class="col-md-4 noncash-only d-none">
              <label class="form-label">Upload Bukti Pembayaran</label>
              <input type="file" name="payment_proof" id="paymentProof"
                class="form-control" accept="image/*">
            </div>

            <div class="col-md-4">
              <label class="form-label">Total Bayar</label>
              <input type="text" id="paymentTotal" class="form-control" readonly value="Rp 0">
            </div>

            <div class="col-md-4 cash-only d-none">
              <label class="form-label">Kembalian</label>
              <input type="text" id="paymentChange" class="form-control" readonly value="Rp 0">
            </div>
          </div>
        </div>
      </div>

      <button type="button" class="btn btn-secondary btn-sm" id="addRow">
        + Tambah Produk
      </button>

      <!-- TOTAL -->
      <div class="mt-4 text-end">
        <h4>Total: <span id="grandTotal">0</span></h4>
      </div>

      <div class="mt-4">
        <button type="submit" class="btn btn-primary">
          Simpan Transaksi
        </button>
        <a href="<?= site_url('offline_sales/v_index') ?>" class="btn btn-secondary">
          Batal
        </a>
      </div>
    </form>
  </div>
</div>

<script>
  let index = 1;

  function loadVariants(productId, row) {
    const variantSelect = row.querySelector('.variant-select');
    const priceInput = row.querySelector('.price');

    const productPrice =
      row.querySelector('.product-select')
      .selectedOptions[0]
      ?.dataset.price || 0;

    fetch('<?= base_url('api/variants?productId=') ?>' + productId)
      .then(res => res.json())
      .then(({
        data
      }) => {

        variantSelect.innerHTML = '<option value="">-- Pilih Variant --</option>';

        // TIDAK ADA VARIANT
        if (!data || data.length === 0) {
          variantSelect.disabled = true;
          priceInput.value = productPrice;
          updateSubtotal(row);
          return;
        }

        // ADA VARIANT
        data.forEach(v => {
          const opt = document.createElement('option');
          opt.value = v.variant_id;
          opt.textContent = v.variant_name;
          opt.dataset.price = v.price;
          variantSelect.appendChild(opt);
        });

        variantSelect.disabled = false;

        // ⬅️ DEFAULT KEMBALI KE PRODUCT PRICE
        priceInput.value = productPrice;
        updateSubtotal(row);
      });
  }

  document.addEventListener('change', function(e) {
    if (e.target.classList.contains('variant-select')) {
      const row = e.target.closest('tr');
      const priceInput = row.querySelector('.price');

      const productPrice =
        row.querySelector('.product-select')
        .selectedOptions[0]
        ?.dataset.price || 0;

      const selectedOption = e.target.selectedOptions[0];

      // ⬅️ JIKA variant dikosongkan
      if (!selectedOption || !selectedOption.value) {
        priceInput.value = productPrice;
        updateSubtotal(row);
        return;
      }

      // ⬅️ JIKA variant dipilih
      priceInput.value = selectedOption.dataset.price || productPrice;
      updateSubtotal(row);
    }
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
  document.addEventListener('change', function(e) {
    const row = e.target.closest('tr');

    // PRODUCT CHANGE
    if (e.target.classList.contains('product-select')) {
      const price = e.target.selectedOptions[0]?.dataset.price || 0;
      row.querySelector('.price').value = price;
      row.querySelector('.qty').value = 1;
      loadVariants(e.target.value, row);
      updateSubtotal(row);
    }

    // QTY CHANGE
    if (e.target.classList.contains('qty')) {
      updateSubtotal(row);
    }
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
    const newRow = tbody.rows[0].cloneNode(true);

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

    // default tetap none
    const rxType = newRow.querySelector('.rx-type');
    rxType.value = 'none';

    const rxForm = newRow.querySelector('.rx-form');
    rxForm.classList.add('d-none');

    newRow.querySelector('.variant-select').disabled = true;

    tbody.appendChild(newRow);
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
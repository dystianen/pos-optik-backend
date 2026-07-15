<?= $this->extend('layouts/l_dashboard') ?>
<?= $this->section('content') ?>
<div class="container-fluid card">
  <div class="card-header pb-0">
    <h4><?= isset($transaction) ? 'Edit Inventory Transaction' : 'Create Inventory Transaction' ?></h4>
  </div>

  <div class="card-body">
    <!-- Error Messages Display -->
    <?php if (session()->getFlashdata('failed')): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <h5 class="alert-heading"><i class="fas fa-exclamation-circle"></i> Validation Error</h5>
        <div><?= session()->getFlashdata('failed') ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <!-- Success Messages Display -->
    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <h5 class="alert-heading"><i class="fas fa-check-circle"></i> Success</h5>
        <div><?= session()->getFlashdata('success') ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <form action="<?= site_url('inventory/save') ?>" method="post" novalidate>
      <input type="hidden" name="id" value="<?= isset($transaction) ? htmlspecialchars($transaction['inventory_transaction_id']) : '' ?>">

      <!-- HIDDEN FIELDS FOR EDIT MODE -->
      <?php if (isset($transaction)): ?>
        <input type="hidden" name="product_id" value="<?= htmlspecialchars($transaction['product_id']) ?>">
        <input type="hidden" name="variant_id" value="<?= htmlspecialchars($transaction['variant_id']) ?>">
      <?php endif; ?>

      <div class="row">

        <!-- PRODUCT -->
        <div class="col-12 col-md-6 mb-3">
          <label for="product_id" class="form-label">Product <span class="text-danger">*</span></label>
          <select
            <?= isset($transaction) ? "disabled" : "" ?>
            class="form-control"
            <?= !isset($transaction) ? 'name="product_id"' : '' ?>
            id="product_id"
            <?= !isset($transaction) ? 'required' : '' ?>>
            <option value="">-- Select a Product --</option>
            <?php foreach ($products as $product) : ?>
              <option value="<?= htmlspecialchars($product['product_id']) ?>"
                <?= isset($transaction) && $transaction['product_id'] == $product['product_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($product['product_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <div class="invalid-feedback">Please select a product.</div>
          <small class="form-text text-muted d-block mt-1">Select the product for this transaction</small>
        </div>

        <!-- VARIANT -->
        <div class="col-12 col-md-6 mb-3" id="variant_wrapper" style="display:none;">
          <label for="variant_id" class="form-label">Variant</label>
          <select
            <?= isset($transaction) ? "disabled" : "" ?>
            class="form-control"
            <?= !isset($transaction) ? 'name="variant_id"' : '' ?>
            id="variant_id">
            <option value="">-- Select Variant --</option>
          </select>
          <small class="form-text text-muted d-block mt-1">Optional: Select specific variant if available</small>
        </div>

        <!-- TRANSACTION TYPE -->
        <div class="col-12 col-md-6 mb-3">
          <label for="transaction_type" class="form-label">Transaction Type <span class="text-danger">*</span></label>
          <select class="form-control" name="transaction_type" id="transaction_type" required>
            <option value="">-- Select Type --</option>
            <option value="in" <?= isset($transaction) && $transaction['transaction_type'] === 'in' ? 'selected' : '' ?>>IN (Stock In)</option>
            <option value="out" <?= isset($transaction) && $transaction['transaction_type'] === 'out' ? 'selected' : '' ?>>OUT (Stock Out)</option>
          </select>
          <div class="invalid-feedback">Please select a transaction type.</div>
          <small class="form-text text-muted d-block mt-1">Indicate whether this is a stock increase (IN) or decrease (OUT)</small>
        </div>

        <!-- REFERENCE TYPE -->
        <div class="col-12 col-md-6 mb-3">
          <label for="reference_type" class="form-label">Reference Type</label>
          <select class="form-control" name="reference_type" id="reference_type">
            <option value="">-- No Reference --</option>
            <?php
            $referenceTypes = [
              'order'       => 'Order',
              'adjustment'  => 'Adjustment',
              'return'      => 'Return',
              'transfer'    => 'Transfer',
              'initial'     => 'Initial Stock'
            ];
            foreach ($referenceTypes as $key => $label):
            ?>
              <option value="<?= htmlspecialchars($key) ?>" <?= isset($transaction) && $transaction['reference_type'] === $key ? 'selected' : '' ?>>
                <?= htmlspecialchars($label) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <small class="form-text text-muted d-block mt-1">Optional: Link this transaction to an order, adjustment, or process</small>
        </div>

        <!-- REFERENCE ID -->
        <div class="col-12 col-md-6 mb-3">
          <label for="reference_id" class="form-label">Reference ID</label>
          <input
            type="text"
            class="form-control"
            name="reference_id"
            id="reference_id"
            placeholder="e.g., ORDER-UUID or TRANSFER-ID"
            value="<?= htmlspecialchars($transaction['reference_id'] ?? '') ?>">
          <small class="text-muted d-block mt-1">
            Optional: Enter the ID of the related order, adjustment, or process.
          </small>
        </div>

        <!-- TRANSACTION DATE (If Create) -->
        <?php if (!isset($transaction)): ?>
          <div class="col-12 col-md-6 mb-3">
            <label for="transaction_date" class="form-label">Transaction Date <span class="text-danger">*</span></label>
            <input
              type="date"
              class="form-control"
              name="transaction_date"
              id="transaction_date"
              value="<?= old('transaction_date', date('Y-m-d')) ?>"
              required>
            <div class="invalid-feedback">Please select a valid transaction date.</div>
            <small class="form-text text-muted d-block mt-1">Date when the transaction occurs (format: YYYY-MM-DD)</small>
          </div>
        <?php endif; ?>

        <!-- QUANTITY -->
        <div class="col-12 col-md-6 mb-3">
          <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
          <input class="form-control" type="number" placeholder="0" name="quantity" id="quantity" required min="1"
            value="<?= old('quantity', isset($transaction) ? $transaction['quantity'] : '') ?>">
          <div class="invalid-feedback">Please enter a valid quantity (minimum 1).</div>
          <small class="form-text text-muted d-block mt-1">Enter the quantity to be added or removed (must be greater than 0)</small>
        </div>

        <!-- DESCRIPTION -->
        <div class="col-12 mb-3">
          <label for="description" class="form-label">Description</label>
          <textarea class="form-control" name="description" id="description" rows="4" placeholder="Enter notes or details about this transaction..."><?= old('description', isset($transaction) ? htmlspecialchars($transaction['description']) : '') ?></textarea>
          <small class="form-text text-muted d-block mt-1">Optional: Add notes or details (max 500 characters)</small>
        </div>

        <div class="col-12 mt-4">
          <a href="<?= base_url('/inventory') ?>" class="btn btn-secondary">
            <i class="fas fa-times"></i> Cancel
          </a>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> <?= isset($transaction) ? 'Update Transaction' : 'Create Transaction' ?>
          </button>
        </div>

      </div>
    </form>
  </div>
</div>


<!-- AJAX SCRIPT -->
<script>
  document.addEventListener("DOMContentLoaded", function() {

    function loadVariants(productId, selectedVariant = null) {
      if (!productId) {
        document.getElementById("variant_wrapper").style.display = "none";
        document.getElementById("variant_id").innerHTML = "<option value=''>-- Select Variant --</option>";
        return;
      }

      fetch('<?= base_url('api/variants?productId=') ?>' + productId)
        .then(response => response.json())
        .then(({
          data
        }) => {
          let variantSelect = document.getElementById("variant_id");
          variantSelect.innerHTML = "";

          if (data.length === 0) {
            document.getElementById("variant_wrapper").style.display = "none";
            return;
          }

          // Show Variant field
          document.getElementById("variant_wrapper").style.display = "block";

          variantSelect.innerHTML = "<option value=''>-- Select Variant --</option>";

          data.forEach(v => {
            let option = document.createElement("option");
            option.value = v.variant_id;
            option.textContent = v.variant_name;

            if (selectedVariant && selectedVariant == v.variant_id) {
              option.selected = true;
            }

            variantSelect.appendChild(option);
          });
        })
        .catch(err => console.error("Failed to load variants", err));
    }

    // Initial load (for edit form)
    let initialProduct = document.getElementById("product_id").value;
    let initialVariant = "<?= isset($transaction) ? $transaction['variant_id'] ?? '' : '' ?>";

    if (initialProduct) {
      loadVariants(initialProduct, initialVariant);
    }

    // When product changes (only for create mode)
    <?php if (!isset($transaction)): ?>
      document.getElementById("product_id").addEventListener("change", function() {
        loadVariants(this.value, null);
      });
    <?php endif; ?>

    // Form client-side validation
    const form = document.querySelector('form');
    if (form) {
      form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
          e.preventDefault();
          e.stopPropagation();
        }
        form.classList.add('was-validated');
      });
    }
  });
</script>

<?= $this->endSection() ?>
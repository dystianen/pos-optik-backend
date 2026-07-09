<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>

<div class="container-fluid card">
  <div class="card-header pb-0">
    <h4><?= isset($attribute) ? 'Edit Product Attribute' : 'Add Product Attribute' ?></h4>
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

    <form action="<?= site_url('/product-attribute/save') ?>" method="post" novalidate>
      <?= csrf_field() ?>

      <input type="hidden" name="id" value="<?= htmlspecialchars($attribute['attribute_id'] ?? '') ?>">

      <!-- Attribute Name -->
      <div class="mb-3">
        <label class="form-label">Attribute Name <span class="text-danger">*</span></label>
        <input type="text" name="attribute_name" class="form-control"
          placeholder="e.g., Color, Size, Frame Type"
          value="<?= htmlspecialchars($attribute['attribute_name'] ?? '') ?>" required>
        <small class="form-text text-muted d-block mt-1">Enter attribute name (max 50 characters)</small>
      </div>

      <!-- Category -->
      <div class="mb-3">
        <label class="form-label">Category</label>
        <select class="form-control" name="category_id">
          <option value="">-- Select Category --</option>
          <?php foreach ($categories ?? [] as $cat): ?>
            <option value="<?= htmlspecialchars($cat['category_id']) ?>"
              <?= isset($attribute) && $attribute['category_id'] === $cat['category_id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($cat['category_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <small class="form-text text-muted d-block mt-1">Link this attribute to a specific product category (optional)</small>
      </div>

      <!-- Attribute Type -->
      <div class="mb-3">
        <label class="form-label">Attribute Type <span class="text-danger">*</span></label>
        <select class="form-control" name="attribute_type" id="attribute_type" required>
          <option value="" disabled>-- Select attribute type --</option>
          <option value="text" <?= isset($attribute) && $attribute['attribute_type'] === 'text' ? 'selected' : '' ?>>Text (e.g., description)</option>
          <option value="number" <?= isset($attribute) && $attribute['attribute_type'] === 'number' ? 'selected' : '' ?>>Number (e.g., quantity)</option>
          <option value="dropdown" <?= isset($attribute) && $attribute['attribute_type'] === 'dropdown' ? 'selected' : '' ?>>Dropdown (predefined options)</option>
        </select>
        <small class="form-text text-muted d-block mt-1">Select the type of attribute data</small>
      </div>

      <!-- Attribute Options -->
      <div class="row mb-3">
        <div class="col-md-6">
          <div class="form-check">
            <input type="checkbox" name="is_variantable" id="is_variantable" class="form-check-input" value="1"
              <?= isset($attribute) && $attribute['is_variantable'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="is_variantable">
              Is Variantable
            </label>
            <small class="form-text text-muted d-block">Check if this attribute creates product variants</small>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-check">
            <input type="checkbox" name="is_required" id="is_required" class="form-check-input" value="1"
              <?= isset($attribute) && $attribute['is_required'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="is_required">
              Is Required
            </label>
            <small class="form-text text-muted d-block">Check if customers must select this attribute</small>
          </div>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-6">
          <div class="form-check">
            <input type="checkbox" name="is_filterable" id="is_filterable" class="form-check-input" value="1"
              <?= isset($attribute) && $attribute['is_filterable'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="is_filterable">
              Is Filterable
            </label>
            <small class="form-text text-muted d-block">Allow filtering products by this attribute</small>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-check">
            <input type="checkbox" name="use_master_values" id="use_master_values" class="form-check-input" value="1"
              <?= isset($attribute) && $attribute['use_master_values'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="use_master_values">
              Use Master Values
            </label>
            <small class="form-text text-muted d-block">Use predefined master values for this attribute</small>
          </div>
        </div>
      </div>

      <!-- Sort Order -->
      <div class="mb-3">
        <label class="form-label">Sort Order</label>
        <input type="number" name="sort_order" class="form-control"
          value="<?= htmlspecialchars($attribute['sort_order'] ?? 0) ?>" min="0">
        <small class="form-text text-muted d-block mt-1">Display order (lower numbers appear first)</small>
      </div>

      <!-- Dynamic Dropdown Values -->
      <div id="dropdown-values" class="mb-3" style="display: none;">
        <label class="form-label">Dropdown Options</label>
        <p class="text-muted small">Add the options that will be available in the dropdown menu.</p>

        <div id="value-list">
          <?php if (isset($options)): ?>
            <?php foreach ($options as $opt): ?>
              <div class="dropdown-row mb-2 d-flex align-items-center" style="gap: 10px;">
                <input type="hidden" name="value_ids[]" value="<?= htmlspecialchars($opt['attribute_master_id']) ?>">
                <input type="text" name="values[]" class="form-control" placeholder="Option value" value="<?= htmlspecialchars($opt['value']) ?>">
                <button type="button" class="btn btn-danger remove-value" title="Remove option">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <button type="button" id="add-value" class="btn btn-secondary btn-sm">
          <i class="fas fa-plus"></i> Add Option
        </button>
      </div>

      <div class="mt-4">
        <a href="<?= base_url('/product-attribute') ?>" class="btn btn-secondary">
          <i class="fas fa-times"></i> Cancel
        </a>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save"></i> <?= isset($attribute) ? 'Update Attribute' : 'Create Attribute' ?>
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  function refreshDropdownVisibility() {
    const type = document.getElementById("attribute_type").value;
    const isDropdown = (type === "dropdown");
    document.getElementById("dropdown-values").style.display = isDropdown ? "block" : "none";

    const useMasterCheckbox = document.getElementById("use_master_values");
    if (isDropdown) {
      useMasterCheckbox.checked = true;
      useMasterCheckbox.disabled = true;
      
      // Inject hidden input to ensure it is submitted when form is posted
      if (!document.getElementById("use_master_values_hidden")) {
        const hiddenInput = document.createElement("input");
        hiddenInput.type = "hidden";
        hiddenInput.name = "use_master_values";
        hiddenInput.value = "1";
        hiddenInput.id = "use_master_values_hidden";
        useMasterCheckbox.parentNode.appendChild(hiddenInput);
      }
    } else {
      useMasterCheckbox.disabled = false;
      const hidden = document.getElementById("use_master_values_hidden");
      if (hidden) {
        hidden.remove();
      }
    }
  }

  document.getElementById("attribute_type").addEventListener("change", refreshDropdownVisibility);
  refreshDropdownVisibility();

  document.getElementById("add-value").addEventListener("click", function() {
    const div = document.createElement("div");
    div.classList.add("dropdown-row", "mb-2", "d-flex", "align-items-center");
    div.style.gap = "10px";

    div.innerHTML = `
        <input type="hidden" name="value_ids[]" value="">
        <input type="text" name="values[]" class="form-control" placeholder="Option value">
        <button type="button" class="btn btn-danger remove-value" title="Remove option">
            <i class="fas fa-trash"></i>
        </button>
    `;

    document.getElementById("value-list").appendChild(div);
  });

  document.addEventListener("click", function(e) {
    const delBtn = e.target.closest(".remove-value");
    if (delBtn) {
      delBtn.closest(".dropdown-row").remove();
    }
  });

  // Form validation
  document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
      }
      form.classList.add('was-validated');
    });
  });
</script>

<?= $this->endSection() ?>
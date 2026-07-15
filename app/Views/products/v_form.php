<?= $this->extend('layouts/l_dashboard') ?>
<?= $this->section('content') ?>

<div class="container-fluid card">
  <div class="card-header pb-0">
    <h4><?= isset($product) ? 'Edit Product' : 'Create Product' ?></h4>
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

    <form id="productForm" action="<?= site_url('products/save') ?>" method="post" enctype="multipart/form-data" novalidate>
      <?= csrf_field() ?>

      <input type="hidden" name="id" value="<?= $product['product_id'] ?? '' ?>">

      <div class="row">
        <!-- Category -->
        <div class="col-12 col-md-6 mb-3">
          <label class="form-label">Category <span class="text-danger">*</span></label>
          <select class="form-control" name="category_id" required>
            <option value="" disabled <?= !isset($product) ? 'selected' : '' ?>>-- Select a category --</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= htmlspecialchars($c['category_id']) ?>"
                <?= (old('category_id', $product['category_id'] ?? '') == $c['category_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['category_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <div class="invalid-feedback">Please select a category.</div>
          <small class="form-text text-muted d-block mt-1">Select the product category</small>
        </div>

        <!-- Name -->
        <div class="col-12 col-md-6 mb-3">
          <label class="form-label">Product Name <span class="text-danger">*</span></label>
          <input type="text" name="product_name" class="form-control"
            placeholder="e.g., Blue Light Glasses"
            value="<?= old('product_name', $product['product_name'] ?? '') ?>" required>
          <div class="invalid-feedback">Please enter the product name.</div>
        </div>

        <!-- Price (base) -->
        <div class="col-12 col-md-6 mb-3">
          <label class="form-label">Base Price <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text">Rp</span>
            <input type="number" step="0.01" name="product_price" class="form-control"
              placeholder="0.00"
              value="<?= old('product_price', $product['product_price'] ?? '') ?>" required>
            <div class="invalid-feedback">Please enter a valid base price.</div>
          </div>
          <small class="text-muted d-block mt-1">Default price used if variant price not provided</small>
        </div>

        <!-- Stock (base) -->
        <div class="col-12 col-md-6 mb-3">
          <label class="form-label">Base Stock</label>
          <input disabled type="number" name="product_stock" class="form-control"
            value="<?= old('product_stock', $product['product_stock'] ?? '') ?>" placeholder="Auto-calculated">
          <small class="text-muted d-block mt-1">Auto-calculated from variant stocks</small>
        </div>

        <!-- Brand -->
        <div class="col-12 col-md-6 mb-3">
          <label class="form-label">Brand <span class="text-danger">*</span></label>
          <input type="text" name="product_brand" class="form-control"
            placeholder="e.g., Ray-Ban"
            value="<?= old('product_brand', $product['product_brand'] ?? '') ?>" required>
          <div class="invalid-feedback">Please enter the brand.</div>
        </div>

        <!-- SKU -->
        <div class="col-12 col-md-6 mb-3">
          <label class="form-label">Product SKU</label>
          <input disabled type="text" id="product_sku" name="product_sku" class="form-control"
            placeholder="Auto-generated SKU"
            value="<?= old('product_sku', $product['product_sku'] ?? '') ?>" readonly>
        </div>

        <!-- Description -->
        <div class="col-12 mb-3">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="4"
            placeholder="Enter product description..."><?= old('description', $product['description'] ?? '') ?></textarea>
          <small class="form-text text-muted d-block mt-1">Optional: Detailed product description (max 1000 characters)</small>
        </div>

        <!-- MULTIPLE IMAGES -->
        <div class="col-12 mb-3">
          <label class="form-label">Product Images <?= !isset($product) || empty($product_images) ? '<span class="text-danger">*</span>' : '' ?></label>
          <input type="file" name="images[]" class="form-control" multiple accept=".jpg,.jpeg,.png" <?= !isset($product) || empty($product_images) ? 'required' : '' ?>>
          <div class="invalid-feedback">Please upload at least one product image.</div>
          <small class="text-muted d-block mt-1">Upload multiple images (JPG, JPEG, or PNG). Used as product images and fallback for variants.</small>

          <?php if (!empty($product_images)): ?>
            <div class="mt-3">
              <label class="fw-bold">Current Images:</label>
              <div class="d-flex flex-wrap">
                <?php foreach ($product_images as $img): ?>
                  <div class="me-2 mb-2 position-relative image-container" style="width: 150px;">
                    <img src="<?= htmlspecialchars($img['url']) ?>" class="rounded border w-100 h-100" style="object-fit: contain;" alt="<?= htmlspecialchars($img['alt_text']) ?>">

                    <!-- Overlay dengan button delete di tengah -->
                    <div class="image-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                      <button
                        type="button"
                        class="btn btn-danger btn-sm delete-image-btn"
                        data-image-id="<?= htmlspecialchars($img['product_image_id']) ?>"
                        data-product-id="<?= htmlspecialchars($product['product_id']) ?>"
                        title="Delete Image">
                        <i class="fas fa-trash"></i>
                      </button>

                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
        </div>

        <!-- DYNAMIC ATTRIBUTES -->
        <div class="col-12 mt-4" id="attributesSection" <?= empty($attributes) ? 'style="display:none;"' : '' ?>>
          <h5>Product Attributes</h5>
          <p class="text-muted">Fill in the attributes and select which one you want to make a variant.</p>

          <div class="row g-4" id="attributesContainer">
            <?= view('products/partials/v_attributes_partial', [
                'attributes' => $attributes,
                'pav_values' => $pav_values ?? [],
                'selected_attribute_values' => $selected_attribute_values ?? [],
                'selected_attributes' => $selected_attributes ?? []
            ]) ?>
          </div>
        </div>


        <!-- VARIANT SECTION -->
        <div class="col-12 mt-3" id="variantSection" style="display: none;">
          <h5>Variant List</h5>
          <p class="text-muted">Automated combination of selected variant attributes. You can edit price, stock and image per variant.</p>

          <div class="table-responsive">
            <table class="table table-bordered" id="variantTable">
              <thead>
                <tr>
                  <th>Variant</th>
                  <th>SKU</th>
                  <th>Price</th>
                  <th>Stock</th>
                  <th>Image</th>
                  <th class="sticky-action text-center">Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>

          <button type="button" id="rebuildVariants" class="btn btn-sm btn-secondary mt-2">Regenerate Variants</button>
        </div>

      </div>

      <div class="mt-4 d-flex gap-2">
        <a href="<?= base_url('/products') ?>" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
          <?= isset($product) ? 'Update' : 'Save' ?>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- CSS -->
<style>
  .image-container {
    overflow: hidden;
  }

  .image-overlay {
    background-color: rgba(0, 0, 0, 0);
    transition: background-color 0.3s ease;
    opacity: 0;
    border-radius: 0.25rem;
  }

  .image-container:hover .image-overlay {
    background-color: rgba(0, 0, 0, 0.6);
    opacity: 1;
  }

  .delete-image-btn {
    padding: 8px 12px;
    font-size: 16px;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    transform: scale(0.8);
    transition: transform 0.2s ease;
  }

  .image-container:hover .delete-image-btn {
    transform: scale(1);
  }

  .delete-image-btn:hover {
    background-color: #c82333 !important;
    transform: scale(1.1) !important;
  }
</style>


<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  // ========================================
  // IMPROVED VARIANT GENERATION
  // ========================================
  (function() {
    const form = document.getElementById('productForm');
    const variantSection = document.getElementById('variantSection');
    const variantTableBody = document.querySelector('#variantTable tbody');
    const rebuildBtn = document.getElementById('rebuildVariants');
    const categorySelect = document.querySelector('select[name="category_id"]');
    const attributesContainer = document.getElementById('attributesContainer');
    const productIdInput = document.querySelector('input[name="id"]');

    // REHYDRATE VARIANTS FROM PHP
    const existingVariants = <?= isset($variants) ? json_encode($variants) : '[]' ?>;

    // TRACK NEXT INDEX untuk variant baru
    let nextVariantIndex = existingVariants.length;

    function getVariantAttributes() {
      const checked = Array.from(document.querySelectorAll('input[name="variant_attributes[]"]:checked'));

      return checked.map(cb => {
        const attrId = cb.value;
        const attrName = cb.closest('.p-3').querySelector('label.fw-bold').textContent.trim();

        let values = [];

        // 1. Check if there are checkboxes checked for this attribute (multiselect group)
        const checkedBoxes = document.querySelectorAll(`.checkbox-group[data-attr-id="${attrId}"] input.attribute-checkbox:checked`);
        if (checkedBoxes.length > 0) {
          values = Array.from(checkedBoxes).map(c => c.value);
        } else {
          // 2. Check if it is a radio group
          const checkedRadio = document.querySelector(`.radio-group[data-attr-id="${attrId}"] input.attribute-radio:checked`);
          if (checkedRadio) {
            values = [checkedRadio.value];
          } else {
            // 3. Check if it is a select element
            const selectEl = document.querySelector(`select.attribute-input[data-attr-id="${attrId}"]`);
            if (selectEl) {
              if (selectEl.value) {
                values = [selectEl.value];
              }
            } else {
              // 4. Check if it is a textarea
              const textareaEl = document.querySelector(`textarea.attribute-input[data-attr-id="${attrId}"]`);
              if (textareaEl) {
                const raw = textareaEl.value || '';
                values = raw.split(',').map(s => s.trim()).filter(Boolean);
              } else {
                // 5. Fallback: text, number, or boolean checkbox input
                const textInput = document.querySelector(`input.attribute-input[data-attr-id="${attrId}"]`);
                if (textInput) {
                  if (textInput.type === 'checkbox') {
                    // Single boolean checkbox
                    values = textInput.checked ? ['1'] : [];
                  } else {
                    const raw = textInput.value || '';
                    values = raw.split(',').map(s => s.trim()).filter(Boolean);
                  }
                }
              }
            }
          }
        }

        return {
          id: attrId,
          name: attrName,
          values: values
        };
      });
    }

    function generateCombinations(arrays) {
      if (!arrays.length) return [];
      return arrays.reduce((acc, curr) => {
        const res = [];
        acc.forEach(a => {
          curr.values.forEach(v => res.push(a.concat([{
            attrId: curr.id,
            attrName: curr.name,
            value: v
          }])));
        });
        return res;
      }, [
        []
      ]);
    }

    function renderVariants() {
      const attrs = getVariantAttributes();

      if (!attrs.length) {
        variantSection.style.display = 'none';
        return;
      }

      // Ensure each selected attribute has at least one value
      for (const a of attrs) {
        if (!a.values.length) {
          console.warn(`Attribute "${a.name}" has no values`);
          variantSection.style.display = 'none';
          return;
        }
      }

      const combos = generateCombinations(attrs);
      variantSection.style.display = combos.length ? 'block' : 'none';

      // ✅ STEP 1: Simpan SEMUA data variant yang ada dengan mapping attribute mereka
      const existingVariantsMap = {};
      Array.from(variantTableBody.querySelectorAll('tr')).forEach(tr => {
        const variantIdInput = tr.querySelector('input[name*="[variant_id]"]');
        const signatureInput = tr.querySelector('input[name*="[variant_signature]"]');
        const skuInput = tr.querySelector('input[name*="[variant_sku]"]');
        const priceInput = tr.querySelector('input[name*="[price]"]');
        const stockInput = tr.querySelector('input[name*="[stock]"]');
        const imagePreview = tr.querySelector('img');

        const signature = signatureInput ? signatureInput.value : '';
        if (signature) {
          existingVariantsMap[signature] = {
            variant_id: variantIdInput ? variantIdInput.value : null,
            variant_sku: skuInput ? skuInput.value : '',
            price: priceInput ? priceInput.value : '',
            stock: stockInput ? stockInput.value : '',
            image_url: imagePreview ? imagePreview.src : ''
          };
        }
      });

      console.log('📦 All existing variants map:', existingVariantsMap);

      // ✅ STEP 2: Hapus SEMUA variant dari table
      variantTableBody.innerHTML = '';

      // ✅ STEP 3: Reset index
      nextVariantIndex = 0;

      // ✅ STEP 4: Generate variant baru berdasarkan kombinasi
      combos.forEach((combo) => {
        const variantLabel = combo.map(c => c.value).join(' - ');
        const signature = generateSignature(combo);

        // LOOKUP BY SIGNATURE
        const match = existingVariantsMap[signature];

        const idx = nextVariantIndex++;
        const tr = document.createElement('tr');

        const computedSku = match && match.variant_sku ? match.variant_sku : generateVariantSku(null, combo);

        if (match) {
          const hasVariantId = match.variant_id;

          tr.innerHTML = `
            <td>
              ${variantLabel}
              <input type="hidden" name="variants[${idx}][label]" value="${escapeHtml(variantLabel)}">
              <input type="hidden" name="variants[${idx}][variant_signature]" value="${escapeHtml(signature)}">
              ${hasVariantId ? `<input type="hidden" name="variants[${idx}][variant_id]" value="${match.variant_id}">` : ''}
            </td>
            <td>
              <input type="text" name="variants[${idx}][variant_sku]" class="form-control form-control-sm"
                value="${escapeHtml(computedSku)}" readonly placeholder="Auto-generated">
            </td>
            <td>
              <input type="number" step="0.01" name="variants[${idx}][price]" class="form-control form-control-sm"
                value="${match.price || ''}" placeholder="Leave empty to use base price">
            </td>
            <td>
              <input disabled type="number" name="variants[${idx}][stock]" class="form-control form-control-sm"
                value="${match.stock || ''}" placeholder="Auto-calculated">
            </td>
            <td>
              <input type="file" name="variants[${idx}][image]" accept=".jpg,.jpeg,.png" class="form-control form-control-sm mb-1">
              ${match.image_url ? `<img src="${match.image_url}" width="30" class="rounded">` : ''}
            </td>
            <td>
              <button type="button" class="btn btn-sm btn-danger remove-variant">Remove</button>
            </td>
          `;
        } else {
          // Variant baru yang belum pernah ada
          tr.innerHTML = `
            <td>
              ${variantLabel}
              <input type="hidden" name="variants[${idx}][label]" value="${escapeHtml(variantLabel)}">
              <input type="hidden" name="variants[${idx}][variant_signature]" value="${escapeHtml(signature)}">
            </td>
            <td>
              <input type="text" name="variants[${idx}][variant_sku]" class="form-control form-control-sm"
                value="${escapeHtml(computedSku)}" readonly placeholder="Auto-generated">
            </td>
            <td><input type="number" step="0.01" name="variants[${idx}][price]" class="form-control form-control-sm" placeholder="Leave empty to use base price"></td>
            <td><input disabled type="number" name="variants[${idx}][stock]" class="form-control form-control-sm" placeholder="Auto-calculated"></td>
            <td>
              <input type="file" name="variants[${idx}][image]" accept=".jpg,.jpeg,.png" class="form-control form-control-sm">
            </td>
            <td>
              <button type="button" class="btn btn-sm btn-danger remove-variant">Remove</button>
            </td>
          `;
        }

        // Store attribute mapping untuk variant baru/existing
        const hiddenMapping = document.createElement('input');
        hiddenMapping.type = 'hidden';
        hiddenMapping.name = `variants[${idx}][mapping]`;
        hiddenMapping.value = JSON.stringify(combo.map(c => ({
          attribute_id: c.attrId,
          value: c.value
        })));
        tr.appendChild(hiddenMapping);

        variantTableBody.appendChild(tr);
      });

      attachRemoveHandlers();
    }

    function renderExistingVariants() {
      existingVariants.forEach((v, idx) => {
        const alreadyRendered = variantTableBody.querySelector(`input[name="variants[${idx}][variant_id]"][value="${v.variant_id}"]`);
        if (alreadyRendered) {
          return;
        }

        const tr = document.createElement('tr');
        const mappingJson = JSON.stringify((v.pav_mapping || []).map(m => ({
          attribute_id: m.attribute_id,
          value: m.value
        })));

        let signature = v.variant_signature || '';
        if (!signature && v.pav_mapping) {
          const formattedMapping = v.pav_mapping.map(m => ({
            attrId: m.attribute_id,
            attrName: m.attribute_name || '',
            value: m.value
          }));
          signature = generateSignature(formattedMapping);
        }

        tr.innerHTML = `
        <td>
          ${v.variant_name}
          <input type="hidden" name="variants[${idx}][label]" value="${escapeHtml(v.variant_name)}">
          <input type="hidden" name="variants[${idx}][variant_signature]" value="${escapeHtml(signature)}">
          <input type="hidden" name="variants[${idx}][variant_id]" value="${v.variant_id}">
        </td>
        <td>
          <input type="text" name="variants[${idx}][variant_sku]" class="form-control form-control-sm"
            value="${escapeHtml(v.variant_sku || '')}" readonly placeholder="Auto-generated">
        </td>
        <td>
          <input type="number" step="0.01" name="variants[${idx}][price]" class="form-control form-control-sm"
            value="${v.price || ''}">
        </td>
        <td>
          <input disabled type="number" name="variants[${idx}][stock]" class="form-control form-control-sm"
            value="${v.stock || ''}">
        </td>
        <td>
          <input type="file" name="variants[${idx}][image]" accept=".jpg,.jpeg,.png" class="form-control form-control-sm mb-1">
          ${v.variant_image ? `<img src="${v.variant_image.url}" width="30" class="rounded">` : ''}
        </td>
        <td>
          <button type="button" class="btn btn-sm btn-danger remove-variant">Remove</button>
        </td>
      `;

        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = `variants[${idx}][mapping]`;
        hidden.value = mappingJson;
        tr.appendChild(hidden);

        variantTableBody.appendChild(tr);
      });

      attachRemoveHandlers();
      nextVariantIndex = existingVariants.length;
    }

    function attachRemoveHandlers() {
      document.querySelectorAll('.remove-variant').forEach(btn => {
        btn.removeEventListener('click', handleRemove);
        btn.addEventListener('click', handleRemove);
      });
    }

    function handleRemove(e) {
      e.target.closest('tr').remove();
    }

    function escapeHtml(unsafe) {
      return unsafe.replace(/[&<"'>]/g, function(m) {
        return ({
          '&': '&amp;',
          '<': '&lt;',
          '>': '&gt;',
          '"': '&quot;',
          "'": '&#039;'
        })[m];
      });
    }

    function toSlug(str) {
      if (!str) return '';
      return str
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s_-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
    }

    function generateSignature(combo) {
      const sorted = [...combo].sort((a, b) => a.attrId.localeCompare(b.attrId));
      return sorted
        .map(item => toSlug(item.attrName) + ':' + toSlug(item.value))
        .join('|');
    }

    function abbreviateValue(val) {
      val = (val || '').trim();
      if (!val) return '';
      
      const digits = val.replace(/[^0-9]/g, '');
      if (digits !== '' && (!isNaN(val) || /^\d+(\.\d+)?$/.test(val))) {
        return digits;
      }
      
      const clean = val.replace(/[^A-Za-z0-9\s-]/g, '');
      const words = clean.split(/[\s-]+/).filter(Boolean);
      if (words.length > 1) {
        return words.map(w => w[0]).join('').toUpperCase();
      }
      
      const upper = clean.toUpperCase();
      const consonants = upper.replace(/[AEIOU]/g, '');
      if (consonants.length >= 3) {
        return consonants.substring(0, 3);
      }
      return upper.substring(0, Math.min(3, upper.length));
    }

    function generateVariantSku(productSku, combo) {
      if (!productSku) {
        productSku = document.getElementById('product_sku').value || 'OPT-TEMP';
      }
      const sortedCombo = [...combo].sort((a, b) => a.attrId.localeCompare(b.attrId));
      const abbrs = sortedCombo.map(c => abbreviateValue(c.value)).filter(Boolean);
      if (abbrs.length > 0) {
        return productSku + '-' + abbrs.join('-');
      }
      return productSku;
    }

    function updateProductSkuPreview() {
      const productSkuInput = document.getElementById('product_sku');
      if (!productSkuInput) return;
      
      const categoryId = categorySelect.value;
      if (!categoryId) {
        productSkuInput.value = '';
        return;
      }
      
      const selectedOption = categorySelect.options[categorySelect.selectedIndex];
      const categoryText = selectedOption ? selectedOption.text.trim() : '';
      if (!categoryText || categoryText.startsWith('--')) {
        productSkuInput.value = '';
        return;
      }
      
      let word = categoryText.split(' ')[0];
      word = word.replace(/[^A-Za-z0-9]/g, '').toUpperCase();
      if (word.length > 3 && word.endsWith('S')) {
        word = word.substring(0, word.length - 1);
      }
      
      const savedSku = '<?= $product['product_sku'] ?? '' ?>';
      const savedCategoryId = '<?= $product['category_id'] ?? '' ?>';
      if (savedSku && categoryId === savedCategoryId) {
        productSkuInput.value = savedSku;
      } else {
        productSkuInput.value = `OPT-${word}-TEMP`;
      }
    }

    document.querySelectorAll('.delete-image-btn').forEach((btn) => {
      btn.addEventListener('click', async function() {
        const imageId = this.dataset.imageId
        const productId = this.dataset.productId

        const confirm = await Swal.fire({
          title: 'Delete image?',
          text: 'This action cannot be undone',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          confirmButtonText: 'Yes, delete it'
        })

        if (!confirm.isConfirmed) return

        Swal.fire({
          title: 'Deleting...',
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading()
        })

        try {
          const response = await fetch('<?= site_url('products/delete-image') ?>', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
              image_id: imageId,
              product_id: productId
            })
          })

          const result = await response.json()

          if (!response.ok || !result.success) {
            throw new Error(result.message || 'Failed to delete image')
          }

          // ✅ INI FIX UTAMANYA
          const wrapper = this.closest('.image-container')
          if (wrapper) {
            wrapper.remove()
          }

          Swal.fire({
            icon: 'success',
            title: 'Deleted',
            text: result.message,
            timer: 1200,
            showConfirmButton: false
          })
        } catch (error) {
          Swal.fire({
            icon: 'error',
            title: 'Failed',
            text: error.message
          })
        }
      })
    })

    // ✅ EVENT LISTENERS
    // Trigger saat:
    // 1. Toggle variant checkbox
    // 2. Input text berubah (untuk text type)
    // 3. Checkbox attribute berubah (untuk dropdown type)
    const attributesSection = document.getElementById('attributesSection');

    if (categorySelect && attributesContainer) {
      categorySelect.addEventListener('change', async function() {
        const categoryId = this.value;
        const productId = productIdInput ? productIdInput.value : '';
        
        updateProductSkuPreview();
        
        if (!categoryId) {
          attributesContainer.innerHTML = '';
          if (attributesSection) attributesSection.style.display = 'none';
          renderVariants();
          return;
        }

        // Show a loading indicator (also show the section while loading)
        if (attributesSection) attributesSection.style.display = 'block';
        attributesContainer.innerHTML = `
          <div class="col-12 text-center py-4">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading attributes...</span>
            </div>
            <p class="text-muted mt-2 mb-0">Loading attributes...</p>
          </div>
        `;

        try {
          const response = await fetch('<?= site_url('products/attributes-partial') ?>?category_id=' + categoryId + '&product_id=' + productId);
          if (!response.ok) {
            throw new Error('Failed to fetch attributes');
          }
          const html = await response.text();
          attributesContainer.innerHTML = html;
          // Hide section if partial returned no attribute cards
          if (attributesSection) {
            attributesSection.style.display = html.trim() === '' ? 'none' : 'block';
          }
          renderVariants();
        } catch (error) {
          console.error(error);
          if (attributesSection) attributesSection.style.display = 'block';
          attributesContainer.innerHTML = `
            <div class="col-12">
              <div class="alert alert-danger mb-0">
                <i class="fas fa-exclamation-circle me-2"></i> Failed to load attributes. Please try again.
              </div>
            </div>
          `;
        }
      });
    }

    document.addEventListener('change', function(e) {
      if (
        e.target.matches('.variant-toggle') ||
        e.target.matches('.attribute-input') ||
        e.target.matches('.attribute-checkbox')
      ) {
        clearTimeout(window._variantTimer);
        window._variantTimer = setTimeout(renderVariants, 300);
      }
    });

    rebuildBtn.addEventListener('click', function() {
      const rowsToRemove = Array.from(variantTableBody.querySelectorAll('tr')).filter(tr => {
        return !tr.querySelector('input[name*="[variant_id]"]');
      });
      rowsToRemove.forEach(row => row.remove());
      renderVariants();
    });

    // ✅ INITIAL RENDER
    window.addEventListener('load', function() {
      updateProductSkuPreview();
      if (existingVariants.length > 0) {
        renderExistingVariants();
        variantSection.style.display = 'block';
        return;
      }

      renderVariants();
    });

    // ✅ Form client-side validation
    if (form) {
      form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
          e.preventDefault();
          e.stopPropagation();
        }
        form.classList.add('was-validated');
      });
    }

  })();
</script>
<?= $this->endSection() ?>
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
          <small class="form-text text-muted d-block mt-1">Select the product category</small>
        </div>

        <!-- Name -->
        <div class="col-12 col-md-6 mb-3">
          <label class="form-label">Product Name <span class="text-danger">*</span></label>
          <input type="text" name="product_name" class="form-control"
            placeholder="e.g., Blue Light Glasses"
            value="<?= old('product_name', $product['product_name'] ?? '') ?>" required>
          <small class="form-text text-muted d-block mt-1">Enter product name (max 100 characters)</small>
        </div>

        <!-- Price (base) -->
        <div class="col-12 col-md-6 mb-3">
          <label class="form-label">Base Price <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text">Rp</span>
            <input type="number" step="0.01" name="product_price" class="form-control"
              placeholder="0.00"
              value="<?= old('product_price', $product['product_price'] ?? '') ?>" required>
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
          <label class="form-label">Brand</label>
          <input type="text" name="product_brand" class="form-control"
            placeholder="e.g., Ray-Ban"
            value="<?= old('product_brand', $product['product_brand'] ?? '') ?>">
          <small class="form-text text-muted d-block mt-1">Optional: Product brand (max 50 characters)</small>
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
          <label class="form-label">Product Images</label>
          <input type="file" name="images[]" class="form-control" multiple accept=".jpg,.jpeg,.png">
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
        <div class="col-12 mt-4">
          <h5>Product Attributes</h5>
          <p class="text-muted">Fill in the attributes and select which one you want to make a variant.</p>

          <div class="row g-4">

            <?php
            // Daftar attribute yang boleh jadi variant
            $variantAllowed = [
              'Color',
              'Lens Type',
              'Frame Size (Width)',
              'Bridge Size',
              'Temple Length',
            ];
            ?>

            <?php foreach ($attributes as $attr): ?>

              <?php
              $attrId = $attr['attribute_id'];
              $attrName = $attr['attribute_name'];
              $attrType = $attr['attribute_type'];

              // ✅ Data yang sudah dipilih sebelumnya (array of values)
              $selectedValues = $selected_attribute_values[$attrId] ?? [];

              // ✅ Value untuk text input (join dengan koma)
              $existingTextValue = isset($pav_values[$attrId])
                ? implode(', ', $pav_values[$attrId]['values'])
                : '';

              // ✅ Apakah attribute ini dipilih sebagai variant
              $isVariant = in_array($attrId, $selected_attributes ?? []) ? 'checked' : '';

              // ✅ Cek apakah attribute boleh jadi variant
              $allowed = in_array($attrName, $variantAllowed);
              ?>

              <div class="col-12 col-md-6">
                <div class="p-3 border rounded-3 h-100">

                  <!-- NAMA ATTRIBUTE + TOGGLE VARIANT -->
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="fw-bold mb-1"><?= esc($attrName) ?></label>

                    <?php if ($allowed): ?>
                      <div class="form-check form-switch">
                        <input type="checkbox"
                          class="form-check-input variant-toggle"
                          name="variant_attributes[]"
                          value="<?= $attrId ?>"
                          data-attr-id="<?= $attrId ?>"
                          <?= in_array($attrId, $selected_attributes ?? []) ? 'checked' : '' ?>>

                        <label class="form-check-label">Variant</label>
                      </div>
                    <?php endif; ?>

                  </div>

                  <!-- JIKA TIPE TEXT -->
                  <?php if ($attrType === 'text'): ?>

                    <input
                      type="text"
                      class="form-control attribute-input"
                      name="attributes[<?= $attrId ?>]"
                      data-attr-id="<?= $attrId ?>"
                      placeholder="Enter <?= strtolower($attrName) ?> (comma separated for variants)"
                      value="<?= esc($existingTextValue) ?>">


                  <?php endif; ?>

                  <!-- JIKA TIPE DROPDOWN: checkbox list -->
                  <?php if ($attrType === 'dropdown'): ?>

                    <?php if (!empty($attr['values'])): ?>
                      <div class="mt-2 checkbox-group" data-attr-id="<?= $attrId ?>">
                        <small class="text-muted d-block mb-2">Select one or more options:</small>

                        <?php foreach ($attr['values'] as $val): ?>
                          <div class="form-check mb-2">
                            <input
                              class="form-check-input attribute-checkbox"
                              type="checkbox"
                              name="attributes[<?= $attrId ?>][]"
                              value="<?= esc($val['value']) ?>"
                              data-attr-id="<?= $attrId ?>"
                              id="attr_<?= $attrId ?>_<?= $val['attribute_master_id'] ?>"
                              <?= in_array($val['value'], $selectedValues) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="attr_<?= $attrId ?>_<?= $val['attribute_master_id'] ?>">
                              <?= esc($val['value']) ?>
                            </label>
                          </div>
                        <?php endforeach; ?>

                      </div>
                    <?php else: ?>
                      <p class="text-muted"><em>No options available</em></p>
                    <?php endif; ?>

                  <?php endif; ?>
                </div>
              </div>

            <?php endforeach; ?>

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

    // REHYDRATE VARIANTS FROM PHP
    const existingVariants = <?= isset($variants) ? json_encode($variants) : '[]' ?>;

    // TRACK NEXT INDEX untuk variant baru
    let nextVariantIndex = existingVariants.length;

    /**
     * ✅ Get variant attributes WITH CORRECT VALUES
     * - Untuk TEXT: ambil dari input, split by comma
     * - Untuk DROPDOWN: ambil dari checked checkboxes
     */
    function getVariantAttributes() {
      const checked = Array.from(document.querySelectorAll('input[name="variant_attributes[]"]:checked'));

      return checked.map(cb => {
        const attrId = cb.value;
        const attrName = cb.closest('.p-3').querySelector('label.fw-bold').textContent.trim();

        // ✅ CEK TYPE: text atau dropdown
        const textInput = document.querySelector(`input.attribute-input[data-attr-id="${attrId}"]`);
        const checkboxGroup = document.querySelector(`.checkbox-group[data-attr-id="${attrId}"]`);

        let values = [];

        if (textInput) {
          // TEXT TYPE: split by comma
          const raw = textInput.value || '';
          values = raw.split(',').map(s => s.trim()).filter(Boolean);
        } else if (checkboxGroup) {
          // DROPDOWN TYPE: ambil dari checked checkboxes
          const checkedBoxes = checkboxGroup.querySelectorAll('input.attribute-checkbox:checked');
          values = Array.from(checkedBoxes).map(cb => cb.value);
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
      const existingVariantsData = [];
      Array.from(variantTableBody.querySelectorAll('tr')).forEach(tr => {
        const labelInput = tr.querySelector('input[name*="[label]"]');
        const variantIdInput = tr.querySelector('input[name*="[variant_id]"]');
        const priceInput = tr.querySelector('input[name*="[price]"]');
        const stockInput = tr.querySelector('input[name*="[stock]"]');
        const imagePreview = tr.querySelector('img');
        const mappingInput = tr.querySelector('input[name*="[mapping]"]');

        let mapping = [];
        const label = labelInput?.value || '';
        const labelParts = label.split(' - ').map(s => s.trim());

        try {
          if (mappingInput && mappingInput.value) {
            const parsed = JSON.parse(mappingInput.value);
            console.log('📥 Raw mapping:', parsed);

            // ✅ CEK FORMAT: apakah array of strings atau array of objects?
            if (parsed.length > 0 && typeof parsed[0] === 'string') {
              // Format lama: ['id1', 'id2'] → reconstruct dari label
              console.log('🔄 Old format detected, reconstructing from label...');
              mapping = parsed.map((attrId, index) => ({
                attribute_id: attrId,
                value: labelParts[index] || ''
              }));
            } else if (parsed.length > 0 && typeof parsed[0] === 'object') {
              // Format baru: [{attribute_id: ..., value: ...}]
              mapping = parsed;
            }

            console.log('✅ Final mapping:', mapping);
          } else {
            console.warn('⚠️ No mapping input, using label fallback');
            // Fallback: parse dari label
            const currentAttrs = getVariantAttributes();
            mapping = labelParts.map((value, i) => ({
              attribute_id: currentAttrs[i]?.id || null,
              value: value
            })).filter(m => m.attribute_id);
          }
        } catch (e) {
          console.error('❌ Failed to parse mapping:', e);
        }

        existingVariantsData.push({
          variant_id: variantIdInput ? variantIdInput.value : null,
          label: label,
          price: priceInput ? priceInput.value : '',
          stock: stockInput ? stockInput.value : '',
          image_url: imagePreview ? imagePreview.src : '',
          mapping: mapping
        });
      });

      console.log('📦 All existing variants data:', existingVariantsData);

      // ✅ STEP 2: Hapus SEMUA variant dari table
      variantTableBody.innerHTML = '';

      // ✅ STEP 3: Reset index
      nextVariantIndex = 0;

      // ✅ STEP 4: Generate variant baru berdasarkan kombinasi
      combos.forEach((combo) => {
        const variantLabel = combo.map(c => c.value).join(' - ');

        // ✅ MATCHING LOGIC: Cari variant existing yang VALUE-nya adalah SUBSET dari combo baru
        // Jadi jika combo baru = [Photochromic, Blue, 14mm]
        // Dan ada variant lama = [Photochromic, Blue]
        // Maka variant lama ini MATCH dan datanya akan digunakan

        let bestMatch = null;
        let maxMatches = 0;

        existingVariantsData.forEach(v => {
          const existingValues = v.mapping.map(m => m.value);
          const newValues = combo.map(c => c.value);
          const matchCount = existingValues.filter(ev => newValues.includes(ev)).length;

          console.log('🔍 Checking variant:', v.label);
          console.log('   Existing values:', existingValues);
          console.log('   New combo values:', newValues);
          console.log('   Match count:', matchCount, '/', existingValues.length);

          if (matchCount === existingValues.length && matchCount > 0 && matchCount > maxMatches) {
            console.log('   ✅ BEST MATCH FOUND!');
            bestMatch = v;
            maxMatches = matchCount;
          }
        });

        console.log('📦 Final best match for', variantLabel, ':', bestMatch);


        const idx = nextVariantIndex++;
        const tr = document.createElement('tr');

        // Jika ada best match, gunakan data dari variant tersebut
        if (bestMatch) {
          const hasVariantId = bestMatch.variant_id;

          tr.innerHTML = `
            <td>
              ${variantLabel}
              <input type="hidden" name="variants[${idx}][label]" value="${escapeHtml(variantLabel)}">
              ${hasVariantId ? `<input type="hidden" name="variants[${idx}][variant_id]" value="${bestMatch.variant_id}">` : ''}
            </td>
            <td>
              <input type="number" step="0.01" name="variants[${idx}][price]" class="form-control form-control-sm"
                value="${bestMatch.price || ''}" placeholder="Leave empty to use base price">
            </td>
            <td>
              <input disabled type="number" name="variants[${idx}][stock]" class="form-control form-control-sm"
                value="${bestMatch.stock || ''}" placeholder="Auto-calculated">
            </td>
            <td>
              <input type="file" name="variants[${idx}][image]" accept=".jpg,.jpeg,.png" class="form-control form-control-sm mb-1">
              ${bestMatch.image_url ? `<img src="${bestMatch.image_url}" width="30" class="rounded">` : ''}
            </td>
            <td>
              <button type="button" class="btn btn-sm btn-danger remove-variant">Remove</button>
            </td>
          `;

          // Update mapping dengan combo BARU (bukan mapping lama)
          const hidden = document.createElement('input');
          hidden.type = 'hidden';
          hidden.name = `variants[${idx}][mapping]`;
          hidden.value = JSON.stringify(combo.map(c => ({
            attribute_id: c.attrId,
            value: c.value
          })));
          tr.appendChild(hidden);
        } else {
          // Variant baru yang belum pernah ada
          tr.innerHTML = `
            <td>${variantLabel}
              <input type="hidden" name="variants[${idx}][label]" value="${escapeHtml(variantLabel)}">
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

          // Store attribute mapping untuk variant baru
          const hidden = document.createElement('input');
          hidden.type = 'hidden';
          hidden.name = `variants[${idx}][mapping]`;
          hidden.value = JSON.stringify(combo.map(c => ({
            attribute_id: c.attrId,
            value: c.value
          })));
          tr.appendChild(hidden);
        }

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
        const mappingJson = JSON.stringify(v.pav_mapping || []);

        tr.innerHTML = `
        <td>
          ${v.variant_name}
          <input type="hidden" name="variants[${idx}][label]" value="${escapeHtml(v.variant_name)}">
          <input type="hidden" name="variants[${idx}][variant_id]" value="${v.variant_id}">
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
      if (existingVariants.length > 0) {
        renderExistingVariants();
        variantSection.style.display = 'block';
        return;
      }

      renderVariants();
    });


  })();
</script>
<?= $this->endSection() ?>
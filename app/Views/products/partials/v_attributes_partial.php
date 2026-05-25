<?php foreach ($attributes as $attr): ?>

  <?php
  $attrId = $attr['attribute_id'];
  $attrName = $attr['attribute_name'];
  $attrType = $attr['attribute_type'];
  $isRequired = (bool)($attr['is_required'] ?? false);
  $useMasterValues = (bool)($attr['use_master_values'] ?? false);

  // ✅ Data yang sudah dipilih sebelumnya (array of values)
  $selectedValues = $selected_attribute_values[$attrId] ?? [];

  // ✅ Value untuk text input (join dengan koma)
  $existingTextValue = isset($pav_values[$attrId])
    ? implode(', ', $pav_values[$attrId]['values'])
    : '';

  // ✅ Apakah attribute ini dipilih sebagai variant
  $isVariant = in_array($attrId, $selected_attributes ?? []) ? 'checked' : '';

  // ✅ Cek apakah attribute boleh jadi variant (dari database)
  $allowed = (bool)($attr['is_variantable'] ?? false);
  ?>

  <div class="col-12 col-md-6">
    <div class="p-3 border rounded-3 h-100">

      <!-- NAMA ATTRIBUTE + TOGGLE VARIANT -->
      <div class="d-flex justify-content-between align-items-center mb-2">
        <label class="fw-bold mb-1">
          <?= esc($attrName) ?>
          <?php if ($isRequired): ?>
            <span class="text-danger">*</span>
          <?php endif; ?>
        </label>

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

      <!-- DYNAMIC RENDER BASED ON ATTRIBUTE TYPE -->
      <?php if ($attrType === 'text'): ?>
        <input
          type="text"
          class="form-control attribute-input"
          name="attributes[<?= $attrId ?>]"
          data-attr-id="<?= $attrId ?>"
          placeholder="Enter <?= esc(strtolower($attrName)) ?> (comma separated for variants)"
          value="<?= esc($existingTextValue) ?>"
          <?= $isRequired ? 'required' : '' ?>>

      <?php elseif ($attrType === 'textarea'): ?>
        <textarea
          class="form-control attribute-input"
          name="attributes[<?= $attrId ?>]"
          data-attr-id="<?= $attrId ?>"
          placeholder="Enter <?= esc(strtolower($attrName)) ?>..."
          rows="3"
          <?= $isRequired ? 'required' : '' ?>><?= esc($existingTextValue) ?></textarea>

      <?php elseif ($attrType === 'number'): ?>
        <input
          type="number"
          class="form-control attribute-input"
          name="attributes[<?= $attrId ?>]"
          data-attr-id="<?= $attrId ?>"
          placeholder="Enter <?= esc(strtolower($attrName)) ?>"
          value="<?= esc($existingTextValue) ?>"
          <?= $isRequired ? 'required' : '' ?>>

      <?php elseif ($attrType === 'dropdown'): ?>
        <?php if ($useMasterValues && !empty($attr['values'])): ?>
          <select
            class="form-select attribute-input"
            name="attributes[<?= $attrId ?>]"
            data-attr-id="<?= $attrId ?>"
            <?= $isRequired ? 'required' : '' ?>>
            <option value="" <?= empty($selectedValues) ? 'selected' : '' ?>>-- Select option --</option>
            <?php foreach ($attr['values'] as $val): ?>
              <option value="<?= esc($val['value']) ?>" <?= in_array($val['value'], $selectedValues) ? 'selected' : '' ?>>
                <?= esc($val['value']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        <?php else: ?>
          <input
            type="text"
            class="form-control attribute-input"
            name="attributes[<?= $attrId ?>]"
            data-attr-id="<?= $attrId ?>"
            placeholder="Enter <?= esc(strtolower($attrName)) ?>"
            value="<?= esc($existingTextValue) ?>"
            <?= $isRequired ? 'required' : '' ?>>
        <?php endif; ?>

      <?php elseif ($attrType === 'multiselect'): ?>
        <?php if ($useMasterValues && !empty($attr['values'])): ?>
          <div class="mt-2 checkbox-group" data-attr-id="<?= $attrId ?>">
            <small class="text-muted d-block mb-2">Select options:</small>
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
          <input
            type="text"
            class="form-control attribute-input"
            name="attributes[<?= $attrId ?>]"
            data-attr-id="<?= $attrId ?>"
            placeholder="Enter <?= esc(strtolower($attrName)) ?> (comma separated)"
            value="<?= esc($existingTextValue) ?>"
            <?= $isRequired ? 'required' : '' ?>>
        <?php endif; ?>

      <?php elseif ($attrType === 'checkbox'): ?>
        <div class="form-check mt-2">
          <input type="hidden" name="attributes[<?= $attrId ?>]" value="0">
          <input
            type="checkbox"
            class="form-check-input attribute-input"
            name="attributes[<?= $attrId ?>]"
            value="1"
            data-attr-id="<?= $attrId ?>"
            id="attr_<?= $attrId ?>_chk"
            <?= in_array('1', $selectedValues) || in_array('true', $selectedValues) || ($existingTextValue == '1') ? 'checked' : '' ?>>
          <label class="form-check-label" for="attr_<?= $attrId ?>_chk">
            Yes / Enabled
          </label>
        </div>

      <?php elseif ($attrType === 'radio'): ?>
        <?php if ($useMasterValues && !empty($attr['values'])): ?>
          <div class="mt-2 radio-group" data-attr-id="<?= $attrId ?>">
            <small class="text-muted d-block mb-2">Select option:</small>
            <?php foreach ($attr['values'] as $val): ?>
              <div class="form-check mb-2">
                <input
                  class="form-check-input attribute-radio"
                  type="radio"
                  name="attributes[<?= $attrId ?>]"
                  value="<?= esc($val['value']) ?>"
                  data-attr-id="<?= $attrId ?>"
                  id="attr_<?= $attrId ?>_<?= $val['attribute_master_id'] ?>"
                  <?= in_array($val['value'], $selectedValues) ? 'checked' : '' ?>
                  <?= $isRequired ? 'required' : '' ?>>
                <label class="form-check-label" for="attr_<?= $attrId ?>_<?= $val['attribute_master_id'] ?>">
                  <?= esc($val['value']) ?>
                </label>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <input
            type="text"
            class="form-control attribute-input"
            name="attributes[<?= $attrId ?>]"
            data-attr-id="<?= $attrId ?>"
            placeholder="Enter <?= esc(strtolower($attrName)) ?>"
            value="<?= esc($existingTextValue) ?>"
            <?= $isRequired ? 'required' : '' ?>>
        <?php endif; ?>

      <?php endif; ?>

    </div>
  </div>

<?php endforeach; ?>

<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>
<div class="container-fluid card">
  <div class="card-header mb-4 pb-0 d-flex align-items-center justify-content-between">
    <h4>Coupon & Discount List</h4>
    <a href="<?= base_url('/coupons/form') ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Coupon</a>
  </div>

  <div class="card-body pt-0 pb-2">
    <div class="table-responsive">
      <table class="table align-items-center mb-0 table-bordered" style="min-width: 1300px; width: 100%;">
        <thead>
          <tr>
            <th class="text-center">No</th>
            <th>Code</th>
            <th>Description</th>
            <th>Discount Type</th>
            <th>Discount Value</th>
            <th>Min. Spend</th>
            <th>Max. Discount</th>
            <th class="text-center">Active Period</th>
            <th class="text-center">Usage Limits (Global/User)</th>
            <th class="text-center">First Order Only</th>
            <th class="text-center">Status</th>
            <th class="sticky-action text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php $startIndex = ($pager["currentPage"] - 1) * $pager["limit"] + 1; ?>

          <?php if (empty($coupons)): ?>
            <tr>
              <td colspan="12" class="text-center text-muted">No coupon data available.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($coupons as $coupon): ?>
              <tr>
                <td class="text-center"><?= $startIndex++ ?></td>
                <td><strong><?= htmlspecialchars($coupon['code']) ?></strong></td>
                <td style="min-width: 300px; white-space: normal;"><?= htmlspecialchars($coupon['description'] ?? '-') ?></td>
                <td>
                  <span class="badge bg-<?= $coupon['discount_type'] === 'percentage' ? 'info' : ($coupon['discount_type'] === 'free_shipping' ? 'primary' : 'success') ?>">
                    <?= $coupon['discount_type'] === 'percentage' ? 'Percentage' : ($coupon['discount_type'] === 'free_shipping' ? 'Free Shipping' : 'Fixed Amount') ?>
                  </span>
                </td>
                <td>
                  <?= $coupon['discount_type'] === 'percentage' ? 
                      ((float)$coupon['discount_value']) . '%' : 
                      ($coupon['discount_type'] === 'free_shipping' ? 'Free Shipping' : 'Rp ' . number_format($coupon['discount_value'], 0, ',', '.')) ?>
                </td>
                <td>
                  <?= empty($coupon['min_order_amount']) ? '-' : 'Rp ' . number_format($coupon['min_order_amount'], 0, ',', '.') ?>
                </td>
                <td>
                  <?= empty($coupon['max_discount']) ? '-' : 'Rp ' . number_format($coupon['max_discount'], 0, ',', '.') ?>
                </td>
                <td class="text-center" style="font-size: 12px;">
                  <?= date('d/m/Y H:i', strtotime($coupon['start_date'])) ?> <br> to <br>
                  <?= date('d/m/Y H:i', strtotime($coupon['end_date'])) ?>
                </td>
                <td class="text-center">
                  Global: <?= $coupon['usage_limit'] ?? '∞' ?> <br>
                  Per User: <?= $coupon['per_user_limit'] ?? '∞' ?>
                </td>
                <td class="text-center">
                  <?= $coupon['first_order_only'] ?
                    '<span class="badge bg-primary"><i class="fas fa-check"></i> Yes</span>' :
                    '<span class="badge bg-secondary"><i class="fas fa-times"></i> No</span>'
                  ?>
                </td>
                <td class="text-center">
                  <?= $coupon['is_active'] ?
                    '<span class="badge bg-success">Active</span>' :
                    '<span class="badge bg-danger">Inactive</span>'
                  ?>
                </td>
                <td class="sticky-action text-center">
                  <a href="<?= base_url('/coupons/form?id=' . $coupon['coupon_id']) ?>" class="btn btn-sm btn-warning">
                    <i class="fa-solid fa-pen-to-square"></i>
                  </a>
                  <form action="<?= base_url('/coupons/delete/' . $coupon['coupon_id']) ?>" method="post" style="display:inline-block;" class="confirm-delete">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-danger">
                      <i class="fa-solid fa-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <nav aria-label="Page navigation" class="mt-4">
      <ul class="pagination" id="pagination">
      </ul>
    </nav>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="text/javascript">
  // PAGINATION
  function handlePagination(pageNumber) {
    window.location.replace(`<?php echo base_url(); ?>coupons?page=${pageNumber}`);
  }

  var paginationContainer = document.getElementById('pagination');
  var totalPages = <?= $pager["totalPages"] ?>;
  if (totalPages > 1) {
    for (var i = 1; i <= totalPages; i++) {
      var pageItem = document.createElement('li');
      pageItem.classList.add('page-item');
      pageItem.classList.add('primary');
      if (i === <?= $pager["currentPage"] ?>) {
        pageItem.classList.add('active');
      }

      var pageLink = document.createElement('a');
      pageLink.classList.add('page-link');
      pageLink.href = 'javascript:void(0);';
      pageLink.textContent = i;

      pageLink.addEventListener('click', function() {
        var pageNumber = parseInt(this.textContent);
        handlePagination(pageNumber);
      });

      pageItem.appendChild(pageLink);
      paginationContainer.appendChild(pageItem);
    }
  }

  // SweetAlert delete confirmation
  document.querySelectorAll('.confirm-delete').forEach(form => {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      Swal.fire({
        title: 'Are you sure?',
        text: "This coupon will be deleted permanently!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete!',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          form.submit();
        }
      });
    });
  });
</script>
<?= $this->endSection() ?>

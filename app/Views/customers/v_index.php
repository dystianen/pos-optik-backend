<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>
<div class="container-fluid card">
  <div class="card-header mb-4 pb-0 d-flex align-items-center justify-content-between">
    <h4>Customers List</h4>
    <a href="<?= base_url('/customers/form') ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Customer</a>
  </div>

  <div class="card-body pt-0 pb-2">
    <div class="table-responsive">
      <table class="table align-items-center mb-0 table-bordered">
        <thead>
          <tr>
            <th class="text-center">No</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Date of Birth</th>
            <th>Gender</th>
            <th class="sticky-action text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php $startIndex = ($pager["currentPage"] - 1) * $pager["limit"] + 1; ?>

          <?php if (empty($customers)): ?>
            <tr>
              <td colspan="8" class="text-center text-muted">No customer data available.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($customers as $customer): ?>
              <tr>
                <td class="text-center"><?= $startIndex++ ?></td>
                <td><?= $customer['customer_name'] ?></td>
                <td><?= $customer['customer_email'] ?></td>
                <td><?= $customer['customer_phone'] ?></td>
                <td><?= date('d/m/Y', strtotime($customer['customer_dob'])) ?></td>
                <td><?= $customer['customer_gender'] ?></td>
                <td class="sticky-action text-center">
                  <a href="<?= base_url('/customers/form?id=' . $customer['customer_id']) ?>" class="btn btn-sm btn-warning" title="Edit Customer">
                    <i class="fa-solid fa-pen-to-square"></i>
                  </a>
                  <form action="<?= base_url('/customers/reset-password/' . $customer['customer_id']) ?>" method="post" style="display:inline-block;" class="reset-password-form">
                    <?= csrf_field() ?>
                    <button type="button" class="btn btn-sm btn-info reset-btn" data-name="<?= esc($customer['customer_name']) ?>" title="Reset Password">
                      <i class="fa-solid fa-key"></i>
                    </button>
                  </form>
                  <form action="<?= base_url('/customers/delete/' . $customer['customer_id']) ?>" method="post" style="display:inline-block;">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')" title="Delete Customer"><i class="fa-solid fa-trash"></i></button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>

      </table>
    </div>

    <nav aria-label="Page navigation example" class="mt-4">
      <ul class="pagination" id="pagination">
      </ul>
    </nav>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="text/javascript">
  var currentURL = window.location.search;
  var urlParams = new URLSearchParams(currentURL);
  var pageParam = urlParams.get('page');

  // PAGINATION
  function handlePagination(pageNumber) {
    window.location.replace(`<?php echo base_url(); ?>customers?page=${pageNumber}`);
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
      pageLink.href = 'javascript:void(0);'
      pageLink.textContent = i;

      pageLink.addEventListener('click', function() {
        var pageNumber = parseInt(this.textContent);
        handlePagination(pageNumber);
      });

      pageItem.appendChild(pageLink);
      paginationContainer.appendChild(pageItem);
    }
  }

  $(document).ready(function() {
    // Confirmation dialog for Reset Password
    $('.reset-btn').on('click', function(e) {
      e.preventDefault();
      var form = $(this).closest('form');
      var customerName = $(this).data('name');
      
      Swal.fire({
        title: 'Reset Password?',
        html: `Apakah Anda yakin ingin mereset password untuk customer <b>${customerName}</b>? Password baru akan digenerate secara acak.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#7048E8',
        cancelButtonColor: '#8392ab',
        confirmButtonText: 'Ya, Reset!',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          form.submit();
        }
      });
    });

    // Alert showing newly reset random password
    <?php if (session()->getFlashdata('reset_password')): ?>
      var tempPassword = '<?= esc(session()->getFlashdata('reset_password')) ?>';
      Swal.fire({
        title: 'Password Berhasil Direset!',
        html: `Berikut adalah password baru untuk customer <b><?= esc(session()->getFlashdata('customer_name')) ?></b>:<br><br><h3 class="text-primary font-weight-bold" style="letter-spacing: 2px; font-family: monospace; background: #f3f3f3; padding: 10px; border-radius: 5px; border: 1px dashed #7048E8; display: inline-block; margin: 10px 0;">${tempPassword}</h3><br><br>Silakan salin dan berikan password ini kepada customer.`,
        icon: 'success',
        showCancelButton: true,
        confirmButtonColor: '#7048E8',
        cancelButtonColor: '#8392ab',
        confirmButtonText: '<i class="fa fa-copy"></i> Salin Password',
        cancelButtonText: 'Tutup'
      }).then((result) => {
        if (result.isConfirmed) {
          navigator.clipboard.writeText(tempPassword).then(function() {
            Swal.fire({
              title: 'Disalin!',
              text: 'Password telah disalin ke clipboard.',
              icon: 'success',
              timer: 1500,
              showConfirmButton: false
            });
          }).catch(function(err) {
            Swal.fire('Error', 'Gagal menyalin password ke clipboard.', 'error');
          });
        }
      });
    <?php endif; ?>
  });
</script>
<?= $this->endSection() ?>
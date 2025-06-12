<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>
<div class="container-fluid card  py-4">
  <div class="card-header pb-0 d-flex justify-content-between">
    <h4>Customers List</h4>
    <a href="<?= base_url('/customers/form') ?>" class="btn btn-primary mb-3">Add Customer</a>
  </div>
  <?php if (session()->getFlashdata('message')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('message') ?></div>
  <?php endif; ?>
  <div class="card-body px-0 pt-0 pb-2">
    <div class="table-responsive px-4">
      <table class="table align-items-center mb-0">
        <thead>
          <tr>
            <th>No</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Date of Birth</th>
            <th>Gender</th>
            <th>Occupation</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php $startIndex = ($pager["currentPage"] - 1) * $pager["limit"] + 1; ?>
          <?php foreach ($customers as $customer): ?>
            <tr>
              <td><?= $startIndex++ ?></td>
              <td><?= $customer['customer_name'] ?></td>
              <td><?= $customer['customer_email'] ?></td>
              <td><?= $customer['customer_phone'] ?></td>
              <td><?= date('d/m/Y', strtotime($customer['customer_dob'])) ?></td>
              <td><?= $customer['customer_gender'] ?></td>
              <td><?= $customer['customer_occupation'] ?></td>
              <td>
                <a href="<?= base_url('/customers/form?id=' . $customer['customer_id']) ?>" class="btn btn-sm btn-warning">Edit</a>
                <form action="<?= base_url('/customers/delete/' . $customer['customer_id']) ?>" method="post" style="display:inline-block;">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
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
  if (totalPages >= 1) {
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
</script>
<?= $this->endSection() ?>
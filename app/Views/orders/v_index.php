<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>
<div class="container-fluid card  py-4">
  <div class="card-header pb-0 d-flex justify-content-between">
    <h4>Order List</h4>
  </div>

  <div class="card-body px-0 pt-0 pb-2">
    <div class="table-responsive px-4">
      <table class="table align-items-center mb-0">
        <thead>
          <tr>
            <th>No</th>
            <th>Customer</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Order Date</th>
            <th>Total Price</th>
            <th>Proof of Payment</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php $startIndex = ($pager["currentPage"] - 1) * $pager["limit"] + 1; ?>

          <?php if (empty($orders)): ?>
            <tr>
              <td colspan="4" class="text-center text-muted">No user data available.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($orders as $order): ?>
              <tr>
                <td><?= $startIndex++ ?></td>
                <td><?= $order['customer_name'] ?></td>
                <td><?= $order['customer_email'] ?></td>
                <td><?= $order['customer_phone'] ?></td>
                <td><?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></td>
                <td><?= number_format($order['total_price'], 0, ',', '.') ?></td>
                <td>
                  <?php if (!empty($order['proof_of_payment'])): ?>
                    <img src="<?= base_url(esc($order['proof_of_payment'])) ?>" alt="image" width="70" height="70" style="border-radius: 15px">
                  <?php else: ?>
                    -
                  <?php endif; ?>
                </td>
                <td>
                  <?php
                  $status = $order['status'];
                  $badgeClass = match ($status) {
                    'cart' => 'secondary',
                    'pending' => 'warning',
                    'waiting_confirmation' => 'info',
                    'paid' => 'primary',
                    'shipped' => 'dark',
                    'done' => 'success',
                    'cancelled' => 'danger',
                    default => 'secondary',
                  };

                  $label = ucwords(str_replace('_', ' ', $status));
                  ?>
                  <span class="badge bg-<?= $badgeClass ?>"><?= $label ?></span>
                </td>
                <td>
                  <a href="<?= base_url('/orders/form?id=' . $order['order_id']) ?>" class="btn btn-sm btn-warning">Edit</a>
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
    window.location.replace(`<?php echo base_url(); ?>product-category?page=${pageNumber}`);
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
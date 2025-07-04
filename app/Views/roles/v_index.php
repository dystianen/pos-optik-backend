<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>
<div class="container-fluid card">
  <div class="card-header mb-4 pb-0 d-flex align-items-center justify-content-between">
    <h4>Roles List</h4>
    <a href="<?= base_url('/roles/form') ?>" class="btn btn-primary btn-sm">Add Role</a>
  </div>

  <div class="card-body px-0 pt-0 pb-2">
    <div class="table-responsive px-4">
      <table class="table align-items-center mb-0">
        <thead>
          <tr>
            <th>No</th>
            <th>Name</th>
            <th>Description</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php $startIndex = ($pager["currentPage"] - 1) * $pager["limit"] + 1; ?>

          <?php if (empty($roles)): ?>
            <tr>
              <td colspan="4" class="text-center text-muted">No user data available.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($roles as $role): ?>
              <tr>
                <td><?= $startIndex++ ?></td>
                <td><?= $role['role_name'] ?></td>
                <td><?= $role['role_description'] ?></td>
                <td>
                  <a href="<?= base_url('/roles/form?id=' . $role['role_id']) ?>" class="btn btn-sm btn-warning">Edit</a>
                  <form action="<?= base_url('/roles/delete/' . $role['role_id']) ?>" method="post" style="display:inline-block;">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>

      </table>
    </div>

    <nav aria-label="Page navigation example" class="mt-4 mx-4">
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
    window.location.replace(`<?php echo base_url(); ?>roles?page=${pageNumber}`);
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
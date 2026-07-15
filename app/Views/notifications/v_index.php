<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>

<?php
function notifTypeBadge($type)
{
  $map = [
    'new_order' => ['label' => 'New Order', 'class' => 'bg-primary'],
    'low_stock' => ['label' => 'Low Stock', 'class' => 'bg-warning text-dark'],
    'stock_empty' => ['label' => 'Empty Stock', 'class' => 'bg-danger text-white'],
    'payment'   => ['label' => 'Payment', 'class' => 'bg-success'],
    'system'    => ['label' => 'System', 'class' => 'bg-secondary'],
  ];

  $data = $map[$type] ?? ['label' => ucfirst($type), 'class' => 'bg-dark'];

  return '<span class="badge ' . $data['class'] . '">' . $data['label'] . '</span>';
}
?>

<div class="container-fluid card">
  <div class="card-header mb-4 pb-0 d-flex align-items-center justify-content-between">
    <h4>Notification List</h4>

    <button class="btn btn-sm btn-outline-primary"
      onclick="markAllRead()">
      Mark All as Read
    </button>
  </div>

  <div class="card-body pt-0 pb-2">
    <div class="table-responsive">
      <table class="table align-items-center mb-0 table-bordered">
        <thead>
          <tr>
            <th class="text-center">No</th>
            <th>Type</th>
            <th>Message</th>
            <th>Related ID</th>
            <th class="sticky-action text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php $startIndex = ($pager["currentPage"] - 1) * $pager["limit"] + 1; ?>

          <?php if (empty($data)): ?>
            <tr>
              <td colspan="4" class="text-center text-muted">No notification data available.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($data as $d): ?>
              <tr>
                <td class="text-center"><?= $startIndex++ ?></td>
                <td><?= notifTypeBadge($d['type']) ?></td>
                <td><?= $d['message'] ?></td>
                <td>
                  <?= !empty($d['related_id'])
                    ? '<strong>#' . esc($d['related_id']) . '</strong>'
                    : '-' ?>
                </td>
                <td class="sticky-action text-center">
                  <?php if ($d['is_read'] == 0): ?>
                    <button class="btn btn-sm btn-success"
                      onclick="markRead('<?= esc($d['notification_id']) ?>', this)">
                      Mark Read
                    </button>
                  <?php else: ?>
                    <span class="badge bg-secondary">Read</span>
                  <?php endif; ?>
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
    window.location.replace(`<?php echo base_url(); ?>notifications?page=${pageNumber}`);
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

  function markRead(id, btn) {
    fetch('/notifications/read/' + id, {
        method: 'POST'
      })
      .then(res => res.json())
      .then(res => {
        if (res.status) {
          btn.outerHTML = '<span class="badge bg-secondary">Read</span>';
        }
      });
  }

  function markAllRead() {
    Swal.fire({
      title: 'Mark All as Read?',
      text: 'Are you sure you want to mark all notifications as read?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#7048E8',
      cancelButtonColor: '#8392ab',
      confirmButtonText: 'Yes, Mark Read',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.isConfirmed) {
        fetch('/notifications/read-all', {
          method: 'POST'
        })
        .then(res => res.json())
        .then(res => {
          if (res.status) {
            location.reload();
          }
        });
      }
    });
  }
</script>
<?= $this->endSection() ?>
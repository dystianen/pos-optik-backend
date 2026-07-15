<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>
<div class="container-fluid card">
    <div class="card-header mb-4 pb-0 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
    <h4>Eye Examinations List</h4>
    <a href="<?= base_url('/eye-examinations/form') ?>"
      class="btn btn-primary btn-sm mb-0">
      <i class="fas fa-plus"></i> Add Examination
    </a>
  </div>

  <div class="card-body pt-0 pb-2">
    <!-- Filter Form -->
    <form action="<?= base_url('/eye-examinations') ?>" method="get" class="row g-2 mb-4 align-items-end">
      <div class="col-lg-3 col-md-6 col-12">
        <label class="form-label text-xs font-weight-bold">Search Inspection</label>
        <input
          type="text"
          name="search"
          class="form-control form-control-sm"
          placeholder="Search customer, symptoms, diagnosis..."
          value="<?= esc($search ?? '') ?>">
      </div>
      <div class="col-lg-3 col-md-6 col-6">
        <label class="form-label text-xs font-weight-bold">Start Date</label>
        <input
          type="date"
          name="start_date"
          class="form-control form-control-sm"
          value="<?= esc($startDate ?? '') ?>">
      </div>
      <div class="col-lg-3 col-md-6 col-6">
        <label class="form-label text-xs font-weight-bold">End Date</label>
        <input
          type="date"
          name="end_date"
          class="form-control form-control-sm"
          value="<?= esc($endDate ?? '') ?>">
      </div>
      <div class="col-lg-3 col-md-12 col-12 d-flex gap-2 mt-lg-0 mt-3">
        <button type="submit" class="btn btn-sm btn-primary mb-0 d-flex align-items-center justify-content-center gap-1" style="height: 31px;" title="Filter">
          <i class="fa-solid fa-filter"></i> <span>Filter</span>
        </button>
        <a href="<?= base_url('/eye-examinations') ?>" class="btn btn-sm btn-outline-secondary mb-0 d-flex align-items-center justify-content-center gap-1" style="height: 31px;" title="Reset">
          <i class="fa-solid fa-arrows-rotate"></i> <span>Reset</span>
        </a>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table align-items-center mb-0 table-bordered">
        <thead>
          <tr>
            <th class="text-center">No</th>
            <th>Customer</th>
            <th>Inspection Date</th>
            <th>Symptoms</th>
            <th>Diagnosis</th>
            <th class="sticky-action text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php $startIndex = ($pager["currentPage"] - 1) * $pager["limit"] + 1; ?>
          <?php if (empty($eyeExaminations)): ?>
            <tr>
              <td colspan="6" class="text-center text-muted">No eye examination data available.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($eyeExaminations as $eyeExamination): ?>
              <tr>
                <td class="text-center"><?= $startIndex++ ?></td>
                <td><?= $eyeExamination['customer_name'] ?></td>
                <td><?= date('d/m/Y H:i', strtotime($eyeExamination['created_at'])) ?></td>
                <td><?= esc($eyeExamination['symptoms']) ?></td>
                <td><?= esc($eyeExamination['diagnosis']) ?></td>
                <td class="sticky-action text-center">
                  <a href="<?= base_url('/eye-examinations/form?id=' . $eyeExamination['eye_examination_id']) ?>" class="btn btn-sm btn-warning"> <i class="fa-solid fa-pen-to-square"></i></a>
                  <form action="<?= base_url('/eye-examinations/delete/' . $eyeExamination['eye_examination_id']) ?>" method="post" style="display:inline-block;" class="confirm-delete">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-danger"> <i class="fa-solid fa-trash"></i></button>
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
    const params = new URLSearchParams(window.location.search);
    params.set('page', pageNumber);
    window.location.replace(`<?php echo base_url(); ?>eye-examinations?${params.toString()}`);
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
</script>
<?= $this->endSection() ?>
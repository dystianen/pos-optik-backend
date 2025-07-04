<?= $this->extend('layouts/l_dashboard.php') ?>
<?= $this->section('content') ?>
<div class="container-fluid card  py-4">
  <div class="card-header pb-0 d-flex justify-content-between">
    <h4>Eye Examinations</h4>
    <a href="<?= base_url('/eye-examinations/form') ?>" class="btn btn-primary mb-3">Add Examinations</a>
  </div>

  <div class="card-body px-0 pt-0 pb-2">
    <div class="table-responsive px-4">
      <table class="table align-items-center mb-0">
        <thead>
          <tr>
            <th>No</th>
            <th>Customer</th>
            <th>Left Eye Axis</th>
            <th>Left Eye Sphere</th>
            <th>Left Eye Cylinder</th>
            <th>Right Eye Axis</th>
            <th>Right Eye Sphere</th>
            <th>Right Eye Cylinder</th>
            <th>Symptomps</th>
            <th>Diagnosis</th>
            <th>Created Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php $startIndex = ($pager["currentPage"] - 1) * $pager["limit"] + 1; ?>
          <?php if (empty($eyeExaminations)): ?>
            <tr>
              <td colspan="11" class="text-center text-muted">No eye examination data available.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($eyeExaminations as $eyeExamination): ?>
              <tr>
                <td><?= $startIndex++ ?></td>
                <td><?= $eyeExamination['customer_name'] ?></td>
                <td><?= $eyeExamination['left_eye_axis'] ?></td>
                <td><?= $eyeExamination['left_eye_sphere'] ?></td>
                <td><?= $eyeExamination['left_eye_cylinder'] ?></td>
                <td><?= $eyeExamination['right_eye_axis'] ?></td>
                <td><?= $eyeExamination['right_eye_sphere'] ?></td>
                <td><?= $eyeExamination['right_eye_cylinder'] ?></td>
                <td><?= esc($eyeExamination['symptoms']) ?></td>
                <td><?= esc($eyeExamination['diagnosis']) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($eyeExamination['created_at'])) ?></td>
                <td>
                  <a href="<?= base_url('/eye-examinations/form?id=' . $eyeExamination['eye_examination_id']) ?>" class="btn btn-sm btn-warning">Edit</a>
                  <form action="<?= base_url('/eye-examinations/delete/' . $eyeExamination['eye_examination_id']) ?>" method="post" style="display:inline-block;">
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
    window.location.replace(`<?php echo base_url(); ?>eye-examinations?page=${pageNumber}`);
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
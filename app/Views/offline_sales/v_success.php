<?= $this->extend('layouts/l_dashboard') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
  <div class="alert alert-success">
    ✅ Transaksi berhasil disimpan
  </div>
</div>

<div class="modal fade show" style="display:block;background:rgba(0,0,0,.5)">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Cetak Struk</h5>
      </div>

      <div class="modal-body text-center">
        <p>Apakah Anda ingin mencetak struk transaksi?</p>
      </div>

      <div class="modal-footer">
        <a href="<?= site_url('offline-sales') ?>"
          class="btn btn-secondary">
          Tidak
        </a>

        <a href="<?= site_url('offline-sales/print/' . $order_id) ?>"
          target="_blank"
          class="btn btn-primary">
          🖨️ Cetak Struk
        </a>
      </div>

    </div>
  </div>
</div>

<?= $this->endSection() ?>
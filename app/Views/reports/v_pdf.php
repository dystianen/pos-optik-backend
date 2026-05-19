<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Laporan Penjualan Optikers</title>
  <style>
    body {
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      color: #333;
      margin: 0;
      padding: 0;
      font-size: 11px;
    }
    .header {
      margin-bottom: 20px;
      border-bottom: 2px solid #2FB8AA;
      padding-bottom: 10px;
    }
    .header h2 {
      margin: 0 0 5px 0;
      color: #2FB8AA;
      font-size: 20px;
      text-transform: uppercase;
      font-weight: bold;
    }
    .header p {
      margin: 2px 0;
      color: #666;
    }
    .info-table {
      width: 100%;
      margin-bottom: 20px;
    }
    .info-table td {
      padding: 3px 0;
    }
    .summary-grid {
      width: 100%;
      margin-bottom: 25px;
      border-collapse: collapse;
    }
    .summary-card {
      background-color: #f8f9fa;
      border: 1px solid #dee2e6;
      border-radius: 6px;
      padding: 10px;
      text-align: center;
      width: 23%;
    }
    .summary-card .title {
      font-size: 9px;
      text-transform: uppercase;
      color: #6c757d;
      margin-bottom: 5px;
      font-weight: bold;
    }
    .summary-card .value {
      font-size: 14px;
      font-weight: bold;
      color: #212529;
    }
    .data-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    .data-table th {
      background-color: #2FB8AA;
      color: #ffffff;
      text-align: left;
      padding: 8px;
      font-weight: bold;
      font-size: 10px;
      text-transform: uppercase;
    }
    .data-table td {
      padding: 7px 8px;
      border-bottom: 1px solid #dee2e6;
      font-size: 10px;
    }
    .data-table tr:nth-child(even) {
      background-color: #f8f9fa;
    }
    .text-center {
      text-align: center;
    }
    .text-right {
      text-align: right;
    }
    .badge {
      padding: 2px 6px;
      border-radius: 4px;
      font-size: 9px;
      font-weight: bold;
      text-transform: uppercase;
    }
    .badge-online {
      background-color: #e8f4fd;
      color: #0b69a3;
    }
    .badge-offline {
      background-color: #e9ecef;
      color: #495057;
    }
    .badge-refund {
      background-color: #fff3cd;
      color: #856404;
    }
    .badge-cancellation {
      background-color: #f8d7da;
      color: #721c24;
    }
    .footer {
      position: fixed;
      bottom: 0;
      width: 100%;
      text-align: center;
      color: #999;
      font-size: 8px;
      border-top: 1px solid #eee;
      padding-top: 5px;
    }
  </style>
</head>
<body>

  <!-- HEADER -->
  <div class="header">
    <table style="width: 100%;">
      <tr>
        <td>
          <h2>OPTIKERS Sales Report</h2>
          <p>Category: <strong><?= $categoryText ?></strong> | Period: <strong><?= $startDate ?> to <?= $endDate ?></strong></p>
        </td>
        <td class="text-right" style="vertical-align: bottom;">
          <p>Downloaded at: <?= date('d M Y H:i') ?></p>
        </td>
      </tr>
    </table>
  </div>

  <!-- KPI SUMMARY -->
  <table style="width: 100%; margin-bottom: 20px;">
    <tr>
      <td class="summary-card">
        <div class="title"><?= $category === 'refund' ? 'Total Refund' : ($category === 'cancellation' ? 'Total Cancelled' : 'Total Revenue') ?></div>
        <div class="value" style="color: #2FB8AA;">Rp <?= number_format($summary['total_revenue'], 0, ',', '.') ?></div>
      </td>
      <td style="width: 2%;"></td>
      <td class="summary-card">
        <div class="title"><?= $category === 'refund' ? 'Total Returns' : ($category === 'cancellation' ? 'Total Cancellations' : 'Total Transactions') ?></div>
        <div class="value" style="color: #2dce89;"><?= number_format($summary['total_transactions'], 0, ',', '.') ?></div>
      </td>
      <td style="width: 2%;"></td>
      <td class="summary-card">
        <div class="title"><?= $category === 'refund' ? 'Refunded Items' : ($category === 'cancellation' ? 'Cancelled Items' : 'Items Sold') ?></div>
        <div class="value" style="color: #11cdef;"><?= number_format($summary['total_items'], 0, ',', '.') ?></div>
      </td>
      <td style="width: 2%;"></td>
      <td class="summary-card">
        <div class="title">Average Transaction</div>
        <div class="value" style="color: #fb6340;">Rp <?= number_format($summary['average_value'], 0, ',', '.') ?></div>
      </td>
    </tr>
  </table>

  <!-- TRANSACTION DETAIL TABLE -->
  <h3 style="margin: 0 0 10px 0; color: #333; text-transform: uppercase; font-size: 12px;">Sales Transaction Details</h3>
  <table class="data-table">
    <thead>
      <tr>
        <th class="text-center" style="width: 30px;">No</th>
        <th style="width: 80px;">Transaction ID</th>
        <th style="width: 100px;">Date</th>
        <th style="width: 70px;">Category</th>
        <th>Customer</th>
        <th>Email</th>
        <th class="text-center" style="width: 70px;">Total Items</th>
        <th class="text-right" style="width: 110px;">Grand Total</th>
        <th class="text-center" style="width: 80px;">Status</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($orders)): ?>
        <tr>
          <td colspan="9" class="text-center" style="padding: 20px; font-weight: bold; color: #666;">
            No transaction data found.
          </td>
        </tr>
      <?php else: ?>
        <?php $no = 1; foreach ($orders as $order): ?>
          <tr>
            <td class="text-center"><?= $no++ ?></td>
            <td><strong>#<?= $order['order_id'] ?></strong></td>
            <td><?= date('d M Y H:i', strtotime($order['created_at'])) ?></td>
            <td>
              <span class="badge badge-<?= $order['order_type'] ?>">
                <?= $order['order_type'] ?>
              </span>
            </td>
            <td><?= esc($order['customer_name'] ?? '-') ?></td>
            <td><?= esc($order['customer_email'] ?? '-') ?></td>
            <td class="text-center"><?= $order['total_items'] ?></td>
            <td class="text-right"><strong>Rp <?= number_format($order['grand_total'], 0, ',', '.') ?></strong></td>
            <td class="text-center">
              <?php
              $statusBg = '#e2e3e5';
              $statusColor = '#383d41';
              $lowerStatus = strtolower($order['status_name'] ?? '');
              if (in_array($lowerStatus, ['approved', 'refunded', 'completed'])) {
                $statusBg = '#d4edda';
                $statusColor = '#155724';
              } elseif (in_array($lowerStatus, ['pending', 'requested', 'processing', 'return_approved', 'return_shipped', 'return_received'])) {
                $statusBg = '#fff3cd';
                $statusColor = '#856404';
              } elseif (in_array($lowerStatus, ['rejected', 'cancelled', 'request_rejected', 'return_rejected', 'payment expired', 'expired'])) {
                $statusBg = '#f8d7da';
                $statusColor = '#721c24';
              }
              ?>
              <span class="badge" style="background-color: <?= $statusBg ?>; color: <?= $statusColor ?>;">
                <?= esc($order['status_name'] ?? 'Completed') ?>
              </span>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- FOOTER -->
  <div class="footer">
    Optikers Sales Report - This document is automatically generated by the system. Page 1 of 1
  </div>

</body>
</html>

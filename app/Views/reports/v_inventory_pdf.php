<?php

/**
 * @var string $transactionType
 * @var string $startDate
 * @var string $endDate
 * @var array $summary
 * @var array $transactions
 * @var string $typeText
 */
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>OPTIKERS Inventory Report</title>
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

    .badge-in {
      background-color: #d4edda;
      color: #155724;
    }

    .badge-out {
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
          <h2>OPTIKERS Inventory Report</h2>
          <p>Transaction Type: <strong><?= $typeText ?></strong> | Period: <strong><?= $startDate ?> to <?= $endDate ?></strong></p>
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
        <div class="title">Total Transactions</div>
        <div class="value"><?= number_format($summary['total_transactions'], 0, ',', '.') ?></div>
      </td>
      <td style="width: 2%;"></td>
      <td class="summary-card">
        <div class="title">Total IN Quantity</div>
        <div class="value" style="color: #2dce89;"><?= number_format($summary['total_in'], 0, ',', '.') ?></div>
      </td>
      <td style="width: 2%;"></td>
      <td class="summary-card">
        <div class="title">Total OUT Quantity</div>
        <div class="value" style="color: #11cdef;"><?= number_format($summary['total_out'], 0, ',', '.') ?></div>
      </td>
      <td style="width: 2%;"></td>
      <td class="summary-card">
        <div class="title">Net Quantity</div>
        <div class="value" style="color: #fb6340;"><?= number_format($summary['net_quantity'], 0, ',', '.') ?></div>
      </td>
    </tr>
  </table>

  <!-- TRANSACTION DETAIL TABLE -->
  <h3 style="margin: 0 0 10px 0; color: #333; text-transform: uppercase; font-size: 12px;">Inventory Transaction Details</h3>
  <table class="data-table">
    <thead>
      <tr>
        <th class="text-center">No</th>
        <th>Date</th>
        <th>Type</th>
        <th>Reference</th>
        <th>Product</th>
        <th>Variant</th>
        <th>User</th>
        <th>Quantity</th>
        <th>Description</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($transactions)): ?>
        <tr>
          <td colspan="9" class="text-center" style="padding: 20px; font-weight: bold; color: #666;">No inventory transaction data found.</td>
        </tr>
      <?php else: ?>
        <?php 
        $refLabels = [
          'order'      => 'Order',
          'adjustment' => 'Adjustment',
          'return'     => 'Return',
          'transfer'   => 'Transfer',
          'initial'    => 'Initial Stock',
        ];
        $no = 1;
        foreach ($transactions as $transaction): ?>
          <tr>
            <td class="text-center"><?= $no++ ?></td>
            <td><?= date('d M Y H:i', strtotime($transaction['transaction_date'])) ?></td>
            <td>
              <span class="badge <?= strtolower($transaction['transaction_type']) === 'in' ? 'badge-in' : 'badge-out' ?>">
                <?= esc(strtoupper($transaction['transaction_type'])) ?>
              </span>
            </td>
            <td>
              <?php
              $refType  = strtolower($transaction['reference_type'] ?? '');
              $refLabel = $refLabels[$refType] ?? strtoupper($refType ?: 'N/A');
              ?>
              <?= esc($refLabel) ?> <?= !empty($transaction['reference_id']) ? '/ #' . esc($transaction['reference_id']) : '' ?>
            </td>
            <td><?= esc($transaction['product_name'] ?? '-') ?></td>
            <td><?= esc($transaction['variant_name'] ?? '-') ?></td>
            <td><?= esc($transaction['user_name'] ?? 'System') ?></td>
            <td class="text-center" style="font-weight: bold; color: <?= strtolower($transaction['transaction_type']) === 'in' ? '#2dce89' : '#dc3545' ?>;">
              <?= strtolower($transaction['transaction_type']) === 'in' ? '+' : '-' ?><?= esc($transaction['quantity']) ?>
            </td>
            <td><?= esc($transaction['description'] ?? '-') ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- FOOTER -->
  <div class="footer">
    Optikers Inventory Report - This document is automatically generated by the system. Page 1 of 1
  </div>

</body>

</html>
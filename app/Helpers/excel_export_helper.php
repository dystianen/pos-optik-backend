<?php

use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Entity\Row;

if (!function_exists('createTransactionSheetSpout')) {
  function createTransactionSheetSpout($writer, $orders, $sheetName = 'Laporan Transaksi')
  {
    $sheet = $writer->getCurrentSheet();
    $sheet->setName($sheetName);

    // Header Style
    $headerStyle = (new Style())
      ->setFontBold()
      ->setFontSize(12)
      ->setBackgroundColor(Color::rgb(68, 114, 196))
      ->setFontColor(Color::WHITE);

    // Header Row
    $headers = ['No', 'Order ID', 'Tanggal', 'Jam', 'Customer', 'Email', 'Total Item', 'Grand Total', 'Status'];
    $headerCells = Row::fromValues($headers, $headerStyle);
    $writer->addRow($headerCells);

    // ✅ SET COLUMN WIDTH (dalam karakter)
    // Sayangnya Spout tidak support setWidth, jadi kita buat workaround dengan padding

    // Data Rows dengan padding untuk mengatur lebar
    $no = 1;
    foreach ($orders as $order) {
      $date = date('d M Y', strtotime($order['created_at']));
      $time = date('H:i', strtotime($order['created_at']));

      $rowData = [
        $no++,                                              // No (5)
        $order['order_id'],                                 // Order ID (40)
        $date,                                              // Tanggal (15)
        $time,                                              // Jam (8)
        $order['customer_name'],                            // Customer (25)
        $order['customer_email'],                           // Email (30)
        (int)$order['total_items'],                         // Total Item (10)
        'Rp ' . number_format($order['grand_total'], 0, ',', '.'), // Grand Total (18)
        $order['status_name']                               // Status (15)
      ];

      $row = Row::fromValues($rowData);
      $writer->addRow($row);
    }
  }
}

if (!function_exists('createSummarySheetSpout')) {
  function createSummarySheetSpout($writer, $orders, $title = 'RINGKASAN PENJUALAN IN-STORE')
  {
    $boldStyle = (new Style())->setFontBold()->setFontSize(12);
    $headerStyle = (new Style())
      ->setFontBold()
      ->setBackgroundColor(Color::rgb(217, 225, 242));

    // Title
    $titleRow = Row::fromValues([$title], $boldStyle);
    $writer->addRow($titleRow);
    $writer->addRow(Row::fromValues(['']));

    // Calculate summaries
    $totalTransaksi = count($orders);
    $totalRevenue = array_sum(array_column($orders, 'grand_total'));
    $totalItems = array_sum(array_column($orders, 'total_items'));
    $avgTransaction = $totalTransaksi > 0 ? $totalRevenue / $totalTransaksi : 0;

    // Summary Header
    $summaryHeader = Row::fromValues(['Metrik', 'Nilai'], $headerStyle);
    $writer->addRow($summaryHeader);

    // Summary data
    $summaryData = [
      ['Total Transaksi', number_format($totalTransaksi, 0, ',', '.')],
      ['Total Revenue', 'Rp ' . number_format($totalRevenue, 0, ',', '.')],
      ['Total Item Terjual', number_format($totalItems, 0, ',', '.')],
      ['Rata-rata Transaksi', 'Rp ' . number_format($avgTransaction, 0, ',', '.')],
    ];

    foreach ($summaryData as $data) {
      $row = Row::fromValues($data);
      $writer->addRow($row);
    }

    // Penjualan per Hari
    $writer->addRow(Row::fromValues(['']));
    $writer->addRow(Row::fromValues(['PENJUALAN PER HARI'], $boldStyle));

    $salesByDate = [];
    foreach ($orders as $order) {
      $date = date('Y-m-d', strtotime($order['created_at']));
      if (!isset($salesByDate[$date])) {
        $salesByDate[$date] = ['count' => 0, 'total' => 0];
      }
      $salesByDate[$date]['count']++;
      $salesByDate[$date]['total'] += $order['grand_total'];
    }

    $dateHeader = Row::fromValues(
      ['Tanggal', 'Jumlah Transaksi', 'Total Penjualan'],
      $headerStyle
    );
    $writer->addRow($dateHeader);

    foreach ($salesByDate as $date => $data) {
      $rowData = [
        date('d M Y', strtotime($date)),
        number_format($data['count'], 0, ',', '.'),
        'Rp ' . number_format($data['total'], 0, ',', '.')
      ];
      $writer->addRow(Row::fromValues($rowData));
    }
  }
}

if (!function_exists('createTopCustomersSheetSpout')) {
  function createTopCustomersSheetSpout($writer, $orders, $limit = 20)
  {
    $boldStyle = (new Style())->setFontBold()->setFontSize(12);
    $headerStyle = (new Style())
      ->setFontBold()
      ->setBackgroundColor(Color::rgb(112, 173, 71))
      ->setFontColor(Color::WHITE);

    // Title
    $title = Row::fromValues(["TOP {$limit} CUSTOMERS"], $boldStyle);
    $writer->addRow($title);
    $writer->addRow(Row::fromValues(['']));

    // Group by customer
    $customerSales = [];
    foreach ($orders as $order) {
      $email = $order['customer_email'];
      if (!isset($customerSales[$email])) {
        $customerSales[$email] = [
          'name' => $order['customer_name'],
          'email' => $email,
          'count' => 0,
          'total' => 0,
        ];
      }
      $customerSales[$email]['count']++;
      $customerSales[$email]['total'] += $order['grand_total'];
    }

    // Sort and limit
    usort($customerSales, function ($a, $b) {
      return $b['total'] - $a['total'];
    });
    $customerSales = array_slice($customerSales, 0, $limit);

    // Header
    $headers = ['Rank', 'Customer Name', 'Email', 'Total Transaksi', 'Total Pembelian'];
    $writer->addRow(Row::fromValues($headers, $headerStyle));

    // Data
    $rank = 1;
    foreach ($customerSales as $customer) {
      $rowData = [
        $rank++,
        $customer['name'],
        $customer['email'],
        number_format($customer['count'], 0, ',', '.'),
        'Rp ' . number_format($customer['total'], 0, ',', '.')
      ];
      $writer->addRow(Row::fromValues($rowData));
    }
  }
}

if (!function_exists('exportSalesExcelSpout')) {
  function exportSalesExcelSpout($orders, $filename, $type = 'in-store')
  {
    $filePath = WRITEPATH . 'uploads/' . $filename;

    if (!is_dir(WRITEPATH . 'uploads/')) {
      mkdir(WRITEPATH . 'uploads/', 0777, true);
    }

    $writer = new Writer();
    $writer->openToFile($filePath);

    $title = $type === 'in-store' ? 'RINGKASAN PENJUALAN IN-STORE' : 'RINGKASAN PENJUALAN ONLINE';
    $sheetTitle = $type === 'in-store' ? 'Laporan Transaksi' : 'Laporan Online';

    createTransactionSheetSpout($writer, $orders, $sheetTitle);

    $writer->addNewSheetAndMakeItCurrent();
    $writer->getCurrentSheet()->setName('Ringkasan Penjualan');
    createSummarySheetSpout($writer, $orders, $title);

    $writer->addNewSheetAndMakeItCurrent();
    $writer->getCurrentSheet()->setName('Top Customers');
    createTopCustomersSheetSpout($writer, $orders, 20);

    $writer->close();

    return service('response')->download($filePath, null)->setFileName($filename);
  }
}

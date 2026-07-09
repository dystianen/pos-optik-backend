<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Dompdf\Dompdf;
use Dompdf\Options;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;

class ReportController extends BaseController
{
    protected $orderModel;
    protected $db;

    public function __construct()
    {
        $this->orderModel = new \App\Models\OrderModel();
        $this->db         = \Config\Database::connect();
    }

    public function index()
    {
        $category  = $this->request->getVar('category') ?? 'all';
        $startDate = $this->request->getVar('start_date') ?? date('Y-m-01');
        $endDate   = $this->request->getVar('end_date') ?? date('Y-m-d');

        $allOrders = $this->getFilteredOrders($category, $startDate, $endDate);
        $summary = $this->calculateSummary($allOrders, $category);

        $currentPage = $this->request->getVar('page') ? max(1, (int)$this->request->getVar('page')) : 1;
        $limit = 10;
        $offset = ($currentPage - 1) * $limit;
        $totalRows = count($allOrders);
        $totalPages = (int) ceil($totalRows / $limit);

        $orders = array_slice($allOrders, $offset, $limit);

        return view('reports/v_sales', [
            'orders'    => $orders,
            'category'  => $category,
            'startDate' => $startDate,
            'endDate'   => $endDate,
            'summary'   => $summary,
            'pager' => [
                'totalPages'  => $totalPages,
                'currentPage' => $currentPage,
                'limit'       => $limit,
            ],
        ]);
    }

    public function export()
    {
        $category  = $this->request->getVar('category') ?? 'all';
        $startDate = $this->request->getVar('start_date') ?? date('Y-m-01');
        $endDate   = $this->request->getVar('end_date') ?? date('Y-m-d');
        $format    = $this->request->getVar('format') ?? 'excel';

        $orders  = $this->getFilteredOrders($category, $startDate, $endDate);
        $summary = $this->calculateSummary($orders, $category);

        $categoryText = $this->getCategoryText($category);
        $filename = 'Sales_Report_' . str_replace(' ', '_', $categoryText) . '_' . str_replace('-', '', $startDate) . '_' . str_replace('-', '', $endDate);

        if ($format === 'pdf') {
            return $this->exportToPdf($orders, $categoryText, $startDate, $endDate, $summary, $filename . '.pdf', $category);
        } else {
            return $this->exportToExcel($orders, $categoryText, $startDate, $endDate, $summary, $filename . '.xlsx', $category);
        }
    }

    public function inventory()
    {
        $transactionType = $this->request->getVar('transaction_type') ?? 'all';
        $startDate       = $this->request->getVar('start_date') ?? date('Y-m-01');
        $endDate         = $this->request->getVar('end_date') ?? date('Y-m-d');

        $allTransactions = $this->getFilteredInventory($transactionType, $startDate, $endDate);
        $summary      = $this->calculateInventorySummary($allTransactions);

        $currentPage = $this->request->getVar('page') ? max(1, (int)$this->request->getVar('page')) : 1;
        $limit = 10;
        $offset = ($currentPage - 1) * $limit;
        $totalRows = count($allTransactions);
        $totalPages = (int) ceil($totalRows / $limit);

        $transactions = array_slice($allTransactions, $offset, $limit);

        return view('reports/v_inventory', [
            'transactions'    => $transactions,
            'transactionType' => $transactionType,
            'startDate'       => $startDate,
            'endDate'         => $endDate,
            'summary'         => $summary,
            'pager' => [
                'totalPages'  => $totalPages,
                'currentPage' => $currentPage,
                'limit'       => $limit,
            ],
        ]);
    }

    public function exportInventory()
    {
        $transactionType = $this->request->getVar('transaction_type') ?? 'all';
        $startDate       = $this->request->getVar('start_date') ?? date('Y-m-01');
        $endDate         = $this->request->getVar('end_date') ?? date('Y-m-d');
        $format          = $this->request->getVar('format') ?? 'excel';

        $transactions = $this->getFilteredInventory($transactionType, $startDate, $endDate);
        $summary      = $this->calculateInventorySummary($transactions);
        $typeText     = $this->getInventoryTypeText($transactionType);
        $filename     = 'Inventory_Report_' . str_replace(' ', '_', $typeText) . '_' . str_replace('-', '', $startDate) . '_' . str_replace('-', '', $endDate);

        if ($format === 'pdf') {
            return $this->exportInventoryPdf($transactions, $typeText, $startDate, $endDate, $summary, $filename . '.pdf', $transactionType);
        }

        return $this->exportInventoryExcel($transactions, $typeText, $startDate, $endDate, $summary, $filename . '.xlsx', $transactionType);
    }

    private function getInventoryTypeText($transactionType)
    {
        return match ($transactionType) {
            'in' => 'Inbound',
            'out' => 'Outbound',
            default => 'All_Transactions',
        };
    }

    private function getFilteredInventory($transactionType, $startDate, $endDate)
    {
        $builder = $this->db->table('inventory_transactions')
            ->select('inventory_transactions.*, products.product_name, product_variants.variant_name, users.user_name')
            ->join('products', 'products.product_id = inventory_transactions.product_id', 'left')
            ->join('product_variants', 'product_variants.variant_id = inventory_transactions.variant_id', 'left')
            ->join('users', 'users.user_id = inventory_transactions.user_id', 'left')
            ->orderBy('inventory_transactions.transaction_date', 'DESC');

        if ($transactionType === 'in' || $transactionType === 'out') {
            $builder->where('inventory_transactions.transaction_type', $transactionType);
        }

        if (!empty($startDate)) {
            $builder->where('DATE(inventory_transactions.transaction_date) >=', $startDate);
        }

        if (!empty($endDate)) {
            $builder->where('DATE(inventory_transactions.transaction_date) <=', $endDate);
        }

        return $builder->get()->getResultArray();
    }

    private function calculateInventorySummary(array $transactions)
    {
        $totalTransactions = count($transactions);
        $totalIn           = 0;
        $totalOut          = 0;
        $totalQuantity     = 0;

        foreach ($transactions as $transaction) {
            if (strtolower($transaction['transaction_type']) === 'in') {
                $totalIn += (int) $transaction['quantity'];
            } else {
                $totalOut += (int) $transaction['quantity'];
            }

            $totalQuantity += (int) $transaction['quantity'];
        }

        return [
            'total_transactions' => $totalTransactions,
            'total_in'           => $totalIn,
            'total_out'          => $totalOut,
            'net_quantity'       => $totalIn - $totalOut,
            'average_quantity'   => $totalTransactions > 0 ? $totalQuantity / $totalTransactions : 0,
        ];
    }

    private function exportInventoryPdf($transactions, $typeText, $startDate, $endDate, $summary, $filename, $transactionType)
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);

        $dompdf = new Dompdf($options);

        $html = view('reports/v_inventory_pdf', [
            'transactions'    => $transactions,
            'typeText'        => $typeText,
            'transactionType' => $transactionType,
            'startDate'       => date('d M Y', strtotime($startDate)),
            'endDate'         => date('d M Y', strtotime($endDate)),
            'summary'         => $summary,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    private function exportInventoryExcel($transactions, $typeText, $startDate, $endDate, $summary, $filename, $transactionType)
    {
        $filePath = WRITEPATH . 'uploads/' . $filename;

        if (!is_dir(WRITEPATH . 'uploads/')) {
            mkdir(WRITEPATH . 'uploads/', 0777, true);
        }

        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToFile($filePath);

        $titleStyle = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(16)
            ->setFontColor(Color::rgb(33, 37, 41))
            ->build();

        $headerStyle = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(11)
            ->setBackgroundColor(Color::rgb(47, 184, 170))
            ->setFontColor(Color::WHITE)
            ->build();

        $subHeaderStyle = (new StyleBuilder())
            ->setFontBold()
            ->setBackgroundColor(Color::rgb(241, 243, 245))
            ->build();

        $sheet = $writer->getCurrentSheet();
        $sheet->setName('Inventory Summary');

        $writer->addRow(WriterEntityFactory::createRowFromArray(['OPTIKERS INVENTORY REPORT'], $titleStyle));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['Transaction Type:', $typeText]));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['Period:', date('d M Y', strtotime($startDate)) . ' to ' . date('d M Y', strtotime($endDate))]));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['Downloaded At:', date('d M Y H:i')]));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['']));

        $writer->addRow(WriterEntityFactory::createRowFromArray(['Summary Metrics', 'Value'], $subHeaderStyle));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['Total Transactions', number_format($summary['total_transactions'], 0, ',', '.')]));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['Total In Quantity', number_format($summary['total_in'], 0, ',', '.')]));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['Total Out Quantity', number_format($summary['total_out'], 0, ',', '.')]));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['Net Quantity', number_format($summary['net_quantity'], 0, ',', '.')]));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['Average Quantity per Transaction', number_format($summary['average_quantity'], 2, ',', '.')]));

        $writer->addNewSheetAndMakeItCurrent();
        $writer->getCurrentSheet()->setName('Transaction Details');

        $headers = ['No', 'Transaction ID', 'Date', 'Transaction Type', 'Reference Type', 'Reference ID', 'Product', 'Variant', 'User', 'Quantity', 'Description'];
        $writer->addRow(WriterEntityFactory::createRowFromArray($headers, $headerStyle));

        $refLabels = [
            'order'      => 'ORDER',
            'adjustment' => 'ADJUSTMENT',
            'return'     => 'RETURN',
            'transfer'   => 'TRANSFER',
            'initial'    => 'INITIAL STOCK',
        ];

        $no = 1;
        foreach ($transactions as $transaction) {
            $refType  = strtolower($transaction['reference_type'] ?? '');
            $refLabel = $refLabels[$refType] ?? strtoupper($refType ?: 'N/A');
            $qty = (int) $transaction['quantity'];
            if (strtolower($transaction['transaction_type']) !== 'in') {
                $qty = -$qty;
            }

            $rowData = [
                $no++,
                '#' . $transaction['inventory_transaction_id'],
                date('d M Y H:i', strtotime($transaction['transaction_date'])),
                strtolower($transaction['transaction_type']) === 'in' ? 'IN' : 'OUT',
                $refLabel,
                $transaction['reference_id'] ?? '-',
                $transaction['product_name'] ?? '-',
                $transaction['variant_name'] ?? '-',
                $transaction['user_name'] ?? 'System',
                $qty,
                $transaction['description'] ?? '-',
            ];
            $writer->addRow(WriterEntityFactory::createRowFromArray($rowData));
        }

        $writer->close();

        return $this->response->download($filePath, null)->setFileName($filename);
    }

    private function getCategoryText($category)
    {
        return match ($category) {
            'online'       => 'Online Sales',
            'offline'      => 'Offline Sales',
            'refund'       => 'Refund Sales',
            'cancellation' => 'Cancellation Sales',
            default        => 'All Sales',
        };
    }

    private function getFilteredOrders($category, $startDate, $endDate)
    {
        if ($category === 'refund') {
            $refundModel = new \App\Models\OrderRefundModel();
            $builder = $refundModel
                ->select('
                    order_refunds.order_refund_id as order_id,
                    order_refunds.created_at,
                    order_refunds.refund_amount as grand_total,
                    "refund" as order_type,
                    customers.customer_name,
                    customers.customer_email,
                    order_refunds.status as status_name,
                    COUNT(order_items.order_item_id) as total_items
                ')
                ->join('orders', 'orders.order_id = order_refunds.order_id', 'left')
                ->join('customers', 'customers.customer_id = orders.customer_id', 'left')
                ->join('order_items', 'order_items.order_id = orders.order_id', 'left')
                ->groupBy('order_refunds.order_refund_id')
                ->orderBy('order_refunds.created_at', 'DESC');
        } elseif ($category === 'cancellation') {
            $cancelModel = new \App\Models\OrderCancellationModel();
            $builder = $cancelModel
                ->select('
                    order_cancellations.order_cancellation_id as order_id,
                    order_cancellations.created_at,
                    orders.grand_total,
                    "cancellation" as order_type,
                    customers.customer_name,
                    customers.customer_email,
                    order_cancellations.status as status_name,
                    COUNT(order_items.order_item_id) as total_items
                ')
                ->join('orders', 'orders.order_id = order_cancellations.order_id', 'left')
                ->join('customers', 'customers.customer_id = orders.customer_id', 'left')
                ->join('order_items', 'order_items.order_id = orders.order_id', 'left')
                ->groupBy('order_cancellations.order_cancellation_id')
                ->orderBy('order_cancellations.created_at', 'DESC');
        } else {
            $builder = $this->orderModel
                ->select('
                    orders.order_id,
                    orders.created_at,
                    orders.grand_total,
                    orders.order_type,
                    customers.customer_name,
                    customers.customer_email,
                    order_statuses.status_name,
                    COUNT(order_items.order_item_id) as total_items
                ')
                ->join('customers', 'customers.customer_id = orders.customer_id', 'left')
                ->join('order_statuses', 'order_statuses.status_id = orders.status_id', 'left')
                ->join('order_items', 'order_items.order_id = orders.order_id', 'left')
                ->groupBy('orders.order_id')
                ->orderBy('orders.created_at', 'DESC');

            if ($category === 'online') {
                $builder->where('orders.order_type', 'online');
            } elseif ($category === 'offline') {
                $builder->where('orders.order_type', 'offline');
            }
        }

        if (!empty($startDate)) {
            if ($category === 'refund') {
                $builder->where('DATE(order_refunds.created_at) >=', $startDate);
            } elseif ($category === 'cancellation') {
                $builder->where('DATE(order_cancellations.created_at) >=', $startDate);
            } else {
                $builder->where('DATE(orders.created_at) >=', $startDate);
            }
        }

        if (!empty($endDate)) {
            if ($category === 'refund') {
                $builder->where('DATE(order_refunds.created_at) <=', $endDate);
            } elseif ($category === 'cancellation') {
                $builder->where('DATE(order_cancellations.created_at) <=', $endDate);
            } else {
                $builder->where('DATE(orders.created_at) <=', $endDate);
            }
        }

        return $builder->findAll();
    }

    private function calculateSummary($orders, $category)
    {
        $totalTransactions = count($orders);
        $totalRevenue      = 0;
        $totalItems        = 0;

        foreach ($orders as $order) {
            $totalRevenue += (float) $order['grand_total'];
            $totalItems   += (int) $order['total_items'];
        }

        $averageOrderValue = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

        return [
            'total_transactions' => $totalTransactions,
            'total_revenue'      => $totalRevenue,
            'total_items'        => $totalItems,
            'average_value'      => $averageOrderValue,
        ];
    }

    private function exportToPdf($orders, $categoryText, $startDate, $endDate, $summary, $filename, $category)
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);

        $dompdf = new Dompdf($options);

        $html = view('reports/v_sales_pdf', [
            'orders'       => $orders,
            'categoryText' => $categoryText,
            'category'     => $category,
            'startDate'    => date('d M Y', strtotime($startDate)),
            'endDate'      => date('d M Y', strtotime($endDate)),
            'summary'      => $summary,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    private function exportToExcel($orders, $categoryText, $startDate, $endDate, $summary, $filename, $category)
    {
        $filePath = WRITEPATH . 'uploads/' . $filename;

        if (!is_dir(WRITEPATH . 'uploads/')) {
            mkdir(WRITEPATH . 'uploads/', 0777, true);
        }

        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToFile($filePath);

        // Styling
        $titleStyle = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(16)
            ->setFontColor(Color::rgb(33, 37, 41))
            ->build();

        $headerStyle = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(11)
            ->setBackgroundColor(Color::rgb(47, 184, 170))
            ->setFontColor(Color::WHITE)
            ->build();

        $subHeaderStyle = (new StyleBuilder())
            ->setFontBold()
            ->setBackgroundColor(Color::rgb(241, 243, 245))
            ->build();

        // 1. SALES SUMMARY SHEET
        $sheet = $writer->getCurrentSheet();
        $sheet->setName('Sales Summary');

        $writer->addRow(WriterEntityFactory::createRowFromArray(['OPTIKERS SALES REPORT'], $titleStyle));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['Category:', $categoryText]));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['Period:', date('d M Y', strtotime($startDate)) . ' to ' . date('d M Y', strtotime($endDate))]));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['Downloaded At:', date('d M Y H:i')]));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['']));

        $writer->addRow(WriterEntityFactory::createRowFromArray(['Summary Metrics', 'Value'], $subHeaderStyle));
        $writer->addRow(WriterEntityFactory::createRowFromArray([$category === 'refund' ? 'Total Returns' : ($category === 'cancellation' ? 'Total Cancellations' : 'Total Transactions'), number_format($summary['total_transactions'], 0, ',', '.')]));
        $writer->addRow(WriterEntityFactory::createRowFromArray([$category === 'refund' ? 'Total Refund Amount' : ($category === 'cancellation' ? 'Total Cancelled Amount' : 'Total Revenue'), 'Rp ' . number_format($summary['total_revenue'], 0, ',', '.')]));
        $writer->addRow(WriterEntityFactory::createRowFromArray([$category === 'refund' ? 'Total Refunded Items' : ($category === 'cancellation' ? 'Total Cancelled Items' : 'Total Items Sold'), number_format($summary['total_items'], 0, ',', '.')]));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['Average Transaction Value', 'Rp ' . number_format($summary['average_value'], 0, ',', '.')]));

        // 2. TRANSACTION DETAILS SHEET
        $writer->addNewSheetAndMakeItCurrent();
        $writer->getCurrentSheet()->setName('Transaction Details');

        $headers = ['No', 'Transaction ID', 'Date', 'Category', 'Customer', 'Email', 'Total Items', 'Grand Total', 'Status'];
        $writer->addRow(WriterEntityFactory::createRowFromArray($headers, $headerStyle));

        $no = 1;
        foreach ($orders as $order) {
            $rowData = [
                $no++,
                '#' . $order['order_id'],
                date('d M Y H:i', strtotime($order['created_at'])),
                ucfirst($order['order_type']),
                $order['customer_name'] ?? '-',
                $order['customer_email'] ?? '-',
                (int) $order['total_items'],
                'Rp ' . number_format($order['grand_total'], 0, ',', '.'),
                $order['status_name'] ?? 'Completed'
            ];
            $writer->addRow(WriterEntityFactory::createRowFromArray($rowData));
        }

        $writer->close();

        return $this->response->download($filePath, null)->setFileName($filename);
    }
}

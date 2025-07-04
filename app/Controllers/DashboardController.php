<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use App\Models\InventoryTransactionsModel;
use App\Models\OrderModel;
use App\Models\ProductModel;
use DateTime;

class DashboardController extends BaseController
{
    protected $customerModel, $productModel, $inventoryTransactionsModel, $ordersModel;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
        $this->productModel = new ProductModel();
        $this->inventoryTransactionsModel = new InventoryTransactionsModel();
        $this->ordersModel = new OrderModel();
    }

    public function index()
    {
        $totalProducts = $this->productModel->countAllResults();
        $totalCustomers = $this->customerModel->countAllResults();

        // Total Selling dari Inventory Transactions (unit)
        $totalSellingUnits = $this->inventoryTransactionsModel
            ->selectSum('quantity')
            ->where('transaction_type', 'out')
            ->first()['quantity'] ?? 0;

        // Total Selling dari Orders (rupiah)
        $totalSellingRupiah = $this->ordersModel
            ->selectSum('total_price')
            ->whereIn('status', ['paid', 'shipped'])
            ->first()['total_price'] ?? 0;

        // Monthly Sales (Units)
        $monthlySalesUnits = $this->inventoryTransactionsModel
            ->select("MONTH(created_at) AS month, SUM(quantity) AS total")
            ->where('transaction_type', 'out')
            ->where('YEAR(created_at)', date('Y'))
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->findAll();

        // Monthly Sales (Rupiah)
        $monthlySalesRupiah = $this->ordersModel
            ->select("MONTH(order_date) AS month, SUM(total_price) AS total")
            ->whereIn('status', ['paid', 'shipped'])
            ->where('YEAR(order_date)', date('Y'))
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->findAll();

        $unitsMap = [];
        foreach ($monthlySalesUnits as $row) {
            $unitsMap[(int)$row['month']] = (int)$row['total'];
        }

        $rupiahMap = [];
        foreach ($monthlySalesRupiah as $row) {
            $rupiahMap[(int)$row['month']] = (int)$row['total'];
        }

        $months = [];
        $unitTotals = [];
        $rupiahTotals = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[] = date('F', mktime(0, 0, 0, $i, 1));
            $unitTotals[] = $unitsMap[$i] ?? 0;
            $rupiahTotals[] = $rupiahMap[$i] ?? 0;
        }

        // Total Barang Masuk dan Keluar
        $totalIn = $this->inventoryTransactionsModel
            ->selectSum('quantity')
            ->where('transaction_type', 'in')
            ->first()['quantity'] ?? 0;

        $totalOut = $this->inventoryTransactionsModel
            ->selectSum('quantity')
            ->where('transaction_type', 'out')
            ->first()['quantity'] ?? 0;

        // Chart Barang Masuk / Keluar per bulan (6 bulan terakhir)
        $monthlyInOutRaw = $this->inventoryTransactionsModel
            ->select("MONTH(transaction_date) AS month, 
              SUM(CASE WHEN transaction_type = 'in' THEN quantity ELSE 0 END) AS total_in, 
              SUM(CASE WHEN transaction_type = 'out' THEN quantity ELSE 0 END) AS total_out")
            ->where('YEAR(transaction_date)', date('Y'))
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->findAll();


        $inMap = [];
        $outMap = [];

        foreach ($monthlyInOutRaw as $row) {
            $inMap[(int)$row['month']] = (int)$row['total_in'];
            $outMap[(int)$row['month']] = (int)$row['total_out'];
        }

        $monthsIO = [];
        $inQuantities = [];
        $outQuantities = [];

        for ($i = 1; $i <= 12; $i++) {
            $monthsIO[] = date('F', mktime(0, 0, 0, $i, 1));
            $inQuantities[] = $inMap[$i] ?? 0;
            $outQuantities[] = $outMap[$i] ?? 0;
        }

        $data = [
            'totalProducts' => $totalProducts,
            'totalCustomers' => $totalCustomers,
            'totalSellingUnits' => $totalSellingUnits,
            'totalSellingRupiah' => $totalSellingRupiah,
            'months' => json_encode($months),
            'unitTotals' => json_encode($unitTotals),
            'rupiahTotals' => json_encode($rupiahTotals),

            'totalIn' => $totalIn,
            'totalOut' => $totalOut,
            'monthsIO' => json_encode($monthsIO),
            'inQuantities' => json_encode($inQuantities),
            'outQuantities' => json_encode($outQuantities),
        ];

        return view('v_dashboard', $data);
    }
}

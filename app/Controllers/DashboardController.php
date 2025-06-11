<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use App\Models\InventoryTransactionsModel;
use App\Models\ProductModel;
use CodeIgniter\HTTP\ResponseInterface;

class DashboardController extends BaseController
{
    protected $customerModel, $productModel, $inventoryTransactionsModel;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
        $this->productModel = new ProductModel();
        $this->inventoryTransactionsModel = new InventoryTransactionsModel();
    }

    public function index()
    {
        $totalProducts = $this->productModel->countAllResults();
        $totalCustomers = $this->customerModel->countAllResults();
        $totalSelling = $this->inventoryTransactionsModel
            ->where('transaction_type', 'out')
            ->countAllResults();

        // Query total penjualan per bulan
        $monthlySales = $this->inventoryTransactionsModel
            ->select("MONTH(created_at) AS month, SUM(quantity) AS total")
            ->where('transaction_type', 'out')
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->findAll();

        // Siapkan array bulan dan totalnya
        $months = [];
        $totals = [];
        foreach ($monthlySales as $sale) {
            $months[] = date('F', mktime(0, 0, 0, $sale['month'], 1));
            $totals[] = (int) $sale['total'];
        }

        $data = [
            'totalProducts' => $totalProducts,
            'totalCustomers' => $totalCustomers,
            'totalSelling' => $totalSelling,
            'months' => json_encode($months),
            'totals' => json_encode($totals)
        ];

        return view('v_dashboard', $data);
    }
}

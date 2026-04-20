<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\OrderModel;
use App\Models\ProductModel;
use App\Models\CustomerModel;
use App\Models\InventoryTransactionModel;

class DashboardController extends BaseController
{
    protected $orderModel, $productModel, $customerModel, $inventoryModel;

    public function __construct()
    {
        $this->orderModel     = new OrderModel();
        $this->productModel   = new ProductModel();
        $this->customerModel  = new CustomerModel();
        $this->inventoryModel = new InventoryTransactionModel();
    }

    public function index()
    {
        $stats = cache()->remember('dashboard_stats', 300, function () {
            return $this->getStats();
        });

        return view('v_dashboard', array_merge($stats, [
            'pusherKey' => env('pusher.key')
        ]));
    }

    /**
     * AJAX endpoint for real-time updates
     */
    public function apiStats()
    {
        $stats = cache()->remember('dashboard_stats', 300, function () {
            return $this->getStats();
        });

        return $this->response->setJSON($stats);
    }

    /**
     * Centralized logic for dashboard statistics calculation
     */
    private function getStats(): array
    {
        /** =======================
         * CARD SUMMARY
         * ======================= */

        // TOTAL REVENUE
        $totalRevenue = $this->orderModel
            ->selectSum('orders.grand_total', 'total')
            ->join('order_statuses os', 'orders.status_id = os.status_id')
            ->whereIn('os.status_code', ['paid', 'shipped', 'completed'])
            ->first()['total'] ?? 0;

        // TOTAL ORDERS TODAY
        $totalOrdersToday = $this->orderModel
            ->selectSum('order_items.quantity', 'total_quantity')
            ->join('order_items', 'order_items.order_id = orders.order_id')
            ->where('DATE(orders.created_at)', date('Y-m-d'))
            ->get()
            ->getRow()
            ->total_quantity ?? 0;

        // ONLINE SALES
        $onlineSales = $this->orderModel
            ->selectSum('orders.grand_total', 'total')
            ->join('order_statuses os', 'orders.status_id = os.status_id')
            ->where('orders.order_type', 'online')
            ->whereIn('os.status_code', ['paid', 'shipped', 'completed'])
            ->first()['total'] ?? 0;

        // POS SALES
        $posSales = $this->orderModel
            ->selectSum('orders.grand_total', 'total')
            ->join('order_statuses os', 'orders.status_id = os.status_id')
            ->where('orders.order_type', 'offline')
            ->whereIn('os.status_code', ['paid', 'completed'])
            ->first()['total'] ?? 0;

        // TOTAL CUSTOMERS
        $totalCustomers = $this->customerModel->countAllResults() ?? 0;

        // LOW STOCK
        $lowStockCount = $this->productModel
            ->where('product_stock <= 5')
            ->countAllResults();

        /** =======================
         * MONTHLY SALES CHART
         * ======================= */
        $monthly = $this->orderModel
            ->select("MONTH(orders.created_at) AS month, SUM(orders.grand_total) AS total")
            ->join('order_statuses os', 'orders.status_id = os.status_id')
            ->where('YEAR(orders.created_at)', date('Y'))
            ->whereIn('os.status_code', ['paid', 'shipped', 'completed'])
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->findAll();

        $months = [];
        $revenues = array_fill(0, 12, 0);

        for ($i = 1; $i <= 12; $i++) {
            $months[] = date('F', mktime(0, 0, 0, $i, 1));
        }

        foreach ($monthly as $row) {
            $revenues[$row['month'] - 1] = (int)$row['total'];
        }

        /** =======================
         * TOP 5 PRODUCTS
         * ======================= */
        $topProducts = $this->inventoryModel
            ->select('products.product_name, SUM(inventory_transactions.quantity) AS sold')
            ->join('products', 'products.product_id = inventory_transactions.product_id')
            ->where('inventory_transactions.transaction_type', 'out')
            ->groupBy('products.product_id')
            ->orderBy('sold', 'DESC')
            ->limit(5)
            ->findAll();

        /** =======================
         * ORDER STATUS SUMMARY
         * ======================= */
        $orderStatuses = $this->orderModel
            ->select('os.status_name AS status, os.status_code, COUNT(order_id) AS total')
            ->join('order_statuses os', 'orders.status_id = os.status_id')
            ->groupBy(['os.status_code', 'os.status_name'])
            ->findAll();

        return [
            'totalRevenue'      => (int)$totalRevenue,
            'totalOrdersToday'  => (int)$totalOrdersToday,
            'onlineSales'       => (int)$onlineSales,
            'posSales'          => (int)$posSales,
            'totalCustomers'    => (int)$totalCustomers,
            'lowStockCount'     => (int)$lowStockCount,
            'months'            => json_encode($months),
            'revenues'          => json_encode($revenues),
            'topProducts'       => $topProducts,
            'orderStatuses'     => $orderStatuses,
        ];
    }
}

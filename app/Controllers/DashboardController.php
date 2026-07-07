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
        /**
         * =========================
         * DATE RANGE
         * =========================
         */
        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd   = date('Y-m-d 23:59:59');

        $yearStart  = date('Y-01-01 00:00:00');
        $yearEnd    = date('Y-12-31 23:59:59');

        /**
         * =========================
         * CACHE
         * =========================
         */
        $cacheKey = 'dashboard_stats';

        if ($cache = cache($cacheKey)) {
            return $cache;
        }

        /**
         * =========================
         * SALES SUMMARY
         * =========================
         */
        $salesStats = $this->db->table('orders')
            ->select("
            SUM(
                CASE
                    WHEN os.status_code IN ('paid', 'shipped', 'completed')
                    THEN orders.grand_total
                    ELSE 0
                END
            ) AS total_revenue,

            SUM(
                CASE
                    WHEN orders.order_type = 'online'
                    AND os.status_code IN ('paid', 'shipped', 'completed')
                    THEN orders.grand_total
                    ELSE 0
                END
            ) AS online_sales,

            SUM(
                CASE
                    WHEN orders.order_type = 'offline'
                    AND os.status_code IN ('paid', 'completed')
                    THEN orders.grand_total
                    ELSE 0
                END
            ) AS pos_sales
        ")
            ->join(
                'order_statuses os',
                'orders.status_id = os.status_id',
                'left'
            )
            ->get()
            ->getRowArray();

        /**
         * =========================
         * TOTAL ORDERS TODAY
         * =========================
         */
        $totalOrdersToday = $this->db->table('orders')
            ->selectSum('order_items.quantity', 'total_quantity')
            ->join(
                'order_items',
                'order_items.order_id = orders.order_id'
            )
            ->where('orders.created_at >=', $todayStart)
            ->where('orders.created_at <=', $todayEnd)
            ->get()
            ->getRow()
            ->total_quantity ?? 0;

        /**
         * =========================
         * TOTAL CUSTOMERS
         * =========================
         */
        $totalCustomers = $this->db->table('customers')
            ->countAllResults();

        /**
         * =========================
         * LOW STOCK
         * =========================
         */
        $lowStockCount = $this->db->table('products')
            ->where('product_stock <=', 5)
            ->countAllResults();

        /**
         * =========================
         * MONTHLY SALES
         * =========================
         */
        $monthly = $this->db->table('orders')
            ->select("
            MONTH(orders.created_at) AS month,
            SUM(orders.grand_total) AS total
        ")
            ->join(
                'order_statuses os',
                'orders.status_id = os.status_id'
            )
            ->where('orders.created_at >=', $yearStart)
            ->where('orders.created_at <=', $yearEnd)
            ->whereIn(
                'os.status_code',
                ['paid', 'shipped', 'completed']
            )
            ->groupBy('MONTH(orders.created_at)')
            ->orderBy('month', 'ASC')
            ->get()
            ->getResultArray();

        $months = [];
        $revenues = array_fill(0, 12, 0);

        for ($i = 1; $i <= 12; $i++) {
            $months[] = date('F', mktime(0, 0, 0, $i, 1));
        }

        foreach ($monthly as $row) {
            $revenues[$row['month'] - 1] = (int) $row['total'];
        }

        /**
         * =========================
         * TOP PRODUCTS
         * =========================
         */
        $topProducts = $this->db->table('inventory_transactions')
            ->select("
            products.product_name,
            SUM(inventory_transactions.quantity) AS sold
        ")
            ->join(
                'products',
                'products.product_id = inventory_transactions.product_id'
            )
            ->where('inventory_transactions.transaction_type', 'out')
            ->groupBy('inventory_transactions.product_id')
            ->orderBy('sold', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        /**
         * =========================
         * ORDER STATUS SUMMARY
         * =========================
         */
        $orderStatuses = $this->db->table('orders')
            ->select("
            os.status_name AS status,
            os.status_code,
            COUNT(orders.order_id) AS total
        ")
            ->join(
                'order_statuses os',
                'orders.status_id = os.status_id'
            )
            ->groupBy([
                'os.status_code',
                'os.status_name'
            ])
            ->get()
            ->getResultArray();

        /**
         * =========================
         * RESPONSE
         * =========================
         */
        $data = [
            'totalRevenue'      => (int) ($salesStats['total_revenue'] ?? 0),
            'totalOrdersToday'  => (int) $totalOrdersToday,
            'onlineSales'       => (int) ($salesStats['online_sales'] ?? 0),
            'posSales'          => (int) ($salesStats['pos_sales'] ?? 0),
            'totalCustomers'    => (int) $totalCustomers,
            'lowStockCount'     => (int) $lowStockCount,
            'months'            => json_encode($months),
            'revenues'          => json_encode($revenues),
            'topProducts'       => $topProducts,
            'orderStatuses'     => $orderStatuses,
        ];

        /**
         * =========================
         * SAVE CACHE
         * =========================
         */
        cache()->save($cacheKey, $data, 60);

        return $data;
    }

    public function recommendationDebug()
    {
        $products = $this->productModel
            ->select('product_id, product_name, product_brand')
            ->where('deleted_at', null)
            ->orderBy('product_name', 'ASC')
            ->findAll();

        $customers = $this->customerModel
            ->select('customer_id, customer_name')
            ->where('deleted_at', null)
            ->orderBy('customer_name', 'ASC')
            ->findAll();

        return view('v_recommendation_debug', [
            'products' => $products,
            'customers' => $customers
        ]);
    }
}

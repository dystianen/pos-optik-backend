<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PaymentMethodModel;
use App\Models\PaymentModel;
use Config\OrderStatus;

class OfflineSalesController extends BaseController
{
    protected $orderModel;
    protected $productModel;
    protected $productVariantModel;
    protected $orderItemPrescriptionModel;
    protected $customerModel;
    protected $orderItemModel;
    protected $paymentModel;
    protected $paymentMethodModel;
    protected $notificationModel;
    protected $statusModel;
    protected $db;

    public function __construct()
    {
        $this->orderModel           = new \App\Models\OrderModel();
        $this->productModel         = new \App\Models\ProductModel();
        $this->productVariantModel  = new \App\Models\ProductVariantModel();
        $this->orderItemPrescriptionModel  = new \App\Models\OrderItemPrescriptionModel();
        $this->customerModel        = new \App\Models\CustomerModel();
        $this->orderItemModel       = new \App\Models\OrderItemModel();
        $this->paymentModel         = new PaymentModel();
        $this->paymentMethodModel   = new PaymentMethodModel();
        $this->notificationModel    = new \App\Models\NotificationModel();
        $this->statusModel          = new \App\Models\OrderStatusModel();
        $this->db                   = \Config\Database::connect();
    }

    public function index()
    {
        $currentPage = max(
            1,
            (int) ($this->request->getVar('page') ?? 1)
        );

        $search    = trim($this->request->getVar('q') ?? '');
        $startDate = $this->request->getVar('start_date');
        $endDate   = $this->request->getVar('end_date');

        $limit  = 10;
        $offset = ($currentPage - 1) * $limit;

        /**
         * =========================
         * MAIN QUERY
         * =========================
         */
        $builder = $this->db->table('orders');

        $builder->select("
            orders.order_id,
            orders.created_at,
            orders.grand_total,

            customers.customer_name,
            customers.customer_email,

            order_statuses.status_name,

            (
                SELECT COUNT(*)
                FROM order_items oi
                WHERE oi.order_id = orders.order_id
            ) AS total_items
        ");

        $builder->join(
            'customers',
            'customers.customer_id = orders.customer_id'
        );

        $builder->join(
            'order_statuses',
            'order_statuses.status_id = orders.status_id'
        );

        $builder->where('orders.order_type', 'offline');

        /**
         * =========================
         * SEARCH
         * =========================
         */
        if ($search !== '') {

            $builder->groupStart()
                ->like('orders.order_id', $search)
                ->orLike('customers.customer_name', $search)
                ->orLike('customers.customer_email', $search)
                ->orLike('order_statuses.status_name', $search)
                ->groupEnd();
        }

        /**
         * =========================
         * DATE FILTER
         * =========================
         */
        if (!empty($startDate)) {
            $builder->where(
                'orders.created_at >=',
                $startDate . ' 00:00:00'
            );
        }

        if (!empty($endDate)) {
            $builder->where(
                'orders.created_at <=',
                $endDate . ' 23:59:59'
            );
        }

        /**
         * =========================
         * DATA
         * =========================
         */
        $orders = $builder
            ->orderBy('orders.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResultArray();

        /**
         * =========================
         * COUNT QUERY
         * =========================
         */
        $countBuilder = $this->db->table('orders');

        $countBuilder->join(
            'customers',
            'customers.customer_id = orders.customer_id'
        );

        $countBuilder->join(
            'order_statuses',
            'order_statuses.status_id = orders.status_id'
        );

        $countBuilder->where('orders.order_type', 'offline');

        if ($search !== '') {

            $countBuilder->groupStart()
                ->like('orders.order_id', $search)
                ->orLike('customers.customer_name', $search)
                ->orLike('customers.customer_email', $search)
                ->orLike('order_statuses.status_name', $search)
                ->groupEnd();
        }

        if (!empty($startDate)) {
            $countBuilder->where(
                'orders.created_at >=',
                $startDate . ' 00:00:00'
            );
        }

        if (!empty($endDate)) {
            $countBuilder->where(
                'orders.created_at <=',
                $endDate . ' 23:59:59'
            );
        }

        $totalRows = $countBuilder->countAllResults();

        $totalPages = (int) ceil($totalRows / $limit);

        return view('offline_sales/v_index', [
            'orders' => $orders,

            'search' => $search,

            'startDate' => $startDate,
            'endDate'   => $endDate,

            'pager' => [
                'totalPages'  => $totalPages,
                'currentPage' => $currentPage,
                'limit'       => $limit,
            ],
        ]);
    }

    public function create()
    {
        return view('offline_sales/v_create', [
            'customers'      => $this->customerModel->findAll(),
            'products'       => $this->productModel->findAll(),
            'paymentMethods' => $this->paymentMethodModel->where('is_active', 1)->orderBy('method_name', 'ASC')->findAll(),
        ]);
    }

    public function store()
    {
        $db = db_connect();
        $db->transStart();

        try {
            $customerId     = $this->request->getPost('customer_id');
            $items          = $this->request->getPost('items');
            $paymentMethodId = $this->request->getPost('payment_method_id');
            $cashReceived   = $this->request->getPost('cash_received');
            $paymentProof   = $this->request->getFile('payment_proof');

            if (!$customerId) {
                throw new \Exception('Customer wajib dipilih');
            }

            if (empty($items)) {
                throw new \Exception('Item tidak boleh kosong');
            }

            if (!$paymentMethodId) {
                throw new \Exception('Metode pembayaran wajib dipilih');
            }

            $paymentMethod = $this->paymentMethodModel->find($paymentMethodId);
            if (!$paymentMethod || ! (int) $paymentMethod['is_active']) {
                throw new \Exception('Metode pembayaran tidak valid');
            }

            // ======================
            // HITUNG GRAND TOTAL
            // ======================
            $grandTotal = 0;

            foreach ($items as $item) {
                if (
                    empty($item['product_id']) ||
                    empty($item['qty']) ||
                    empty($item['price'])
                ) {
                    throw new \Exception('Data item tidak lengkap');
                }

                $price = (float) $item['price'];
                $qty   = (int) $item['qty'];

                if ($price <= 0 || $qty <= 0) {
                    throw new \Exception('Harga / Qty tidak valid');
                }

                $grandTotal += $price * $qty;
            }

            // ======================
            // VALIDASI PAYMENT
            // ======================
            $paymentAmount = $grandTotal;
            $paymentProofUrl = null;

            if ($paymentMethod['method_type'] === 'cash') {
                if ($cashReceived === null || $cashReceived === '') {
                    throw new \Exception('Nominal uang customer wajib diisi untuk pembayaran tunai');
                }

                $cashReceivedValue = (float) $cashReceived;
                if ($cashReceivedValue < $grandTotal) {
                    throw new \Exception('Nominal uang customer kurang dari total pembayaran');
                }

                $paymentAmount = $cashReceivedValue;
            } else {
                if (!$paymentProof || !$paymentProof->isValid()) {
                    throw new \Exception('Bukti pembayaran wajib diunggah untuk metode non-tunai');
                }

                $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
                if (!in_array($paymentProof->getMimeType(), $allowedMime)) {
                    throw new \Exception('Bukti pembayaran harus berupa gambar JPG, PNG, atau WEBP');
                }
            }

            // ======================
            // VALIDASI STOK (WAJIB)
            // ======================
            foreach ($items as $item) {

                $productId = $item['product_id'];
                $variantId = $item['variant_id'] ?? null;
                $qty       = (int) $item['qty'];

                if ($variantId) {
                    $variant = $db->query(
                        'SELECT stock FROM product_variants WHERE variant_id = ? FOR UPDATE',
                        [$variantId]
                    )->getRowArray();

                    if (!$variant) {
                        throw new \Exception('Variant tidak ditemukan');
                    }

                    if ($variant['stock'] < $qty) {
                        throw new \Exception('Stok variant tidak mencukupi');
                    }
                } else {

                    $product = $db->query(
                        'SELECT product_stock FROM products WHERE product_id = ? FOR UPDATE',
                        [$productId]
                    )->getRowArray();

                    if (!$product) {
                        throw new \Exception('Produk tidak ditemukan');
                    }

                    if ($product['product_stock'] < $qty) {
                        throw new \Exception('Stok produk tidak mencukupi');
                    }
                }
            }

            // ======================
            // INSERT ORDER
            // ======================
            $this->orderModel->insert([
                'customer_id'     => $customerId,
                'status_id'       => $this->statusModel->getIdByCode(OrderStatus::PROCESSING),
                'shipping_cost'   => 0,
                'coupon_discount' => 0,
                'grand_total'     => $grandTotal,
                'order_type'      => 'offline'
            ]);

            $orderId = $this->orderModel->getInsertID();

            // ======================
            // INSERT ORDER ITEMS
            // ======================
            foreach ($items as $item) {

                $productId = $item['product_id'];
                $variantId = $item['variant_id'] ?: null;
                $qty       = (int) $item['qty'];
                $price     = (float) $item['price'];

                // ======================
                // INSERT ORDER ITEM
                // ======================
                $this->orderItemModel->insert([
                    'order_id'   => $orderId,
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'quantity'   => $qty,
                    'price'      => $price,
                ]);

                $orderItemId = $this->orderItemModel->getInsertID();

                // ======================
                // KURANGI STOK
                // ======================
                if ($variantId) {
                    $this->productVariantModel
                        ->where('variant_id', $variantId)
                        ->set('stock', 'stock - ' . $qty, false)
                        ->update();

                    $db->query("
                        UPDATE products p
                        SET p.product_stock = (
                            SELECT COALESCE(SUM(pv.stock), 0)
                            FROM product_variants pv
                            WHERE pv.product_id = p.product_id
                        )
                        WHERE p.product_id = ?
                    ", [$productId]);

                    // 🔍 CEK STOK TERBARU
                    $updatedVariant = $this->productVariantModel
                        ->select('stock, variant_name')
                        ->where('variant_id', $variantId)
                        ->get()
                        ->getRowArray();

                    // 🚨 JIKA STOK HABIS
                    if ($updatedVariant && (int)$updatedVariant['stock'] === 0) {
                        $this->notificationModel->addNotification(
                            'stock_empty',
                            'Stok variant "' . $updatedVariant['variant_name'] . '" telah habis',
                            $productId
                        );
                    }
                } else {
                    $this->productModel
                        ->where('product_id', $productId)
                        ->set('product_stock', 'product_stock - ' . $qty, false)
                        ->update();

                    // 🔍 CEK STOK TERBARU
                    $updatedProduct = $this->productModel
                        ->select('product_stock, product_name')
                        ->where('product_id', $productId)
                        ->get()
                        ->getRowArray();

                    // 🚨 JIKA STOK HABIS
                    if ($updatedProduct && (int)$updatedProduct['product_stock'] === 0) {
                        $this->notificationModel->addNotification(
                            'stock_empty',
                            'Stok produk "' . $updatedProduct['product_name'] . '" telah habis',
                            $productId
                        );
                    }
                }

                // ======================
                // PRESCRIPTION PER ITEM (FIX)
                // ======================
                $prescription = $item['prescription'] ?? null;

                if (
                    $prescription &&
                    isset($prescription['type']) &&
                    $prescription['type'] === 'manual'
                ) {
                    $this->orderItemPrescriptionModel->insert([
                        'order_item_id' => $orderItemId,

                        'right_sph'  => $prescription['right']['sph'] ?? null,
                        'right_cyl'  => $prescription['right']['cyl'] ?? null,
                        'right_axis' => $prescription['right']['axis'] ?? null,
                        'pd_right'   => $prescription['right']['pd'] ?? null,

                        'left_sph'   => $prescription['left']['sph'] ?? null,
                        'left_cyl'   => $prescription['left']['cyl'] ?? null,
                        'left_axis'  => $prescription['left']['axis'] ?? null,
                        'pd_left'    => $prescription['left']['pd'] ?? null,
                    ]);
                }
            }

            // ======================
            // INSERT PAYMENT
            // ======================
            if ($paymentMethod['method_type'] !== 'cash') {
                $paymentDir = PUBLICPATH . 'uploads/payments/' . $orderId . '/';
                if (!is_dir($paymentDir)) {
                    mkdir($paymentDir, 0755, true);
                }

                $proofName = $paymentProof->getRandomName();
                $paymentProof->move($paymentDir, $proofName);
                $paymentProofUrl = base_url('uploads/payments/' . $orderId . '/' . $proofName);
            }

            $this->paymentModel->insert([
                'order_id'          => $orderId,
                'payment_method_id' => $paymentMethodId,
                'amount'            => $paymentAmount,
                'proof'             => $paymentProofUrl,
                'paid_at'           => date('Y-m-d H:i:s'),
            ]);

            $db->transComplete();

            // 🔥 TRIGGER REAL-TIME UPDATE
            \App\Libraries\Realtime::triggerUpdate('order-new');

            return redirect()
                ->to(site_url('offline-sales/success/' . $orderId))
                ->with('success', 'Transaksi berhasil disimpan');
        } catch (\Throwable $e) {
            $db->transRollback();

            log_message('error', $e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function detail($orderId)
    {
        $order = $this->orderModel
            ->select('
                orders.order_id,
                orders.created_at AS order_date,
                orders.grand_total,
                orders.shipping_cost,
                orders.status_id,
                orders.tracking_number,
                orders.courier,

                customers.customer_name,
                customers.customer_email,

                order_statuses.status_name,
                order_statuses.status_code,
            ')
            ->join('customers', 'customers.customer_id = orders.customer_id', 'left')
            ->join('order_statuses', 'order_statuses.status_id = orders.status_id', 'left')
            ->where('orders.order_id', $orderId)
            ->where('orders.deleted_at', null)
            ->first();

        if (!$order) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Order not found');
        }

        // 📦 Order items
        $items = $this->orderModel->getOrderItems($orderId);

        $payment = $this->paymentModel
            ->select('payments.*, payment_methods.method_name, payment_methods.method_type')
            ->join('payment_methods', 'payment_methods.payment_method_id = payments.payment_method_id', 'left')
            ->where('payments.order_id', $orderId)
            ->orderBy('payments.created_at', 'DESC')
            ->first();

        $data = [
            'order'   => $order,
            'items'   => $items,
            'payment' => $payment,
        ];

        return view('offline_sales/v_detail', $data);
    }

    public function success($orderId)
    {
        return view('offline_sales/v_success', [
            'order_id' => $orderId
        ]);
    }

    public function print($orderId)
    {
        $order = $this->orderModel
            ->select('
                orders.order_id,
                orders.created_at,
                orders.grand_total,
                customers.customer_name
            ')
            ->join('customers', 'customers.customer_id = orders.customer_id')
            ->where('orders.order_id', $orderId)
            ->first();

        if (!$order) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Order tidak ditemukan');
        }

        $items = $this->orderItemModel
            ->select('
            order_items.quantity,
            order_items.price,
            products.product_name,
            product_variants.variant_name
        ')
            ->join('products', 'products.product_id = order_items.product_id')
            ->join('product_variants', 'product_variants.variant_id = order_items.variant_id', 'left')
            ->where('order_items.order_id', $orderId)
            ->findAll();

        return view('offline_sales/v_print_struk', [
            'order' => $order,
            'items' => $items
        ]);
    }
}

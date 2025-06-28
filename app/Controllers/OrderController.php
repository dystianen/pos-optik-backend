<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\InventoryTransactionsModel;
use App\Models\OrderItemModel;
use App\Models\OrderModel;
use App\Models\ProductModel;
use CodeIgniter\API\ResponseTrait;

class OrderController extends BaseController
{
    use ResponseTrait;
    protected $orderModel, $orderItemModel, $inventoryTransactionsModel, $productModel;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->orderItemModel = new OrderItemModel();
        $this->inventoryTransactionsModel = new InventoryTransactionsModel();
        $this->productModel = new ProductModel();
    }

    public function orders()
    {
        $decoded = $this->decodedToken();
        $customerId = $decoded->user_id;

        if (!$customerId) {
            return $this->respond(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $orders = $this->orderModel
            ->where('customer_id', $customerId)
            ->whereNotIn('status', ['cart'])
            ->orderBy('order_date', 'DESC')
            ->findAll();

        if (!$orders) {
            return $this->respond(['status' => 404, 'message' => 'No orders found'], 404);
        }

        // Ambil semua orderItems untuk setiap order
        $orderData = [];

        foreach ($orders as $order) {
            $items = $this->orderItemModel
                ->join('products', 'products.product_id = order_items.product_id')
                ->where('order_id', $order['order_id'])
                ->findAll();

            $order['items'] = $items;
            $orderData[] = $order;
        }

        return $this->respond([
            'status' => 200,
            'message' => 'Orders retrieved successfully',
            'data' => $orderData
        ]);
    }

    public function checkout()
    {
        $decoded = $this->decodedToken();
        $customerId = $decoded->user_id;

        if (!$customerId) {
            return $this->respond(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        $order = $this->orderModel
            ->where('customer_id', $customerId)
            ->where('status', 'cart')
            ->first();

        if (!$order) {
            return $this->respond(['status' => 404, 'message' => 'Cart not found'], 404);
        }

        $shippingAddress = $this->request->getVar('shipping_address');
        $shippingCost = 20000;

        if (!$shippingAddress) {
            return $this->respond(['status' => 400, 'message' => 'Incomplete checkout data'], 400);
        }

        $finalTotal = $order['total_price'] + $shippingCost;

        // Update order data
        $this->orderModel->update($order['order_id'], [
            'address' => $shippingAddress,
            'proof_of_payment' => null,
            'order_date' => date('Y-m-d H:i:s'),
            'status' => 'pending',
            'shipping_costs' => $shippingCost,
            'grand_total' => $finalTotal,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Ambil item-item dari order (order_details)
        $orderDetails = $this->orderItemModel
            ->where('order_id', $order['order_id'])
            ->findAll();

        foreach ($orderDetails as $item) {
            // 1. Insert ke inventory_transactions (out)
            $this->inventoryTransactionsModel->insert([
                'user_id' => $customerId,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'transaction_type' => 'out',
                'description' => 'Checkout Order #' . $order['order_id'],
                'transaction_date' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // 2. Kurangi stok produk
            $product = $this->productModel->find($item['product_id']);
            $currentStock = (int)$product['product_stock'];
            $newStock = $currentStock - (int)$item['quantity'];

            $this->productModel->update($item['product_id'], [
                'product_stock' => $newStock
            ]);
        }

        return $this->respond([
            'status' => 200,
            'message' => 'Checkout successful. Awaiting payment confirmation.',
            'data' => [
                'order_id' => $order['order_id'],
                'grand_total' => $finalTotal
            ]
        ]);
    }



    public function uploadPaymentProof()
    {
        $decoded = $this->decodedToken();
        $customerId = $decoded->user_id;

        if (!$customerId) {
            return $this->respond(['status' => 401, 'message' => 'Unauthorized'], 401);
        }
        log_message('debug', print_r($_FILES, true));

        // Cari order yang masih 'pending'
        $order = $this->orderModel
            ->where('customer_id', $customerId)
            ->where('status', 'pending')
            ->first();

        if (!$order) {
            return $this->respond([
                'status' => 404,
                'message' => 'No pending order found'
            ], 404);
        }

        $file = $this->request->getFile('proof_of_payment');

        if (!$file || !$file->isValid()) {
            return $this->respond([
                'status' => 400,
                'message' => 'Proof of payment file is required'
            ], 400);
        }

        // Simpan file ke folder uploads/payments/
        $newName = $file->getRandomName();
        $file->move(FCPATH . 'uploads/payments', $newName);

        // Update order
        $this->orderModel->update($order['order_id'], [
            'proof_of_payment' => 'uploads/payments/' . $newName,
            'status' => 'waiting_confirmation',
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return $this->respond([
            'status' => 200,
            'message' => 'Payment proof uploaded successfully',
            'data' => [
                'order_id' => $order['order_id'],
                'proof_of_payment' => base_url('uploads/payments/' . $newName)
            ]
        ]);
    }

    public function index()
    {
        $page = $this->request->getVar('page') ?? 1;
        $perPage = 10;
        $orders = $this->orderModel
            ->join('customers', 'customers.customer_id = orders.customer_id')
            ->paginate($perPage, 'default', $page);

        $pager = [
            'currentPage' => $this->orderModel->pager->getCurrentPage('default'),
            'totalPages' => $this->orderModel->pager->getPageCount('default'),
            'limit' => $perPage
        ];

        return view('orders/v_index', [
            'orders' => $orders,
            'pager' => $pager
        ]);
    }

    public function form()
    {
        $id = $this->request->getVar('id');

        if (!$id) {
            return view('orders/v_form');
        }

        $order = $this->orderModel
            ->join('customers', 'customers.customer_id = orders.customer_id')
            ->find($id);

        if (!$order) {
            return redirect()->to('/orders')->with('error', 'Order not found.');
        }

        $orderItems = $this->orderItemModel
            ->join('products', 'products.product_id = order_items.product_id')
            ->where('order_id', $id)
            ->findAll();


        return view('orders/v_form', [
            'order' => $order,
            'orderItems' => $orderItems,
        ]);
    }

    public function save()
    {
        $id = $this->request->getVar('id');
        $data = [
            'status' => $this->request->getPost('status'),
        ];

        $this->orderModel->update($id, $data);
        $message = 'Order updated successfully!';

        return redirect()->to('/orders')->with('success', $message);
    }

    public function checkIfPaid()
    {
        $decoded = $this->decodedToken();
        $customerId = $decoded->user_id;

        if (!$customerId) {
            return $this->respond(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        // Ambil order terakhir (atau bisa disesuaikan)
        $order = $this->orderModel
            ->where('customer_id', $customerId)
            ->orderBy('order_date', 'DESC')
            ->first();

        if (!$order) {
            return $this->respond(['status' => 404, 'message' => 'Order not found'], 404);
        }

        $isShipped = $order['status'] === 'paid';

        return $this->respond([
            'status' => 200,
            'message' => $isShipped ? 'Order has been shipped' : 'Order not yet shipped',
            'data' => [
                'isShipped' => $isShipped
            ]
        ]);
    }
}

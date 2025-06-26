<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\OrderItemModel;
use App\Models\OrderModel;
use CodeIgniter\API\ResponseTrait;

class CartController extends BaseController
{
    use ResponseTrait;
    protected $orderModel;
    protected $orderItemModel;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->orderItemModel = new OrderItemModel();
    }

    public function addToCart()
    {
        $decoded = $this->decodedToken();
        $customerId = $decoded->user_id;

        if (!$customerId) {
            return $this->respond([
                'status' => 401,
                'message' => 'Please login first to add items to the cart.'
            ], 401);
        }

        $productId = $this->request->getVar('product_id') ?? null;
        $quantity = $this->request->getVar('quantity') ?? 1;
        $price = $this->request->getVar('price') ?? '0';
        $shipping_cost = 20000;

        if (!$productId || !$price) {
            return $this->respond([
                'status' => 400,
                'message' => 'Product ID and price are required.'
            ], 400);
        }

        // Check for existing 'cart' order
        $order = $this->orderModel
            ->where('customer_id', $customerId)
            ->where('status', 'cart')
            ->first();

        if (!$order) {
            $orderData = [
                'customer_id' => $customerId,
                'order_date' => date('Y-m-d H:i:s'),
                'grand_total' => 0,
                'total_price' => 0,
                'shipping_costs' => $shipping_cost,
                'proof_of_payment' => null,
                'status' => 'cart',
            ];
            $orderId = $this->orderModel->insert($orderData);
        } else {
            $orderId = $order['order_id'];
        }

        // Insert item
        $orderItemData = [
            'order_id' => $orderId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'price' => $price
        ];
        $this->orderItemModel->insert($orderItemData);

        // Update total_price
        $totalPrice = $this->orderItemModel
            ->select('SUM(quantity * price) AS total')
            ->where('order_id', $orderId)
            ->get()
            ->getRow()
            ->total;

        $this->orderModel->update($orderId, [
            'total_price' => $totalPrice,
            'grand_total' => $totalPrice + $shipping_cost
        ]);

        return $this->respond([
            'status' => 200,
            'message' => 'Product added to cart.',
            'data' => [
                'order_id' => $orderId,
                'total_price' => $totalPrice
            ]
        ], 200);
    }

    public function getCart()
    {
        $decoded = $this->decodedToken();
        $customerId = $decoded->user_id;

        if (!$customerId) {
            return $this->respond([
                'status' => 401,
                'message' => 'Please login first to view the cart.'
            ], 401);
        }

        $order = $this->orderModel
            ->where('customer_id', $customerId)
            ->where('status', 'cart')
            ->first();

        if (!$order) {
            return $this->respond([
                'status' => 404,
                'message' => 'No cart found.'
            ], 404);
        }

        $orderItems = $this->orderItemModel
            ->where('order_id', $order['order_id'])
            ->join('products', 'products.product_id = order_items.product_id')
            ->findAll();

        return $this->respond([
            'status' => 200,
            'message' => 'Cart retrieved successfully.',
            'data' => [
                'order_id' => $order['order_id'],
                'shipping_costs' => $order['shipping_costs'],
                'total_price' => $order['total_price'],
                'grand_total' => $order['grand_total'],
                'items' => $orderItems,
            ]
        ], 200);
    }

    public function getTotalCart()
    {
        $decoded = $this->decodedToken();
        $customerId = $decoded->user_id;

        if (!$customerId) {
            return $this->respond([
                'status' => 401,
                'message' => 'Please login first to view the total cart quantity.'
            ], 401);
        }

        // Ambil order dengan status cart
        $order = $this->orderModel
            ->where('customer_id', $customerId)
            ->where('status', 'cart')
            ->first();

        if (!$order) {
            // Tidak ada cart, tetap return sukses dengan 0 item
            return $this->respond([
                'status' => 200,
                'message' => 'No cart found.',
                'data' => [
                    'order_id' => null,
                    'total_items' => 0
                ]
            ]);
        }

        // Hitung total item di order tersebut
        $totalItems = $this->orderItemModel
            ->select('SUM(quantity) AS total_items')
            ->where('order_id', $order['order_id'])
            ->get()
            ->getRow()
            ->total_items;

        return $this->respond([
            'status' => 200,
            'message' => 'Total cart quantity retrieved successfully.',
            'data' => [
                'order_id' => $order['order_id'],
                'total_items' => (int) $totalItems
            ]
        ]);
    }

    public function deleteCartItem($itemId)
    {
        $decoded = $this->decodedToken();
        $customerId = $decoded->user_id;

        if (!$customerId) {
            return $this->respond([
                'status' => 401,
                'message' => 'Please login first to delete items from the cart.'
            ], 401);
        }

        // Ambil item yang akan dihapus
        $item = $this->orderItemModel->find($itemId);

        if (!$item) {
            return $this->respond([
                'status' => 404,
                'message' => 'Item not found.'
            ], 404);
        }

        // Ambil order cart milik user
        $order = $this->orderModel
            ->where('order_id', $item['order_id'])
            ->where('customer_id', $customerId)
            ->where('status', 'cart')
            ->first();

        if (!$order) {
            return $this->respond([
                'status' => 403,
                'message' => 'Unauthorized access to cart.'
            ], 403);
        }

        // Hapus item dari order_items
        $this->orderItemModel->delete($itemId);

        // Hitung ulang total_price
        $totalPrice = $this->orderItemModel
            ->select('SUM(quantity * price) AS total')
            ->where('order_id', $order['order_id'])
            ->get()
            ->getRow()
            ->total ?? 0;

        // Update total_price di order
        $this->orderModel->update($order['order_id'], ['total_price' => $totalPrice]);

        return $this->respond([
            'status' => 200,
            'message' => 'Item deleted and cart updated.',
            'data' => [
                'order_id' => $order['order_id'],
                'total_price' => $totalPrice
            ]
        ]);
    }
}

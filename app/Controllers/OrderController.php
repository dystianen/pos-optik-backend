<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\OrderItemModel;
use App\Models\OrderModel;
use CodeIgniter\API\ResponseTrait;

class OrderController extends BaseController
{
    use ResponseTrait;
    protected $orderModel;
    protected $orderItemModel;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->orderItemModel = new OrderItemModel();
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

        // Validasi input
        $shippingAddress = $this->request->getVar('shipping_address');
        $shippingCost = 20000;

        if (!$shippingAddress) {
            return $this->respond(['status' => 400, 'message' => 'Incomplete checkout data'], 400);
        }

        // Hitung total final: total_price + shipping_cost
        $finalTotal = $order['total_price'] + $shippingCost;

        // Update order
        $this->orderModel->update($order['order_id'], [
            'address' => $shippingAddress,
            'proof_of_payment' => null,
            'order_date' => date('Y-m-d H:i:s'),
            'status' => 'pending',
        ]);

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
            'status' => 'paid',
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
}

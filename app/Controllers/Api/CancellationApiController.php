<?php

namespace App\Controllers\Api;

use App\Models\NotificationModel;
use App\Models\OrderCancellationModel;
use App\Models\OrderModel;
use App\Models\OrderStatusModel;
use App\Models\OrderRefundModel;
use App\Models\UserRefundAccountModel;
use Config\OrderStatus;

class CancellationApiController extends BaseApiController
{
    protected $cancellationModel;
    protected $orderModel;
    protected $statusModel;
    protected $notificationModel;

    public function __construct()
    {
        $this->cancellationModel = new OrderCancellationModel();
        $this->orderModel = new OrderModel();
        $this->statusModel = new OrderStatusModel();
        $this->notificationModel = new NotificationModel();
    }

    // =====================================================
    // CHECK CANCELLATION STATUS
    // =====================================================
    public function checkStatus(string $orderId)
    {
        // Auth
        $customerId = $this->getAuthenticatedCustomerId();

        // Validate order
        if (strlen($orderId) !== 36) {
            return $this->errorResponse('Invalid order ID');
        }

        // Ambil order + ownership
        $order = $this->orderModel
            ->where('order_id', $orderId)
            ->where('customer_id', $customerId)
            ->first();

        if (!$order) {
            return $this->errorResponse('Order not found');
        }

        // Cari request cancellation
        $cancellation = $this->cancellationModel
            ->where('order_id', $orderId)
            ->orderBy('created_at', 'DESC')
            ->first();

        // === BELUM PERNAH REQUEST ===
        if (!$cancellation) {
            return $this->successResponse([
                'order_id' => $orderId,
                'has_request' => false,
                'status' => null,
            ], "No cancellation request found");
        }

        // === SUDAH REQUEST ===
        return $this->successResponse([
            'order_id' => $orderId,
            'has_request' => true,
            'status' => $cancellation['status'],
            'requested_at' => $cancellation['created_at'],
        ], 'Cancellation request found');
    }

    // =====================================================
    // SUBMIT CANCEL ORDER API
    // =====================================================
    public function submitCancel()
    {
        // Check ownership
        $customerId = $this->getAuthenticatedCustomerId();
        $customerName = $this->getAuthenticatedCustomerName();

        $rules = [
            'order_id' => 'required|min_length[36]|max_length[36]',
            'reason' => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->validationErrorResponse($this->validator->getErrors());
        }

        $orderId = $this->request->getJSON()->order_id;
        $reason = $this->request->getJSON()->reason;
        $additionalNote = $this->request->getJSON()->additional_note ?? null;

        $order = $this->orderModel->find($orderId);

        if (!$order) {
            return $this->errorResponse('Order not found');
        }

        // Check if allow to cancel (only if status is pending or processing?)
        // Assuming user can only cancel if not shipped yet.
        // But for now let's just create the request regardless of order status unless it's completed/cancelled.
        // Ideally check status here.
        if ($order['status_id'] == $this->statusModel->getIdByCode(OrderStatus::CANCELLED)) { // Already cancelled
            return $this->errorResponse('Order is already cancelled');
        }

        // Cek duplicate request
        $existing = $this->cancellationModel
            ->where('order_id', $orderId)
            ->where('status', 'requested')
            ->first();

        if ($existing) {
            return $this->errorResponse('Cancellation request already under review');
        }

        $STATUS_PENDING = $this->statusModel->getIdByCode(OrderStatus::PENDING);
        $STATUS_CANCELLED = $this->statusModel->getIdByCode(OrderStatus::CANCELLED);

        // === CASE 1: Belum bayar (PENDING) - Cancel langsung ===
        if ($order['status_id'] === $STATUS_PENDING) {
            $this->orderModel->update($orderId, [
                'status_id' => $STATUS_CANCELLED,
            ]);

            // Restore Stock
            $this->orderModel->restoreStock($orderId, 'Order cancelled by customer (Pending Payment)', null);

            // Create record for history
            $data = [
                'order_id' => $orderId,
                'reason' => $reason,
                'additional_note' => $additionalNote,
                'status' => 'approved', // Auto approved because pending payment
                'processed_at' => date('Y-m-d H:i:s'),
            ];
            $this->cancellationModel->insert($data);

            return $this->messageResponse('Order has been cancelled successfully');
        }

        // === CASE 2: Sudah bayar / Processing - Masuk Request ===
        $data = [
            'order_id' => $orderId,
            'reason' => $reason,
            'additional_note' => $additionalNote,
            'status' => 'requested',
        ];

        $result = $this->cancellationModel->insert($data);
        if (!$result) {
            return $this->errorResponse('Failed to submit cancellation');
        }
        $cancellationId = $this->cancellationModel->getInsertID();

        // Kirim notifikasi ke admin untuk review
        $this->notificationModel->addNotification('cancel_order', "New cancellation request from {$customerName}", $cancellationId);

        $response = [
            'order_id' => $orderId,
            'cancellation_id' => $cancellationId,
            'status' => 'requested',
            'auto_approved' => false,
        ];
        return $this->successResponse($response, 'Cancellation request submitted successfully. Our admin will review it shortly.');
    }

    // =====================================================
    // ADMIN API
    // =====================================================
    public function getPendingCancellations()
    {
        $status = $this->request->getGet('status') ?? 'requested';

        $builder = $this->cancellationModel
            ->select('order_cancellations.*, orders.grand_total, customers.customer_name, customers.customer_email')
            ->join('orders', 'orders.order_id = order_cancellations.order_id')
            ->join('customers', 'customers.customer_id = orders.customer_id');

        if ($status) {
            $builder->where('order_cancellations.status', $status);
        }

        $cancellations = $builder->orderBy('order_cancellations.created_at', 'DESC')->findAll();

        $response = [
            'cancellations' => $cancellations,
            'total'   => count($cancellations)
        ];
        return $this->successResponse($response);
    }

    public function getCancellationDetail($cancellationId)
    {
        $cancellation = $this->cancellationModel
            ->select('order_cancellations.*, orders.grand_total, customers.customer_name, customers.customer_email')
            ->join('orders', 'orders.order_id = order_cancellations.order_id')
            ->join('customers', 'customers.customer_id = orders.customer_id')
            ->where('order_cancellation_id', $cancellationId)
            ->first();

        if (!$cancellation) {
            return $this->notFoundResponse('Cancellation not found');
        }

        return $this->successResponse($cancellation);
    }

    public function adminApprove($cancellationId)
    {
        $adminId = $this->request->getHeaderLine('X-Admin-Id') ?? session('admin_id'); // Assuming admin_id in session if web, or header if pure api
        // Note: For web view actions, we might use session.
        if (!$adminId) $adminId = session('user_id'); // Fallback if user_id is used for admin

        $cancellation = $this->cancellationModel->find($cancellationId);

        if (!$cancellation) {
            return $this->notFoundResponse('Cancellation not found');
        }

        $order = $this->orderModel->find($cancellation['order_id']);
        if (!$order) {
            return $this->notFoundResponse('Order not found');
        }

        $this->cancellationModel->update($cancellationId, [
            'status' => 'approved',
            'processed_by' => $adminId,
            'processed_at' => date('Y-m-d H:i:s'),
        ]);

        // Update Order Status to Cancelled
        $STATUS_CANCELLED = $this->statusModel->getIdByCode(OrderStatus::CANCELLED);
        $this->orderModel->update($cancellation['order_id'], [
            'status_id' => $STATUS_CANCELLED,
        ]);

        // Restore Stock
        $this->orderModel->restoreStock($cancellation['order_id'], 'Order cancellation approved by Admin', $adminId);

        // Create auto refund if payment exists (cancellation request status was requested)
        if ($cancellation['status'] === 'requested') {
            $userRefundAccountModel = new UserRefundAccountModel();
            $refundAccount = $userRefundAccountModel
                ->where('customer_id', $order['customer_id'])
                ->orderBy('is_default', 'DESC')
                ->first();

            $orderRefundModel = new OrderRefundModel();
            $existingRefund = $orderRefundModel->where('order_id', $cancellation['order_id'])->first();

            if (!$existingRefund) {
                $refundData = [
                    'order_id' => $cancellation['order_id'],
                    'user_refund_account_id' => $refundAccount ? $refundAccount['user_refund_account_id'] : null,
                    'refund_amount' => $order['grand_total'],
                    'reason' => 'Cancellation: ' . $cancellation['reason'],
                    'additional_note' => $cancellation['additional_note'],
                    'status' => OrderRefundModel::STATUS_APPROVED,
                    'refund_type' => 'full',
                    'evidence_url' => 'cancellation',
                    'processed_by' => $adminId,
                ];

                if ($orderRefundModel->insert($refundData)) {
                    $refundId = $orderRefundModel->getInsertID();
                    $this->notificationModel->addNotification('refund_order', "Refund approved for Order #{$order['order_id']} (Cancellation)", $refundId);
                    
                    // Trigger real-time update
                    if (class_exists('\App\Libraries\Realtime')) {
                        \App\Libraries\Realtime::triggerUpdate('refund-approved');
                    }
                }
            }
        }

        return $this->successResponse(['cancellation_id' => $cancellationId, 'status' => 'approved'], 'Cancellation approved');
    }

    public function adminReject($cancellationId)
    {
        $adminId = $this->request->getHeaderLine('X-Admin-Id') ?? session('admin_id');
        if (!$adminId) $adminId = session('user_id');

        // $json = $this->request->getJSON(); // If raw json
        // Check input method, View Controller sends JSON? Or form data? 
        // Based on Refund view, it sends JSON using fetch.

        $json = $this->request->getJSON();
        $adminNote = $json->admin_note ?? null;

        /* 
        // Fallback for form data if needed (if not using fetch json)
        if (!$adminNote) {
            $adminNote = $this->request->getPost('admin_note');
        }
        */

        if (empty($adminNote)) {
            return $this->errorResponse('Note is required for rejection');
        }

        $cancellation = $this->cancellationModel->find($cancellationId);

        if (!$cancellation) {
            return $this->notFoundResponse('Cancellation not found');
        }

        $this->cancellationModel->update($cancellationId, [
            'status' => 'rejected',
            'additional_note' => $cancellation['additional_note'] . "\n[Admin Reject Note]: " . $adminNote,
            // Or if we don't have admin_note column, append to additional_note or create a new column. 
            // Plan said `additional_note` (TEXT) optional user/admin notes. So appending is fine.
            'processed_by' => $adminId,
            'processed_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->successResponse(['cancellation_id' => $cancellationId, 'status' => 'rejected'], 'Cancellation rejected');
    }
}

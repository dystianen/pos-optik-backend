<?php

namespace App\Controllers\Api;

use App\Models\NotificationModel;
use App\Models\OrderRefundModel;
use App\Models\OrderRefundItemModel;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\OrderStatusModel;
use App\Models\UserRefundAccountModel;
use Config\OrderStatus;

use App\Libraries\R2Storage;

class RefundApiController extends BaseApiController
{
  protected $refundModel;
  protected $orderModel;
  protected $orderItemModel;
  protected $userRefundAccountModel;
  protected $statusModel;
  protected $notificationModel;
  protected $refundItemModel;
  protected $r2;

  public function __construct()
  {
    $this->refundModel = new OrderRefundModel();
    $this->orderModel = new OrderModel();
    $this->orderItemModel = new OrderItemModel();
    $this->userRefundAccountModel = new UserRefundAccountModel();
    $this->statusModel = new OrderStatusModel();
    $this->notificationModel = new NotificationModel();
    $this->refundItemModel = new OrderRefundItemModel();
    $this->r2 = new R2Storage();
  }

  // =====================================================
  // UNIFIED STATUS CHECK (CANCEL / REFUND)
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

    $refund = $this->refundModel
      ->select('order_refund_id, status, refund_amount, created_at')
      ->where('order_id', $orderId)
      ->orderBy('created_at', 'DESC')
      ->first();

    // === BELUM PERNAH REQUEST ===
    if (!$refund) {
      return $this->successResponse([
        'order_id' => $orderId,
        'has_request' => false,
        'status' => null,
      ], "No refund request found");
    }

    // === SUDAH REQUEST ===
    return $this->successResponse([
      'order_id' => $orderId,
      'refund_id' => $refund['order_refund_id'],
      'has_request' => true,
      'status' => $refund['status'],
      'refund_amount' => $refund['refund_amount'],
      'requested_at' => $refund['created_at'],
    ], 'Refund Request found');
  }

  // =====================================================
  // CANCEL ORDER API
  // =====================================================
  public function submitCancel()
  {
    // Check ownership
    $customerId = $this->getAuthenticatedCustomerId();
    $customerName = $this->getAuthenticatedCustomerName();

    if (!$this->validate($this->refundModel->validationRules)) {
      return $this->validationErrorResponse($this->validator->getErrors(), 'Validation failed');
    }

    $orderId = $this->request->getJSON()->order_id;
    $reason = $this->request->getJSON()->reason;
    $additionalNote = $this->request->getJSON()->additional_note;

    $order = $this->orderModel->find($orderId);

    if (!$order) {
      return $this->errorResponse('Order not found');
    }

    $STATUS_PENDING = $this->statusModel->getIdByCode(OrderStatus::PENDING);
    $STATUS_CANCELLED = $this->statusModel->getIdByCode(OrderStatus::CANCELLED);

    // === CASE 1: Belum bayar - Cancel langsung tanpa refund ===
    if ($order['status_id'] === $STATUS_PENDING) {
      $this->orderModel->update($orderId, [
        'status_id' => $STATUS_CANCELLED,
      ]);

      // 🔥 TRIGGER REAL-TIME UPDATE
      \App\Libraries\Realtime::triggerUpdate('order-cancelled');

      return $this->messageResponse('Order has been cancelled successfully');
    } else {
      // === CASE 2: Sudah bayar - Perlu proses refund ===
      $refundAccount = $this->userRefundAccountModel
        ->select('user_refund_account_id')
        ->where('customer_id', $customerId)
        ->first();
      log_message('debug', $refundAccount['user_refund_account_id']);

      if (!$refundAccount) {
        return $this->errorResponse(
          'User refund account is required because the order has already been paid'
        );
      }

      $data = [
        'order_id' => $orderId,
        'user_refund_account_id' => $refundAccount['user_refund_account_id'],
        'refund_amount' => $order['grand_total'],
        'reason' => $reason,
        'additional_note' => $additionalNote,
      ];

      $result = $this->refundModel->insert($data);
      if (!$result) {
        return $this->errorResponse($result['errors'] ?? null,  $result['message']);
      }
      $refundId = $this->refundModel->getInsertID();

      // Kirim notifikasi ke admin untuk review
      $this->notificationModel->addNotification('cancel_order', "New cancellation request from {$customerName}", $refundId);

      // 🔥 TRIGGER REAL-TIME UPDATE
      \App\Libraries\Realtime::triggerUpdate('cancellation-requested');

      $response = [
        'order_id' => $orderId,
        'refund_id' => $refundId,
        'status' => 'cancellation_requested',
        'refund_status' => 'requested',
        'refund_amount' => $order['grand_total'],
        'auto_approved' => false,
      ];
      return $this->successResponse($response, 'Cancellation request submitted successfully. Our admin will review it shortly.');
    }
  }

  // =====================================================
  // REFUND ORDER API
  // =====================================================
  public function submitRefund()
  {
    // Rules validation
    $rules = [
      'order_id' => 'required|min_length[36]|max_length[36]',
      'refund_type' => 'required|in_list[full,partial]',
      'refund_amount' => 'required|decimal|greater_than[0]',
      'reason' => 'required|min_length[10]',
      'user_refund_account_id' => 'required',
      'evidence' => [
        'rules' => 'uploaded[evidence]|mime_in[evidence,image/jpg,image/jpeg,image/png,image/webp,video/mp4,video/webm,video/ogg,video/quicktime]|max_size[evidence,51200]',
        'label' => 'Evidence (Image/Video)'
      ]
    ];

    if (!$this->validate($rules)) {
      return $this->validationErrorResponse($this->validator->getErrors());
    }

    // Get Post Data (FormData)
    $orderId = $this->request->getPost('order_id');
    $refundType = $this->request->getPost('refund_type');
    $refundAmount = $this->request->getPost('refund_amount');
    $reason = $this->request->getPost('reason');
    $userRefundAccountId = $this->request->getPost('user_refund_account_id');
    $additionalNote = $this->request->getPost('additional_note');
    
    // Handle selected items (parsed from JSON string if sent as string in FormData)
    $selectedItems = $this->request->getPost('selected_items');
    if (is_string($selectedItems)) {
        $selectedItems = json_decode($selectedItems, true) ?? [];
    } elseif (!is_array($selectedItems)) {
        $selectedItems = [];
    }

    $order = $this->orderModel
      ->select('orders.*, order_statuses.status_code')
      ->join('order_statuses', 'order_statuses.status_id = orders.status_id', 'left')
      ->find($orderId);

    if (!$order) {
      return $this->notFoundResponse('Order not found');
    }

    // Check ownership
    $this->getAuthenticatedUser();

    // Check if order eligible for refund
    if (!$this->isEligibleForRefund($order)) {
      return $this->errorResponse('Order tidak eligible untuk refund');
    }

    // Validasi refund amount
    $maxRefundAmount = $this->calculateMaxRefundAmount($orderId, $refundType, $selectedItems);

    if ($refundAmount > $maxRefundAmount) {
      return $this->errorResponse('Jumlah refund melebihi maksimal yang diperbolehkan. Max: ' . $maxRefundAmount . ', Requested: ' . $refundAmount);
    }

    // --- HANDLE FILE UPLOAD ---
    $file = $this->request->getFile('evidence');
    $evidenceUrl = null;

    if ($file && $file->isValid() && !$file->hasMoved()) {
        $evidenceUrl = $this->r2->uploadFile(
            $file->getTempName(),
            $file->getRandomName()
        );

        if (!$evidenceUrl) {
            return $this->errorResponse('Failed to upload evidence image');
        }
    } else {
        return $this->errorResponse('Evidence image is required and must be valid');
    }

    // Create refund request
    $data = [
      'order_id' => $orderId,
      'refund_type' => $refundType,
      'user_refund_account_id' => $userRefundAccountId,
      'refund_amount' => $refundAmount,
      'reason' => $reason,
      'additional_note' => $additionalNote,
      'evidence_url' => $evidenceUrl,
      'status' => 'requested',
    ];

    $result = $this->refundModel->createRefund($data);

    if (!$result['success']) {
      return $this->errorResponse($result['message'], $result['errors'] ?? null);
    }

    $refundId = $result['refund_id'];

    // Kirim notifikasi ke admin untuk review
    $customerName = $this->getAuthenticatedCustomerName();
    $this->notificationModel->addNotification('refund_order', "New refund request from {$customerName}", $refundId);

    // Jika partial refund, insert items ke order_refund_items table
    $itemsCreated = 0;
    if ($refundType === 'partial' && !empty($selectedItems)) {
      log_message('debug', 'Creating refund items - refund_id: ' . $refundId . ', items count: ' . count($selectedItems));
      $itemsCreated = $this->createRefundItems($refundId, $selectedItems, $refundAmount);
    } else {
      log_message('debug', 'Skipping refund items - refund_type: ' . $refundType . ', selectedItems: ' . json_encode($selectedItems));
    }

    $response = [
      'order_id' => $orderId,
      'refund_id' => $refundId,
      'refund_type' => $refundType,
      'refund_amount' => $refundAmount,
      'evidence_url' => $evidenceUrl,
      'status' => 'requested',
      'refund_items_created' => $itemsCreated,
      'selected_items_count' => count($selectedItems),
    ];

    // 🔥 TRIGGER REAL-TIME UPDATE
    \App\Libraries\Realtime::triggerUpdate('refund-requested');

    return $this->successResponse($response);
  }

  public function submitReturnShipping()
  {
    $rules = [
        'refund_id' => 'required',
        'courier' => 'required',
        'tracking_number' => 'required',
    ];

    if (!$this->validate($rules)) {
        return $this->validationErrorResponse($this->validator->getErrors());
    }

    // Get input (handling both Form-Data and JSON)
    $input = $this->request->getJSON(true);
    $refundId = $input['refund_id'] ?? null;
    $courier = $input['courier'] ?? null;
    $trackingNumber = $input['tracking_number'] ?? null;

    if (!$refundId) {
        return $this->errorResponse('Refund ID is required');
    }

    $refund = $this->refundModel->find($refundId);
    if (!$refund) {
        return $this->notFoundResponse('Refund request not found');
    }

    // Since find() can return a list if passed an array, ensure we have the associative array
    if (isset($refund[0]) && is_array($refund[0])) {
        $refund = $refund[0];
    }

    // Ensure status is return_approved
    if ($refund['status'] !== OrderRefundModel::STATUS_RETURN_APPROVED) {
        return $this->errorResponse('Refund status is not eligible for shipping info update');
    }

    if (!$this->refundModel->markReturnShipped($refundId, $courier, $trackingNumber)) {
        return $this->errorResponse('Failed to update shipping info');
    }

    return $this->successResponse([
        'refund_id' => $refundId,
        'status' => OrderRefundModel::STATUS_RETURN_SHIPPED,
        'courier' => $courier,
        'tracking_number' => $trackingNumber
    ], 'Return shipping information submitted successfully');

    // 🔥 TRIGGER REAL-TIME UPDATE
    \App\Libraries\Realtime::triggerUpdate('refund-return-shipped');
  }

  // =====================================================
  // ADMIN API
  // =====================================================
  public function getRefundDetail($refundId)
  {
    $refund = $this->refundModel->withAll()->find($refundId);

    if (!$refund) {
      return $this->notFoundResponse('Refund not found');
    }

    return $this->successResponse($refund);
  }

  public function getPendingRefunds()
  {
    $status = $this->request->getGet('status') ?? 'pending';

    $builder = $this->refundModel->withAll();

    if ($status) {
      $builder->where('order_refunds.status', $status);
    }

    $refunds = $builder->orderBy('order_refunds.created_at', 'DESC')->findAll();

    $response = [
      'refunds' => $refunds,
      'total'   => count($refunds)
    ];
    return $this->successResponse($response);
  }

  public function adminApprove($refundId)
  {
    $adminId = session()->get('id');
    $json = $this->request->getJSON();
    $adminNote = $json->admin_note ?? null;
    $adjustedAmount = $json->adjusted_amount ?? null;

    $refund = $this->refundModel->find($refundId);

    if (!$refund) {
      return $this->notFoundResponse('Refund not found');
    }

    // Jika admin adjust amount
    if ($adjustedAmount && $adjustedAmount != $refund['refund_amount']) {
      $this->refundModel->update($refundId, [
        'refund_amount' => $adjustedAmount,
      ]);
    }

    // Approve refund
    if (!$this->refundModel->markReturnApproved($refundId, $adminId, $adminNote)) {
      return $this->errorResponse('Return approve refund failed');
    }

    $response = [
      'refund_id' => $refundId,
      'order_id' => $refund['order_id'],
      'status' => 'return_approved',
      'refund_amount' => $adjustedAmount ?? $refund['refund_amount'],
    ];

    return $this->successResponse($response, 'Refund approve successfully');

    // 🔥 TRIGGER REAL-TIME UPDATE
    \App\Libraries\Realtime::triggerUpdate('refund-approved');
  }

  public function adminReject($refundId)
  {
    $adminId = session()->get('id');
    $json = $this->request->getJSON();
    $adminNote = $json->admin_note ?? null;

    if (empty($adminNote)) {
      return $this->errorResponse('Note is required!');
    }

    $refund = $this->refundModel->find($refundId);

    if (!$refund) {
      return $this->notFoundResponse('Refund not found');
    }

    if (!$this->refundModel->markRejected($refundId, $adminId, $adminNote)) {
      return $this->errorResponse('Reject refund failed');
    }

    $response = [
      'refund_id' => $refundId,
      'order_id' => $refund['order_id'],
      'status' => 'rejected',
      'admin_note' => $adminNote,
    ];
    return $this->successResponse($response, 'Refund reject successfully');

    // 🔥 TRIGGER REAL-TIME UPDATE
    \App\Libraries\Realtime::triggerUpdate('refund-rejected');
  }

  public function adminReceive($refundId)
  {
    $adminId = session()->get('id');
    $json = $this->request->getJSON();
    $adminNote = $json->admin_note ?? null;

    $refund = $this->refundModel->find($refundId);
    if (!$refund) {
        return $this->notFoundResponse('Refund not found');
    }

    if (!$this->refundModel->markReturnReceived($refundId, $adminId, $adminNote)) {
        return $this->errorResponse('Failed to mark as return received');
    }

    return $this->successResponse([
        'refund_id' => $refundId,
        'status' => OrderRefundModel::STATUS_RETURN_RECEIVED
    ], 'Refund item marked as received');

    // 🔥 TRIGGER REAL-TIME UPDATE
    \App\Libraries\Realtime::triggerUpdate('refund-item-received');
  }

  public function adminFinalApprove($refundId)
  {
    $adminId = session()->get('id');
    $json = $this->request->getJSON();
    $adminNote = $json->admin_note ?? null;

    $refund = $this->refundModel->find($refundId);
    if (!$refund) {
        return $this->notFoundResponse('Refund not found');
    }

    if (!$this->refundModel->markApproved($refundId, $adminId, $adminNote)) {
        return $this->errorResponse('Final approval failed');
    }

    return $this->successResponse([
        'refund_id' => $refundId,
        'status' => OrderRefundModel::STATUS_APPROVED
    ], 'Refund approved. You can now proceed to process the payment.');

    // 🔥 TRIGGER REAL-TIME UPDATE
    \App\Libraries\Realtime::triggerUpdate('refund-final-approved');
  }

  public function adminRefund($refundId)
  {
    $adminId = session()->get('id');
    $json = $this->request->getJSON();
    $adminNote = $json->admin_note ?? null;

    $refund = $this->refundModel->find($refundId);
    if (!$refund) {
        return $this->notFoundResponse('Refund not found');
    }

    if (!$this->refundModel->markRefunded($refundId, $adminId, $adminNote)) {
        return $this->errorResponse('Marking as refunded failed');
    }

    // Update order status based on remaining items
    try {
        $orderId = $refund['order_id'];
        
        // 1. Get total items purchased
        $totalItemsPurchased = $this->orderItemModel
            ->where('order_id', $orderId)
            ->where('deleted_at', null)
            ->selectSum('quantity', 'total')
            ->get()
            ->getRow()
            ->total ?? 0;

        // 2. Get total items already refunded (including this one)
        $totalItemsRefunded = $this->db->table('order_refund_items ori')
            ->join('order_refunds orf', 'orf.order_refund_id = ori.order_refund_id')
            ->where('orf.order_id', $orderId)
            ->where('orf.status', OrderRefundModel::STATUS_REFUNDED)
            ->selectSum('ori.qty_refunded', 'total')
            ->get()
            ->getRow()
            ->total ?? 0;

        // 3. Determine Status
        if ($refund['refund_type'] === 'full') {
            $statusCode = OrderStatus::REFUNDED;
        } else {
            $statusCode = ($totalItemsRefunded >= $totalItemsPurchased) 
                ? OrderStatus::REFUNDED 
                : OrderStatus::PARTIALLY_REFUNDED;
        }

        $statusId = $this->statusModel->getIdByCode($statusCode);
        $this->orderModel->update($orderId, [
            'status_id' => $statusId,
        ]);

        $statusLabel = ($statusCode === OrderStatus::REFUNDED) ? 'Fully Refunded' : 'Partially Refunded';

    } catch (\Exception $e) {
        log_message('error', 'Failed to update order status during refund: ' . $e->getMessage());
        $statusLabel = 'Refunded (Status update failed)';
    }

    return $this->successResponse([
        'refund_id' => $refundId,
        'status' => OrderRefundModel::STATUS_REFUNDED,
        'order_status' => $statusCode ?? null
    ], 'Refund marked as completed and order status updated to ' . $statusLabel);

    // 🔥 TRIGGER REAL-TIME UPDATE
    \App\Libraries\Realtime::triggerUpdate('refund-completed');
  }

  // =====================================================
  // HELPER METHODS
  // =====================================================

  private function isEligibleForRefund($order): bool
  {
    $eligibleStatuses = ['shipped', 'completed'];
    if (!in_array($order['status_code'], $eligibleStatuses)) {
      return false;
    }

    // Check refund period (7 days)
    // if (!empty($order['delivered_at'])) {
    //   $deliveredDate = strtotime($order['delivered_at']);
    //   $currentDate = time();
    //   $daysDiff = ($currentDate - $deliveredDate) / (60 * 60 * 24);

    //   if ($daysDiff > 7) {
    //     return false;
    //   }
    // }

    return true;
  }

  private function calculateMaxRefundAmount($orderId, $refundType, $selectedItems = [])
  {
    $order = $this->orderModel->find($orderId);

    if ($refundType === 'full') {
      return $order['grand_total'] ?? 0;
    }

    if (!empty($selectedItems)) {
      $items = $this->orderItemModel->whereIn('order_item_id', $selectedItems)->findAll();

      // Calculate subtotal as quantity * price
      $total = 0;
      foreach ($items as $item) {
        $subtotal = ($item['quantity'] ?? 0) * ($item['price'] ?? 0);
        $total += $subtotal;
      }

      return $total;
    }

    return 0;
  }

  private function createRefundItems($refundId, $selectedItemIds, $totalRefundAmount)
  {
    $itemsCreated = 0;

    if (empty($selectedItemIds)) {
      log_message('debug', 'createRefundItems: selectedItemIds is empty');
      return $itemsCreated;
    }

    log_message('debug', 'createRefundItems START - refund_id: ' . $refundId . ', items: ' . json_encode($selectedItemIds));

    // Fetch selected order items
    $items = $this->orderItemModel->whereIn('order_item_id', $selectedItemIds)->findAll();

    if (empty($items)) {
      log_message('warning', 'createRefundItems: No items found for ids: ' . json_encode($selectedItemIds));
      return $itemsCreated;
    }

    log_message('debug', 'Found ' . count($items) . ' items to refund');

    // Calculate refund amount per item proportionally
    // Since order_items doesn't have subtotal field, calculate it as quantity * price
    $totalItemAmount = 0;
    $itemsWithSubtotal = [];
    foreach ($items as $item) {
      $itemSubtotal = ($item['quantity'] ?? 0) * ($item['price'] ?? 0);
      $itemsWithSubtotal[$item['order_item_id']] = $itemSubtotal;
      $totalItemAmount += $itemSubtotal;
    }

    if ($totalItemAmount <= 0) {
      log_message('warning', 'createRefundItems: Total item amount is 0 or negative');
      return $itemsCreated;
    }

    foreach ($items as $item) {
      // Calculate refund amount for this item proportionally
      $itemSubtotal = $itemsWithSubtotal[$item['order_item_id']];
      $itemRefundAmount = ($itemSubtotal / $totalItemAmount) * $totalRefundAmount;

      $refundItemData = [
        'order_refund_id' => $refundId,
        'order_item_id' => $item['order_item_id'],
        'qty_refunded' => $item['quantity'],
        'price_per_item' => $item['price'],
        'subtotal_refunded' => $itemRefundAmount,
      ];

      log_message('debug', 'Inserting refund item: ' . json_encode($refundItemData));

      $result = $this->refundItemModel->insert($refundItemData);

      if (!$result) {
        log_message('error', 'Failed to insert refund item: ' . json_encode($this->refundItemModel->errors()));
      } else {
        $itemsCreated++;
      }
    }

    log_message('debug', 'createRefundItems DONE - refund_id: ' . $refundId . ', items created: ' . $itemsCreated);
    return $itemsCreated;
  }
}

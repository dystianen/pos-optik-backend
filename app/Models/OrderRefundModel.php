<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderRefundModel extends Model
{
    protected $table            = 'order_refunds';
    protected $primaryKey       = 'order_refund_id';
    protected $useAutoIncrement = false;

    protected $returnType     = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'order_refund_id',
        'order_id',
        'refund_type',
        'user_refund_account_id',
        'refund_amount',
        'reason',
        'additional_note',
        'status',
        'evidence_url',
        'admin_note',
        'processed_by',
        'completed_at',
        'return_courier',
        'return_tracking_number',
        'return_shipped_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // =====================
    // STATUS CONSTANTS
    // =====================
    public const STATUS_REQUESTED        = 'requested';
    public const STATUS_REQUEST_REJECTED = 'request_rejected';
    public const STATUS_RETURN_APPROVED  = 'return_approved';
    public const STATUS_RETURN_SHIPPED   = 'return_shipped';
    public const STATUS_RETURN_RECEIVED  = 'return_received';
    public const STATUS_RETURN_REJECTED  = 'return_rejected';
    public const STATUS_APPROVED         = 'approved';
    public const STATUS_REFUNDED         = 'refunded';
    public const STATUS_EXPIRED          = 'expired';

    // =====================
    // CALLBACKS
    // =====================
    protected $beforeInsert = ['generateUUID'];
    protected $beforeUpdate = [];

    protected function generateUuid(array $data)
    {
        $data['data']['order_refund_id'] = service('uuid')->uuid4()->toString();
        return $data;
    }

    // =====================
    // RELATIONSHIP METHODS
    // =====================
    public function withOrder()
    {
        return $this->select('order_refunds.*, orders.*')
            ->join('orders', 'orders.order_id = order_refunds.order_id', 'left');
    }

    public function withRefundAccount()
    {
        return $this->select('order_refunds.*, user_refund_accounts.*')
            ->join('user_refund_accounts', 'user_refund_accounts.user_refund_account_id = order_refunds.user_refund_account_id', 'left');
    }

    public function withProcessedBy()
    {
        return $this->select('order_refunds.*, users.user_name as admin_name, users.user_email as admin_email')
            ->join('users', 'users.user_id = order_refunds.processed_by', 'left');
    }

    public function withAll()
    {
        return $this->select('order_refunds.*,
                     orders.order_id, orders.created_at as order_date, orders.grand_total as order_amount,
                     customers.customer_name, customers.customer_email,
                     user_refund_accounts.account_name, user_refund_accounts.account_number, user_refund_accounts.bank_name,
                     users.user_name as admin_name, users.user_email as admin_email')
            ->join('orders', 'orders.order_id = order_refunds.order_id', 'left')
            ->join('customers', 'customers.customer_id = orders.customer_id', 'left')
            ->join('user_refund_accounts', 'user_refund_accounts.user_refund_account_id = order_refunds.user_refund_account_id', 'left')
            ->join('users', 'users.user_id = order_refunds.processed_by', 'left');
    }

    // =====================
    // QUERY HELPERS
    // =====================
    public function getByOrderId(string $orderId)
    {
        return $this->where('order_id', $orderId)->findAll();
    }

    public function getByStatus(string $status)
    {
        return $this->where('status', $status)->findAll();
    }

    public function getPendingRefunds()
    {
        return $this->where('status', self::STATUS_REQUESTED)
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }

    public function hasActiveRefund(string $orderId): bool
    {
        return $this->where('order_id', $orderId)
            ->whereIn('status', [
                self::STATUS_REQUESTED, 
                self::STATUS_RETURN_APPROVED, 
                self::STATUS_RETURN_SHIPPED,
                self::STATUS_RETURN_RECEIVED
            ])
            ->countAllResults() > 0;
    }

    // =====================
    // STATUS UPDATE METHODS
    // =====================
    // =====================
    // STATUS UPDATE METHODS
    // =====================
    // 'Processing' concept might be 'return_approved' (waiting for return)
    public function markReturnApproved(string $id, string $adminId = null, string $note = null)
    {
        $data = [
            'status' => self::STATUS_RETURN_APPROVED,
        ];

        if ($adminId) {
            $data['processed_by'] = $adminId;
        }

        if ($note) {
            $data['admin_note'] = $note;
        }

        return $this->update($id, $data);
    }

    public function markReturnShipped(string $id, string $courier, string $trackingNumber)
    {
        $data = [
            'status' => self::STATUS_RETURN_SHIPPED,
            'return_courier' => $courier,
            'return_tracking_number' => $trackingNumber,
            'return_shipped_at' => date('Y-m-d H:i:s'),
        ];

        return $this->update($id, $data);
    }

    public function markReturnReceived(string $id, string $adminId = null, string $note = null)
    {
        $data = [
            'status' => self::STATUS_RETURN_RECEIVED,
        ];

        if ($adminId) {
            $data['processed_by'] = $adminId;
        }

        if ($note) {
            $data['admin_note'] = $note;
        }

        return $this->update($id, $data);
    }

    public function markApproved(string $id, string $adminId = null, string $note = null)
    {
        $data = [
            'status' => self::STATUS_APPROVED, // Final approval after return received
            'completed_at' => date('Y-m-d H:i:s'),
        ];

        if ($adminId) {
            $data['processed_by'] = $adminId;
        }

        if ($note) {
            $data['admin_note'] = $note;
        }

        return $this->update($id, $data);
    }
    public function markRefunded(string $id, string $adminId = null, string $note = null)
    {
        $data = [
            'status' => self::STATUS_REFUNDED,
        ];

        if ($adminId) {
            $data['processed_by'] = $adminId;
        }

        if ($note) {
            $data['admin_note'] = $note;
        }

        return $this->update($id, $data);
    }
    public function markRejected(string $id, string $adminId = null, string $note = null)
    {
        // Usually rejects the initial request
        $data = [
            'status' => self::STATUS_REQUEST_REJECTED,
            'completed_at' => date('Y-m-d H:i:s'),
        ];

        if ($adminId) {
            $data['processed_by'] = $adminId;
        }

        if ($note) {
            $data['admin_note'] = $note;
        }

        return $this->update($id, $data);
    }

    // =====================
    // STATISTICS METHODS
    // =====================
    public function getRefundStats(string $startDate = null, string $endDate = null)
    {
        $builder = $this->builder();

        if ($startDate) {
            $builder->where('created_at >=', $startDate);
        }

        if ($endDate) {
            $builder->where('created_at <=', $endDate);
        }

        return [
            'total'             => $builder->countAllResults(false),
            'requested'         => $builder->where('status', self::STATUS_REQUESTED)->countAllResults(false),
            'request_rejected'  => $builder->where('status', self::STATUS_REQUEST_REJECTED)->countAllResults(false),
            'return_approved'   => $builder->where('status', self::STATUS_RETURN_APPROVED)->countAllResults(false),
            'return_shipped'    => $builder->where('status', self::STATUS_RETURN_SHIPPED)->countAllResults(false),
            'return_received'   => $builder->where('status', self::STATUS_RETURN_RECEIVED)->countAllResults(false),
            'return_rejected'   => $builder->where('status', self::STATUS_RETURN_REJECTED)->countAllResults(false),
            'approved'          => $builder->where('status', self::STATUS_APPROVED)->countAllResults(false),
            'refunded'          => $builder->where('status', self::STATUS_REFUNDED)->countAllResults(false),
            'expired'           => $builder->where('status', self::STATUS_EXPIRED)->countAllResults(false),
            'total_amount'      => $builder->selectSum('refund_amount')->get()->getRow()->refund_amount ?? 0,
        ];
    }

    // =====================
    // BUSINESS LOGIC
    // =====================
    public function createRefund(array $data)
    {
        // Cek apakah order sudah punya refund yang masih aktif
        if ($this->hasActiveRefund($data['order_id'])) {
            return [
                'success' => false,
                'message' => 'Order ini sudah memiliki permintaan refund yang sedang diproses',
            ];
        }

        // Set default status
        $data['status'] = self::STATUS_REQUESTED;

        if ($this->insert($data)) {
            return [
                'success' => true,
                'message' => 'Permintaan refund berhasil dibuat',
                'refund_id' => $this->getInsertID(),
            ];
        }

        return [
            'success' => false,
            'message' => 'Gagal membuat permintaan refund',
            'errors' => $this->errors(),
        ];
    }
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table            = 'orders';
    protected $primaryKey       = 'order_id';
    protected $useSoftDeletes   = true;

    protected $useAutoIncrement = false;
    protected $insertID         = '';

    protected $useTimestamps    = true;
    protected $dateFormat       = 'datetime';
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $deletedField     = 'deleted_at';

    protected $allowedFields = [
        'order_id',
        'customer_id',
        'status_id',
        'shipping_method_id',
        'shipping_cost',
        'shipping_discount',
        'coupon_discount',
        'grand_total',
        'tracking_number',
        'courier',
        'order_type',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $validationRules = [
        'order_id'            => 'permit_empty|alpha_numeric_punct|min_length[1]|max_length[36]',
        'customer_id'         => 'permit_empty|alpha_numeric_punct|min_length[1]|max_length[36]',
        'status_id'           => 'permit_empty|alpha_numeric_punct|min_length[1]|max_length[36]',
        'shipping_method_id'  => 'permit_empty|alpha_numeric_punct|min_length[1]|max_length[36]',

        'shipping_cost'       => 'permit_empty|decimal',
        'shipping_discount'   => 'permit_empty|decimal',
        'coupon_discount'     => 'permit_empty|decimal',
        'grand_total'         => 'required|decimal',
    ];

    protected $validationMessages = [];
    protected $skipValidation     = false;

    protected $beforeInsert = ['generateUuid'];

    protected function generateUuid(array $data)
    {
        $data['data']['order_id'] = service('uuid')->uuid4()->toString();
        return $data;
    }

    public function getOrderItems($orderId)
    {
        return $this->db->table('order_items')
            ->select('
                products.product_sku,
                products.product_name,
                order_items.quantity AS qty,
                order_items.price
            ')
            ->join('products', 'products.product_id = order_items.product_id')
            ->where('order_items.order_id', $orderId)
            ->get()
            ->getResultArray();
    }

    /**
     * Shipping address (1 order = 1 address)
     */
    public function getShippingAddress($orderId)
    {
        return $this->db->table('order_shipping_addresses')
            ->where('order_id', $orderId)
            ->get()
            ->getRowArray();
    }

    /**
     * Restore order items stock back to products and product_variants
     */
    public function restoreStock(string $orderId, string $reason, ?string $userId = null)
    {
        $items = $this->db->table('order_items')
            ->where('order_id', $orderId)
            ->get()
            ->getResultArray();

        foreach ($items as $item) {
            $qty = (int)$item['quantity'];

            // 1️⃣ Increase stock
            if (!empty($item['variant_id'])) {
                // Restore variant stock
                $this->db->table('product_variants')
                    ->where('variant_id', $item['variant_id'])
                    ->set('stock', 'stock + ' . $qty, false)
                    ->update();

                // sync total product stock
                $this->db->query("
                    UPDATE products p
                    SET product_stock = (
                        SELECT COALESCE(SUM(stock), 0)
                        FROM product_variants
                        WHERE product_id = p.product_id
                    )
                    WHERE p.product_id = ?
                ", [$item['product_id']]);
            } else {
                // Restore simple product stock
                $this->db->table('products')
                    ->where('product_id', $item['product_id'])
                    ->set('product_stock', 'product_stock + ' . $qty, false)
                    ->update();
            }

            // 2️⃣ Record inventory transaction IN (to balance it out)
            $this->db->table('inventory_transactions')->insert([
                'inventory_transaction_id' => service('uuid')->uuid4()->toString(),
                'user_id'                  => $userId,
                'product_id'               => $item['product_id'],
                'variant_id'               => $item['variant_id'] ?: null,
                'transaction_type'         => 'in',
                'reference_type'           => 'order_return',
                'reference_id'             => $orderId,
                'quantity'                 => $qty,
                'transaction_date'         => date('Y-m-d H:i:s'),
                'description'              => $reason,
                'created_at'               => date('Y-m-d H:i:s'),
                'updated_at'               => date('Y-m-d H:i:s')
            ]);
        }
    }

    /**
     * Bulk check and expire any pending orders that have exceeded the 12-hour deadline.
     */
    public function bulkCheckAndExpirePendingOrders()
    {
        $statusModel = new \App\Models\OrderStatusModel();
        $pendingStatusId = $statusModel->getIdByCode(\Config\OrderStatus::PENDING);
        $expiredStatusId = $statusModel->getIdByCode(\Config\OrderStatus::EXPIRED);

        // Find all pending orders older than 12 hours
        $twelveHoursAgo = date('Y-m-d H:i:s', time() - (12 * 3600));
        $expiredOrders = $this->where('status_id', $pendingStatusId)
            ->where('created_at <', $twelveHoursAgo)
            ->findAll();

        foreach ($expiredOrders as $order) {
            // Update status to EXPIRED
            $this->update($order['order_id'], [
                'status_id' => $expiredStatusId,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Restore Stock
            $this->restoreStock($order['order_id'], 'Payment deadline exceeded (Auto-Expired)', 'system');
        }
    }

    /**
     * Check if a pending order has exceeded the payment deadline (e.g., 12 hours),
     * and if so, mark it as expired and restore the stock.
     */
    public function checkAndExpireOrder(array $order): array
    {
        $statusModel = new \App\Models\OrderStatusModel();
        $pendingStatusId = $statusModel->getIdByCode(\Config\OrderStatus::PENDING);

        if ($order['status_id'] === $pendingStatusId) {
            $createdAt = strtotime($order['created_at']);
            $deadline = $createdAt + (12 * 3600); // 12 hours deadline

            if (time() >= $deadline) {
                $expiredStatusId = $statusModel->getIdByCode(\Config\OrderStatus::EXPIRED);

                // Update database
                $this->update($order['order_id'], [
                    'status_id' => $expiredStatusId,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                // Restore stock
                $this->restoreStock($order['order_id'], 'Payment deadline exceeded (Auto-Expired)', 'system');

                // Return the updated status data
                $order['status_id'] = $expiredStatusId;
                $order['status_code'] = \Config\OrderStatus::EXPIRED;
                $order['status_name'] = 'Payment Expired';
            }
        }

        return $order;
    }
}

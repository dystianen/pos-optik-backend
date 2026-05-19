<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderStatusModel extends Model
{
    protected $table            = 'order_statuses';
    protected $primaryKey       = 'status_id';
    protected $useAutoIncrement = false;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $useTimestamps    = true;
    protected $dateFormat       = 'datetime';
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $deletedField     = 'deleted_at';

    protected $allowedFields = [
        'status_id',
        'status_code',
        'status_name',
    ];

    protected $validationRules = [
        'status_id'   => 'permit_empty|alpha_numeric_punct|min_length[1]|max_length[36]',
        'status_code' => 'permit_empty|max_length[20]',
        'status_name' => 'permit_empty|max_length[50]',
    ];

    protected $beforeInsert = ['generateUuid'];

    protected function generateUuid(array $data)
    {
        $data['data']['status_id'] = service('uuid')->uuid4()->toString();
        return $data;
    }

    private static array $statusMap = [];

    public function getIdByCode(string $code): string
    {
        if (empty(self::$statusMap)) {
            self::$statusMap = $this->select('status_id, status_code')
                ->findAll();

            self::$statusMap = array_column(
                self::$statusMap,
                'status_id',
                'status_code'
            );
        }

        if (!isset(self::$statusMap[$code])) {
            if ($code === 'expired') {
                $statusId = service('uuid')->uuid4()->toString();
                // Direct database insert to bypass recursive callbacks or static maps
                $this->db->table($this->table)->insert([
                    'status_id'   => $statusId,
                    'status_code' => 'expired',
                    'status_name' => 'Payment Expired',
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ]);
                self::$statusMap['expired'] = $statusId;
            }
        }

        return self::$statusMap[$code]
            ?? throw new \Exception("Invalid order status: {$code}");
    }
}

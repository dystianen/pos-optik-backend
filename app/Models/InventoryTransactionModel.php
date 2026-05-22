<?php

namespace App\Models;

use CodeIgniter\Model;

class InventoryTransactionModel extends Model
{
    protected $table            = 'inventory_transactions';
    protected $primaryKey       = 'inventory_transaction_id';
    protected $useAutoIncrement = false;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $useTimestamps    = true;
    protected $dateFormat       = 'datetime';
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $deletedField     = 'deleted_at';

    protected $allowedFields = [
        'inventory_transaction_id',
        'product_id',
        'variant_id',
        'transaction_type',
        'reference_type',
        'reference_id',
        'quantity',
        'transaction_date',
        'description',
        'user_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $validationRules = [
        'product_id'        => 'required',
        'transaction_type'  => 'required|in_list[in,out]',
        'quantity'          => 'required|integer|is_natural_no_zero',
        'transaction_date'  => 'required|valid_date',
        'description'       => 'permit_empty|max_length[500]',
        'user_id'           => 'permit_empty',
    ];

    protected $validationMessages = [
        'product_id' => [
            'required' => 'Please select a product for the inventory transaction.',
        ],
        'transaction_type' => [
            'required' => 'Transaction type is required.',
            'in_list' => 'Transaction type must be either "In" or "Out".',
        ],
        'quantity' => [
            'required' => 'Quantity is required.',
            'integer' => 'Quantity must be a whole number.',
            'is_natural_no_zero' => 'Quantity must be a positive number greater than zero.',
        ],
        'transaction_date' => [
            'required'   => 'Transaction date is required.',
            'valid_date' => 'Transaction date must be a valid date (format: YYYY-MM-DD).',
        ],
        'description' => [
            'max_length' => 'Description must not exceed 500 characters.',
        ],
    ];

    protected $skipValidation = false;

    protected $beforeInsert = ['generateUuid'];

    protected function generateUuid(array $data)
    {
        $data['data']['inventory_transaction_id'] = service('uuid')->uuid4()->toString();
        return $data;
    }
}

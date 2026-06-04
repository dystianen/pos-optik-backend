<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table            = 'products';
    protected $primaryKey       = 'product_id';
    protected $useSoftDeletes   = true;

    protected $useAutoIncrement = false;
    protected $insertID         = '';

    // timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $allowedFields = [
        'product_id',
        'category_id',
        'product_name',
        'product_sku',
        'product_price',
        'product_stock',
        'product_brand',
        'description',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $validationRules = [
        'product_id'    => 'permit_empty|alpha_numeric_punct|min_length[1]|max_length[36]',
        'category_id'   => 'required',
        'product_name'  => 'required|string|max_length[100]',
        'product_price' => 'required|decimal|greater_than[0]',
        'product_stock' => 'permit_empty|is_natural',
        'product_brand' => 'permit_empty|string|max_length[50]',
        'description'   => 'permit_empty|max_length[65535]',
    ];

    protected $validationMessages = [
        'category_id' => [
            'required' => 'Please select a product category.',
        ],
        'product_name' => [
            'required'   => 'Product name is required.',
            'string'     => 'Product name must be text.',
            'max_length' => 'Product name must not exceed 100 characters.',
        ],
        'product_price' => [
            'required'      => 'Product price is required.',
            'decimal'       => 'Product price must be a valid decimal number (e.g., 99.99).',
            'greater_than'  => 'Product price must be greater than 0.',
        ],
        'product_stock' => [
            'is_natural' => 'Product stock must be a positive number.',
        ],
        'product_brand' => [
            'string'     => 'Product brand must be text.',
            'max_length' => 'Product brand must not exceed 50 characters.',
        ],
        'description' => [
            'max_length' => 'Description must not exceed 65535 characters.',
        ],
    ];
    protected $skipValidation = false;

    protected $beforeInsert = ['generateUUID'];
    protected function generateUuid(array $data)
    {
        $data['data']['product_id'] = service('uuid')->uuid4()->toString();
        return $data;
    }
}

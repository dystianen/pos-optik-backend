<?php

namespace App\Models;

use CodeIgniter\Model;
use Ramsey\Uuid\Uuid;

class ProductCategoryModel extends Model
{
    protected $table            = 'product_categories';
    protected $primaryKey       = 'category_id';
    protected $useAutoIncrement = false;
    protected $useSoftDeletes   = true;

    protected $allowedFields = [
        'category_id',
        'category_name',
        'category_slug',
        'category_description',
        'variant_mode',
        'is_prescription_supported',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // timestamps
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'category_name'        => 'required|max_length[50]',
        'category_description' => 'permit_empty|max_length[500]',
    ];

    protected $validationMessages = [
        'category_name' => [
            'required'   => 'Category name is required.',
            'max_length' => 'Category name must not exceed 50 characters.',
        ],
        'category_description' => [
            'max_length' => 'Category description must not exceed 500 characters.',
        ],
    ];
    protected $skipValidation     = false;

    // UUID generator
    protected $beforeInsert = ['generateUUID'];

    protected function generateUUID(array $data)
    {
        if (!isset($data['data'][$this->primaryKey])) {
            $data['data'][$this->primaryKey] = Uuid::uuid4()->toString();
        }
        return $data;
    }
}

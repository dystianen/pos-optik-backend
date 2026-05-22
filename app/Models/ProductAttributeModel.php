<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductAttributeModel extends Model
{
    protected $table            = 'product_attributes';
    protected $primaryKey       = 'attribute_id';
    protected $useSoftDeletes   = true;

    protected $useAutoIncrement = false;
    protected $insertID         = '';

    protected $useTimestamps    = true;
    protected $dateFormat       = 'datetime';
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $deletedField     = 'deleted_at';

    protected $allowedFields = [
        'attribute_id',
        'attribute_name',
        'attribute_type',
    ];

    protected $validationRules = [
        'attribute_id'   => 'permit_empty|alpha_numeric_punct|min_length[1]|max_length[36]',
        'attribute_name' => 'required|string|max_length[50]',
        'attribute_type' => 'required|string|max_length[20]',
    ];

    protected $validationMessages = [
        'attribute_name' => [
            'required'   => 'Attribute name is required.',
            'string'     => 'Attribute name must be text.',
            'max_length' => 'Attribute name must not exceed 50 characters.',
        ],
        'attribute_type' => [
            'required'   => 'Attribute type is required.',
            'string'     => 'Attribute type must be text.',
            'max_length' => 'Attribute type must not exceed 20 characters.',
        ],
    ];
    protected $skipValidation     = false;

    protected $beforeInsert = ['generateUuid'];

    protected function generateUuid(array $data)
    {
        $data['data']['attribute_id'] = service('uuid')->uuid4()->toString();
        return $data;
    }

    public function getWithValues($id)
    {
        return $this->select('product_attributes.*')
            ->select('attribute_master_values.attribute_master_id AS option_id, attribute_master_values.value AS option_value')
            ->join('attribute_master_values', 'attribute_master_values.attribute_id = product_attributes.attribute_id', 'left')
            ->where('product_attributes.attribute_id', $id)
            ->findAll();
    }
}

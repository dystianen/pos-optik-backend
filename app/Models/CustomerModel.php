<?php

namespace App\Models;

use CodeIgniter\Model;
use Ramsey\Uuid\Uuid;

class CustomerModel extends Model
{
    protected $table            = 'customers';
    protected $primaryKey       = 'customer_id';
    protected $useAutoIncrement = false;
    protected $useSoftDeletes   = true;

    protected $allowedFields = [
        'customer_id',
        'customer_name',
        'customer_email',
        'customer_password',
        'customer_phone',
        'customer_dob',
        'customer_gender',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // timestamps
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'customer_name'     => 'required|max_length[100]',
        'customer_email'    => 'required|valid_email|max_length[100]',
        'customer_password' => 'required|max_length[255]',
        'customer_phone'    => 'permit_empty|max_length[20]',
        'customer_dob'      => 'permit_empty|valid_date',
        'customer_gender'   => 'permit_empty|in_list[male,female,other]',
    ];

    protected $validationMessages = [
        'customer_name' => [
            'required'   => 'Customer name is required.',
            'max_length' => 'Customer name must not exceed 100 characters.',
        ],
        'customer_email' => [
            'required'    => 'Email address is required.',
            'valid_email' => 'Please enter a valid email address (e.g., customer@example.com).',
            'max_length'  => 'Email address must not exceed 100 characters.',
        ],
        'customer_password' => [
            'required'   => 'Password is required when creating a new customer account.',
            'max_length' => 'Password must not exceed 255 characters.',
        ],
        'customer_phone' => [
            'max_length' => 'Phone number must not exceed 20 characters.',
        ],
        'customer_dob' => [
            'valid_date' => 'Date of birth must be a valid date (format: YYYY-MM-DD).',
        ],
        'customer_gender' => [
            'in_list' => 'Gender must be one of: Male, Female, or Other.',
        ],
    ];
    protected $skipValidation     = false;

    // UUID Generator
    protected $beforeInsert = ['generateUUID'];

    protected function generateUUID(array $data)
    {
        if (!isset($data['data'][$this->primaryKey])) {
            $data['data'][$this->primaryKey] = Uuid::uuid4()->toString();
        }
        return $data;
    }
}

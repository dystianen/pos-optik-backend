<?php

namespace App\Models;

use CodeIgniter\Model;
use Ramsey\Uuid\Uuid;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'user_id';
    protected $useAutoIncrement = false;

    protected $allowedFields = [
        'user_id',
        'user_name',
        'user_email',
        'password',
        'role_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
    protected $useSoftDeletes = true;

    // Validation
    protected $validationRules = [
        'user_name'  => 'required|max_length[100]',
        'user_email' => 'required|valid_email|max_length[100]',
        'password'   => 'required|max_length[255]',
        'role_id'    => 'required',
    ];

    protected $validationMessages = [
        'user_name' => [
            'required'   => 'Full name is required.',
            'max_length' => 'Full name must not exceed 100 characters.',
        ],
        'user_email' => [
            'required'    => 'Email address is required.',
            'valid_email' => 'Please enter a valid email address (e.g., user@example.com).',
            'max_length'  => 'Email address must not exceed 100 characters.',
        ],
        'password' => [
            'required'   => 'Password is required.',
            'max_length' => 'Password must not exceed 255 characters.',
        ],
        'role_id' => [
            'required' => 'Please select a role for the user.',
        ],
    ];

    protected $skipValidation = false;

    // Auto generate UUID
    protected $beforeInsert = ['generateUUID'];

    protected function generateUUID(array $data)
    {
        if (!isset($data['data'][$this->primaryKey])) {
            $data['data'][$this->primaryKey] = Uuid::uuid4()->toString();
        }
        return $data;
    }
}

<?php

namespace App\Models;

use CodeIgniter\Model;
use Ramsey\Uuid\Uuid;

class RoleModel extends Model
{
    protected $table            = 'roles';
    protected $primaryKey       = 'role_id';
    protected $useAutoIncrement = false;

    protected $allowedFields = [
        'role_id',
        'role_name',
        'role_description',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
    protected $useSoftDeletes = true;

    // Validation Rules
    protected $validationRules = [
        'role_name'        => 'required|max_length[100]',
        'role_description' => 'permit_empty|max_length[500]',
    ];

    protected $validationMessages = [
        'role_name' => [
            'required'   => 'Role name is required.',
            'max_length' => 'Role name must not exceed 100 characters.',
        ],
        'role_description' => [
            'max_length' => 'Role description must not exceed 500 characters.',
        ],
    ];
    protected $skipValidation     = false;

    // Auto-generate UUID
    protected $beforeInsert = ['generateUUID'];

    protected function generateUUID(array $data)
    {
        if (!isset($data['data'][$this->primaryKey])) {
            $data['data'][$this->primaryKey] = Uuid::uuid4()->toString();
        }
        return $data;
    }
}

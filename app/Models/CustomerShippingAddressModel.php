<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerShippingAddressModel extends Model
{
    protected $table            = 'customer_shipping_addresses';
    protected $primaryKey       = 'csa_id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';

    protected $useSoftDeletes   = true;
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'csa_id',
        'customer_id',
        'recipient_name',
        'phone',
        'address',
        'city',
        'city_id',
        'district',
        'district_id',
        'province',
        'province_id',
        'postal_code',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $validationRules = [
        'recipient_name' => 'required|min_length[1]',
        'phone' => 'required|min_length[1]',
        'address' => 'required|min_length[1]',
        'city' => 'required|min_length[1]',
        'city_id' => 'required|min_length[1]',
        'district' => 'required|min_length[1]',
        'district_id' => 'required|min_length[1]',
        'province' => 'required|min_length[1]',
        'province_id' => 'required|min_length[1]',
        'postal_code' => 'required|min_length[1]',
    ];


    protected $beforeInsert = ['generateUuid'];

    protected function generateUuid(array $data)
    {
        $data['data']['csa_id'] = service('uuid')->uuid4()->toString();
        return $data;
    }
}

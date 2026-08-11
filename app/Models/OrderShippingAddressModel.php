<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderShippingAddressModel extends Model
{
    protected $table            = 'order_shipping_addresses';
    protected $primaryKey       = 'osa_id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';

    protected $useSoftDeletes   = true;
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'osa_id',
        'order_id',
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

    protected $beforeInsert = ['generateUuid'];

    protected function generateUuid(array $data)
    {
        $data['data']['osa_id'] = service('uuid')->uuid4()->toString();
        return $data;
    }
}

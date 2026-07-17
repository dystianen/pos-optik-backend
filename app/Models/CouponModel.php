<?php

namespace App\Models;

use CodeIgniter\Model;

class CouponModel extends Model
{
    protected $table            = 'coupons';
    protected $primaryKey       = 'coupon_id';
    protected $useAutoIncrement = false;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $useTimestamps    = true;
    protected $dateFormat       = 'datetime';
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $deletedField     = 'deleted_at';

    protected $allowedFields = [
        'coupon_id',
        'code',
        'description',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'max_discount',
        'start_date',
        'end_date',
        'usage_limit',
        'per_user_limit',
        'is_active',
        'first_order_only',
    ];

    protected $validationRules = [
        'code'             => 'required|max_length[50]|is_unique[coupons.code]',
        'discount_type'    => 'required|max_length[20]',
        'discount_value'   => 'required|decimal',
        'min_order_amount' => 'permit_empty|decimal',
        'max_discount'     => 'permit_empty|decimal',
        'start_date'       => 'required|valid_date',
        'end_date'         => 'required|valid_date',
        'usage_limit'      => 'permit_empty|integer',
        'per_user_limit'   => 'permit_empty|integer',
        'is_active'        => 'permit_empty|in_list[0,1]',
        'first_order_only' => 'permit_empty|in_list[0,1]',
    ];

    protected $beforeInsert = ['generateUuid'];

    protected function generateUuid(array $data)
    {
        $data['data']['coupon_id'] = service('uuid')->uuid4()->toString();
        return $data;
    }
}

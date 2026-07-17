<?php

namespace App\Database\Seeds;

use App\Models\CouponModel;
use CodeIgniter\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run()
    {
        $couponModel = new CouponModel();

        $coupons = [
            [
                'code'             => 'PROMO10',
                'description'      => 'Get 10% off on your purchase. Maximum discount up to Rp 50.000.',
                'discount_type'    => 'percentage',
                'discount_value'   => 10.00,
                'min_order_amount' => 100000.00,
                'max_discount'     => 50000.00,
                'start_date'       => date('Y-m-d H:i:s', strtotime('-1 week')),
                'end_date'         => date('Y-m-d H:i:s', strtotime('+1 year')),
                'usage_limit'      => 100,
                'per_user_limit'   => 2,
                'is_active'        => 1,
                'first_order_only' => 0,
            ],
            [
                'code'             => 'FIXED50K',
                'description'      => 'Direct Rp 50.000 discount with a minimum spend of Rp 200.000.',
                'discount_type'    => 'fixed',
                'discount_value'   => 50000.00,
                'min_order_amount' => 200000.00,
                'max_discount'     => null,
                'start_date'       => date('Y-m-d H:i:s', strtotime('-1 week')),
                'end_date'         => date('Y-m-d H:i:s', strtotime('+1 year')),
                'usage_limit'      => 50,
                'per_user_limit'   => 1,
                'is_active'        => 1,
                'first_order_only' => 0,
            ],
            [
                'code'             => 'NEWUSER',
                'description'      => 'Exclusive 15% discount up to Rp 100.000 for your first transaction.',
                'discount_type'    => 'percentage',
                'discount_value'   => 15.00,
                'min_order_amount' => 50000.00,
                'max_discount'     => 100000.00,
                'start_date'       => date('Y-m-d H:i:s', strtotime('-1 week')),
                'end_date'         => date('Y-m-d H:i:s', strtotime('+1 year')),
                'usage_limit'      => 200,
                'per_user_limit'   => 1,
                'is_active'        => 1,
                'first_order_only' => 1,
            ],
            [
                'code'             => 'FREESHIP',
                'description'      => 'Free shipping discount for your order. Minimum spend Rp 150.000.',
                'discount_type'    => 'free_shipping',
                'discount_value'   => 0.00,
                'min_order_amount' => 150000.00,
                'max_discount'     => null,
                'start_date'       => date('Y-m-d H:i:s', strtotime('-1 week')),
                'end_date'         => date('Y-m-d H:i:s', strtotime('+1 year')),
                'usage_limit'      => 150,
                'per_user_limit'   => 2,
                'is_active'        => 1,
                'first_order_only' => 0,
            ]
        ];

        foreach ($coupons as $coupon) {
            // Check if coupon already exists
            $existing = $couponModel->where('code', $coupon['code'])->first();
            if (!$existing) {
                $couponModel->insert($coupon);
            }
        }
    }
}

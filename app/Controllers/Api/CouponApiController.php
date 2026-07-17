<?php

namespace App\Controllers\Api;

use App\Models\CouponModel;

class CouponApiController extends BaseApiController
{
    protected $couponModel;

    public function __construct()
    {
        $this->couponModel = new CouponModel();
    }

    // GET /api/coupons
    public function listActiveCoupons()
    {
        try {
            // Check if user is authenticated (optional)
            $jwtUser = getJWTUser();
            $customerId = $jwtUser ? $jwtUser->user_id : null;
            $db = db_connect();

            // Find excluded order status IDs (cancelled, rejected, expired)
            $excludedCodes = ['cancelled', 'rejected', 'expired'];
            $excludedStatusIds = [];
            foreach ($excludedCodes as $code) {
                $status = $db->table('order_statuses')->where('status_code', $code)->get()->getRowArray();
                if ($status) {
                    $excludedStatusIds[] = $status['status_id'];
                }
            }

            // Check if user has any existing valid orders
            $hasPriorOrders = false;
            if ($customerId) {
                $orderCountQuery = $db->table('orders')
                    ->where('customer_id', $customerId)
                    ->where('deleted_at', null);
                if (!empty($excludedStatusIds)) {
                    $orderCountQuery->whereNotIn('status_id', $excludedStatusIds);
                }
                $orderCount = $orderCountQuery->countAllResults();
                $hasPriorOrders = ($orderCount > 0);
            }

            // Fetch active coupons
            $now = date('Y-m-d H:i:s');
            $coupons = $this->couponModel
                ->where('is_active', 1)
                ->where('start_date <=', $now)
                ->where('end_date >=', $now)
                ->findAll();

            $mappedCoupons = [];

            foreach ($coupons as $coupon) {
                // Get global usage count
                $usageQuery = $db->table('order_coupons')
                    ->join('orders', 'orders.order_id = order_coupons.order_id')
                    ->where('order_coupons.coupon_id', $coupon['coupon_id'])
                    ->where('orders.deleted_at', null);
                if (!empty($excludedStatusIds)) {
                    $usageQuery->whereNotIn('orders.status_id', $excludedStatusIds);
                }
                $globalUsage = $usageQuery->countAllResults();

                // Get user usage count
                $userUsage = 0;
                if ($customerId) {
                    $userUsageQuery = $db->table('order_coupons')
                        ->join('orders', 'orders.order_id = order_coupons.order_id')
                        ->where('order_coupons.coupon_id', $coupon['coupon_id'])
                        ->where('orders.customer_id', $customerId)
                        ->where('orders.deleted_at', null);
                    if (!empty($excludedStatusIds)) {
                        $userUsageQuery->whereNotIn('orders.status_id', $excludedStatusIds);
                    }
                    $userUsage = $userUsageQuery->countAllResults();
                }

                // Determine eligibility
                $isEligible = true;
                $reason = null;

                if (!empty($coupon['usage_limit']) && $globalUsage >= $coupon['usage_limit']) {
                    $isEligible = false;
                    $reason = 'Coupon usage limit has been reached.';
                } elseif ($customerId && !empty($coupon['per_user_limit']) && $userUsage >= $coupon['per_user_limit']) {
                    $isEligible = false;
                    $reason = 'Your usage limit for this coupon has been reached.';
                } elseif ($coupon['first_order_only'] && $hasPriorOrders) {
                    $isEligible = false;
                    $reason = 'Only valid for first transaction.';
                }

                $mappedCoupons[] = [
                    'coupon_id'         => $coupon['coupon_id'],
                    'code'              => $coupon['code'],
                    'description'       => $coupon['description'],
                    'discount_type'     => $coupon['discount_type'],
                    'discount_value'    => (float)$coupon['discount_value'],
                    'min_order_amount'  => $coupon['min_order_amount'] !== null ? (float)$coupon['min_order_amount'] : null,
                    'max_discount'      => $coupon['max_discount'] !== null ? (float)$coupon['max_discount'] : null,
                    'first_order_only'  => (bool)$coupon['first_order_only'],
                    'is_eligible'       => $isEligible,
                    'ineligible_reason' => $reason,
                ];
            }

            return $this->successResponse($mappedCoupons);
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}

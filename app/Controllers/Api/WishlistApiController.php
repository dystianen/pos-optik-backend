<?php

namespace App\Controllers\Api;

use App\Models\OrderStatusModel;
use App\Models\WishlistModel;
use Config\OrderStatus;

class WishlistApiController extends BaseApiController
{
    protected $wishlistModel;
    protected $statusModel;

    public function __construct()
    {
        $this->wishlistModel = new WishlistModel();
        $this->statusModel = new OrderStatusModel();
    }

    // GET /api/wishlist
    public function index()
    {
        $customerId = $this->getAuthenticatedCustomerId();

        $search = $this->request->getVar('search');
        $page   = (int) ($this->request->getVar('page') ?? 1);
        $limit  = (int) ($this->request->getVar('limit') ?? 10);

        /**
         * ==========================
         * SUBQUERY TOTAL SOLD
         * ==========================
         */
        $soldStatusIds = $this->getSoldStatusIds();
        $subQuery = $this->db->table('order_items oi')
            ->select('oi.product_id, SUM(oi.quantity) AS total_sold')
            ->join('orders o', 'o.order_id = oi.order_id');
        if (!empty($soldStatusIds)) {
            $subQuery->whereIn('o.status_id', $soldStatusIds);
        } else {
            $subQuery->where('1 = 0', null, false);
        }
        $subQuery = $subQuery->groupBy('oi.product_id');

        /**
         * ==========================
         * MAIN QUERY
         * ==========================
         */
        $builder = $this->wishlistModel
            ->select('
            p.product_id,
            p.product_name,
            p.product_price,
            p.product_stock,
            p.product_brand,
            pi.url AS product_image_url,
            COALESCE(ts.total_sold, 0) AS total_sold,
            1 AS is_wishlist
        ')
            ->join('products p', 'p.product_id = wishlists.product_id')
            ->join(
                "({$subQuery->getCompiledSelect()}) ts",
                'ts.product_id = p.product_id',
                'left'
            )
            ->join(
                'product_images pi',
                'pi.product_id = p.product_id AND pi.is_primary = 1',
                'left'
            )
            ->where('wishlists.customer_id', $customerId)
            ->where('wishlists.deleted_at', null)
            ->where('p.deleted_at', null);

        /**
         * 🔍 SEARCH
         */
        if (!empty($search)) {
            $builder->groupStart()
                ->like('p.product_name', $search)
                ->orLike('p.product_brand', $search)
                ->groupEnd();
        }

        $wishlist = $builder
            ->orderBy('wishlists.created_at', 'DESC')
            ->paginate($limit, 'wishlist', $page);

        return $this->successResponse($wishlist);
    }


    // GET /api/wishlist/count
    public function count()
    {
        $jwtUser    = getJWTUser();
        $customerId = $jwtUser->user_id;

        $total = $this->wishlistModel
            ->where('customer_id', $customerId)
            ->countAllResults();

        return $this->successResponse($total);
    }

    // GET /api/wishlist/toggle
    public function toggle()
    {
        $customerId = $this->getAuthenticatedCustomerId();
        $productId  = $this->request->getVar('product_id');

        if (!$productId) {
            return $this->errorResponse('Product ID required');
        }

        $exists = $this->wishlistModel
            ->where('customer_id', $customerId)
            ->where('product_id', $productId)
            ->first();

        if ($exists) {
            $this->wishlistModel->delete($exists['wishlist_id']);

            $total = $this->wishlistModel
                ->where('customer_id', $customerId)
                ->countAllResults();

            $response = [
                'is_wishlist' => false,
                'total' => $total
            ];
            return $this->successResponse($response, 'Removed from wishlist');
        }

        $this->wishlistModel->insert([
            'customer_id' => $customerId,
            'product_id'  => $productId
        ]);

        $total = $this->wishlistModel
            ->where('customer_id', $customerId)
            ->countAllResults();

        $response = [
            'is_wishlist' => true,
            'total' => $total
        ];
        return $this->successResponse($response, 'Added to wishlist');
    }
}

<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\R2Storage;
use App\Models\CartItemModel;
use App\Models\CartItemPrescriptionModel;
use App\Models\CartModel;
use App\Models\CustomerShippingAddressModel;
use App\Models\InventoryTransactionModel;
use App\Models\NotificationModel;
use App\Models\OrderItemModel;
use App\Models\OrderItemPrescriptionModel;
use App\Models\OrderModel;
use App\Models\OrderRefundModel;
use App\Models\OrderShippingAddressModel;
use App\Models\PaymentModel;
use App\Models\ProductModel;
use App\Models\ProductVariantModel;
use App\Models\ShippingRateModel;
use App\Models\UserRefundAccountModel;
use CodeIgniter\API\ResponseTrait;

class OnlineSalesController extends BaseController
{
    use ResponseTrait;
    protected $orderModel, $orderItemModel, $InventoryTransactionModel, $productModel, $productVariantModel, $csaModel, $cartModel, $cartItemModel, $shippingRateModel, $cartItemPrescriptionModel, $orderShippingAddressModel, $orderItemPrescriptionModel, $paymentModel, $notificationModel, $userRefundAccountModel, $orderRefundModel, $r2;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->orderItemModel = new OrderItemModel();
        $this->InventoryTransactionModel = new InventoryTransactionModel();
        $this->productModel = new ProductModel();
        $this->productVariantModel = new ProductVariantModel();
        $this->csaModel = new CustomerShippingAddressModel();
        $this->cartModel = new CartModel();
        $this->cartItemModel = new CartItemModel();
        $this->shippingRateModel = new ShippingRateModel();
        $this->cartItemPrescriptionModel = new CartItemPrescriptionModel();
        $this->orderShippingAddressModel = new OrderShippingAddressModel();
        $this->orderItemPrescriptionModel = new OrderItemPrescriptionModel();
        $this->paymentModel = new PaymentModel();
        $this->notificationModel = new NotificationModel();
        $this->userRefundAccountModel = new UserRefundAccountModel();
        $this->orderRefundModel = new OrderRefundModel();
        $this->r2 = new R2Storage();
    }

    // =======================
    // WEB DASHBOARD FUNCTIONS
    // =======================

    public function index()
    {
        $currentPage = max(
            1,
            (int) ($this->request->getVar('page') ?? 1)
        );

        $search    = trim($this->request->getVar('q') ?? '');
        $startDate = $this->request->getVar('start_date');
        $endDate   = $this->request->getVar('end_date');
        $statusId  = $this->request->getVar('status_id');

        $limit  = 10;
        $offset = ($currentPage - 1) * $limit;

        /**
         * =========================
         * MAIN QUERY
         * =========================
         */
        $builder = $this->db->table('orders');

        $builder->select("
            orders.order_id,
            orders.created_at,
            orders.grand_total,

            customers.customer_name,
            customers.customer_email,

            order_statuses.status_name,
            order_statuses.status_code,

            COUNT(order_items.order_item_id) AS total_items
        ");

        $builder->join(
            'customers',
            'customers.customer_id = orders.customer_id'
        );

        $builder->join(
            'order_statuses',
            'order_statuses.status_id = orders.status_id'
        );

        $builder->join(
            'order_items',
            'order_items.order_id = orders.order_id',
            'left'
        );

        $builder->where('orders.order_type', 'online');

        /**
         * =========================
         * SEARCH
         * =========================
         */
        if ($search !== '') {

            $builder->groupStart()
                ->like('orders.order_id', $search)
                ->orLike('customers.customer_name', $search)
                ->orLike('customers.customer_email', $search)
                ->orLike('order_statuses.status_name', $search)
                ->groupEnd();
        }

        /**
         * =========================
         * STATUS FILTER
         * =========================
         */
        if (!empty($statusId)) {
            $builder->where('orders.status_id', $statusId);
        }

        /**
         * =========================
         * DATE FILTER
         * =========================
         */
        if (!empty($startDate)) {
            $builder->where(
                'orders.created_at >=',
                $startDate . ' 00:00:00'
            );
        }

        if (!empty($endDate)) {
            $builder->where(
                'orders.created_at <=',
                $endDate . ' 23:59:59'
            );
        }

        /**
         * =========================
         * GET DATA
         * =========================
         */
        $orders = $builder
            ->groupBy('orders.order_id')
            ->orderBy('orders.created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResultArray();

        /**
         * =========================
         * COUNT QUERY (LIGHTWEIGHT)
         * =========================
         */
        $countBuilder = $this->db->table('orders');

        $countBuilder->join(
            'customers',
            'customers.customer_id = orders.customer_id'
        );

        $countBuilder->join(
            'order_statuses',
            'order_statuses.status_id = orders.status_id'
        );

        $countBuilder->where('orders.order_type', 'online');

        if ($search !== '') {

            $countBuilder->groupStart()
                ->like('orders.order_id', $search)
                ->orLike('customers.customer_name', $search)
                ->orLike('customers.customer_email', $search)
                ->orLike('order_statuses.status_name', $search)
                ->groupEnd();
        }

        if (!empty($statusId)) {
            $countBuilder->where('orders.status_id', $statusId);
        }

        if (!empty($startDate)) {
            $countBuilder->where(
                'orders.created_at >=',
                $startDate . ' 00:00:00'
            );
        }

        if (!empty($endDate)) {
            $countBuilder->where(
                'orders.created_at <=',
                $endDate . ' 23:59:59'
            );
        }

        $totalRows = $countBuilder
            ->countAllResults();

        $totalPages = (int) ceil($totalRows / $limit);

        $statuses = $this->db->table('order_statuses')->get()->getResultArray();

        return view('online_sales/v_index', [
            'orders' => $orders,
            'search' => $search,
            'startDate' => $startDate,
            'endDate'   => $endDate,
            'statuses'  => $statuses,
            'selectedStatusId' => $statusId,
            'pager' => [
                'totalPages'  => $totalPages,
                'currentPage' => $currentPage,
                'limit'       => $limit,
            ],
        ]);
    }

    public function detail($orderId)
    {
        /**
         * =========================
         * ORDER
         * =========================
         */
        $order = $this->db->table('orders')
            ->select("
                orders.order_id,
                orders.created_at AS order_date,
                orders.grand_total,
                orders.shipping_cost,
                orders.coupon_discount,
                orders.status_id,
                orders.tracking_number,
                orders.courier,

                customers.customer_name,
                customers.customer_email,

                order_statuses.status_name,
                order_statuses.status_code,

                shipping_methods.name AS shipping_method,
                shipping_methods.estimated_days
            ")
            ->join(
                'customers',
                'customers.customer_id = orders.customer_id',
                'left'
            )
            ->join(
                'order_statuses',
                'order_statuses.status_id = orders.status_id',
                'left'
            )
            ->join(
                'shipping_methods',
                'shipping_methods.shipping_method_id = orders.shipping_method_id',
                'left'
            )
            ->where('orders.order_id', $orderId)
            ->where('orders.deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$order) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException(
                'Order not found'
            );
        }

        /**
         * =========================
         * ORDER ITEMS
         * =========================
         */
        $items = $this->orderModel->getOrderItems($orderId);

        /**
         * =========================
         * PAYMENT
         * =========================
         */
        $payment = $this->db->table('payments')
            ->select("
            payments.proof,
            payments.amount,
            payments.paid_at,

            payment_methods.method_name
        ")
            ->join(
                'payment_methods',
                'payment_methods.payment_method_id = payments.payment_method_id',
                'left'
            )
            ->where('payments.order_id', $orderId)
            ->get()
            ->getRowArray();

        /**
         * =========================
         * SHIPPING ADDRESS
         * =========================
         */
        $shippingAddress = $this->orderModel
            ->getShippingAddress($orderId);

        /**
         * =========================
         * REFUND ACCOUNT
         * =========================
         */
        $refundAccount = $this->db->table('order_refunds')
            ->select("
                user_refund_accounts.user_refund_account_id,
                user_refund_accounts.account_name,
                user_refund_accounts.bank_name,
                user_refund_accounts.account_number
            ")
            ->join(
                'user_refund_accounts',
                'user_refund_accounts.user_refund_account_id =
            order_refunds.user_refund_account_id',
                'left'
            )
            ->where('order_refunds.order_id', $orderId)
            ->get()
            ->getRowArray();

        /**
         * =========================
         * ACTIVE REQUESTS CHECK
         * =========================
         */
        $activeCancellation = $this->db->table('order_cancellations')
            ->where('order_id', $orderId)
            ->where('status', 'requested')
            ->get()
            ->getRowArray();

        $activeRefund = $this->db->table('order_refunds')
            ->where('order_id', $orderId)
            ->whereIn('status', ['requested', 'return_approved', 'return_shipped', 'return_received', 'approved'])
            ->get()
            ->getRowArray();

        $appliedCoupon = $this->db->table('order_coupons')
            ->join('coupons', 'coupons.coupon_id = order_coupons.coupon_id')
            ->where('order_coupons.order_id', $orderId)
            ->get()
            ->getRowArray();

        return view('online_sales/v_detail', [
            'order'              => $order,
            'items'              => $items,
            'payment'            => $payment,
            'shippingAddress'    => $shippingAddress,
            'refundAccount'      => $refundAccount,
            'activeCancellation' => $activeCancellation,
            'activeRefund'       => $activeRefund,
            'appliedCoupon'      => $appliedCoupon,
        ]);
    }
}

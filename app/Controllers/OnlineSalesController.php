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
        // Auto check and expire expired orders
        $this->orderModel->bulkCheckAndExpirePendingOrders();

        $currentPage = (int) ($this->request->getVar('page') ?? 1);
        $search      = $this->request->getVar('q'); // keyword search
        $startDate   = $this->request->getVar('start_date');
        $endDate     = $this->request->getVar('end_date');

        $limit  = 10;
        $offset = ($currentPage - 1) * $limit;

        // ============================
        // BASE QUERY
        // ============================
        $builder = $this->orderModel
            ->select('
            orders.order_id,
            orders.created_at,
            orders.grand_total,
            customers.customer_name,
            customers.customer_email,
            order_statuses.status_name,
            order_statuses.status_code,
            COUNT(order_items.order_item_id) as total_items
        ')
            ->join('customers', 'customers.customer_id = orders.customer_id')
            ->join('order_statuses', 'order_statuses.status_id = orders.status_id')
            ->join('order_items', 'order_items.order_id = orders.order_id', 'left')
            ->where('orders.order_type', 'online');

        // ============================
        // SEARCH FILTER
        // ============================
        if (!empty($search)) {
            $builder->groupStart()
                ->like('orders.order_id', $search)
                ->orLike('customers.customer_name', $search)
                ->orLike('customers.customer_email', $search)
                ->orLike('order_statuses.status_name', $search)
                ->groupEnd();
        }

        // ============================
        // DATE FILTER
        // ============================
        if (!empty($startDate)) {
            $builder->where('DATE(orders.created_at) >=', $startDate);
        }
        if (!empty($endDate)) {
            $builder->where('DATE(orders.created_at) <=', $endDate);
        }

        // ============================
        // DATA
        // ============================
        $orders = $builder
            ->groupBy('orders.order_id')
            ->orderBy('orders.created_at', 'DESC')
            ->findAll($limit, $offset);

        // ============================
        // COUNT FOR PAGINATION
        // ============================
        $countBuilder = $this->orderModel
            ->join('customers', 'customers.customer_id = orders.customer_id')
            ->join('order_statuses', 'order_statuses.status_id = orders.status_id')
            ->where('orders.order_type', 'online');

        if (!empty($search)) {
            $countBuilder->groupStart()
                ->like('orders.order_id', $search)
                ->orLike('customers.customer_name', $search)
                ->orLike('customers.customer_email', $search)
                ->orLike('order_statuses.status_name', $search)
                ->groupEnd();
        }

        if (!empty($startDate)) {
            $countBuilder->where('DATE(orders.created_at) >=', $startDate);
        }
        if (!empty($endDate)) {
            $countBuilder->where('DATE(orders.created_at) <=', $endDate);
        }

        $totalRows = $countBuilder->countAllResults();
        $totalPages = ceil($totalRows / $limit);

        return view('online_sales/v_index', [
            'orders'    => $orders,
            'search'    => $search,
            'startDate' => $startDate,
            'endDate'   => $endDate,
            'pager'     => [
                'totalPages'  => $totalPages,
                'currentPage' => $currentPage,
                'limit'       => $limit,
            ],
        ]);
    }

    public function detail($orderId)
    {
        // Auto check and expire expired orders
        $this->orderModel->bulkCheckAndExpirePendingOrders();

        // 🧾 ORDER
        $order = $this->orderModel
            ->select('
            orders.order_id,
            orders.created_at AS order_date,
            orders.grand_total,
            orders.shipping_cost,
            orders.status_id,
            orders.tracking_number,
            orders.courier,

            customers.customer_name,
            customers.customer_email,

            order_statuses.status_name,
            order_statuses.status_code,

            shipping_methods.name AS shipping_method,
            shipping_methods.estimated_days
        ')
            ->join('customers', 'customers.customer_id = orders.customer_id', 'left')
            ->join('order_statuses', 'order_statuses.status_id = orders.status_id', 'left')
            ->join('shipping_methods', 'shipping_methods.shipping_method_id = orders.shipping_method_id', 'left')
            ->where('orders.order_id', $orderId)
            ->where('orders.deleted_at', null)
            ->first();

        if (!$order) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Order not found');
        }

        // 📦 ORDER ITEMS
        $items = $this->orderModel->getOrderItems($orderId);

        // 💳 PAYMENT
        $payment = $this->paymentModel
            ->select('
            payments.proof,
            payments.amount,
            payments.paid_at,
            payment_methods.method_name
        ')
            ->join('payment_methods', 'payment_methods.payment_method_id = payments.payment_method_id', 'left')
            ->where('payments.order_id', $orderId)
            ->first();

        // 📍 SHIPPING ADDRESS
        $shippingAddress = $this->orderModel->getShippingAddress($orderId);

        // 🔁 REFUND ACCOUNT
        $refundAccount = $this->orderRefundModel
            ->select('
            user_refund_accounts.user_refund_account_id,
            user_refund_accounts.account_name,
            user_refund_accounts.bank_name,
            user_refund_accounts.account_number
        ')
            ->join(
                'user_refund_accounts',
                'user_refund_accounts.user_refund_account_id = order_refunds.user_refund_account_id',
                'left'
            )
            ->where('order_refunds.order_id', $orderId)
            ->first();

        $data = [
            'order'           => $order,
            'items'           => $items,
            'payment'         => $payment,
            'shippingAddress' => $shippingAddress,
            'refundAccount'   => $refundAccount,
        ];

        return view('online_sales/v_detail', $data);
    }
}

<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\OrderCancellationModel;
use App\Models\OrderModel;
use App\Models\OrderShippingAddressModel;
use App\Models\PaymentModel;
use App\Models\UserRefundAccountModel;

class CancellationSalesController extends BaseController
{
    protected $cancellationModel;
    protected $orderModel;
    protected $paymentModel;
    protected $orderShippingAddressModel;

    public function __construct()
    {
        $this->cancellationModel = new OrderCancellationModel();
        $this->orderModel = new OrderModel();
        $this->paymentModel = new PaymentModel();
        $this->orderShippingAddressModel = new OrderShippingAddressModel();
    }

    public function index()
    {
        $search    = $this->request->getVar('q');
        $startDate = $this->request->getVar('start_date');
        $endDate   = $this->request->getVar('end_date');

        $builder = $this->cancellationModel
            ->select('order_cancellations.*, orders.grand_total, customers.customer_name, customers.customer_email, orders.created_at as order_date')
            ->join('orders', 'orders.order_id = order_cancellations.order_id')
            ->join('customers', 'customers.customer_id = orders.customer_id')
            ->orderBy('order_cancellations.created_at', 'DESC');

        if (!empty($search)) {
            $builder->groupStart()
                ->like('order_cancellations.order_id', $search)
                ->orLike('customers.customer_name', $search)
                ->orLike('order_cancellations.status', $search)
                ->groupEnd();
        }

        if (!empty($startDate)) {
            $builder->where('DATE(order_cancellations.created_at) >=', $startDate);
        }
        if (!empty($endDate)) {
            $builder->where('DATE(order_cancellations.created_at) <=', $endDate);
        }

        $cancellations = $builder->findAll();

        return view('cancellation_sales/v_index', [
            'cancellations' => $cancellations,
            'search'        => $search,
            'startDate'     => $startDate,
            'endDate'       => $endDate,
        ]);
    }

    public function detail($cancellationId)
    {
        $cancellation = $this->cancellationModel
            ->select('order_cancellations.*, users.user_name as admin_name')
            ->join('users', 'users.user_id = order_cancellations.processed_by', 'left')
            ->where('order_cancellation_id', $cancellationId)
            ->first();

        if (!$cancellation) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cancellation not found');
        }

        $orderId = $cancellation['order_id'];

        $order = $this->orderModel
            ->select('orders.order_id, orders.customer_id, orders.created_at AS order_date, orders.grand_total, orders.shipping_cost, orders.status_id, orders.tracking_number, orders.courier, customers.customer_name, customers.customer_email, order_statuses.status_name, order_statuses.status_code, shipping_methods.name AS shipping_method, shipping_methods.estimated_days')
            ->join('customers', 'customers.customer_id = orders.customer_id', 'left')
            ->join('order_statuses', 'order_statuses.status_id = orders.status_id', 'left')
            ->join('shipping_methods', 'shipping_methods.shipping_method_id = orders.shipping_method_id', 'left')
            ->where('orders.order_id', $orderId)
            ->first();

        if (!$order) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Order not found');
        }

        $items = $this->orderModel->getOrderItems($orderId);

        $payment = $this->paymentModel->select('payments.proof, payments.amount, payments.paid_at, payment_methods.method_name')
            ->join('payment_methods', 'payment_methods.payment_method_id = payments.payment_method_id', 'left')
            ->where('payments.order_id', $orderId)
            ->first();

        $shippingAddress = $this->orderModel->getShippingAddress($orderId);

        $userRefundAccountModel = new UserRefundAccountModel();
        $refundAccount = $userRefundAccountModel
            ->where('customer_id', $order['customer_id'])
            ->orderBy('is_default', 'DESC')
            ->first();

        $data = [
            'order' => $order,
            'items' => $items,
            'payment' => $payment,
            'shippingAddress' => $shippingAddress,
            'cancellation' => $cancellation,
            'refundAccount' => $refundAccount,
        ];

        return view('cancellation_sales/v_detail', $data);
    }
}

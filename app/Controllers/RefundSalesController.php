<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\OrderRefundModel;
use App\Models\OrderRefundItemModel;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\PaymentModel;
use App\Models\OrderShippingAddressModel;

class RefundSalesController extends BaseController
{
  protected $refundModel;
  protected $refundItemModel;
  protected $orderModel;
  protected $orderItemModel;
  protected $paymentModel;
  protected $orderShippingAddressModel;

  public function __construct()
  {
    $this->refundModel = new OrderRefundModel();
    $this->refundItemModel = new OrderRefundItemModel();
    $this->orderModel = new OrderModel();
    $this->orderItemModel = new OrderItemModel();
    $this->paymentModel = new PaymentModel();
    $this->orderShippingAddressModel = new OrderShippingAddressModel();
  }

  public function index()
  {
    $search    = $this->request->getVar('q');
    $startDate = $this->request->getVar('start_date');
    $endDate   = $this->request->getVar('end_date');

    $builder = $this->refundModel->withAll()->orderBy('order_refunds.created_at', 'DESC');

    if (!empty($search)) {
      $builder->groupStart()
        ->like('order_refunds.order_id', $search)
        ->orLike('user_refund_accounts.account_name', $search)
        ->orLike('order_refunds.status', $search)
        ->groupEnd();
    }

    if (!empty($startDate)) {
      $builder->where('DATE(order_refunds.created_at) >=', $startDate);
    }
    if (!empty($endDate)) {
      $builder->where('DATE(order_refunds.created_at) <=', $endDate);
    }

    $refunds = $builder->findAll();

    return view('refund_sales/v_index', [
      'refunds'   => $refunds,
      'search'    => $search,
      'startDate' => $startDate,
      'endDate'   => $endDate,
    ]);
  }

  public function detail($refundId)
  {
    $refund = $this->refundModel->withAll()->where('order_refunds.order_refund_id', $refundId)->first();

    if (!$refund) {
      throw new \CodeIgniter\Exceptions\PageNotFoundException('Refund not found');
    }

    $orderId = $refund['order_id'];

    $order = $this->orderModel
      ->select('orders.order_id, orders.created_at AS order_date, orders.grand_total, orders.shipping_cost, orders.status_id, orders.tracking_number, orders.courier, customers.customer_name, customers.customer_email, order_statuses.status_name, order_statuses.status_code, shipping_methods.name AS shipping_method, shipping_methods.estimated_days')
      ->join('customers', 'customers.customer_id = orders.customer_id', 'left')
      ->join('order_statuses', 'order_statuses.status_id = orders.status_id', 'left')
      ->join('shipping_methods', 'shipping_methods.shipping_method_id = orders.shipping_method_id', 'left')
      ->where('orders.order_id', $orderId)
      ->where('orders.deleted_at', null)
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

    $refundItems = $this->refundItemModel->getByRefundId($refundId);

    $data = [
      'order' => $order,
      'items' => $items,
      'payment' => $payment,
      'shippingAddress' => $shippingAddress,
      'refund' => $refund,
      'refundItems' => $refundItems,
    ];

    return view('refund_sales/v_detail', $data);
  }
}

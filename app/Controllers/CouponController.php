<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CouponModel;

class CouponController extends BaseController
{
    protected $couponModel;

    public function __construct()
    {
        $this->couponModel = new CouponModel();
    }

    public function webIndex()
    {
        $page = $this->request->getVar('page') ?? 1;
        $perPage = 10;
        
        $coupons = $this->couponModel->orderBy('created_at', 'DESC')->paginate($perPage, 'default', $page);
        
        $pager = [
            'currentPage' => $this->couponModel->pager->getCurrentPage('default'),
            'totalPages'  => $this->couponModel->pager->getPageCount('default'),
            'limit'       => $perPage
        ];

        return view('coupons/v_index', [
            'coupons' => $coupons,
            'pager'   => $pager
        ]);
    }

    public function form()
    {
        $id = $this->request->getVar('id');
        $data = [];
        
        if ($id) {
            $coupon = $this->couponModel->find($id);
            if (!$coupon) {
                return redirect()->to('/coupons')->with('failed', 'Coupon not found.');
            }
            $data['coupon'] = $coupon;
        }

        return view('coupons/v_form', $data);
    }

    public function save()
    {
        $id = $this->request->getVar('id');
        $discountType = $this->request->getPost('discount_type');

        $rules = [
            'code'             => 'required|max_length[50]',
            'description'      => 'permit_empty',
            'discount_type'    => 'required|in_list[percentage,fixed,free_shipping]',
            'discount_value'   => $discountType === 'free_shipping' ? 'permit_empty|numeric' : 'required|numeric',
            'min_order_amount' => 'permit_empty|numeric',
            'max_discount'     => 'permit_empty|numeric',
            'start_date'       => 'required|valid_date',
            'end_date'         => 'required|valid_date',
            'usage_limit'      => 'permit_empty|integer',
            'per_user_limit'   => 'permit_empty|integer',
        ];

        if (!$id) {
            $rules['code'] .= '|is_unique[coupons.code]';
        } else {
            $rules['code'] .= "|is_unique[coupons.code,coupon_id,{$id}]";
        }

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            $errorMessage = implode('<br>', $errors);
            return redirect()->back()->withInput()->with('failed', $errorMessage);
        }

        // Format dates correctly for database (datetime-local from HTML outputs Y-m-d\TH:i)
        $startDate = $this->request->getPost('start_date');
        $endDate = $this->request->getPost('end_date');
        
        $startDate = str_replace('T', ' ', $startDate);
        if (strlen($startDate) == 16) {
            $startDate .= ':00';
        }
        
        $endDate = str_replace('T', ' ', $endDate);
        if (strlen($endDate) == 16) {
            $endDate .= ':00';
        }

        $discountValue = (float)$this->request->getPost('discount_value');
        if ($discountType === 'free_shipping') {
            $discountValue = 0;
        }

        $data = [
            'code'             => strtoupper($this->request->getPost('code')),
            'description'      => $this->request->getPost('description'),
            'discount_type'    => $discountType,
            'discount_value'   => $discountValue,
            'min_order_amount' => $this->request->getPost('min_order_amount') !== '' ? (float)$this->request->getPost('min_order_amount') : null,
            'max_discount'     => $this->request->getPost('max_discount') !== '' ? (float)$this->request->getPost('max_discount') : null,
            'start_date'       => $startDate,
            'end_date'         => $endDate,
            'usage_limit'      => $this->request->getPost('usage_limit') !== '' ? (int)$this->request->getPost('usage_limit') : null,
            'per_user_limit'   => $this->request->getPost('per_user_limit') !== '' ? (int)$this->request->getPost('per_user_limit') : null,
            'is_active'        => $this->request->getPost('is_active') ? 1 : 0,
            'first_order_only' => $this->request->getPost('first_order_only') ? 1 : 0,
        ];

        // Skip model validation because we already validated in the controller
        $this->couponModel->skipValidation(true);

        if ($id) {
            $this->couponModel->update($id, $data);
            $message = 'Coupon updated successfully!';
        } else {
            $this->couponModel->insert($data);
            $message = 'Coupon created successfully!';
        }

        return redirect()->to('/coupons')->with('success', $message);
    }

    public function webDelete($id)
    {
        $this->couponModel->delete($id);
        return redirect()->to('/coupons')->with('success', 'Coupon deleted successfully.');
    }
}

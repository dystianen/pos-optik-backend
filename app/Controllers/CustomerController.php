<?php

namespace App\Controllers;

use App\Models\CustomerModel;

class CustomerController extends BaseController
{
    protected $customerModel;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
    }

    public function index()
    {
        $currentPage = $this->request->getVar('page') ? (int)$this->request->getVar('page') : 1;
        $totalLimit = 10;
        $offset = ($currentPage - 1) * $totalLimit;

        $customers = $this->customerModel
            ->findAll($totalLimit, $offset);

        $totalRows = $this->customerModel
            ->countAllResults();

        $totalPages = ceil($totalRows / $totalLimit);

        $data = [
            "customers" => $customers,
            "pager" => [
                "totalPages" => $totalPages,
                "currentPage" => $currentPage,
                "limit" => $totalLimit,
            ],
        ];

        return view('customers/v_index', $data);
    }

    public function form($id = null)
    {
        $id = $this->request->getVar('id');
        $data = [];
        if ($id) {
            $customer = $this->customerModel->find($id);

            if (!$customer) {
                return redirect()->to('/customers')->with('failed', 'Customer not found');
            }

            $preferences = [];
            if (!empty($customer['customer_preferences'])) {
                $preferences = json_decode($customer['customer_preferences'], true);
            }

            $eyeHistory = [];
            if (!empty($customer['customer_eye_history'])) {
                $eyeHistory = json_decode($customer['customer_eye_history'], true);
            }

            $flattenedEyeHistory = [
                'left_axis' => $eyeHistory['left_eye']['axis'] ?? '',
                'left_sphere' => $eyeHistory['left_eye']['sphere'] ?? '',
                'left_cylinder' => $eyeHistory['left_eye']['cylinder'] ?? '',
                'right_axis' => $eyeHistory['right_eye']['axis'] ?? '',
                'right_sphere' => $eyeHistory['right_eye']['sphere'] ?? '',
                'right_cylinder' => $eyeHistory['right_eye']['cylinder'] ?? '',
                'condition' => $eyeHistory['condition'] ?? '',
                'last_checkup' => $eyeHistory['last_checkup'] ?? ''
            ];

            $data['customer'] = array_merge($customer, $preferences, $flattenedEyeHistory);
        }

        return view('customers/v_form', $data);
    }

    public function save()
    {
        $request = $this->request;

        $id = $request->getVar('id');

        $customerData = [
            'customer_name'       => $request->getVar('customer_name'),
            'customer_email'      => $request->getVar('customer_email'),
            'customer_phone'      => $request->getVar('customer_phone'),
            'customer_dob'        => $request->getVar('customer_dob'),
            'customer_gender'     => $request->getVar('customer_gender'),
            'customer_occupation' => $request->getVar('customer_occupation'),
        ];

        // Jika password diisi (saat create atau edit dan ingin update password)
        $password = $request->getVar('customer_password');
        if (!empty($password)) {
            $customerData['customer_password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        // Preferences dalam bentuk JSON
        $preferences = [
            'color'       => $request->getVar('color'),
            'material'    => $request->getVar('material'),
            'frame_style' => $request->getVar('frame_style'),
        ];
        $customerData['customer_preferences'] = json_encode($preferences);

        // Eye history dalam bentuk JSON
        $eyeHistory = [
            'condition'     => $request->getVar('condition'),
            'last_checkup'  => $request->getVar('last_checkup'),
            'left_eye' => [
                'axis'     => $request->getVar('left_axis'),
                'sphere'   => $request->getVar('left_sphere'),
                'cylinder' => $request->getVar('left_cylinder'),
            ],
            'right_eye' => [
                'axis'     => $request->getVar('right_axis'),
                'sphere'   => $request->getVar('right_sphere'),
                'cylinder' => $request->getVar('right_cylinder'),
            ],
        ];
        $customerData['customer_eye_history'] = json_encode($eyeHistory);

        $model = new CustomerModel();

        if ($id) {
            // Update
            $model->update($id, $customerData);
            return redirect()->to('/customers')->with('success', 'Customer updated successfully.');
        } else {
            // Create
            $model->insert($customerData);
            return redirect()->to('/customers')->with('success', 'Customer created successfully.');
        }
    }

    public function update($id)
    {
        $model = new CustomerModel();

        $data = $this->request->getVar([
            'customer_name',
            'customer_email',
            'customer_password',
            'customer_phone',
            'customer_dob',
            'customer_gender',
            'customer_occupation',
            'customer_eye_history',
            'customer_preferences'
        ]);

        $model->update($id, $data);
        return redirect()->to('/customer/edit/' . $id)->with('success', 'Customer updated successfully.');
    }

    public function delete($id)
    {
        $this->customerModel->delete($id);
        return redirect()->to('customers')->with('success', 'Customer deleted successfully');
    }
}

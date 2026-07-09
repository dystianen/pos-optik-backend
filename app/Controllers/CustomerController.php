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

        $search = $this->request->getVar('search');
        $gender = $this->request->getVar('gender');

        $builder = $this->customerModel;

        if (!empty($search)) {
            $builder->groupStart()
                ->like('customer_name', $search)
                ->orLike('customer_email', $search)
                ->orLike('customer_phone', $search)
                ->groupEnd();
        }

        if (!empty($gender)) {
            $builder->where('customer_gender', $gender);
        }

        // Clone builder for counting
        $countBuilder = clone $builder;

        $customers = $builder->orderBy('created_at', 'DESC')->findAll($totalLimit, $offset);
        $totalRows = $countBuilder->countAllResults(false);
        $totalPages = (int) ceil($totalRows / $totalLimit);

        $data = [
            "customers" => $customers,
            "pager" => [
                "totalPages" => $totalPages,
                "currentPage" => $currentPage,
                "limit" => $totalLimit,
            ],
            "search" => $search,
            "gender" => $gender,
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

            $data = [
                'customer' => $customer
            ];
        }

        return view('customers/v_form', $data);
    }

    public function save()
    {
        $request = $this->request;
        $id = $request->getVar('id');
        $rules = $this->customerModel->validationRules;

        if ($id) {
            $rules['customer_password'] = 'permit_empty|max_length[255]';
        } else {
            $rules['customer_password'] = 'required|max_length[255]';
        }

        // VALIDASI
        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('failed', implode('<br>', $this->validator->getErrors()));
        }

        $customerData = [
            'customer_name'   => $request->getVar('customer_name'),
            'customer_email'  => $request->getVar('customer_email'),
            'customer_phone'  => $request->getVar('customer_phone'),
            'customer_dob'    => $request->getVar('customer_dob'),
            'customer_gender' => strtolower($request->getVar('customer_gender')),
        ];

        $password = $request->getVar('customer_password');
        if (!empty($password)) {
            $customerData['customer_password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        try {
            if ($id) {
                if (!$this->customerModel->update($id, $customerData)) {
                    return redirect()->back()->with('failed', 'Gagal mengupdate customer.');
                }
                return redirect()->to('/customers')->with('success', 'Customer updated successfully.');
            } else {
                if (!$this->customerModel->insert($customerData)) {
                    return redirect()->back()->with('failed', 'Gagal membuat customer baru.');
                }
                return redirect()->to('/customers')->with('success', 'Customer created successfully.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('failed', $e->getMessage());
        }
    }

    public function delete($id)
    {
        $this->customerModel->delete($id);
        return redirect()->to('customers')->with('success', 'Customer deleted successfully');
    }

    public function resetPassword($id)
    {
        $customer = $this->customerModel->find($id);
        if (!$customer) {
            return redirect()->to('/customers')->with('failed', 'Customer tidak ditemukan.');
        }

        // Generate random 8-character password
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $randomPassword = substr(str_shuffle(str_repeat($chars, 5)), 0, 8);

        $customerData = [
            'customer_password' => password_hash($randomPassword, PASSWORD_DEFAULT)
        ];

        try {
            if (!$this->customerModel->update($id, $customerData)) {
                return redirect()->back()->with('failed', 'Gagal mereset password customer.');
            }

            return redirect()->to('/customers')
                ->with('success', 'Password customer berhasil direset.')
                ->with('reset_password', $randomPassword)
                ->with('customer_name', $customer['customer_name']);
        } catch (\Exception $e) {
            return redirect()->back()->with('failed', $e->getMessage());
        }
    }
}


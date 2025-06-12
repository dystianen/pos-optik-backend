<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use App\Models\EyeExaminationModel;
use App\Models\ProductModel;

class EyeExaminationController extends BaseController
{
    protected $eyeExaminationModel, $customerModel;

    public function __construct()
    {
        $this->eyeExaminationModel = new EyeExaminationModel();
        $this->customerModel = new CustomerModel();
    }

    public function index()
    {
        $currentPage = $this->request->getVar('page') ? (int)$this->request->getVar('page') : 1;
        $totalLimit = 10;
        $offset = ($currentPage - 1) * $totalLimit;

        $eyeExaminations = $this->eyeExaminationModel
            ->join('customers', 'customers.customer_id = eye_examinations.customer_id')
            ->findAll($totalLimit, $offset);

        $totalRows = $this->eyeExaminationModel
            ->join('customers', 'customers.customer_id = eye_examinations.customer_id')
            ->countAllResults();

        $totalPages = ceil($totalRows / $totalLimit);

        $data = [
            "eyeExaminations" => $eyeExaminations,
            "pager" => [
                "totalPages" => $totalPages,
                "currentPage" => $currentPage,
                "limit" => $totalLimit,
            ],
        ];

        return view('eye_examinations/v_index', $data);
    }

    public function form()
    {
        $id = $this->request->getVar('id');
        $data['customers'] = $this->customerModel->findAll();

        if ($id) {
            $eyeExamination = $this->eyeExaminationModel->find($id);
            if (!$eyeExamination) {
                return redirect()->to('/eye-examinations')->with('error', 'Transaction not found.');
            }
            $data['eyeExamination'] = $eyeExamination;
        }

        return view('eye_examinations/v_form', $data);
    }

    public function save()
    {
        $id = $this->request->getVar('id');
        $session = session();

        $rules = [
            'product_id' => 'required|integer',
            'transaction_type' => 'required|in_list[IN,OUT]',
            'quantity' => 'required|integer',
            'description' => 'permit_empty|string'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please check your input.');
        }

        $data = [
            'product_id' => $this->request->getVar('product_id'),
            'transaction_type' => $this->request->getVar('transaction_type'),
            'quantity' => $this->request->getVar('quantity'),
            'description' => $this->request->getVar('description'),
            'user_id' => $session->get('id')
        ];

        if ($id) {
            $this->eyeExaminationModel->update($id, $data);
            $message = 'Transaction updated successfully!';
        } else {
            $this->eyeExaminationModel->insert($data);
            $message = 'Transaction created successfully!';
        }

        return redirect()->to('/inventory')->with('success', $message);
    }
}

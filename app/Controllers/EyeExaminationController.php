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

        $rules = [
            'customer_id' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please check your input.');
        }

        $data = [
            'customer_id' => $this->request->getVar('customer_id'),
            'left_eye_axis' => $this->request->getVar('left_eye_axis'),
            'left_eye_sphere' => $this->request->getVar('left_eye_sphere'),
            'left_eye_cylinder' => $this->request->getVar('left_eye_cylinder'),
            'right_eye_axis' => $this->request->getVar('right_eye_axis'),
            'right_eye_sphere' => $this->request->getVar('right_eye_sphere'),
            'right_eye_cylinder' => $this->request->getVar('right_eye_cylinder'),
            'symptomps' => $this->request->getVar('symptomps'),
            'diagnosis' => $this->request->getVar('diagnosis'),
        ];

        if ($id) {
            $this->eyeExaminationModel->update($id, $data);
            $message = 'Eye examination updated successfully!';
        } else {
            $this->eyeExaminationModel->insert($data);
            $message = 'Eye examination created successfully!';
        }

        return redirect()->to('/eye-examinations')->with('success', $message);
    }
}

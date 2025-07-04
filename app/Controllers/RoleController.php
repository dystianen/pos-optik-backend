<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\RoleModel;

class RoleController extends BaseController
{
    protected $roleModel;

    public function __construct()
    {
        $this->roleModel = new RoleModel();
    }

    public function index()
    {
        $currentPage = $this->request->getVar('page') ? (int)$this->request->getVar('page') : 1;
        $totalLimit = 10;
        $offset = ($currentPage - 1) * $totalLimit;

        $roles = $this->roleModel
            ->orderBy('created_at',  'DESC')
            ->findAll($totalLimit, $offset);

        $totalRows = $this->roleModel
            ->countAllResults();

        $totalPages = ceil($totalRows / $totalLimit);

        $data = [
            "roles" => $roles,
            "pager" => [
                "totalPages" => $totalPages,
                "currentPage" => $currentPage,
                "limit" => $totalLimit,
            ],
        ];

        return view('roles/v_index', $data);
    }

    public function form()
    {

        $id = $this->request->getVar('id');
        $data = [];

        if ($id) {
            $role = $this->roleModel->find($id);
            if (!$role) {
                return redirect()->to('/roles')->with('failed', 'Role not found.');
            }
            $data['role'] = $role;
        }

        return view('roles/v_form', $data);
    }

    public function save()
    {
        $id = $this->request->getVar('id');

        $rules = [
            'role_name' => 'required',
            'role_description' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('failed', 'Please check your input.');
        };

        $data = [
            'role_name' => $this->request->getPost('role_name'),
            'role_description' => $this->request->getPost('role_description'),
        ];

        if ($id) {
            $this->roleModel->update($id, $data);
            $message = 'Role updated successfully!';
        } else {
            $this->roleModel->insert($data);
            $message = 'Role created successfully!';
        }

        return redirect()->to('/roles')->with('success', $message);
    }

    public function delete($id)
    {
        $this->roleModel->delete($id);
        return redirect()->to('/roles')->with('success', 'Role deleted successfully.');
    }
}

<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\RoleModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class UserController extends BaseController
{
    protected $userModel, $roleModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
    }

    public function index()
    {
        $currentPage = $this->request->getVar('page') ? (int)$this->request->getVar('page') : 1;
        $totalLimit = 10;
        $offset = ($currentPage - 1) * $totalLimit;

        $users = $this->userModel
            ->join('roles', 'roles.role_id = users.role_id')
            ->findAll($totalLimit, $offset);

        $totalRows = $this->userModel
            ->join('roles', 'roles.role_id = users.role_id')
            ->countAllResults();

        $totalPages = ceil($totalRows / $totalLimit);

        $data = [
            "users" => $users,
            "pager" => [
                "totalPages" => $totalPages,
                "currentPage" => $currentPage,
                "limit" => $totalLimit,
            ],
        ];

        return view('users/v_index', $data);
    }

    public function form()
    {

        $id = $this->request->getVar('id');
        $roles = $this->roleModel->findAll();
        $data = [];
        $data['roles'] = $roles;

        if ($id) {
            $user = $this->userModel->find($id);
            if (!$user) {
                return redirect()->to('/users')->with('error', 'Transaction not found.');
            }
            $data['user'] = $user;
        }

        return view('users/v_form', $data);
    }

    public function save()
    {
        $id = $this->request->getVar('id');

        $rules = [
            'user_name' => 'required',
            'user_email' => 'required',
            'password' => 'required',
            'role_id' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please check your input.');
        };

        $data = [
            'user_name' => $this->request->getPost('user_name'),
            'user_email' => $this->request->getPost('user_email'),
            'password' => $this->request->getPost('password'),
            'role_id' => $this->request->getPost('role_id'),
        ];

        if ($id) {
            $this->userModel->update($id, $data);
            $message = 'User updated successfully!';
        } else {
            $this->userModel->insert($data);
            $message = 'User created successfully!';
        }

        return redirect()->to('/users')->with('success', $message);
    }
}

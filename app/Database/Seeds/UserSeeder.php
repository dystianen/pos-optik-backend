<?php

namespace App\Database\Seeds;

use App\Models\UserModel;
use App\Models\RoleModel;
use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $userModel = new UserModel();
        $roleModel = new RoleModel();

        // Ambil semua role untuk mendapatkan UUID-nya
        $roles = $roleModel->findAll();
        $roleMap = [];

        foreach ($roles as $role) {
            // key = role_name, value = role_id (UUID)
            $roleMap[$role['role_name']] = $role['role_id'];
        }

        // Pastikan role_name sesuai dengan RoleSeeder
        $data = [
            [
                'user_name'  => 'Owner',
                'user_email' => 'owner@gmail.com',
                'password'   => password_hash('123', PASSWORD_DEFAULT),
                'role_id'    => $roleMap['owner'],
            ],
            [
                'user_name'  => 'Cashier',
                'user_email' => 'cashier@gmail.com',
                'password'   => password_hash('123', PASSWORD_DEFAULT),
                'role_id'    => $roleMap['cashier'],
            ],
            [
                'user_name'  => 'Admin',
                'user_email' => 'admin@gmail.com',
                'password'   => password_hash('123', PASSWORD_DEFAULT),
                'role_id'    => $roleMap['admin'],
            ],
        ];

        foreach ($data as $row) {
            $userModel->insert($row);
        }
    }
}

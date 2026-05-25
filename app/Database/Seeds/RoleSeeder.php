<?php

namespace App\Database\Seeds;

use App\Models\RoleModel;
use CodeIgniter\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roleModel = new RoleModel();

        $data = [
            [
                'role_name'        => 'owner',
                'role_description' => 'Owner has full access'
            ],
            [
                'role_name'        => 'admin',
                'role_description' => 'Admin has full access'
            ],
            [
                'role_name'        => 'cashier',
                'role_description' => 'Cashier handles transactions'
            ],
        ];

        foreach ($data as $row) {
            $roleModel->insert($row);
        }
    }
}

<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'user_name' => 'Admin Super',
                'user_email' => 'admin@gmail.com',
                'password' => password_hash('123', PASSWORD_DEFAULT),
                'role_id' => 1
            ],
            [
                'user_name' => 'Dr. Mata',
                'user_email' => 'optometrist@gmail.com',
                'password' => password_hash('123', PASSWORD_DEFAULT),
                'role_id' => 2
            ],
            [
                'user_name' => 'Kasir Toko',
                'user_email' => 'cashier@gmail.com',
                'password' => password_hash('123', PASSWORD_DEFAULT),
                'role_id' => 3
            ],
            [
                'user_name' => 'Petugas Gudang',
                'user_email' => 'inventory@gmail.com',
                'password' => password_hash('123', PASSWORD_DEFAULT),
                'role_id' => 4
            ],
            [
                'user_name' => 'Customer',
                'user_email' => 'customer@gmail.com',
                'password' => password_hash('123', PASSWORD_DEFAULT),
                'role_id' => 5
            ]
        ];

        $this->db->table('users')->insertBatch($data);
    }
}

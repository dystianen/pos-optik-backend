<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DataSeeder extends Seeder
{
    public function run()
    {
        $this->call('RoleSeeder');
        $this->call('CustomerSeeder');
        $this->call('ProductCategorySeeder');
        // $this->call('ProductSeeder');
    }
}

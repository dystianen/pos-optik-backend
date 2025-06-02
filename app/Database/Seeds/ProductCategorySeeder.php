<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'category_name' => 'Frame Kacamata',
                'category_description' => 'Berbagai macam frame kacamata pria dan wanita'
            ],
            [
                'category_name' => 'Lensa Kacamata',
                'category_description' => 'Lensa dengan berbagai jenis dan indeks'
            ],
            [
                'category_name' => 'Softlens',
                'category_description' => 'Lensa kontak sehari-hari dan khusus'
            ],
            [
                'category_name' => 'Aksesoris',
                'category_description' => 'Tali kacamata, case, cleaner, dll'
            ]
        ];

        $this->db->table('product_categories')->insertBatch($data);
    }
}

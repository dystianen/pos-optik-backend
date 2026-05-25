<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\ProductCategoryModel;

class ProductCategorySeeder extends Seeder
{
    public function run()
    {
        $categoryModel = new ProductCategoryModel();

        $data = [
            [
                'category_name' => 'Sunglasses',
                'category_description' => 'Various kinds of sunglasses for men and women'
            ],
            [
                'category_name' => 'Contact Lens',
                'category_description' => 'Various kinds of contact lenses for daily and special'
            ],
            [
                'category_name' => 'Accessories',
                'category_description' => 'Eyewear accessories such as eyeglass straps, cases, cleaners, etc.'
            ]
        ];

        helper(['slug']);
        foreach ($data as $row) {
            $row['category_slug'] = generate_unique_category_slug($row['category_name']);
            $categoryModel->insert($row);
        }
    }
}

<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Faker\Factory;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $faker = Factory::create('id_ID');

        // Ambil semua kategori dari database
        $categories = $this->db->table('product_categories')->get()->getResultArray();

        $products = [];

        // Data specs
        $frameMaterials = ['Acetate', 'Titanium', 'Stainless Steel', 'TR-90'];
        $frameStyles = ['Full-Rim', 'Half-Rim', 'Rimless', 'Cat Eye'];
        $lensTypes = ['Single Vision', 'Bifokal', 'Progressive', 'Blue Cut'];
        $lensIndices = ['1.50', '1.56', '1.60', '1.67', '1.74'];
        $softlensBrands = ['Acuvue', 'Bausch+Lomb', 'Alcon', 'CooperVision'];

        // Generate 30 produk dummy
        for ($i = 0; $i < 30; $i++) {
            $category = $faker->randomElement($categories);
            $categoryName = $category['category_name'];

            // Default specs
            $model = null;
            $duration = null;
            $material = null;
            $base_curve = null;
            $diameter = null;
            $power_range = null;
            $water_content = null;
            $uv_protection = null;
            $color = null;
            $coating = null;

            switch ($categoryName) {
                case 'Frame Kacamata':
                    $material = $faker->randomElement($frameMaterials);
                    $model = $faker->randomElement($frameStyles);
                    $diameter = $faker->randomElement(['135mm', '140mm', '145mm']);
                    break;

                case 'Lensa Kacamata':
                    $model = $faker->randomElement($lensTypes);
                    $material = 'Polycarbonate';
                    $uv_protection = $faker->boolean(70) ? 'Yes' : 'No';
                    $coating = $faker->boolean(50) ? 'Anti-Reflective' : 'Scratch Resistant';
                    break;

                case 'Softlens':
                    $material = $faker->randomElement($softlensBrands);
                    $duration = $faker->randomElement(['Daily', 'Monthly', 'Yearly']);
                    $water_content = $faker->randomElement(['38%', '42%', '55%']);
                    $base_curve = $faker->randomElement(['8.4mm', '8.6mm', '8.8mm']);
                    $power_range = '-10.00 to +6.00';
                    break;

                case 'Aksesoris':
                    $material = $faker->randomElement(['Silicon', 'Polycarbonate', 'Microfiber']);
                    $model = $faker->randomElement(['Case', 'Cleaning Spray', 'Microfiber Cloth', 'Straps']);
                    break;
            }

            $products[] = [
                'category_id' => $category['category_id'],
                'product_name' => $this->generateProductName($categoryName, $faker),
                'product_brand' => $faker->company,
                'product_price' => $this->generatePriceByCategory($categoryName, $faker),
                'product_stock' => $faker->numberBetween(5, 100),
                'model' => $model,
                'duration' => $duration,
                'material' => $material,
                'base_curve' => $base_curve,
                'diameter' => $diameter,
                'power_range' => $power_range,
                'water_content' => $water_content,
                'uv_protection' => $uv_protection,
                'color' => $color,
                'coating' => $coating,
                'product_image_url' => $this->generateImageUrl($categoryName),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }

        $this->db->table('products')->insertBatch($products);
    }

    private function generateProductName(string $category, $faker)
    {
        switch ($category) {
            case 'Frame Kacamata':
                return $faker->randomElement(['Classic', 'Trendy', 'Elegant', 'Sporty']) . ' ' .
                    $faker->randomElement(['Aviator', 'Round', 'Square', 'Oversized']) . ' Frame';

            case 'Lensa Kacamata':
                return $faker->randomElement(['Premium', 'Comfort', 'Ultra']) . ' ' .
                    $faker->randomElement(['Clear', 'Blue Cut', 'Photochromic']) . ' Lens';

            case 'Softlens':
                return $faker->randomElement(['Daily', 'Monthly']) . ' Disposable ' .
                    $faker->randomElement(['Moist', 'Breathable', 'Comfort']) . ' Softlens';

            case 'Aksesoris':
                return $faker->randomElement(['Premium', 'Travel', 'Basic']) . ' ' .
                    $faker->randomElement(['Case', 'Cleaning Kit', 'Straps']);
        }
    }

    private function generatePriceByCategory(string $category, $faker)
    {
        switch ($category) {
            case 'Frame Kacamata':
                return $faker->randomFloat(2, 250000, 3000000);
            case 'Lensa Kacamata':
                return $faker->randomFloat(2, 500000, 5000000);
            case 'Softlens':
                return $faker->randomFloat(2, 100000, 800000);
            case 'Aksesoris':
                return $faker->randomFloat(2, 50000, 500000);
        }
    }

    private function generateImageUrl(string $category)
    {
        $baseUrl = 'https://example.com/images/products/';

        switch ($category) {
            case 'Frame Kacamata':
                return $baseUrl . 'frame-' . rand(1, 10) . '.jpg';
            case 'Lensa Kacamata':
                return $baseUrl . 'lens-' . rand(1, 5) . '.jpg';
            case 'Softlens':
                return $baseUrl . 'softlens-' . rand(1, 8) . '.jpg';
            case 'Aksesoris':
                return $baseUrl . 'accessory-' . rand(1, 6) . '.jpg';
        }
    }
}

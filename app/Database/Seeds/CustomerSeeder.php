<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Faker\Factory;

class CustomerSeeder extends Seeder
{
    public function run()
    {
        $faker = Factory::create('id_ID');

        $eyeConditions = [
            'normal',
            'miopi',
            'hipermetropi',
            'astigmatisme'
        ];

        $customers = [];
        for ($i = 0; $i < 20; $i++) {
            $eyeHistory = [
                'left_eye'  => [
                    'sphere'   => $faker->randomFloat(2, -10, 0), // Contoh: -2.50
                    'cylinder' => $faker->randomFloat(2, -5, 0),
                    'axis'     => $faker->numberBetween(0, 180)
                ],
                'right_eye' => [
                    'sphere'   => $faker->randomFloat(2, -10, 0),
                    'cylinder' => $faker->randomFloat(2, -5, 0),
                    'axis'     => $faker->numberBetween(0, 180)
                ],
                'last_checkup' => $faker->dateTimeBetween('-2 years')->format('Y-m-d'),
                'condition'    => $faker->randomElement($eyeConditions)
            ];

            $customers[] = [
                'customer_name'         => $faker->name,
                'customer_email'        => $faker->unique()->email,
                'customer_password'     => password_hash('123', PASSWORD_DEFAULT),
                'customer_phone'        => $faker->phoneNumber,
                'customer_dob'          => $faker->dateTimeBetween('-70 years', '-18 years')->format('Y-m-d'),
                'customer_gender'       => $faker->randomElement(['male', 'female']),
                'customer_occupation'   => $faker->jobTitle,
                'customer_eye_history'  => json_encode($eyeHistory),
                'customer_preferences'  => json_encode([
                    'frame_style' => $faker->randomElement(['full-rim', 'rimless', 'half-rim']),
                    'color'       => $faker->colorName,
                    'material'    => $faker->randomElement(['acetate', 'metal', 'titanium'])
                ]),
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s')
            ];
        }

        $this->db->table('customers')->insertBatch($customers);
    }
}

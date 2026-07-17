<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DataSeeder extends Seeder
{
    public function run()
    {
        $this->call('RoleSeeder');
        $this->call('UserSeeder');
        $this->call('CustomerSeeder');
        $this->call('ProductCategorySeeder');
        $this->call('ProductAttributeSeeder');
        $this->call('ProductAttributeMasterValuesSeeder');
        $this->call('OrderStatusSeeder');
        $this->call('ShippingMethodSeeder');
        $this->call('ShippingRateSeeder');
        $this->call('PaymentMethodSeeder');
        $this->call('CouponSeeder');
    }
}

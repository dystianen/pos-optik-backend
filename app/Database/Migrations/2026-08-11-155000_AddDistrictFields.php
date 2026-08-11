<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDistrictFields extends Migration
{
    public function up()
    {
        $fields = [
            'district' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'city'
            ],
            'district_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'after'      => 'city_id'
            ],
        ];

        $this->forge->addColumn('customer_shipping_addresses', $fields);
        $this->forge->addColumn('order_shipping_addresses', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('customer_shipping_addresses', ['district', 'district_id']);
        $this->forge->dropColumn('order_shipping_addresses', ['district', 'district_id']);
    }
}

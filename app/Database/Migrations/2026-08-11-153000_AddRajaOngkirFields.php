<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRajaOngkirFields extends Migration
{
    public function up()
    {
        $fields = [
            'city_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'after'      => 'city'
            ],
            'province_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'after'      => 'province'
            ],
        ];

        $this->forge->addColumn('customer_shipping_addresses', $fields);
        $this->forge->addColumn('order_shipping_addresses', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('customer_shipping_addresses', ['city_id', 'province_id']);
        $this->forge->dropColumn('order_shipping_addresses', ['city_id', 'province_id']);
    }
}

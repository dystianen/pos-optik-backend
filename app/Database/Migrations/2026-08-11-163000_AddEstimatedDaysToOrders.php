<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEstimatedDaysToOrders extends Migration
{
    public function up()
    {
        $fields = [
            'estimated_days' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'courier'
            ]
        ];
        $this->forge->addColumn('orders', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('orders', 'estimated_days');
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFirstOrderOnlyToCoupons extends Migration
{
    public function up()
    {
        $fields = [
            'first_order_only' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
                'null'       => false,
                'after'      => 'is_active',
            ],
        ];
        $this->forge->addColumn('coupons', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('coupons', 'first_order_only');
    }
}

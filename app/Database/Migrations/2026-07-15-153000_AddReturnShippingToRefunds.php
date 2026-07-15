<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReturnShippingToRefunds extends Migration
{
    public function up()
    {
        $this->forge->addColumn('order_refunds', [
            'return_courier' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'evidence_url',
                'comment'    => 'Courier used for return shipping',
            ],
            'return_tracking_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'return_courier',
                'comment'    => 'Tracking number for return shipping',
            ],
            'return_shipped_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'after'      => 'return_tracking_number',
                'comment'    => 'Timestamp when return was shipped',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('order_refunds', 'return_courier');
        $this->forge->dropColumn('order_refunds', 'return_tracking_number');
        $this->forge->dropColumn('order_refunds', 'return_shipped_at');
    }
}

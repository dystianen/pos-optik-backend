<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateOrdersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'order_id'            => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'customer_id'   => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'order_date'    => [
                'type' => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
            'total_price'   => [
                'type' => 'DECIMAL',
                'constraint' => '10,2'
            ],
            'payment_method' => [
                'type' => 'VARCHAR',
                'constraint' => 50
            ],
            'status'        => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'paid', 'shipped', 'cancelled']
            ],
        ]);

        $this->forge->addPrimaryKey('order_id');
        $this->forge->addForeignKey('customer_id', 'customers', 'customer_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('orders');
    }

    public function down()
    {
        $this->forge->dropTable('orders');
    }
}

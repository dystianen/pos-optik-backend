<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveShippingMethodsCompletely extends Migration
{
    public function up()
    {
        // 1. Drop FK dari orders ke shipping_methods
        $this->db->query("ALTER TABLE orders DROP FOREIGN KEY orders_shipping_method_id_foreign");

        // 2. Drop kolom shipping_method_id dari orders
        $this->forge->dropColumn('orders', 'shipping_method_id');

        // 3. Drop tabel shipping_rates (ada FK ke shipping_methods)
        $this->forge->dropTable('shipping_rates', true);

        // 4. Drop tabel shipping_methods
        $this->forge->dropTable('shipping_methods', true);
    }

    public function down()
    {
        // Re-create shipping_methods
        $this->forge->addField([
            'shipping_method_id' => [
                'type'       => 'CHAR',
                'constraint' => 36,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'provider' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'estimated_days' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'is_active' => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('shipping_method_id', true);
        $this->forge->createTable('shipping_methods');

        // Re-create shipping_rates
        $this->forge->addField([
            'rate_id' => ['type' => 'CHAR', 'constraint' => 36],
            'shipping_method_id' => ['type' => 'CHAR', 'constraint' => 36, 'null' => false],
            'destination' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => false],
            'cost' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => false],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('rate_id', true);
        $this->forge->addForeignKey('shipping_method_id', 'shipping_methods', 'shipping_method_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('shipping_rates');

        // Re-add shipping_method_id to orders
        $this->forge->addColumn('orders', [
            'shipping_method_id' => [
                'type'       => 'CHAR',
                'constraint' => 36,
                'null'       => true,
                'after'      => 'status_id',
            ],
        ]);
    }
}

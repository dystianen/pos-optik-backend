<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInventoryTransactions extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'inventory_transaction_id' => [
                'type' => 'CHAR',
                'constraint' => 36,
            ],
            'user_id' => [
                'type'       => 'CHAR',
                'constraint' => 36,
                'null'       => true,
                'default'    => null,
            ],
            'product_id' => [
                'type' => 'CHAR',
                'constraint' => 36,
            ],
            'variant_id' => [
                'type' => 'CHAR',
                'constraint' => 36,
                'null'       => true,
                'default'    => null,
            ],
            'transaction_type' => [
                'type' => 'ENUM',
                'constraint' => ['in', 'out'],
            ],
            'reference_type' => [
                'type' => 'ENUM',
                'constraint' => ['order', 'adjustment', 'return', 'transfer', 'initial'],
                'null' => false,
            ],
            'reference_id' => [
                'type' => 'CHAR',
                'constraint' => 36,
            ],
            'quantity' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'transaction_date' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
        ]);

        $this->forge->addKey('inventory_transaction_id', true);

        // Tambahkan foreign key
        $this->forge->addForeignKey('product_id', 'products', 'product_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('variant_id', 'product_variants', 'variant_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'user_id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('inventory_transactions');
    }

    public function down()
    {
        $this->forge->dropTable('inventory_transactions');
    }
}

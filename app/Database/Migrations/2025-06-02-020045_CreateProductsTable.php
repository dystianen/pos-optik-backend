<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'product_id' => [
                'type' => 'CHAR',
                'constraint' => 36,
            ],
            'category_id' => [
                'type' => 'CHAR',
                'constraint' => 36,
            ],
            'product_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
            'product_sku' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'unique' => true,
                'after' => 'product_name',
            ],
            'product_price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2'
            ],
            'product_stock' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0
            ],
            'product_brand' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true
            ],

            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_active' => [
                'type' => 'BOOLEAN',
                'default' => true,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
            'deleted_at'  => [
                'type' => 'DATETIME',
                'null' => true
            ],
        ]);

        $this->forge->addPrimaryKey('product_id');
        $this->forge->addForeignKey('category_id', 'product_categories', 'category_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('products');
    }

    public function down()
    {
        $this->forge->dropTable('products');
    }
}

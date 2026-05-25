<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductCategoriesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'category_id' => [
                'type' => 'CHAR',
                'constraint' => 36,
            ],
            'category_name' => [
                'type' => 'VARCHAR',
                'constraint' => 50
            ],
            'category_slug' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'category_description' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'variant_mode' => [
                'type' => 'ENUM',
                'constraint' => ['off', 'combination'],
                'default' => 'off',
            ],
            'is_prescription_supported' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
        ]);

        $this->forge->addPrimaryKey('category_id');
        $this->forge->createTable('product_categories');
    }

    public function down()
    {
        $this->forge->dropTable('product_categories');
    }
}

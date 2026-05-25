<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ProductAttributes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'attribute_id' => [
                'type'           => 'CHAR',
                'constraint'     => 36,
            ],
            'attribute_name' => [
                'type'           => 'VARCHAR',
                'constraint'     => 50,
            ],
            'category_id' => [
                'type' => 'CHAR',
                'constraint' => 36,
                'null' => true,
            ],
            'attribute_type' => [
                'type' => 'ENUM',
                'constraint' => [
                    'text',
                    'textarea',
                    'number',
                    'dropdown',
                    'multiselect',
                    'checkbox',
                    'radio',
                ],
                'default' => 'text',
            ],
            'is_variantable' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'is_required' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'is_filterable' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'use_master_values' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'sort_order' => [
                'type' => 'INT',
                'default' => 0,
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
                'null' => true,
            ],
        ]);

        $this->forge->addKey('attribute_id', true);
        $this->forge->addForeignKey(
            'category_id',
            'product_categories',
            'category_id',
            'CASCADE',
            'SET NULL'
        );
        $this->forge->createTable('product_attributes');
    }

    public function down()
    {
        $this->forge->dropTable('product_attributes');
    }
}

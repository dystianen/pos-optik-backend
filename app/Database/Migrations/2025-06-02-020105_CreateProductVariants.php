<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductVariants extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'variant_id' => [
                'type' => 'CHAR',
                'constraint' => 36,
            ],

            'product_id' => [
                'type' => 'CHAR',
                'constraint' => 36,
            ],

            'variant_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],

            'variant_sku' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'unique' => true,
            ],

            'variant_signature' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],

            'price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
            ],

            'stock' => [
                'type' => 'INT',
                'constraint' => 11,
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

        /**
         * PRIMARY KEY
         */
        $this->forge->addKey('variant_id', true);

        /**
         * INDEXES
         */
        $this->forge->addKey(
            ['product_id'],
            false,
            'idx_product_variants_product'
        );

        $this->forge->addUniqueKey(
            ['variant_sku'],
            'uidx_variant_sku'
        );

        /**
         * FOREIGN KEY
         */
        $this->forge->addForeignKey(
            'product_id',
            'products',
            'product_id',
            'CASCADE',
            'CASCADE'
        );

        /**
         * CREATE TABLE
         */
        $this->forge->createTable('product_variants');
    }

    public function down()
    {
        $this->forge->dropTable('product_variants');
    }
}

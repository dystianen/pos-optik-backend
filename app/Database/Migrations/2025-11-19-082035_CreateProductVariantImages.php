<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductVariantImages extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'pv_image_id' => [
                'type' => 'CHAR',
                'constraint' => 36,
            ],

            'variant_id' => [
                'type' => 'CHAR',
                'constraint' => 36,
                'null' => false,
            ],

            'product_image_id' => [
                'type' => 'CHAR',
                'constraint' => 36,
                'null' => false,
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
        $this->forge->addKey('pv_image_id', true);

        /**
         * UNIQUE
         * 1 variant = 1 image
         */
        $this->forge->addUniqueKey(
            ['variant_id'],
            'uidx_variant_id'
        );

        /**
         * INDEXES
         */
        $this->forge->addKey(
            ['product_image_id'],
            false,
            'idx_product_image_id'
        );

        /**
         * FOREIGN KEYS
         */
        $this->forge->addForeignKey(
            'variant_id',
            'product_variants',
            'variant_id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->addForeignKey(
            'product_image_id',
            'product_images',
            'product_image_id',
            'CASCADE',
            'CASCADE'
        );

        /**
         * CREATE TABLE
         */
        $this->forge->createTable('product_variant_images');
    }

    public function down()
    {
        $this->forge->dropTable('product_variant_images');
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductImages extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'product_image_id' => [
                'type' => 'CHAR',
                'constraint' => 36,
            ],
            'product_id' => [
                'type' => 'CHAR',
                'constraint' => 36,
                'null' => false,
            ],
            'url' => [
                'type' => 'VARCHAR',
                'constraint' => 1024,
                'null' => false,
            ],
            'alt_text' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'sort_order' => [
                'type' => 'INT',
                'default' => 0,
            ],
            'type' => [
                'type' => 'ENUM',
                'constraint' => ['gallery', 'variant']
            ],
            'is_primary' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'mime_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'size_bytes' => [
                'type' => 'INT',
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
                'null' => true,
            ],
        ]);

        /**
         * PRIMARY KEY
         */
        $this->forge->addKey('product_image_id', true);

        /**
         * INDEXES
         */
        $this->forge->addKey(
            ['product_id', 'type', 'is_primary'],
            false,
            'idx_product_images_lookup'
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
        $this->forge->createTable('product_images');
    }

    public function down()
    {
        $this->forge->dropTable('product_images');
    }
}

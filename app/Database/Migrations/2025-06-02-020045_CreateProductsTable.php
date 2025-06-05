<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'product_id'           => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'category_id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'product_name'          => ['type' => 'VARCHAR', 'constraint' => 100],
            'product_price'         => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'product_stock'         => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'product_brand'         => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'product_image_url'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],

            // Fields tambahan untuk specs kacamata
            'model'                 => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'duration'              => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'material'              => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'base_curve'            => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'diameter'              => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'power_range'           => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'water_content'         => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'uv_protection'         => ['type' => 'BOOLEAN', 'null' => true],
            'color'                 => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'coating'               => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'created_at'            => ['type' => 'DATETIME', 'null' => true],
            'updated_at'            => ['type' => 'DATETIME', 'null' => true],
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

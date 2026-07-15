<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHasVariantsToProducts extends Migration
{
    public function up()
    {
        // Tambah kolom has_variants ke tabel products
        $this->forge->addColumn('products', [
            'has_variants' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'null'       => false,
                'default'    => 0,
                'after'      => 'product_stock',
                'comment'    => '0=simple product, 1=variable product (stock dari variant)',
            ],
        ]);

        // Sync nilai has_variants dari data yang sudah ada
        $this->db->query("
            UPDATE products
            SET has_variants = 1
            WHERE product_id IN (
                SELECT DISTINCT product_id
                FROM product_variants
                WHERE deleted_at IS NULL
            )
        ");
    }

    public function down()
    {
        $this->forge->dropColumn('products', 'has_variants');
    }
}

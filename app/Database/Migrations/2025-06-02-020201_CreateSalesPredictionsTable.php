<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSalesPredictionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'sales_prediction_id'                 => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'product_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'prediction_date'   => ['type' => 'DATE'],
            'predicted_quantity' => ['type' => 'FLOAT'], // Hasil prediksi ML
            'confidence_score'  => ['type' => 'FLOAT'], // Akurasi prediksi (0-1)
            'created_at'         => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('sales_prediction_id');
        $this->forge->addForeignKey('product_id', 'products', 'product_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('sales_predictions');
    }

    public function down()
    {
        $this->forge->dropTable('sales_predictions');
    }
}

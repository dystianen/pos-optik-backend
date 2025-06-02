<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEyeExaminationsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'eye_examination_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'customer_id'        => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'left_eye_sphere'    => [
                'type' => 'FLOAT',
                'null' => true
            ],
            'left_eye_cylinder'  => [
                'type' => 'FLOAT',
                'null' => true
            ],
            'left_eye_axis'      => [
                'type' => 'FLOAT',
                'null' => true
            ],
            'right_eye_sphere'   => [
                'type' => 'FLOAT',
                'null' => true
            ],
            'right_eye_cylinder' => [
                'type' => 'FLOAT',
                'null' => true
            ],
            'right_eye_axis'     => [
                'type' => 'FLOAT',
                'null' => true
            ],
            'symptoms'           => [
                'type' => 'TEXT',
                'null' => true
            ], // Gejala mata
            'diagnosis'          => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true
            ], // e.g., "miopi", "astigmatisme"
            'created_at'         => [
                'type' => 'DATETIME',
                'null' => true
            ],
        ]);

        $this->forge->addPrimaryKey('eye_examination_id');
        $this->forge->addForeignKey('customer_id', 'customers', 'customer_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('eye_examinations');
    }

    public function down()
    {
        $this->forge->dropTable('eye_examinations');
    }
}

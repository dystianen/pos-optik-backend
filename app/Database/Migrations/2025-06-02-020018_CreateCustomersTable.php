<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCustomersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'customer_id'          => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'customer_name'        => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],
            'customer_email'       => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'unique' => true
            ],
            'customer_password' => [
                'type' => 'VARCHAR',
                'constraint' => '255'
            ],
            'customer_phone'       => [
                'type' => 'VARCHAR',
                'constraint' => 20
            ],
            'customer_dob'         => [
                'type' => 'DATE',
                'null' => true
            ],
            'customer_gender'      => [
                'type' => 'ENUM',
                'constraint' => ['male', 'female', 'other']
            ],
            'customer_occupation'  => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true
            ],
            'customer_eye_history' => [
                'type' => 'JSON',
                'null' => true
            ], // Untuk riwayat mata (JSON)
            'customer_preferences' => [
                'type' => 'JSON',
                'null' => true
            ], // Preferensi produk (JSON)
            'created_at'  => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'updated_at'  => [
                'type' => 'DATETIME',
                'null' => true
            ],
        ]);

        $this->forge->addPrimaryKey('customer_id');
        $this->forge->createTable('customers');
    }

    public function down()
    {
        $this->forge->dropTable('customers');
    }
}

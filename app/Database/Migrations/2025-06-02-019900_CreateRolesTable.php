<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRolesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'role_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'role_name' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'unique' => TRUE
            ],
            'role_description' => [
                'type' => 'TEXT',
                'null' => TRUE
            ],
            'created_at timestamp DEFAULT CURRENT_TIMESTAMP',
            'updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]);

        $this->forge->addKey('role_id', TRUE);
        $this->forge->createTable('roles');
    }

    public function down()
    {
        $this->forge->dropTable('roles');
    }
}

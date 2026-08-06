<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGoogleIdToCustomers extends Migration
{
    public function up()
    {
        $fields = [
            'google_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'customer_email',
            ],
        ];
        $this->forge->addColumn('customers', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('customers', 'google_id');
    }
}

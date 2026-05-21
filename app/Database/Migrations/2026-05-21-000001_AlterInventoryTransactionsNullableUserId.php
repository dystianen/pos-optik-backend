<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterInventoryTransactionsNullableUserId extends Migration
{
    public function up()
    {
        // Drop the foreign key constraint on user_id first
        // MySQL requires dropping FK before altering the column
        $this->db->query('ALTER TABLE inventory_transactions DROP FOREIGN KEY inventory_transactions_user_id_foreign');

        // Make user_id nullable (customers are not in the users table)
        $this->forge->modifyColumn('inventory_transactions', [
            'user_id' => [
                'type'       => 'CHAR',
                'constraint' => 36,
                'null'       => true,
                'default'    => null,
            ],
        ]);

        // Re-add foreign key with SET NULL on delete/update so orphaned records are handled cleanly
        $this->db->query('
            ALTER TABLE inventory_transactions
            ADD CONSTRAINT inventory_transactions_user_id_foreign
            FOREIGN KEY (user_id) REFERENCES users(user_id)
            ON DELETE SET NULL ON UPDATE CASCADE
        ');
    }

    public function down()
    {
        // Reverse: drop the nullable FK
        $this->db->query('ALTER TABLE inventory_transactions DROP FOREIGN KEY inventory_transactions_user_id_foreign');

        // Restore original non-nullable constraint (requires all rows to have valid user_id)
        $this->forge->modifyColumn('inventory_transactions', [
            'user_id' => [
                'type'       => 'CHAR',
                'constraint' => 36,
                'null'       => false,
            ],
        ]);

        $this->db->query('
            ALTER TABLE inventory_transactions
            ADD CONSTRAINT inventory_transactions_user_id_foreign
            FOREIGN KEY (user_id) REFERENCES users(user_id)
            ON DELETE CASCADE ON UPDATE CASCADE
        ');
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakeShippingMethodIdNullableInOrders extends Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE orders DROP FOREIGN KEY orders_shipping_method_id_foreign");
        $this->db->query("ALTER TABLE orders MODIFY shipping_method_id CHAR(36) NULL");
        $this->db->query("ALTER TABLE orders ADD CONSTRAINT orders_shipping_method_id_foreign FOREIGN KEY (shipping_method_id) REFERENCES shipping_methods(shipping_method_id) ON DELETE CASCADE ON UPDATE CASCADE");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE orders DROP FOREIGN KEY orders_shipping_method_id_foreign");
        $this->db->query("UPDATE orders SET shipping_method_id = '3e08ee99-750a-4437-a3a9-922437410f6e' WHERE shipping_method_id IS NULL");
        $this->db->query("ALTER TABLE orders MODIFY shipping_method_id CHAR(36) NOT NULL");
        $this->db->query("ALTER TABLE orders ADD CONSTRAINT orders_shipping_method_id_foreign FOREIGN KEY (shipping_method_id) REFERENCES shipping_methods(shipping_method_id) ON DELETE CASCADE ON UPDATE CASCADE");
    }
}

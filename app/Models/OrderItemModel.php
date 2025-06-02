<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderItemModel extends Model
{
    protected $table            = 'order_items';
    protected $primaryKey       = 'order_item_id';
    protected $allowedFields    = ['order_id', 'product_id', 'quantity', 'price'];

    // Relasi ke order
    public function order()
    {
        return $this->belongsTo('App\Models\OrderModel', 'order_id', 'id');
    }

    // Relasi ke product
    public function product()
    {
        return $this->belongsTo('App\Models\ProductModel', 'product_id', 'id');
    }
}

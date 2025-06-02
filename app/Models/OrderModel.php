<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table            = 'orders';
    protected $primaryKey       = 'order_id';
    protected $allowedFields    = ['customer_id', 'order_date', 'total_price', 'payment_method', 'status'];
    protected $useTimestamps    = false;

    // Relasi ke customer
    public function customer()
    {
        return $this->belongsTo('App\Models\CustomerModel', 'customer_id', 'id');
    }

    // Relasi ke order_items (One-to-Many)
    public function items()
    {
        return $this->hasMany('App\Models\OrderItemModel', 'order_id', 'id');
    }

    // Method untuk analisis penjualan
    public function getSalesTrend($period = 'monthly')
    {
        $query = $this->select('DATE_FORMAT(order_date, "%Y-%m") as period, SUM(total_price) as revenue')
            ->groupBy('period')
            ->orderBy('period', 'ASC');

        return $query->findAll();
    }
}

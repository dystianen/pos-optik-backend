<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table            = 'products';
    protected $primaryKey       = 'product_id';
    protected $allowedFields = [
        'category_id',
        'product_name',
        'product_price',
        'product_stock',
        'product_brand',
        'model',
        'duration',
        'material',
        'base_curve',
        'diameter',
        'power_range',
        'water_content',
        'uv_protection',
        'color',
        'coating',
        'product_image_url'
    ];

    protected $useTimestamps = true;

    protected $validationRules = [
        'category_id' => 'required|integer',
        'product_name' => 'required',
        'product_price' => 'required|decimal',
        'product_stock' => 'required|integer',
        'product_brand' => 'required',
        'product_image_url' => 'required',

        // Validasi tambahan untuk specs
        'model' => 'permit_empty|string',
        'duration' => 'permit_empty|string',
        'material' => 'permit_empty|string',
        'base_curve' => 'permit_empty|string',
        'diameter' => 'permit_empty|string',
        'power_range' => 'permit_empty|string',
        'water_content' => 'permit_empty|string',
        'uv_protection' => 'permit_empty|string',
        'color' => 'permit_empty|string',
        'coating' => 'permit_empty|string'
    ];


    // Relasi ke kategori
    public function category()
    {
        return $this->belongsTo('App\Models\ProductCategoryModel', 'category_id', 'id');
    }

    // Relasi ke order_items (One-to-Many)
    public function orderItems()
    {
        return $this->hasMany('App\Models\OrderItemModel', 'product_id', 'id');
    }

    // Relasi ke reviews (One-to-Many)
    public function reviews()
    {
        return $this->hasMany('App\Models\ReviewModel', 'product_id', 'id');
    }

    // Method untuk prediksi stok (Time Series)
    public function predictStock(int $productId)
    {
        // Contoh: Ambil data penjualan 6 bulan terakhir
        $salesData = $this->orderItems()
            ->select('SUM(quantity) as total_sold, MONTH(created_at) as month')
            ->where('product_id', $productId)
            ->groupBy('MONTH(created_at)')
            ->findAll();

        // Logika prediksi sederhana (bisa diganti dengan ARIMA/ML)
        $predictedStock = 0;
        foreach ($salesData as $sale) {
            $predictedStock += $sale['total_sold'];
        }

        return ['predicted_stock' => $predictedStock / count($salesData)];
    }
}

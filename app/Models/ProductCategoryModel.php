<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductCategoryModel extends Model
{
    protected $table            = 'product_categories';
    protected $primaryKey       = 'category_id';
    protected $allowedFields    = ['category_name', 'category_description'];
    protected $useTimestamps    = false;

    // Relasi ke tabel products (One-to-Many)
    public function products()
    {
        return $this->hasMany('App\Models\ProductModel', 'category_id', 'product_id');
    }
}

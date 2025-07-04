<?php

namespace App\Models;

use CodeIgniter\Model;

class ReviewModel extends Model
{
    protected $table            = 'reviews';
    protected $primaryKey       = 'review_id';
    protected $allowedFields    = ['product_id', 'customer_id', 'rating', 'comment'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Relasi ke product
    public function product()
    {
        return $this->belongsTo('App\Models\ProductModel', 'product_id', 'id');
    }

    // Relasi ke customer
    public function customer()
    {
        return $this->belongsTo('App\Models\CustomerModel', 'customer_id', 'id');
    }

    // Method untuk analisis sentimen (NLP)
    public function analyzeSentiment(string $comment)
    {
        // Contoh sederhana (bisa diganti dengan model NLP)
        $positiveWords = ['bagus', 'puas', 'recommended'];
        $negativeWords = ['jelek', 'rugi', 'buruk'];

        $score = 0;
        foreach ($positiveWords as $word) {
            if (stripos($comment, $word) !== false) $score++;
        }
        foreach ($negativeWords as $word) {
            if (stripos($comment, $word) !== false) $score--;
        }

        return ($score > 0) ? 'positive' : (($score < 0) ? 'negative' : 'neutral');
    }
}

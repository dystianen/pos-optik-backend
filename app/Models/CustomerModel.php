<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table            = 'customers';
    protected $primaryKey       = 'customer_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['customer_name', 'customer_email', 'customer_password', 'customer_phone', 'customer_dob', 'customer_gender', 'customer_occupation', 'customer_eye_history', 'customer_preferences'];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';


    // Relasi ke tabel eye_examinations (One-to-Many)
    public function eyeExaminations()
    {
        return $this->hasMany('App\Models\EyeExaminationModel', 'customer_id', 'id');
    }

    // Relasi ke tabel orders (One-to-Many)
    public function orders()
    {
        return $this->hasMany('App\Models\OrderModel', 'customer_id', 'id');
    }

    // Method untuk rekomendasi produk (Naive Bayes)
    public function getProductRecommendations(int $customerId)
    {
        $customer = $this->find($customerId);
        $eyeData = $this->eyeExaminations()->where('customer_id', $customerId)->first();

        // Contoh logika rekomendasi sederhana (bisa diganti dengan model ML)
        if ($eyeData && $eyeData['diagnosis'] === 'miopi') {
            return ['recommendation' => 'Lensa minus dengan blue cut'];
        }

        return ['recommendation' => 'Frame standar'];
    }
}

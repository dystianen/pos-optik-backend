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
    protected $allowedFields    = ['name', 'email', 'phone', 'dob', 'gender', 'occupation', 'eye_history', 'preferences'];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    // Validasi input
    protected $validationRules = [
        'name'     => 'required|min_length[3]',
        'email'    => 'required|valid_email|is_unique[customers.email]',
        'phone'    => 'required|min_length[10]',
    ];

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

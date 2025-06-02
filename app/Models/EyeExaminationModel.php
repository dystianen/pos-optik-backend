<?php

namespace App\Models;

use CodeIgniter\Model;

class EyeExaminationModel extends Model
{
    protected $table            = 'eye_examinations';
    protected $primaryKey       = 'eye_examination_id';
    protected $allowedFields    = [
        'customer_id',
        'left_eye_sphere',
        'left_eye_cylinder',
        'left_eye_axis',
        'right_eye_sphere',
        'right_eye_cylinder',
        'right_eye_axis',
        'symptoms',
        'diagnosis'
    ];

    // Relasi ke customer
    public function customer()
    {
        return $this->belongsTo('App\Models\CustomerModel', 'customer_id', 'id');
    }

    // Method untuk klasifikasi gangguan mata (Naive Bayes)
    public function classifyEyeCondition(array $symptoms)
    {
        // Contoh sederhana (bisa diganti dengan model ML)
        if (strpos($symptoms['symptoms'], 'pandangan buram jauh') !== false) {
            return 'miopi';
        } elseif (strpos($symptoms['symptoms'], 'sulit baca dekat') !== false) {
            return 'hipermetropi';
        }

        return 'unknown';
    }
}

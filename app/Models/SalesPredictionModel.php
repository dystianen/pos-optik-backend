<?php

namespace App\Models;

use CodeIgniter\Model;

class SalesPredictionModel extends Model
{
    protected $table            = 'sales_predictions';
    protected $primaryKey       = 'sales_prediction_id';
    protected $allowedFields    = ['product_id', 'prediction_date', 'predicted_quantity', 'confidence_score'];
    protected $useTimestamps    = true;

    // Relasi ke product
    public function product()
    {
        return $this->belongsTo('App\Models\ProductModel', 'product_id', 'id');
    }

    // Method untuk update prediksi dari model ML
    public function updatePredictionsFromML(array $mlPredictions)
    {
        foreach ($mlPredictions as $prediction) {
            $this->insert([
                'product_id'         => $prediction['product_id'],
                'prediction_date'    => date('Y-m-d'),
                'predicted_quantity' => $prediction['quantity'],
                'confidence_score'   => $prediction['confidence'],
            ]);
        }
    }
}

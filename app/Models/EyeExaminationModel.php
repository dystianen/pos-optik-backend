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
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}

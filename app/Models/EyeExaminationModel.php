<?php

namespace App\Models;

use CodeIgniter\Model;

class EyeExaminationModel extends Model
{
    protected $table            = 'eye_examinations';
    protected $primaryKey       = 'eye_examination_id';
    protected $useAutoIncrement = false;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $useTimestamps    = true;
    protected $dateFormat       = 'datetime';
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $deletedField     = 'deleted_at';

    protected $allowedFields = [
        'eye_examination_id',
        'customer_id',
        'left_eye_sphere',
        'left_eye_cylinder',
        'left_eye_axis',
        'right_eye_sphere',
        'right_eye_cylinder',
        'right_eye_axis',
        'symptoms',
        'diagnosis',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $validationRules = [
        'customer_id'        => 'required',
        'left_eye_sphere'    => 'permit_empty|decimal',
        'left_eye_cylinder'  => 'permit_empty|decimal',
        'left_eye_axis'      => 'permit_empty|integer',
        'right_eye_sphere'   => 'permit_empty|decimal',
        'right_eye_cylinder' => 'permit_empty|decimal',
        'right_eye_axis'     => 'permit_empty|integer',
        'symptoms'           => 'permit_empty|max_length[500]',
        'diagnosis'          => 'permit_empty|max_length[500]',
    ];

    protected $validationMessages = [
        'customer_id' => [
            'required' => 'Please select a customer for the eye examination.',
        ],
        'left_eye_sphere' => [
            'decimal' => 'Left eye sphere must be a valid decimal number.',
        ],
        'left_eye_cylinder' => [
            'decimal' => 'Left eye cylinder must be a valid decimal number.',
        ],
        'left_eye_axis' => [
            'integer' => 'Left eye axis must be a whole number.',
        ],
        'right_eye_sphere' => [
            'decimal' => 'Right eye sphere must be a valid decimal number.',
        ],
        'right_eye_cylinder' => [
            'decimal' => 'Right eye cylinder must be a valid decimal number.',
        ],
        'right_eye_axis' => [
            'integer' => 'Right eye axis must be a whole number.',
        ],
        'symptoms' => [
            'max_length' => 'Symptoms description must not exceed 500 characters.',
        ],
        'diagnosis' => [
            'max_length' => 'Diagnosis must not exceed 500 characters.',
        ],
    ];

    protected $beforeInsert = ['generateUuid'];

    protected function generateUuid(array $data)
    {
        $data['data']['eye_examination_id'] = service('uuid')->uuid4()->toString();
        return $data;
    }
}

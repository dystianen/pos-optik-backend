<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;

class AuthController extends BaseController
{
    protected $customerModel;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
        helper(['form', 'url']); // load form & URL helper
    }

    /**
     * Register
     */
    public function register()
    {
        $rules = [
            'customer_name' => 'required|min_length[3]|max_length[50]|is_unique[customers.customer_name]',
            'customer_email' => 'required|valid_email|is_unique[customers.customer_email]',
            'customer_password' => 'required|min_length[6]',
            'customer_phone' => 'required',
            'customer_dob' => 'required',
            'customer_gender' => 'required',
            'customer_occupation' => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $this->validator->getErrors()
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        // Cek JSON valid atau tidak
        $eyeHistoryJson = $this->request->getVar('customer_eye_history');
        $preferencesJson = $this->request->getVar('customer_preferences');

        json_decode($eyeHistoryJson);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid JSON format in customer_eye_history'
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        json_decode($preferencesJson);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid JSON format in customer_preferences'
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        $data = [
            'customer_name' => $this->request->getVar('customer_name'),
            'customer_email' => $this->request->getVar('customer_email'),
            'customer_password' => password_hash($this->request->getVar('customer_password'), PASSWORD_DEFAULT),
            'customer_phone' => $this->request->getVar('customer_phone'),
            'customer_dob' => $this->request->getVar('customer_dob'),
            'customer_gender' => $this->request->getVar('customer_gender'),
            'customer_occupation' => $this->request->getVar('customer_occupation'),
            'customer_eye_history' => $eyeHistoryJson,
            'customer_preferences' => $preferencesJson,
        ];

        $this->customerModel->insert($data);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Registered successfully'
        ]);
    }

    /**
     * Login
     */
    public function login()
    {
        $email = $this->request->getVar('customer_email');
        $password = $this->request->getVar('customer_password');

        $user = $this->customerModel->where('customer_email', $email)->first();

        if (is_null($user)) {
            return $this->response->setJSON(['error' => 'Invalid username or password.'], 401);
        }

        $pwd_verify = password_verify($password, $user['customer_password']);

        if (!$pwd_verify) {
            return $this->response->setJSON(['error' => 'Invalid username or password.'], 401);
        }

        $key = getenv('JWT_SECRET');
        $iat = time(); // current timestamp value
        $exp = $iat + 3600;

        $payload = array(
            "iss" => "Issuer of the JWT",
            "aud" => "Audience that the JWT",
            "sub" => "Subject of the JWT",
            "iat" => $iat, //Time the JWT issued at
            "exp" => $exp, // Expiration time of token
            "user_id" => $user['customer_id'],
            "user_name" => $user['customer_name'],
            "email" => $user['customer_email'],
        );

        $token = JWT::encode($payload, $key, 'HS256');

        $response = [
            'status' => 200,
            'message' => 'Login Succesfully!',
            'data' => [
                'token' => $token,
            ]
        ];

        return $this->response->setJSON($response);
    }


    /**
     * Logout
     */
    public function logout()
    {
        session()->destroy();

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Logged out successfully'
        ]);
    }
}

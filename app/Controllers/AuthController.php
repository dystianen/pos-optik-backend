<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;

class AuthController extends BaseController
{
    use ResponseTrait;
    protected $customerModel, $userModel;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
        $this->userModel = new UserModel();
        helper(['form', 'url']); // load form & URL helper
    }

    public function signin()
    {
        return view('auth/v_signin');
    }

    public function signinStore()
    {
        $session = session();
        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');

        $data = $this->userModel->where('user_email', $email)->first();

        if ($data) {
            $pass = $data['password'];
            $authenticatePassword = password_verify($password, $pass);
            if ($authenticatePassword) {
                $ses_data = [
                    'id' => $data['user_id'],
                    'full_name' => $data['user_name'],
                    'email' => $data['user_email'],
                    'role_id' => $data['role_id'],
                    'isLoggedIn' => TRUE
                ];

                $session->set($ses_data);

                return redirect()->to(base_url('/dashboard'));
            } else {
                $session->setFlashdata('failed', 'Password is incorrect.');
                return redirect()->to('/signin');
            }
        } else {
            $session->setFlashdata('failed', 'Email does not exist.');
            return redirect()->to('/signin');
        }
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
            return $this->respond([
                'status' => 401,
                'message' => 'Invalid username or password.'
            ], 401);
        }

        if (!password_verify($password, $user['customer_password'])) {
            return $this->respond([
                'status' => 401,
                'message' => 'Invalid username or password.'
            ], 401);
        }

        $key = getenv('JWT_SECRET');
        $iat = time();
        $exp = $iat + 3600;

        $payload = [
            "iss" => "Issuer of the JWT",
            "aud" => "Audience that the JWT",
            "sub" => "Subject of the JWT",
            "iat" => $iat,
            "exp" => $exp,
            "user_id" => $user['customer_id'],
            "user_name" => $user['customer_name'],
            "email" => $user['customer_email'],
        ];

        $token = JWT::encode($payload, $key, 'HS256');

        return $this->respond([
            'status' => 200,
            'message' => 'Login successfully!',
            'data' => [
                'token' => $token
            ]
        ], 200);
    }



    /**
     * Logout
     */
    public function logout()
    {
        session()->destroy();
        return view('auth/v_signin');
    }
}

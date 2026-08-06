<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use App\Models\RoleModel;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;

class AuthController extends BaseController
{
    use ResponseTrait;
    protected $customerModel, $userModel, $roleModel;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
        helper(['form', 'url']);
    }

    public function signin()
    {
        return view('auth/v_signin');
    }

    public function signinStore()
    {
        $session = session();

        // 🛡️ RECAPTCHA VALIDATION
        $token = $this->request->getVar('g-recaptcha-response');
        if (!$this->verifyCaptcha($token, 'login')) {
            $session->setFlashdata('failed', 'reCAPTCHA verification failed. Please try again.');
            return redirect()->to('/signin');
        }

        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');

        $data = $this->userModel->where('user_email', $email)->first();

        if ($data) {
            $role = $this->roleModel->where('role_id', $data['role_id'])->first();
            $pass = $data['password'];
            $authenticatePassword = password_verify($password, $pass);
            if ($authenticatePassword) {
                $ses_data = [
                    'id' => $data['user_id'],
                    'full_name' => $data['user_name'],
                    'email' => $data['user_email'],
                    'role_name' => $role['role_name'],
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

    public function logout()
    {
        session()->destroy();
        return view('auth/v_signin');
    }

    // =======================
    // Helper: Verify Captcha
    // =======================
    private function verifyCaptcha($token, $expectedAction)
    {
        $secret = env('RECAPTCHA_SECRET_KEY');
        if (empty($secret)) {
            return true;
        }

        if (empty($token)) {
            return false;
        }

        try {
            $verifySsl = env('CURL_VERIFY') !== false;
            $client = \Config\Services::curlrequest(['verify' => $verifySsl]);
            $response = $client->post('https://www.google.com/recaptcha/api/siteverify', [
                'form_params' => [
                    'secret'   => $secret,
                    'response' => $token
                ]
            ]);

            $body = json_decode($response->getBody(), true);
            if (empty($body['success'])) {
                log_message('error', 'reCAPTCHA v3 verification failed: ' . json_encode($body));
                return false;
            }

            $score = $body['score'] ?? 0.0;
            if ($score < 0.5) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', 'reCAPTCHA v3 verification exception: ' . $e->getMessage());
            return false;
        }
    }
}

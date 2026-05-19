<?php

namespace App\Controllers\Api;

use App\Models\CustomerModel;
use App\Models\EyeExaminationModel;
use App\Models\RoleModel;
use App\Models\UserModel;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthApiController extends BaseApiController
{
  protected $customerModel, $userModel, $roleModel, $eyeExaminationModel;

  public function __construct()
  {
    $this->customerModel = new CustomerModel();
    $this->userModel     = new UserModel();
    $this->roleModel           = new RoleModel();
    $this->eyeExaminationModel = new EyeExaminationModel();
    helper(['form', 'url']);
  }

  // =======================
  // GET /api/auth/register
  // =======================
  public function register()
  {
    $rules = [
      'customer_name'     => 'required|min_length[3]|max_length[50]|is_unique[customers.customer_name]',
      'customer_email'    => 'required|valid_email|is_unique[customers.customer_email]',
      'customer_password' => 'required|min_length[3]',
      'customer_phone'    => 'required',
      'customer_dob'      => 'required',
      'customer_gender'   => 'required',
    ];

    $validate = $this->validateRequest($rules);
    if ($validate !== true) {
      return $validate;
    }

    $data = [
      'customer_name'     => $this->request->getVar('customer_name'),
      'customer_email'    => $this->request->getVar('customer_email'),
      'customer_password' => password_hash(
        $this->request->getVar('customer_password'),
        PASSWORD_DEFAULT
      ),
      'customer_phone'    => $this->request->getVar('customer_phone'),
      'customer_dob'      => $this->request->getVar('customer_dob'),
      'customer_gender'   => $this->request->getVar('customer_gender'),
    ];

    $this->customerModel->insert($data);

    return $this->messageResponse('Registered successfully');
  }

  // =======================
  // GET /api/auth/login
  // =======================
  public function login()
  {
    $email    = $this->request->getVar('customer_email');
    $password = $this->request->getVar('customer_password');

    $user = $this->customerModel
      ->where('customer_email', $email)
      ->first();

    if (!$user || !password_verify($password, $user['customer_password'])) {
      return $this->validationErrorResponse(['customer_email' => 'Invalid username or password']);
    }

    $key = getenv('JWT_SECRET_KEY');
    if (empty($key)) {
      return $this->serverErrorResponse('JWT secret key not configured');
    }

    $iat = time();

    $accessTokenPayload = [
      'iss'       => 'Your Store',
      'iat'       => $iat,
      'exp'       => $iat + 3600,
      'user_id'   => $user['customer_id'],
      'user_name' => $user['customer_name'],
      'email'     => $user['customer_email'],
      'type'      => 'access',
    ];

    $refreshTokenPayload = [
      'iss'     => 'Your Store',
      'iat'     => $iat,
      'exp'     => $iat + (30 * 24 * 60 * 60),
      'user_id' => $user['customer_id'],
      'type'    => 'refresh',
    ];

    return $this->successResponse([
      'access_token'  => JWT::encode($accessTokenPayload, $key, 'HS256'),
      'refresh_token' => JWT::encode($refreshTokenPayload, $key, 'HS256'),
      'token_type'    => 'Bearer',
      'expires_in'    => 3600,
      'user' => [
        'id'    => $user['customer_id'],
        'name'  => $user['customer_name'],
        'email' => $user['customer_email'],
      ],
    ], 'Login successfully');
  }

  // =======================
  // GET /api/auth/refresh
  // =======================
  public function refresh()
  {
    try {
      $body = $this->getRequestBody();
      $refreshToken = $body->refresh_token ?? null;

      if (!$refreshToken) {
        return $this->validationErrorResponse([
          'refresh_token' => 'Refresh token is required'
        ]);
      }

      $key = getenv('JWT_SECRET_KEY');
      if (empty($key)) {
        return $this->serverErrorResponse('JWT secret key not configured');
      }

      try {
        $decoded = JWT::decode($refreshToken, new Key($key, 'HS256'));
      } catch (Exception $e) {
        return $this->unauthorizedResponse('Refresh token invalid or expired');
      }

      if (($decoded->type ?? null) !== 'refresh') {
        return $this->conflictResponse('Token is not a refresh token');
      }

      $user = $this->customerModel->find($decoded->user_id);
      if (!$user) {
        return $this->notFoundResponse('User not found');
      }

      $iat = time();
      $accessTokenPayload = [
        'iss'       => 'Your Store',
        'iat'       => $iat,
        'exp'       => $iat + 3600,
        'user_id'   => $user['customer_id'],
        'user_name' => $user['customer_name'],
        'email'     => $user['customer_email'],
        'type'      => 'access',
      ];

      return $this->successResponse([
        'access_token' => JWT::encode($accessTokenPayload, $key, 'HS256'),
        'token_type'   => 'Bearer',
        'expires_in'   => 3600,
        'user' => [
          'id'    => $user['customer_id'],
          'name'  => $user['customer_name'],
          'email' => $user['customer_email'],
        ],
      ], 'Token refreshed successfully');
    } catch (Exception $e) {
      return $this->serverErrorResponse('Failed to refresh token');
    }
  }

  // =======================
  // GET /api/auth/profile
  // =======================
  public function profile()
  {
    try {
      $userId = $this->getAuthenticatedCustomerId();
      $user   = $this->customerModel->find($userId);

      if (!$user) {
        return $this->notFoundResponse('User not found');
      }

      // Ambil riwayat pemeriksaan mata terakhir
      $eyeHistory = $this->eyeExaminationModel
        ->where('customer_id', $userId)
        ->orderBy('created_at', 'DESC')
        ->first();

      // Hapus sensitive data
      unset($user['customer_password']);

      // Gabungkan data profil dengan history & preference placeholder
      $response = [
        'personal_info' => $user,
        'preferences_history' => [
          'eye_history' => $eyeHistory ? [
            'last_check' => $eyeHistory['created_at'],
            'diagnosis'  => $eyeHistory['diagnosis'],
            'left_eye'   => [
              'sph' => $eyeHistory['left_eye_sphere'],
              'cyl' => $eyeHistory['left_eye_cylinder'],
              'axs' => $eyeHistory['left_eye_axis'],
            ],
            'right_eye'  => [
              'sph' => $eyeHistory['right_eye_sphere'],
              'cyl' => $eyeHistory['right_eye_cylinder'],
              'axs' => $eyeHistory['right_eye_axis'],
            ],
          ] : null,
        ]
      ];

      return $this->successResponse($response, 'Profile retrieved successfully');
    } catch (Exception $e) {
      return $this->serverErrorResponse('Failed to retrieve profile');
    }
  }

  // =======================
  // POST /api/auth/forgot-password
  // =======================
  public function forgotPassword()
  {
    try {
      $rules = [
        'customer_email'    => 'required|valid_email',
        'confirm_password'  => 'required|matches[customer_password]'
      ];

      $validate = $this->validateRequest($rules);
      if ($validate !== true) {
        return $validate;
      }

      $email       = $this->request->getVar('customer_email');
      $newPassword = $this->request->getVar('customer_password');
      
      $customer = $this->customerModel
        ->where('customer_email', $email)
        ->first();

      if (!$customer) {
        return $this->notFoundResponse('Customer dengan email tersebut tidak ditemukan');
      }

      $customerData = [
        'customer_password' => password_hash($newPassword, PASSWORD_DEFAULT)
      ];

      if (!$this->customerModel->update($customer['customer_id'], $customerData)) {
        return $this->serverErrorResponse('Gagal mereset password customer');
      }

      return $this->messageResponse('Password customer berhasil diperbarui');

    } catch (Exception $e) {
      return $this->serverErrorResponse('Terjadi kesalahan saat memproses reset password');
    }
  }
}


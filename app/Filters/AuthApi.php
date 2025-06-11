<?php

namespace App\Filters;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class AuthApi implements FilterInterface
{
  use ResponseTrait;
  public function before(RequestInterface $request, $arguments = null)
  {
    $authHeader = $request->getHeaderLine('Authorization');
    if (!$authHeader) {
      return service('response')
        ->setJSON([
          'status'  => ResponseInterface::HTTP_UNAUTHORIZED,
          'message' => 'Authorization header missing'
        ])
        ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
    }

    $token = null;
    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
      $token = $matches[1];
    }

    if (!$token) {
      return service('response')
        ->setJSON([
          'status'  => 'error',
          'message' => 'Token not found'
        ])
        ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
    }

    try {
      $key = getenv('JWT_SECRET') ?: 'your_secret_key';
      $decoded = JWT::decode($token, new Key($key, 'HS256'));

      // Simpan data user ke request untuk digunakan di controller
      $request->user = $decoded;
    } catch (Exception $e) {
      return service('response')
        ->setJSON([
          'status'  => ResponseInterface::HTTP_UNAUTHORIZED,
          'message' => 'Invalid or expired token'
        ])
        ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
    }
  }


  public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
  {
    // No action needed after request
  }
}

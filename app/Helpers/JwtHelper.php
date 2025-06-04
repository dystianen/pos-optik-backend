<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if (!function_exists('generateJWT')) {
  function generateJWT($payload)
  {
    $key = getenv('JWT_SECRET') ?: 'your_secret_key';  // simpan di .env
    $issuedAt = time();
    $expirationTime = $issuedAt + 3600; // 1 jam
    $payload = array_merge($payload, [
      'iat' => $issuedAt,
      'exp' => $expirationTime
    ]);

    return JWT::encode($payload, $key, 'HS256');
  }
}

if (!function_exists('validateJWT')) {
  function validateJWT($token)
  {
    try {
      $key = getenv('JWT_SECRET') ?: 'your_secret_key';
      return JWT::decode($token, new Key($key, 'HS256'));
    } catch (Exception $e) {
      return false;
    }
  }
}

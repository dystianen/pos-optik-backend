<?php

namespace App\Controllers\Api;

use App\Traits\ValidationHelperTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\RESTful\ResourceController;
use Psr\Log\LoggerInterface;

/**
 * Base API Controller
 * 
 * Base controller untuk semua REST API endpoints
 * Menyediakan helper methods untuk response yang konsisten
 * 
 * @package App\Controllers\Api
 */
class BaseApiController extends ResourceController
{
  use ValidationHelperTrait;
  /**
   * Instance of the main Request object.
   *
   * @var CLIRequest|IncomingRequest
   */
  protected $request;

  /**
   * An array of helpers to be loaded automatically upon
   * class instantiation. These helpers will be available
   * to all other controllers that extend BaseController.
   *
   * @var list<string>
   */
  protected $helpers = [];

  /**
   * Response format (default: json)
   */
  protected $format = 'json';

  /**
   * Database connection instance
   */
  protected $db;

  protected function getAuthenticatedUser()
  {
    $jwtUser = getJWTUser();

    if (!$jwtUser) {
      $this->unauthorizedResponse()->send();
      exit;
    }

    return $jwtUser;
  }

  protected function getAuthenticatedCustomerId(): string
  {
    return $this->getAuthenticatedUser()->user_id;
  }

  protected function getAuthenticatedCustomerName(): string
  {
    return $this->getAuthenticatedUser()->user_name;
  }


  /**
   * Initialize controller
   * 
   * @param RequestInterface  $request
   * @param ResponseInterface $response
   * @param LoggerInterface   $logger
   * @return void
   */
  public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
  {
    // Do Not Edit This Line
    parent::initController($request, $response, $logger);

    // Initialize database connection
    $this->db = \Config\Database::connect();

    // Preload any models, libraries, etc, here.
    // E.g.: $this->session = \Config\Services::session();
  }

  /**
   * Override validate() to automatically beautify validation error messages.
   * Uses Reflection to inject formatted errors back into the validator instance.
   *
   * @param array|string $rules     Validation rules.
   * @param array        $messages  Custom messages.
   * @return bool
   */
  protected function validate($rules, array $messages = []): bool
  {
    $passed = parent::validate($rules, $messages);

    if (!$passed && $this->validator) {
      $beautified = $this->beautifyValidationErrors($this->validator->getErrors());
      try {
        $ref = new \ReflectionProperty(get_class($this->validator), 'errors');
        $ref->setAccessible(true);
        $ref->setValue($this->validator, $beautified);
      } catch (\Throwable) {
        // Reflection failed — not critical, raw errors will still show
      }
    }

    return $passed;
  }

  /**
   * Success response helper
   * 
   * @param mixed  $data        Data yang akan di-return
   * @param string $message     Success message
   * @param int    $statusCode  HTTP status code (default: 200)
   * @return ResponseInterface
   */
  protected function successResponse($data = null, string $message = 'Success', int $statusCode = ResponseInterface::HTTP_OK)
  {
    return $this->respond([
      'success' => true,
      'message' => $message,
      'data' => $data,
    ], $statusCode);
  }

  /**
   * Success response without data (message only)
   * 
   * @param string $message     Success message
   * @param int    $statusCode  HTTP status code (default: 200)
   * @return ResponseInterface
   */
  protected function messageResponse(string $message = 'Success', int $statusCode = ResponseInterface::HTTP_OK)
  {
    return $this->respond([
      'success' => true,
      'message' => $message,
    ], $statusCode);
  }

  /**
   * Error response helper
   * 
   * @param string $message     Error message
   * @param mixed  $errors      Error details (validation errors, etc)
   * @param int    $statusCode  HTTP status code (default: 400)
   * @return ResponseInterface
   */
  protected function errorResponse(string $message = 'Error', $errors = null, int $statusCode = ResponseInterface::HTTP_BAD_REQUEST)
  {
    $response = [
      'success' => false,
      'message' => $message,
    ];

    if ($errors !== null) {
      $response['errors'] = $errors;
    }

    return $this->respond($response, $statusCode);
  }

  /**
   * Created response helper (HTTP 201)
   * Digunakan saat berhasil create resource baru
   * 
   * @param mixed  $data     Data resource yang baru dibuat
   * @param string $message  Success message
   * @return ResponseInterface
   */
  protected function createdResponse($data = null, string $message = 'Resource created successfully')
  {
    return $this->successResponse($data, $message, ResponseInterface::HTTP_CREATED);
  }

  /**
   * Not found response helper (HTTP 404)
   * 
   * @param string $message  Error message
   * @return ResponseInterface
   */
  protected function notFoundResponse(string $message = 'Resource not found')
  {
    return $this->errorResponse($message, null, ResponseInterface::HTTP_NOT_FOUND);
  }

  /**
   * Unauthorized response helper (HTTP 401)
   * Digunakan saat authentication gagal
   * 
   * @param string $message  Error message
   * @return ResponseInterface
   */
  protected function unauthorizedResponse(string $message = 'Unauthorized')
  {
    return $this->errorResponse($message, null, ResponseInterface::HTTP_UNAUTHORIZED);
  }

  /**
   * Forbidden response helper (HTTP 403)
   * Digunakan saat user tidak punya akses
   * 
   * @param string $message  Error message
   * @return ResponseInterface
   */
  protected function forbiddenResponse(string $message = 'Forbidden')
  {
    return $this->errorResponse($message, null, ResponseInterface::HTTP_FORBIDDEN);
  }

  /**
   * Validation error response helper (HTTP 422)
   *
   * @param array  $errors   Validation errors dari validator
   * @param string $message  Error message
   * @return ResponseInterface
   */
  protected function validationErrorResponse(array $errors, string $message = 'Validation failed')
  {
    $beautified = $this->beautifyValidationErrors($errors);
    return $this->errorResponse($message, $beautified, ResponseInterface::HTTP_UNPROCESSABLE_ENTITY);
  }

  /**
   * Conflict response helper (HTTP 409)
   * Digunakan saat ada konflik (duplicate data, concurrent update, etc)
   * 
   * @param string $message  Error message
   * @return ResponseInterface
   */
  protected function conflictResponse(string $message = 'Conflict')
  {
    return $this->errorResponse($message, null, ResponseInterface::HTTP_CONFLICT);
  }

  /**
   * Internal server error response helper (HTTP 500)
   * 
   * @param string $message  Error message
   * @return ResponseInterface
   */
  protected function serverErrorResponse(string $message = 'Internal server error')
  {
    return $this->errorResponse($message, null, ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
  }

  /**
   * No content response helper (HTTP 204)
   * Digunakan untuk DELETE success atau update tanpa return data
   * 
   * @return ResponseInterface
   */
  protected function noContentResponse()
  {
    return $this->respond(null, ResponseInterface::HTTP_NO_CONTENT);
  }

  /**
   * Get authenticated user ID from header or session
   * 
   * Priority:
   * 1. X-User-Id header (untuk API dengan token)
   * 2. Session user_id (untuk web session)
   * 
   * @return string|null
   */
  protected function getUserId()
  {
    return $this->request->getHeaderLine('X-User-Id') ?: session('user_id');
  }

  /**
   * Get authenticated admin ID from header or session
   * 
   * Priority:
   * 1. X-Admin-Id header (untuk API dengan token)
   * 2. Session admin_id (untuk web session)
   * 
   * @return string|null
   */
  protected function getAdminId()
  {
    return $this->request->getHeaderLine('X-Admin-Id') ?: session('admin_id');
  }

  /**
   * Get authorization token from header
   * 
   * @return string|null
   */
  protected function getBearerToken()
  {
    $header = $this->request->getHeaderLine('Authorization');

    if (!empty($header)) {
      if (preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
        return $matches[1];
      }
    }

    return null;
  }

  /**
   * Validate request with rules
   * Return validation error response jika gagal
   * 
   * @param array $rules     Validation rules
   * @param array $messages  Custom validation messages
   * @return bool|ResponseInterface
   */
  protected function validateRequest(array $rules, array $messages = [])
  {
    if (!$this->validate($rules, $messages)) {
      return $this->validationErrorResponse($this->validator->getErrors());
    }

    return true;
  }

  /**
   * Parse request body as JSON
   * 
   * @param bool $asArray  Return as array (default: false, return as object)
   * @return object|array|null
   */
  protected function getRequestBody(bool $asArray = false)
  {
    return $this->request->getJSON($asArray);
  }

  /**
   * Get pagination parameters from query string
   * 
   * @return array [page, perPage, offset]
   */
  protected function getPaginationParams()
  {
    $page = (int) ($this->request->getGet('page') ?? 1);
    $perPage = (int) ($this->request->getGet('per_page') ?? 10);

    // Limit per_page to max 100
    $perPage = min($perPage, 100);

    // Ensure minimum values
    $page = max($page, 1);
    $perPage = max($perPage, 1);

    $offset = ($page - 1) * $perPage;

    return [
      'page' => $page,
      'per_page' => $perPage,
      'offset' => $offset,
    ];
  }

  /**
   * Paginated success response
   * 
   * @param array  $data       Data items
   * @param int    $total      Total items
   * @param int    $page       Current page
   * @param int    $perPage    Items per page
   * @param string $message    Success message
   * @return ResponseInterface
   */
  protected function paginatedResponse(array $data, int $total, int $page, int $perPage, string $message = 'Success')
  {
    $lastPage = ceil($total / $perPage);

    return $this->successResponse([
      'items' => $data,
      'pagination' => [
        'total' => $total,
        'per_page' => $perPage,
        'current_page' => $page,
        'last_page' => $lastPage,
        'from' => ($page - 1) * $perPage + 1,
        'to' => min($page * $perPage, $total),
      ],
    ], $message);
  }

  /**
   * Log API request for debugging
   * 
   * @param string $action  Action name
   * @param array  $data    Additional data to log
   * @return void
   */
  protected function logApiRequest(string $action, array $data = [])
  {
    $logData = [
      'action' => $action,
      'method' => $this->request->getMethod(),
      'uri' => $this->request->getUri()->getPath(),
      'user_id' => $this->getUserId(),
      'ip' => $this->request->getIPAddress(),
      'timestamp' => date('Y-m-d H:i:s'),
    ];

    if (!empty($data)) {
      $logData['data'] = $data;
    }

    log_message('info', 'API Request: ' . json_encode($logData));
  }
}

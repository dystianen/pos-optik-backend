<?php

namespace App\Controllers;

use App\Traits\ValidationHelperTrait;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
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
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */

    protected $db;
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);
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

    public function decodedToken()
    {
        $key = getenv('JWT_SECRET_KEY');
        $header = $this->request->getHeaderLine("Authorization");
        $token = null;

        if (!empty($header)) {
            if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
                $token = $matches[1];
            }
        }

        if (!$token) {
            // Token tidak ada di header
            throw new Exception("Token not provided", 401);
        }

        try {
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            return $decoded;
        } catch (ExpiredException $e) {
            throw new Exception("Token expired", 401);
        } catch (SignatureInvalidException $e) {
            throw new Exception("Invalid token signature", 401);
        } catch (BeforeValidException $e) {
            throw new Exception("Token not valid yet", 401);
        } catch (Exception $e) {
            throw new Exception("Invalid token", 401);
        }
    }
}

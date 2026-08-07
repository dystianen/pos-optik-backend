<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthGuard implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/signin');
        }

        $roleName = session()->get('role_name');
        $path = uri_string();

        // Skip API routes from page role authorization checks
        if (strpos($path, 'api/') === 0) {
            return;
        }

        if (!$this->isAuthorized((string)$roleName, $path)) {
            $response = service('response');
            $response->setStatusCode(403);
            $response->setBody(view('errors/unauthorized'));
            return $response;
        }
    }

    private function isAuthorized(string $role, string $path): bool
    {
        $segments = explode('/', $path);
        $firstSegment = $segments[0] ?? '';

        if ($role === 'admin') {
            if ($firstSegment === 'reports') {
                return strpos($path, 'reports/inventory') === 0;
            }

            $allowedSegments = [
                'dashboard',
                'reports',
                'products',
                'coupons',
                'inventory',
                'product-category',
                'product-attribute',
                'customers',
                'users',
                'roles',
                'notifications',
            ];
            return in_array($firstSegment, $allowedSegments, true);
        }

        if ($role === 'cashier') {
            if ($path === 'dashboard/recommendation-debug') {
                return false;
            }

            if ($firstSegment === 'reports') {
                return strpos($path, 'reports/sales') === 0;
            }

            $allowedSegments = [
                'dashboard',
                'eye-examinations',
                'customers',
                'online-sales',
                'offline-sales',
                'refund-sales',
                'cancellation-sales',
                'reports',
                'notifications',
            ];
            return in_array($firstSegment, $allowedSegments, true);
        }

        return false;
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}

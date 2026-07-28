<?php

use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;
use App\Filters\AuthGuard;

/**
 * @internal
 */
final class AuthGuardTest extends CIUnitTestCase
{
    protected $filter;
    protected $request;
    protected $session;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filter = new AuthGuard();
        $this->request = Services::request();
        $this->session = Services::session();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->session->destroy();
    }

    public function testRedirectsToSigninIfNotLoggedIn(): void
    {
        $this->session->remove('isLoggedIn');
        $this->session->remove('role_name');

        $this->request->getUri()->setPath('dashboard');
        
        $response = $this->filter->before($this->request);

        $this->assertNotNull($response);
        $this->assertInstanceOf(\CodeIgniter\HTTP\RedirectResponse::class, $response);
    }

    public function testCashierAllowedPaths(): void
    {
        $this->session->set([
            'isLoggedIn' => true,
            'role_name' => 'cashier'
        ]);

        // Dashboard is allowed
        $this->request->getUri()->setPath('dashboard');
        $response = $this->filter->before($this->request);
        $this->assertNull($response);

        // Online sales is allowed
        $this->request->getUri()->setPath('online-sales');
        $response = $this->filter->before($this->request);
        $this->assertNull($response);

        // Reports is allowed
        $this->request->getUri()->setPath('reports/sales');
        $response = $this->filter->before($this->request);
        $this->assertNull($response);
    }

    public function testCashierBlockedPaths(): void
    {
        $this->session->set([
            'isLoggedIn' => true,
            'role_name' => 'cashier'
        ]);

        // Products is blocked
        $this->request->getUri()->setPath('products');
        $response = $this->filter->before($this->request);
        $this->assertNotNull($response);
        $this->assertSame(403, $response->getStatusCode());

        // Users is blocked
        $this->request->getUri()->setPath('users');
        $response = $this->filter->before($this->request);
        $this->assertNotNull($response);
        $this->assertSame(403, $response->getStatusCode());

        // recommendation-debug is blocked
        $this->request->getUri()->setPath('dashboard/recommendation-debug');
        $response = $this->filter->before($this->request);
        $this->assertNotNull($response);
        $this->assertSame(403, $response->getStatusCode());

        // Inventory Report is blocked
        $this->request->getUri()->setPath('reports/inventory');
        $response = $this->filter->before($this->request);
        $this->assertNotNull($response);
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testAdminAllowedPaths(): void
    {
        $this->session->set([
            'isLoggedIn' => true,
            'role_name' => 'admin'
        ]);

        // Products is allowed
        $this->request->getUri()->setPath('products');
        $response = $this->filter->before($this->request);
        $this->assertNull($response);

        // Users is allowed
        $this->request->getUri()->setPath('users');
        $response = $this->filter->before($this->request);
        $this->assertNull($response);

        // Debugger is allowed
        $this->request->getUri()->setPath('dashboard/recommendation-debug');
        $response = $this->filter->before($this->request);
        $this->assertNull($response);

        // Inventory reports is allowed
        $this->request->getUri()->setPath('reports/inventory');
        $response = $this->filter->before($this->request);
        $this->assertNull($response);
    }

    public function testAdminBlockedPaths(): void
    {
        $this->session->set([
            'isLoggedIn' => true,
            'role_name' => 'admin'
        ]);

        // Online Sales is blocked
        $this->request->getUri()->setPath('online-sales');
        $response = $this->filter->before($this->request);
        $this->assertNotNull($response);
        $this->assertSame(403, $response->getStatusCode());

        // Offline Sales is blocked
        $this->request->getUri()->setPath('offline-sales');
        $response = $this->filter->before($this->request);
        $this->assertNotNull($response);
        $this->assertSame(403, $response->getStatusCode());

        // Sales Report is blocked
        $this->request->getUri()->setPath('reports/sales');
        $response = $this->filter->before($this->request);
        $this->assertNotNull($response);
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testApiRoutesAreSkipped(): void
    {
        $this->session->set([
            'isLoggedIn' => true,
            'role_name' => 'cashier'
        ]);

        // Even though cashier cannot access products page, they can access api/products because API checking is skipped at page-level
        $this->request->getUri()->setPath('api/products');
        $response = $this->filter->before($this->request);
        $this->assertNull($response);
    }
}

<?php

use CodeIgniter\Test\CIUnitTestCase;
use App\Controllers\NotificationController;

/**
 * @internal
 */
final class NotificationControllerTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper(['url', 'form']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        session()->destroy();
    }

    public function testGetRoleNotificationTypes(): void
    {
        $controller = new NotificationController();
        
        $reflector = new ReflectionClass(NotificationController::class);
        $method = $reflector->getMethod('getRoleNotificationTypes');
        $method->setAccessible(true);

        // 1. Test owner role
        session()->set('role_name', 'owner');
        $types = $method->invoke($controller);
        $this->assertEquals(['low_stock', 'stock_empty'], $types);

        // 2. Test admin role
        session()->set('role_name', 'admin');
        $types = $method->invoke($controller);
        $this->assertEquals(['stock', 'low_stock', 'stock_empty'], $types);

        // 3. Test cashier role
        session()->set('role_name', 'cashier');
        $types = $method->invoke($controller);
        $this->assertEquals(['new_order', 'cancel_order', 'refund_order', 'order'], $types);

        // 4. Test unknown role
        session()->set('role_name', 'guest');
        $types = $method->invoke($controller);
        $this->assertEquals([], $types);
    }
}

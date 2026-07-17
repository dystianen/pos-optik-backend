<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', function () {
  return redirect()->to('/signin');
});

$routes->get('signin', 'AuthController::signin');
$routes->post('signin/store', 'AuthController::signinStore');
$routes->get('logout', 'AuthController::logout');
$routes->get('/dashboard', 'DashboardController::index', ['filter' => 'authGuard']);
$routes->get('/dashboard/api-stats', 'DashboardController::apiStats', ['filter' => 'authGuard']);
$routes->get('/dashboard/recommendation-debug', 'DashboardController::recommendationDebug', ['filter' => 'authGuard']);

/** ================================= 
 *             ENDPOINT
 * ================================== */
$routes->group('api', ['filter' => 'cors'], function ($routes) {
  $routes->options('(:any)', function () {
    return service('response')->setStatusCode(200);
  });

  // AUTH
  $routes->group('auth', function ($routes) {
    $routes->post('login', 'Api\AuthApiController::login');
    $routes->post('register', 'Api\AuthApiController::register');
    $routes->post('refresh', 'Api\AuthApiController::refresh');
    $routes->post('forgot-password', 'Api\AuthApiController::forgotPassword');
    $routes->get('profile', 'Api\AuthApiController::profile', ['filter' => 'authApi']);
  });

  // PRODUCTS
  $routes->group('products', function ($routes) {
    $routes->get('', 'Api\ProductApiController::apiProduct');
    $routes->get('new-eyewear', 'Api\ProductApiController::apiListNewEyewear');
    $routes->get('best-seller', 'Api\ProductApiController::apiListBestSeller');
    $routes->get('categories', 'Api\ProductCategoryApiController::apiListProductCategory');
    $routes->get('my-recommendations', 'Api\ProductApiController::apiMyRecommendations', ['filter' => 'authApi']);
    $routes->get('recommendations/(:segment)/compare', 'Api\ProductApiController::apiCompareRecommendations/$1');
    $routes->get('recommendations/(:segment)', 'Api\ProductApiController::apiProductRecommendations/$1');
    $routes->get('search', 'Api\ProductApiController::apiSearchProduct');
    $routes->get('(:segment)', 'Api\ProductApiController::apiProductDetail/$1');
    $routes->get('(:segment)/attributes', 'Api\ProductApiController::apiProductAttributes/$1');
  });

  // PRODUCT VARIANTS
  $routes->group('variants', function ($routes) {
    $routes->get('', 'Api\ProductVariantApiController::getByProductId');
  });

  // CART
  $routes->group('cart', ['filter' => 'authApi'], function ($routes) {
    $routes->get('', 'Api\CartApiController::listCart');
    $routes->post('add', 'Api\CartApiController::addToCart');
    $routes->get('count', 'Api\CartApiController::getTotalCart');
    $routes->put('update/(:any)', 'Api\CartApiController::updateCartItemQuantity/$1');
    $routes->delete('delete/(:any)', 'Api\CartApiController::deleteCartItem/$1');
  });

  // ORDER
  $routes->group('orders', ['filter' => 'authApi'], function ($routes) {
    $routes->get('', 'Api\OnlineSalesApiController::listOrders');
    $routes->get('active', 'Api\OnlineSalesApiController::getActiveOrder');
    $routes->post('payment', 'Api\OnlineSalesApiController::uploadPaymentProof');
    $routes->get('check-payment-status/(:segment)', 'Api\OnlineSalesApiController::checkPaymentStatus/$1');
    $routes->get('summary/(:segment)', 'Api\OnlineSalesApiController::summaryOrders/$1');
    $routes->post('submit/(:segment)', 'Api\OnlineSalesApiController::submitOrder/$1');
    $routes->post('(:segment)/status', 'Api\OnlineSalesApiController::updateStatus/$1');
    $routes->get('(:segment)', 'Api\OnlineSalesApiController::getOrderDetail/$1');
  });

  // ONLINE SALES
  $routes->group('online-sales', ['filter' => 'authGuard'], function ($routes) {
    $routes->post('(:segment)/approve', 'Api\OnlineSalesApiController::approvePayment/$1');
    $routes->post('(:segment)/reject', 'Api\OnlineSalesApiController::rejectPayment/$1');
    $routes->post('(:segment)/expire', 'Api\OnlineSalesApiController::expirePayment/$1');
    $routes->post('(:segment)/status', 'Api\OnlineSalesApiController::updateStatus/$1');
    $routes->post('(:segment)/ship', 'Api\OnlineSalesApiController::shipOrder/$1');
  });

  // SHIPPING ADDRESS
  $routes->group('shipping-address', ['filter' => 'authApi'], function ($routes) {
    $routes->get('', 'Api\CustomerShippingAddressApiController::getAllShippingAddress');
    $routes->get('(:segment)', 'Api\CustomerShippingAddressApiController::getById/$1');
    $routes->post('save', 'Api\CustomerShippingAddressApiController::save');
  });

  // WISHLIST
  $routes->group('wishlist', ['filter' => 'authApi'], function ($routes) {
    $routes->get('', 'Api\WishlistApiController::index');
    $routes->post('toggle', 'Api\WishlistApiController::toggle');
    $routes->get('count', 'Api\WishlistApiController::count');
  });

  // COUPONS
  $routes->group('coupons', function ($routes) {
    $routes->get('', 'Api\CouponApiController::listActiveCoupons');
  });

  // REVIEWS
  $routes->group('reviews', function ($routes) {
    $routes->get('', 'Api\ReviewApiController::index');
    $routes->get('product/(:segment)', 'Api\ReviewApiController::getByProduct/$1');
    $routes->post('', 'Api\ReviewApiController::create', ['filter' => 'authApi']);
  });

  $routes->group('refund', ['filter' => 'authApi'], function ($routes) {
    // REFUND ACCOUNTS
    $routes->get('accounts', 'Api\UserRefundAccountApiController::findOne');
    $routes->get('accounts/(:segment)', 'Api\UserRefundAccountApiController::getById/$1');
    $routes->post('accounts/save', 'Api\UserRefundAccountApiController::save');

    // REFUND ORDER
    $routes->post('submit', 'Api\RefundApiController::submitRefund');
    $routes->post('ship', 'Api\RefundApiController::submitReturnShipping');
    $routes->get('status/(:segment)', 'Api\RefundApiController::checkStatus/$1');
  });

  // CANCEL ORDER
  $routes->group('cancel', ['filter' => 'authApi'], function ($routes) {
    $routes->post('submit', 'Api\CancellationApiController::submitCancel');
    $routes->get('status/(:segment)', 'Api\CancellationApiController::checkStatus/$1');
  });

  // ADMIN
  $routes->group('admin', ['filter' => 'authGuard'], function ($routes) {
    // ADMIN REFUNDS
    $routes->group('refund', function ($routes) {
      $routes->get('', 'Api\RefundApiController::getPendingRefunds');
      $routes->get('(:segment)', 'Api\RefundApiController::getRefundDetail/$1');
      $routes->post('(:segment)/approve', 'Api\RefundApiController::adminApprove/$1');
      $routes->post('(:segment)/reject', 'Api\RefundApiController::adminReject/$1');
      $routes->post('(:segment)/receive', 'Api\RefundApiController::adminReceive/$1');
      $routes->post('(:segment)/final-approve', 'Api\RefundApiController::adminFinalApprove/$1');
      $routes->post('(:segment)/refund', 'Api\RefundApiController::adminRefund/$1');
    });

    // ADMIN CANCEL
    $routes->group('cancel', function ($routes) {
      $routes->get('', 'Api\CancellationApiController::getPendingCancellations');
      $routes->get('(:segment)', 'Api\CancellationApiController::getCancellationDetail/$1');
      $routes->post('(:segment)/approve', 'Api\CancellationApiController::adminApprove/$1');
      $routes->post('(:segment)/reject', 'Api\CancellationApiController::adminReject/$1');
    });
  });
});


/** ================================= 
 *          WEB DASHBOARD
 * ================================== */
$routes->group('products', ['filter' => 'authGuard'], function ($routes) {
  $routes->get('/', 'ProductController::webIndex');
  $routes->get('form', 'ProductController::form');
  $routes->post('save', 'ProductController::save');
  $routes->post('delete/(:any)', 'ProductController::webDelete/$1');
  $routes->post('delete-image', 'ProductController::deleteImage');
  $routes->get('attributes-partial', 'ProductController::getAttributesPartial');
});

$routes->group('product-category', ['filter' => 'authGuard'], function ($routes) {
  $routes->get('/', 'ProductCategoryController::webIndex');
  $routes->get('form', 'ProductCategoryController::form');
  $routes->post('save', 'ProductCategoryController::save');
  $routes->post('delete/(:any)', 'ProductCategoryController::webDelete/$1');
});

$routes->group('product-attribute', ['filter' => 'authGuard'], function ($routes) {
  $routes->get('/', 'ProductAttributeController::webIndex');
  $routes->get('form', 'ProductAttributeController::form');
  $routes->post('save', 'ProductAttributeController::save');
  $routes->post('delete/(:any)', 'ProductAttributeController::webDelete/$1');
});

$routes->group('inventory', ['filter' => 'authGuard'], function ($routes) {
  $routes->get('', 'InventoryTransactionsController::webIndex');
  $routes->get('form', 'InventoryTransactionsController::form');
  $routes->post('save', 'InventoryTransactionsController::save');
  $routes->post('delete/(:any)', 'InventoryTransactionsController::delete/$1');
});

$routes->group('customers', ['filter' => 'authGuard'], function ($routes) {
  $routes->get('', 'CustomerController::index');
  $routes->get('form', 'CustomerController::form');
  $routes->post('save', 'CustomerController::save');
  $routes->post('delete/(:any)', 'CustomerController::delete/$1');
  $routes->post('reset-password/(:any)', 'CustomerController::resetPassword/$1');
});

$routes->group('eye-examinations', ['filter' => 'authGuard'], function ($routes) {
  $routes->get('', 'EyeExaminationController::index');
  $routes->get('form', 'EyeExaminationController::form');
  $routes->post('save', 'EyeExaminationController::save');
  $routes->post('delete/(:any)', 'EyeExaminationController::delete/$1');
});

$routes->group('users', ['filter' => 'authGuard'], function ($routes) {
  $routes->get('', 'UserController::index');
  $routes->get('form', 'UserController::form');
  $routes->post('save', 'UserController::save');
  $routes->post('delete/(:any)', 'UserController::delete/$1');
});

$routes->group('coupons', ['filter' => 'authGuard'], function ($routes) {
  $routes->get('/', 'CouponController::webIndex');
  $routes->get('form', 'CouponController::form');
  $routes->post('save', 'CouponController::save');
  $routes->post('delete/(:any)', 'CouponController::webDelete/$1');
});

$routes->group('offline-sales', ['filter' => 'authGuard'], function ($routes) {
  $routes->get('', 'OfflineSalesController::index');
  $routes->get('create', 'OfflineSalesController::create');
  $routes->post('store', 'OfflineSalesController::store');

  $routes->get('success/(:segment)', 'OfflineSalesController::success/$1');
  $routes->get('print/(:segment)', 'OfflineSalesController::print/$1');
  $routes->get('(:segment)', 'OfflineSalesController::detail/$1');
});

$routes->group('online-sales', ['filter' => 'authGuard'], function ($routes) {
  $routes->get('', 'OnlineSalesController::index');
  $routes->get('(:segment)', 'OnlineSalesController::detail/$1');
});

$routes->group('refund-sales', ['filter' => 'authGuard'], function ($routes) {
  $routes->get('', 'RefundSalesController::index');
  $routes->get('(:segment)', 'RefundSalesController::detail/$1');
});

$routes->group('cancellation-sales', ['filter' => 'authGuard'], function ($routes) {
  $routes->get('', 'CancellationSalesController::index');
  $routes->get('(:segment)', 'CancellationSalesController::detail/$1');
});

$routes->group('reports', ['filter' => 'authGuard'], function ($routes) {
  $routes->get('sales', 'ReportController::index');
  $routes->get('sales/export', 'ReportController::export');
  $routes->get('inventory', 'ReportController::inventory');
  $routes->get('inventory/export', 'ReportController::exportInventory');
});

$routes->group('roles', ['filter' => 'authGuard'], function ($routes) {
  $routes->get('', 'RoleController::index');
  $routes->get('form', 'RoleController::form');
  $routes->post('save', 'RoleController::save');
  $routes->post('delete/(:any)', 'RoleController::delete/$1');
});

$routes->group('notifications', ['filter' => 'authGuard'], function ($routes) {
  $routes->get('', 'NotificationController::getAllNotifications');
  $routes->get('unread', 'NotificationController::getUnreadNotifications');
  $routes->post('read-all', 'NotificationController::markAllRead');
  $routes->post('read/(:segment)', 'NotificationController::markRead/$1');
});

<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('signin', 'AuthController::signin');
$routes->post('signin/store', 'AuthController::signinStore');
$routes->get('logout', 'AuthController::logout');
$routes->match(['options'], '(:any)', 'CorsController::handleOptions');

$routes->group('api', ['filter' => 'cors'], function ($routes) {
  // AUTH
  $routes->group('auth', function ($routes) {
    $routes->post('login', 'AuthController::login');
    $routes->post('register', 'AuthController::register');
  });

  // PRODUCTS
  $routes->group('products', function ($routes) {
    $routes->get('new-eyewear', 'ProductController::apiListNewEyewear');
    $routes->get('recommendations', 'ProductController::apiProductRecommendations');
    $routes->get('(:num)', 'ProductController::apiProductDetail/$1');
    $routes->get('category', 'ProductCategoryController::apiListProductCategory');
  });

  // CART
  $routes->group('cart', ['filter' => 'authApi'], function ($routes) {
    $routes->post('add-to-cart', 'CartController::addToCart');
    $routes->get('', 'CartController::getCart');
    $routes->get('total-cart', 'CartController::getTotalCart');
    $routes->delete('delete/(:num)', 'CartController::deleteItemCart/$1');
  });
});

$routes->get('/dashboard', 'DashboardController::index', ['filter' => 'authGuard']);

$routes->group('products', ['filter' => 'authGuard'], function ($routes) {
  $routes->get('/', 'ProductController::webIndex');
  $routes->get('form', 'ProductController::form');
  $routes->post('save', 'ProductController::save');
  $routes->post('delete/(:num)', 'ProductController::webDelete/$1');
});

$routes->group('product-category', ['filter' => 'authGuard'], function ($routes) {
  $routes->get('/', 'ProductCategoryController::webIndex');
  $routes->get('form', 'ProductCategoryController::form');
  $routes->post('save', 'ProductCategoryController::save');
  $routes->post('delete/(:num)', 'ProductCategoryController::webDelete/$1');
});

$routes->group('inventory', ['filter' => 'authGuard'], function ($routes) {
  $routes->get('', 'InventoryTransactionsController::webIndex');
  $routes->get('form', 'InventoryTransactionsController::form');
  $routes->post('save', 'InventoryTransactionsController::save');
});

$routes->group('customers', ['filter' => 'authGuard'], function ($routes) {
  $routes->get('', 'CustomerController::index');
  $routes->get('form', 'CustomerController::form');
  $routes->post('save', 'CustomerController::save');
  $routes->post('delete/(:num)', 'CustomerController::delete/$1');
});

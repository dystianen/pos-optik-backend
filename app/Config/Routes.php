<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/dashboard', 'DashboardController::index');
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
});

$routes->group('products', function ($routes) {
  $routes->get('/', 'ProductController::webIndex');
  $routes->get('create', 'ProductController::webCreateForm');
  $routes->post('store', 'ProductController::webStore');
  $routes->get('edit/(:num)', 'ProductController::webEditForm/$1');
  $routes->put('update/(:num)', 'ProductController::webUpdate/$1');
  $routes->post('delete/(:num)', 'ProductController::webDelete/$1');
});

$routes->group('product-category', function ($routes) {
  $routes->get('/', 'ProductCategoryController::webIndex');
  $routes->get('create', 'ProductCategoryController::webCreate');
  $routes->post('store', 'ProductCategoryController::webStore');
  $routes->get('edit/(:num)', 'ProductCategoryController::webEdit/$1');
  $routes->post('update/(:num)', 'ProductCategoryController::webUpdate/$1');
  $routes->post('delete/(:num)', 'ProductCategoryController::webDelete/$1');
});

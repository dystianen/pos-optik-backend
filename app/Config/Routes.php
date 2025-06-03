<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/dashboard', 'DashboardController::index');

$routes->group('api', function ($routes) {
  $routes->group('products', function ($routes) {
    $routes->get('/', 'ProductController::apiList');
    $routes->get('recommendations/(:num)', 'ProductController::apiProductRecommendations/$1');
    $routes->post('/', 'ProductController::apiCreate');
    $routes->get('(:num)', 'ProductController::apiShow/$1');
    $routes->put('(:num)', 'ProductController::apiUpdate/$1');
    $routes->delete('(:num)', 'ProductController::apiDelete/$1');
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

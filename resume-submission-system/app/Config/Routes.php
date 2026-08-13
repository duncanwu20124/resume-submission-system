<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

$routes->group('student', function ($routes) {
    $routes->get('login', 'Student\AuthController::login');
    $routes->post('login', 'Student\AuthController::processLogin');
    $routes->get('register', 'Student\AuthController::register');
    $routes->post('register', 'Student\AuthController::processRegister');
    $routes->get('logout', 'Student\AuthController::logout');
    $routes->get('dashboard', 'Student\DashboardController::index', ['filter' => 'student_auth']);
});

// 管理員端
$routes->get('AdminController/login', 'AdminController::login');
$routes->post('AdminController/doLogin', 'AdminController::doLogin');
$routes->get('AdminController/register', 'AdminController::register');
$routes->post('AdminController/doRegister', 'AdminController::doRegister');
$routes->get('AdminController', 'AdminController::index');
$routes->get('AdminController/search', 'AdminController::search');
$routes->get('AdminController/show/(:num)', 'AdminController::show/$1');
$routes->get('AdminController/viewFile/(:num)', 'AdminController::viewFile/$1');
$routes->get('AdminController/download/(:num)', 'AdminController::download/$1');
$routes->get('AdminController/logout', 'AdminController::logout');

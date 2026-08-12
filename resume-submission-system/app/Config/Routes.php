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

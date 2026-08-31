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
    $routes->get('forgot-password', 'Student\AuthController::forgotPassword');
    $routes->post('forgot-password', 'Student\AuthController::sendResetCode');
    $routes->get('verify-code', 'Student\AuthController::verifyCode');
    $routes->post('verify-code', 'Student\AuthController::processVerifyCode');
    $routes->get('reset-password', 'Student\AuthController::resetPassword');
    $routes->post('reset-password', 'Student\AuthController::processResetPassword');
    $routes->get('dashboard', 'Student\DashboardController::index', ['filter' => 'student_auth']);
    $routes->post('upload', 'Student\DashboardController::upload', ['filter' => 'student_auth']);
    $routes->get('viewFile', 'Student\DashboardController::viewFile', ['filter' => 'student_auth']);
    $routes->get('download', 'Student\DashboardController::download', ['filter' => 'student_auth']);
    $routes->post('deleteFile', 'Student\DashboardController::deleteFile', ['filter' => 'student_auth']);
});

// 管理員端
$routes->get('AdminController/login', 'AdminController::login');
$routes->post('AdminController/doLogin', 'AdminController::doLogin');
$routes->get('AdminController/register', 'AdminController::register');
$routes->post('AdminController/doRegister', 'AdminController::doRegister');
$routes->get('AdminController/verifyRegistration', 'AdminController::verifyRegistration');
$routes->post('AdminController/doVerifyRegistration', 'AdminController::doVerifyRegistration');
$routes->get('AdminController/resendVerification', 'AdminController::resendVerification');
$routes->get('AdminController', 'AdminController::index');
$routes->get('AdminController/search', 'AdminController::search');
$routes->post('AdminController/keepAlive', 'AdminController::keepAlive');
$routes->get('AdminController/applications', 'AdminController::applications');
$routes->get('AdminController/applications/export', 'AdminController::applicationsExport');
$routes->get('AdminController/applications/(:segment)', 'AdminController::applicationDetail/$1');
$routes->get('AdminController/profile', 'AdminController::profile');
$routes->post('AdminController/profile', 'AdminController::updateProfile');
$routes->get('AdminController/export', 'AdminController::export');
$routes->post('AdminController/batchDownload', 'AdminController::batchDownload');
$routes->get('AdminController/show/(:num)', 'AdminController::show/$1');
$routes->get('AdminController/viewFile/(:num)', 'AdminController::viewFile/$1');
$routes->get('AdminController/download/(:num)', 'AdminController::download/$1');
$routes->get('AdminController/forgotPassword', 'AdminController::forgotPassword');
$routes->post('AdminController/sendResetLink', 'AdminController::sendResetLink');
$routes->get('AdminController/verifyResetCode', 'AdminController::verifyResetCode');
$routes->post('AdminController/doVerifyResetCode', 'AdminController::doVerifyResetCode');
$routes->get('AdminController/resendResetCode', 'AdminController::resendResetCode');
$routes->get('AdminController/resetPassword', 'AdminController::resetPassword');
$routes->post('AdminController/doResetPassword', 'AdminController::doResetPassword');
$routes->get('AdminController/logout', 'AdminController::logout');

<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('schedule', 'Home::schedule');
$routes->get('announcement/(:num)', 'Home::announcement/$1');

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
    $routes->get('preferences', 'Student\PreferenceController::index', ['filter' => 'student_auth']);
    $routes->post('preferences', 'Student\PreferenceController::save', ['filter' => 'student_auth']);
    $routes->get('preferences/receipt', 'Student\PreferenceController::receipt', ['filter' => 'student_auth']);
    $routes->get('feedback', 'Student\FeedbackController::index', ['filter' => 'student_auth']);
    $routes->post('feedback', 'Student\FeedbackController::save', ['filter' => 'student_auth']);
    $routes->get('result', 'Student\ResultController::index', ['filter' => 'student_auth']);
});

// 管理員端 - 舊有路由相容對應（保持所有現有頁面與測試不中斷）
// 1. 認證與帳號管理 (AdminAuthController)
$routes->get('AdminController/login', 'Admin\AdminAuthController::login');
$routes->post('AdminController/doLogin', 'Admin\AdminAuthController::doLogin');
$routes->get('AdminController/register', 'Admin\AdminAuthController::register');
$routes->post('AdminController/doRegister', 'Admin\AdminAuthController::doRegister');
$routes->get('AdminController/verifyRegistration', 'Admin\AdminAuthController::verifyRegistration');
$routes->post('AdminController/doVerifyRegistration', 'Admin\AdminAuthController::doVerifyRegistration');
$routes->get('AdminController/resendVerification', 'Admin\AdminAuthController::resendVerification');
$routes->get('AdminController/profile', 'Admin\AdminAuthController::profile');
$routes->post('AdminController/profile', 'Admin\AdminAuthController::updateProfile');
$routes->get('AdminController/forgotPassword', 'Admin\AdminAuthController::forgotPassword');
$routes->post('AdminController/sendResetLink', 'Admin\AdminAuthController::sendResetLink');
$routes->get('AdminController/verifyResetCode', 'Admin\AdminAuthController::verifyResetCode');
$routes->post('AdminController/doVerifyResetCode', 'Admin\AdminAuthController::doVerifyResetCode');
$routes->get('AdminController/resendResetCode', 'Admin\AdminAuthController::resendResetCode');
$routes->get('AdminController/resetPassword', 'Admin\AdminAuthController::resetPassword');
$routes->post('AdminController/doResetPassword', 'Admin\AdminAuthController::doResetPassword');
$routes->get('AdminController/logout', 'Admin\AdminAuthController::logout');

// 2. 學生履歷管理 (StudentManagementController)
$routes->get('AdminController', 'Admin\StudentManagementController::index');
$routes->get('AdminController/search', 'Admin\StudentManagementController::search');
$routes->post('AdminController/keepAlive', 'Admin\StudentManagementController::keepAlive');
$routes->get('AdminController/export', 'Admin\StudentManagementController::export');
$routes->post('AdminController/batchDownload', 'Admin\StudentManagementController::batchDownload');
$routes->get('AdminController/show/(:num)', 'Admin\StudentManagementController::show/$1');
$routes->get('AdminController/viewFile/(:num)', 'Admin\StudentManagementController::viewFile/$1');
$routes->get('AdminController/download/(:num)', 'Admin\StudentManagementController::download/$1');

// 3. 審查與防弊 (ReviewController)
$routes->get('AdminController/pdfDuplicates', 'Admin\ReviewController::pdfDuplicates');

// 4. 志願序管理 (PreferenceAdminController)
$routes->get('AdminController/preferences', 'Admin\PreferenceAdminController::preferences');
$routes->get('AdminController/preferences/export', 'Admin\PreferenceAdminController::export');
$routes->get('AdminController/preferences/(:num)', 'Admin\PreferenceAdminController::preferenceDetail/$1');
$routes->post('AdminController/preferences/(:num)/reset', 'Admin\PreferenceAdminController::resetPreference/$1');

// 5. 評分管理 (ScoringController)
$routes->get('AdminController/scoring', 'Admin\ScoringController::scoring');
$routes->get('AdminController/scoring/export', 'Admin\ScoringController::export');
$routes->post('AdminController/scoring/(:num)', 'Admin\ScoringController::saveScore/$1');

// 6. 分發管理 (AllocationController)
$routes->get('AdminController/allocation', 'Admin\AllocationController::allocation');
$routes->get('AdminController/allocation/export', 'Admin\AllocationController::export');
$routes->post('AdminController/allocation/preview', 'Admin\AllocationController::createAllocationPreview');
$routes->post('AdminController/allocation/(:num)/publish', 'Admin\AllocationController::publishAllocation/$1');

// 7. 公告管理 (AnnouncementController)
$routes->get('AdminController/announcements', 'Admin\AnnouncementController::announcements');
$routes->post('AdminController/createAnnouncement', 'Admin\AnnouncementController::createAnnouncement');
$routes->post('AdminController/toggleAnnouncement/(:num)', 'Admin\AnnouncementController::toggleAnnouncement/$1');
$routes->post('AdminController/deleteAnnouncement/(:num)', 'Admin\AnnouncementController::deleteAnnouncement/$1');

// 8. 使用回饋管理 (AdminFeedbackController)
$routes->get('AdminController/feedback/export', 'AdminFeedbackController::export');
$routes->get('AdminController/feedback', 'AdminFeedbackController::index');
$routes->get('AdminController/feedback/(:num)', 'AdminFeedbackController::show/$1');

// 9. 操作日誌 (AuditLogController)
$routes->get('AdminController/auditLogs', 'Admin\AuditLogController::index');

// 10. 管理員帳號與角色管理 (AdminAuthController)
$routes->get('AdminController/admins', 'Admin\AdminAuthController::manageAdmins');
$routes->post('AdminController/admins/(:num)/role', 'Admin\AdminAuthController::updateAdminRole/$1');
$routes->post('AdminController/admins/(:num)/delete', 'Admin\AdminAuthController::deleteAdmin/$1');

// 現代化 /admin 群組路由別名
$routes->group('admin', function ($routes) {
    $routes->get('login', 'Admin\AdminAuthController::login');
    $routes->post('login', 'Admin\AdminAuthController::doLogin');
    $routes->get('logout', 'Admin\AdminAuthController::logout');
    $routes->get('profile', 'Admin\AdminAuthController::profile');
    $routes->post('profile', 'Admin\AdminAuthController::updateProfile');
    $routes->get('students', 'Admin\StudentManagementController::index');
    $routes->get('pdf-duplicates', 'Admin\ReviewController::pdfDuplicates');
    $routes->get('preferences', 'Admin\PreferenceAdminController::preferences');
    $routes->get('scoring', 'Admin\ScoringController::scoring');
    $routes->get('allocation', 'Admin\AllocationController::allocation');
    $routes->get('announcements', 'Admin\AnnouncementController::announcements');
    $routes->get('feedback', 'AdminFeedbackController::index');
    $routes->get('feedback/export', 'AdminFeedbackController::export');
    $routes->get('feedback/(:num)', 'AdminFeedbackController::show/$1');
    $routes->get('audit-logs', 'Admin\AuditLogController::index');
    $routes->get('manage-admins', 'Admin\AdminAuthController::manageAdmins');
});


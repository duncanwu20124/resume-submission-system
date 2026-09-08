<?php

namespace Tests\Unit;

use App\Controllers\Admin\AdminAuthController;
use App\Controllers\Admin\AllocationController;
use App\Controllers\Admin\AnnouncementController;
use App\Controllers\Admin\AuditLogController;
use App\Controllers\Admin\BaseAdminController;
use App\Controllers\Admin\PreferenceAdminController;
use App\Controllers\Admin\ReviewController;
use App\Controllers\Admin\ScoringController;
use App\Controllers\Admin\StudentManagementController;
use App\Controllers\AdminController;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AdminControllersDecompositionTest extends CIUnitTestCase
{
    public function testNewControllersExistAndExtendBaseAdminController(): void
    {
        $expectedControllers = [
            AdminAuthController::class,
            StudentManagementController::class,
            ReviewController::class,
            PreferenceAdminController::class,
            ScoringController::class,
            AllocationController::class,
            AnnouncementController::class,
            AuditLogController::class,
        ];

        foreach ($expectedControllers as $controllerClass) {
            $this->assertTrue(class_exists($controllerClass), "Class {$controllerClass} should exist.");
            $this->assertTrue(
                is_subclass_of($controllerClass, BaseAdminController::class),
                "Class {$controllerClass} should extend BaseAdminController."
            );
        }
    }

    public function testAdminControllerIsBackwardCompatibleFacade(): void
    {
        $this->assertTrue(class_exists(AdminController::class));
        $this->assertTrue(is_subclass_of(AdminController::class, StudentManagementController::class));

        $admin = new AdminController();

        $expectedMethods = [
            'login',
            'doLogin',
            'register',
            'doRegister',
            'verifyRegistration',
            'doVerifyRegistration',
            'resendVerification',
            'profile',
            'updateProfile',
            'forgotPassword',
            'sendResetLink',
            'verifyResetCode',
            'doVerifyResetCode',
            'resendResetCode',
            'resetPassword',
            'doResetPassword',
            'logout',
            'index',
            'search',
            'keepAlive',
            'export',
            'batchDownload',
            'show',
            'viewFile',
            'download',
            'pdfDuplicates',
            'preferences',
            'preferenceDetail',
            'resetPreference',
            'scoring',
            'saveScore',
            'allocation',
            'createAllocationPreview',
            'publishAllocation',
            'announcements',
            'createAnnouncement',
            'toggleAnnouncement',
            'deleteAnnouncement',
            'auditLogs',
            'manageAdmins',
            'updateAdminRole',
            'deleteAdmin',
        ];

        foreach ($expectedMethods as $method) {
            $this->assertTrue(method_exists($admin, $method), "AdminController must provide method {$method}");
        }
    }

    public function testRoutesMappedToNewControllers(): void
    {
        $definedRoutes = service('routes')->getRoutes('GET');

        $this->assertSame('\App\Controllers\Admin\AdminAuthController::login', $definedRoutes['AdminController/login'] ?? null);
        $this->assertSame('\App\Controllers\Admin\StudentManagementController::index', $definedRoutes['AdminController'] ?? null);
        $this->assertSame('\App\Controllers\Admin\ScoringController::scoring', $definedRoutes['AdminController/scoring'] ?? null);
        $this->assertSame('\App\Controllers\Admin\PreferenceAdminController::preferences', $definedRoutes['AdminController/preferences'] ?? null);
        $this->assertSame('\App\Controllers\Admin\AllocationController::allocation', $definedRoutes['AdminController/allocation'] ?? null);
        $this->assertSame('\App\Controllers\Admin\ReviewController::pdfDuplicates', $definedRoutes['AdminController/pdfDuplicates'] ?? null);
        $this->assertSame('\App\Controllers\Admin\AnnouncementController::announcements', $definedRoutes['AdminController/announcements'] ?? null);
        $this->assertSame('\App\Controllers\Admin\AuditLogController::index', $definedRoutes['AdminController/auditLogs'] ?? null);
        $this->assertSame('\App\Controllers\Admin\AdminAuthController::manageAdmins', $definedRoutes['AdminController/admins'] ?? null);
    }
}

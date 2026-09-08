<?php

namespace App\Controllers;

use App\Controllers\Admin\AdminAuthController;
use App\Controllers\Admin\AllocationController;
use App\Controllers\Admin\AnnouncementController;
use App\Controllers\Admin\AuditLogController;
use App\Controllers\Admin\PreferenceAdminController;
use App\Controllers\Admin\ReviewController;
use App\Controllers\Admin\ScoringController;
use App\Controllers\Admin\StudentManagementController;

/**
 * AdminController - Backward Compatibility Facade
 *
 * This controller has been refactored and decoupled into dedicated modular controllers:
 * - AdminAuthController: Admin authentication, registration, profile & password reset
 * - StudentManagementController: Student resume browsing, search, view, download & export
 * - ReviewController: Resume duplicate detection & review
 * - PreferenceAdminController: Student preference listing, stats, detail & reset
 * - ScoringController: Student scoring, grading & status management
 * - AllocationController: Quota allocation runs, preview & publishing
 * - AnnouncementController: System announcements management
 * - AuditLogController: Administrator activity logs & audits
 */
class AdminController extends StudentManagementController
{
    private function forward(string $controllerClass, string $method, array $params = [])
    {
        $instance = new $controllerClass();
        $instance->initController($this->request, $this->response, $this->logger);

        return $instance->$method(...$params);
    }

    // --- 1. AdminAuthController Delegation ---
    public function login()
    {
        return $this->forward(AdminAuthController::class, 'login');
    }

    public function doLogin()
    {
        return $this->forward(AdminAuthController::class, 'doLogin');
    }

    public function register()
    {
        return $this->forward(AdminAuthController::class, 'register');
    }

    public function doRegister()
    {
        return $this->forward(AdminAuthController::class, 'doRegister');
    }

    public function verifyRegistration()
    {
        return $this->forward(AdminAuthController::class, 'verifyRegistration');
    }

    public function doVerifyRegistration()
    {
        return $this->forward(AdminAuthController::class, 'doVerifyRegistration');
    }

    public function resendVerification()
    {
        return $this->forward(AdminAuthController::class, 'resendVerification');
    }

    public function profile()
    {
        return $this->forward(AdminAuthController::class, 'profile');
    }

    public function updateProfile()
    {
        return $this->forward(AdminAuthController::class, 'updateProfile');
    }

    public function forgotPassword()
    {
        return $this->forward(AdminAuthController::class, 'forgotPassword');
    }

    public function sendResetLink()
    {
        return $this->forward(AdminAuthController::class, 'sendResetLink');
    }

    public function verifyResetCode()
    {
        return $this->forward(AdminAuthController::class, 'verifyResetCode');
    }

    public function doVerifyResetCode()
    {
        return $this->forward(AdminAuthController::class, 'doVerifyResetCode');
    }

    public function resendResetCode()
    {
        return $this->forward(AdminAuthController::class, 'resendResetCode');
    }

    public function resetPassword()
    {
        return $this->forward(AdminAuthController::class, 'resetPassword');
    }

    public function doResetPassword()
    {
        return $this->forward(AdminAuthController::class, 'doResetPassword');
    }

    public function logout()
    {
        return $this->forward(AdminAuthController::class, 'logout');
    }

    // --- 2. ReviewController Delegation ---
    public function pdfDuplicates()
    {
        return $this->forward(ReviewController::class, 'pdfDuplicates');
    }

    // --- 3. PreferenceAdminController Delegation ---
    public function preferences()
    {
        return $this->forward(PreferenceAdminController::class, 'preferences');
    }

    public function preferenceDetail($studentDbId)
    {
        return $this->forward(PreferenceAdminController::class, 'preferenceDetail', [$studentDbId]);
    }

    public function resetPreference($studentDbId)
    {
        return $this->forward(PreferenceAdminController::class, 'resetPreference', [$studentDbId]);
    }

    public function exportPreferences()
    {
        return $this->forward(PreferenceAdminController::class, 'export');
    }

    // --- 4. ScoringController Delegation ---
    public function scoring()
    {
        return $this->forward(ScoringController::class, 'scoring');
    }

    public function exportScoring()
    {
        return $this->forward(ScoringController::class, 'export');
    }

    public function saveScore($studentDbId)
    {
        return $this->forward(ScoringController::class, 'saveScore', [$studentDbId]);
    }

    // --- 5. AllocationController Delegation ---
    public function allocation()
    {
        return $this->forward(AllocationController::class, 'allocation');
    }

    public function exportAllocation()
    {
        return $this->forward(AllocationController::class, 'export');
    }

    public function createAllocationPreview()
    {
        return $this->forward(AllocationController::class, 'createAllocationPreview');
    }

    public function publishAllocation($runId)
    {
        return $this->forward(AllocationController::class, 'publishAllocation', [$runId]);
    }

    // --- 6. AnnouncementController Delegation ---
    public function announcements()
    {
        return $this->forward(AnnouncementController::class, 'announcements');
    }

    public function createAnnouncement()
    {
        return $this->forward(AnnouncementController::class, 'createAnnouncement');
    }

    public function toggleAnnouncement($id)
    {
        return $this->forward(AnnouncementController::class, 'toggleAnnouncement', [$id]);
    }

    public function deleteAnnouncement($id)
    {
        return $this->forward(AnnouncementController::class, 'deleteAnnouncement', [$id]);
    }

    // --- 7. AuditLogController Delegation ---
    public function auditLogs()
    {
        return $this->forward(AuditLogController::class, 'index');
    }

    // --- 8. Admin Management Delegation (AdminAuthController) ---
    public function manageAdmins()
    {
        return $this->forward(AdminAuthController::class, 'manageAdmins');
    }

    public function updateAdminRole($adminId)
    {
        return $this->forward(AdminAuthController::class, 'updateAdminRole', [$adminId]);
    }

    public function deleteAdmin($adminId)
    {
        return $this->forward(AdminAuthController::class, 'deleteAdmin', [$adminId]);
    }
}

<?php

namespace Tests\Database;

use App\Models\AdminModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class AdminRoutesFullSmokeTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = null;
    protected $migrateOnce = true;
    protected $refresh = false;

    private int $adminId = 0;
    private int $studentId = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $adminModel = new AdminModel();
        $adminModel->where('username', 'smoketestadmin')->delete();
        $this->adminId = $adminModel->insert([
            'username' => 'smoketestadmin',
            'name'     => 'Smoke Admin',
            'email'    => 'smoketest@example.com',
            'role'     => AdminModel::ROLE_SUPER_ADMIN,
            'password' => password_hash('password123', PASSWORD_DEFAULT),
        ], true);

        $userModel = new UserModel();
        $userModel->where('email', 'smoke-student@example.com')->delete();
        $this->studentId = $userModel->insert([
            'student_id' => 'SMOKE-001',
            'name'       => '測試學生',
            'email'      => 'smoke-student@example.com',
            'password'   => password_hash('password123', PASSWORD_DEFAULT),
        ], true);
    }

    protected function tearDown(): void
    {
        if ($this->adminId > 0) {
            (new AdminModel())->delete($this->adminId);
        }
        if ($this->studentId > 0) {
            (new UserModel())->delete($this->studentId);
        }

        parent::tearDown();
    }

    public function testPublicRoutes(): void
    {
        $routes = ['/', '/schedule', '/student/login', '/student/register', '/student/forgot-password', '/AdminController/login', '/AdminController/register', '/AdminController/forgotPassword', '/admin/login'];

        foreach ($routes as $route) {
            $result = $this->get($route);
            $result->assertOK();
        }
    }

    public function testAdminProtectedRoutes(): void
    {
        $adminSession = [
            'admin_logged_in'     => true,
            'admin_id'            => $this->adminId,
            'admin_role'          => AdminModel::ROLE_SUPER_ADMIN,
            'admin_last_activity' => time(),
        ];

        $routes = [
            '/AdminController',
            '/AdminController/search',
            '/AdminController/profile',
            '/AdminController/preferences',
            '/AdminController/preferences?school=東吳大學&department=歷史學系',
            '/AdminController/pdfDuplicates',
            '/AdminController/scoring',
            '/AdminController/scoring?search_by=name&keyword=測試',
            '/AdminController/scoring?search_by=id&keyword=SMOKE',
            '/AdminController/allocation',
            '/AdminController/announcements',
            '/AdminController/auditLogs',
            '/AdminController/admins',
            '/AdminController/show/' . $this->studentId,
            '/admin/students',
            '/admin/preferences',
            '/admin/pdf-duplicates',
            '/admin/scoring',
            '/admin/allocation',
            '/admin/announcements',
            '/admin/audit-logs',
            '/admin/manage-admins',
        ];

        foreach ($routes as $route) {
            $result = $this->withSession($adminSession)->get($route);
            $result->assertOK();
        }

        // Test export CSV route
        $exportResult = $this->withSession($adminSession)->get('/AdminController/export');
        $exportResult->assertOK();
    }

    public function testStudentProtectedRoutes(): void
    {
        $studentSession = [
            'student_logged_in' => true,
            'student_id'        => $this->studentId,
            'student_number'    => 'SMOKE-001',
            'student_name'      => '測試學生',
        ];

        $routes = [
            '/student/dashboard',
            '/student/preferences',
            '/student/result',
        ];

        foreach ($routes as $route) {
            $result = $this->withSession($studentSession)->get($route);
            $result->assertOK();
        }
    }
}

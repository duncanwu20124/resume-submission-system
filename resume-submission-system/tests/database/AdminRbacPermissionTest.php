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
final class AdminRbacPermissionTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = null;
    protected $migrateOnce = true;
    protected $refresh = false;

    private int $superAdminId = 0;
    private int $adminId = 0;
    private int $reviewerId = 0;
    private int $studentId = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $adminModel = new AdminModel();
        $adminModel->whereIn('username', ['rbac_super', 'rbac_admin', 'rbac_reviewer'])->delete();

        $this->superAdminId = $adminModel->insert([
            'username' => 'rbac_super',
            'name'     => 'RBAC Super Admin',
            'email'    => 'rbac_super@example.com',
            'role'     => AdminModel::ROLE_SUPER_ADMIN,
            'password' => password_hash('password123', PASSWORD_DEFAULT),
        ], true);

        $this->adminId = $adminModel->insert([
            'username' => 'rbac_admin',
            'name'     => 'RBAC Admin',
            'email'    => 'rbac_admin@example.com',
            'role'     => AdminModel::ROLE_ADMIN,
            'password' => password_hash('password123', PASSWORD_DEFAULT),
        ], true);

        $this->reviewerId = $adminModel->insert([
            'username' => 'rbac_reviewer',
            'name'     => 'RBAC Reviewer',
            'email'    => 'rbac_reviewer@example.com',
            'role'     => AdminModel::ROLE_REVIEWER,
            'password' => password_hash('password123', PASSWORD_DEFAULT),
        ], true);

        $userModel = new UserModel();
        $userModel->where('email', 'rbac-student@example.com')->delete();
        $this->studentId = $userModel->insert([
            'student_id' => 'RBAC-001',
            'name'       => '權限測試學生',
            'email'      => 'rbac-student@example.com',
            'password'   => password_hash('password123', PASSWORD_DEFAULT),
        ], true);
    }

    protected function tearDown(): void
    {
        $adminModel = new AdminModel();
        if ($this->superAdminId > 0) {
            $adminModel->delete($this->superAdminId);
        }
        if ($this->adminId > 0) {
            $adminModel->delete($this->adminId);
        }
        if ($this->reviewerId > 0) {
            $adminModel->delete($this->reviewerId);
        }
        if ($this->studentId > 0) {
            (new UserModel())->delete($this->studentId);
        }
        db_connect()->table('audit_logs')
            ->whereIn('admin_name', ['RBAC Super Admin', 'Smoke Admin'])
            ->orLike('action', '自動化測試')
            ->delete();

        parent::tearDown();
    }

    public function testReviewerAllowedRoutes(): void
    {
        $reviewerSession = [
            'admin_logged_in'     => true,
            'admin_id'            => $this->reviewerId,
            'admin_role'          => AdminModel::ROLE_REVIEWER,
            'admin_last_activity' => time(),
        ];

        $allowedRoutes = [
            '/AdminController',
            '/AdminController/search',
            '/AdminController/pdfDuplicates',
            '/AdminController/scoring',
            '/AdminController/profile',
            '/AdminController/show/' . $this->studentId,
        ];

        foreach ($allowedRoutes as $route) {
            $result = $this->withSession($reviewerSession)->get($route);
            $result->assertOK();
        }
    }

    public function testReviewerForbiddenRoutes(): void
    {
        $reviewerSession = [
            'admin_logged_in'     => true,
            'admin_id'            => $this->reviewerId,
            'admin_role'          => AdminModel::ROLE_REVIEWER,
            'admin_last_activity' => time(),
        ];

        $forbiddenGetRoutes = [
            '/AdminController/preferences',
            '/AdminController/allocation',
            '/AdminController/announcements',
            '/AdminController/auditLogs',
            '/AdminController/admins',
            '/AdminController/export',
        ];

        foreach ($forbiddenGetRoutes as $route) {
            $result = $this->withSession($reviewerSession)->get($route);
            $result->assertRedirect();
        }

        // Test POST forbidden actions
        $previewPost = $this->withSession($reviewerSession)->post('/AdminController/allocation/preview');
        $previewPost->assertRedirect();

        $publishPost = $this->withSession($reviewerSession)->post('/AdminController/allocation/1/publish');
        $publishPost->assertRedirect();

        $announcementPost = $this->withSession($reviewerSession)->post('/AdminController/createAnnouncement', [
            'title' => '非法公告',
            'content' => '非法內容',
        ]);
        $announcementPost->assertRedirect();
    }

    public function testAdminRoleBoundaries(): void
    {
        $adminSession = [
            'admin_logged_in'     => true,
            'admin_id'            => $this->adminId,
            'admin_role'          => AdminModel::ROLE_ADMIN,
            'admin_last_activity' => time(),
        ];

        // Allowed for admin
        $allowed = [
            '/AdminController',
            '/AdminController/preferences',
            '/AdminController/allocation',
            '/AdminController/announcements',
            '/AdminController/scoring',
            '/AdminController/pdfDuplicates',
        ];

        foreach ($allowed as $route) {
            $result = $this->withSession($adminSession)->get($route);
            $result->assertOK();
        }

        // Forbidden for admin (strictly super_admin only)
        $superOnlyRoutes = [
            '/AdminController/admins',
            '/AdminController/auditLogs',
        ];

        foreach ($superOnlyRoutes as $route) {
            $result = $this->withSession($adminSession)->get($route);
            $result->assertRedirect();
        }

        // Publish allocation is strictly forbidden for admin
        $publishPost = $this->withSession($adminSession)->post('/AdminController/allocation/1/publish');
        $publishPost->assertRedirect();
    }

    public function testSuperAdminManagementAndProtections(): void
    {
        $superSession = [
            'admin_logged_in'     => true,
            'admin_id'            => $this->superAdminId,
            'admin_role'          => AdminModel::ROLE_SUPER_ADMIN,
            'admin_last_activity' => time(),
        ];

        // Super Admin can access admin management
        $result = $this->withSession($superSession)->get('/AdminController/admins');
        $result->assertOK();
        $result->assertSee('管理員帳號與角色權限');

        // Super Admin can access audit logs
        $auditResult = $this->withSession($superSession)->get('/AdminController/auditLogs');
        $auditResult->assertOK();

        // Super Admin can update another admin's role
        $updateResult = $this->withSession($superSession)->post('/AdminController/admins/' . $this->adminId . '/role', [
            'role' => AdminModel::ROLE_REVIEWER,
        ]);
        $updateResult->assertRedirectTo('/AdminController/admins');

        $updatedAdmin = (new AdminModel())->find($this->adminId);
        $this->assertSame(AdminModel::ROLE_REVIEWER, $updatedAdmin['role']);

        // Protection: Super Admin cannot delete own account
        $selfDeleteResult = $this->withSession($superSession)->post('/AdminController/admins/' . $this->superAdminId . '/delete');
        $selfDeleteResult->assertRedirectTo('/AdminController/admins');
        $selfAdmin = (new AdminModel())->find($this->superAdminId);
        $this->assertNotNull($selfAdmin);

        // Super Admin can delete other admin
        $deleteResult = $this->withSession($superSession)->post('/AdminController/admins/' . $this->reviewerId . '/delete');
        $deleteResult->assertRedirectTo('/AdminController/admins');
        $deletedReviewer = (new AdminModel())->find($this->reviewerId);
        $this->assertNull($deletedReviewer);
    }

    public function testRoleAutoSyncFromDatabaseWhenSessionMissingOrStale(): void
    {
        // 模擬超級管理員 session 曾遺失 admin_role 或為舊版 session
        $staleSession = [
            'admin_logged_in'     => true,
            'admin_id'            => $this->superAdminId,
            // 故意不給 admin_role，或給錯誤的 admin
            'admin_role'          => null,
            'admin_last_activity' => time(),
        ];

        // 存取需超級管理員權限的頁面
        $result = $this->withSession($staleSession)->get('/AdminController/admins');
        $result->assertOK();
        $result->assertSee('超級管理員');

        // 同時驗證 session 已自動被補齊為 super_admin
        $this->assertSame(AdminModel::ROLE_SUPER_ADMIN, session()->get('admin_role'));
    }

    public function testLoginWithDefaultAdmin123Credentials(): void
    {
        $adminModel = new AdminModel();

        // 測試 super_admin 帳號
        $superAdmin = $adminModel->findByUsername('super_admin');
        if ($superAdmin) {
            $this->assertTrue(password_verify('admin123', $superAdmin['password']));
            $this->assertSame(AdminModel::ROLE_SUPER_ADMIN, $superAdmin['role']);
        }

        // 測試 admin_user 帳號
        $adminUser = $adminModel->findByUsername('admin_user');
        if ($adminUser) {
            $this->assertTrue(password_verify('admin123', $adminUser['password']));
            $this->assertSame(AdminModel::ROLE_ADMIN, $adminUser['role']);
        }

        // 測試 reviewer 帳號
        $reviewer = $adminModel->findByUsername('reviewer');
        if ($reviewer) {
            $this->assertTrue(password_verify('admin123', $reviewer['password']));
            $this->assertSame(AdminModel::ROLE_REVIEWER, $reviewer['role']);
        }
    }

    public function testAuditLogsModuleAndFilter(): void
    {
        // 1. 驗證未登入無法存取審計日誌
        $guestResult = $this->get('/AdminController/auditLogs');
        $guestResult->assertRedirectTo('/AdminController/login');

        // 2. 寫入一筆測試日誌
        \App\Models\AuditLogModel::log(
            '測試專用模組',
            '執行自動化測試操作日誌驗證',
            '成功',
            '測試細節備註資訊',
            $this->superAdminId,
            'RBAC Super Admin'
        );

        $superSession = [
            'admin_logged_in'     => true,
            'admin_id'            => $this->superAdminId,
            'admin_role'          => AdminModel::ROLE_SUPER_ADMIN,
            'admin_last_activity' => time(),
        ];

        // 3. 超級管理員瀏覽審計日誌頁面
        $result = $this->withSession($superSession)->get('/AdminController/auditLogs');
        $result->assertOK();
        $result->assertSee('系統操作審計紀錄');
        $result->assertSee('測試專用模組');
        $result->assertSee('執行自動化測試操作日誌驗證');

        // 4. 篩選模組
        $filterResult = $this->withSession($superSession)->get('/AdminController/auditLogs?module=' . urlencode('測試專用模組'));
        $filterResult->assertOK();
        $filterResult->assertSee('測試專用模組');
    }
}

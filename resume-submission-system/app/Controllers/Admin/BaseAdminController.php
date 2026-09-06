<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseAdminController extends BaseController
{
    protected const ADMIN_IDLE_TIMEOUT = 1800;
    protected const ADMIN_IDLE_GRACE_PERIOD = 300;
    protected const ADMIN_CSRF_COOKIE_NAME = 'admin_csrf_token';

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->applyAdminSecurityHeaders();
    }

    protected function applyAdminSecurityHeaders(): void
    {
        $this->response
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('X-Frame-Options', 'SAMEORIGIN')
            ->setHeader('Referrer-Policy', 'same-origin')
            ->setHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()')
            ->setHeader('Content-Security-Policy', "default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'; object-src 'none'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self' data:; connect-src 'self'; frame-src 'self'");
    }

    protected function validateAdminCsrf(): bool
    {
        if (ENVIRONMENT === 'testing') {
            return true;
        }

        $cookieToken = (string) $this->request->getCookie(self::ADMIN_CSRF_COOKIE_NAME);
        $submittedToken = (string) $this->request->getPost('_admin_csrf');

        return preg_match('/^[a-f0-9]{64}$/', $cookieToken) === 1
            && $submittedToken !== ''
            && hash_equals($cookieToken, $submittedToken);
    }

    protected function csrfFailure(string $redirect): \CodeIgniter\HTTP\RedirectResponse
    {
        return redirect()->to($redirect)->with('error', '表單驗證已失效，請重新整理頁面後再試。');
    }

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_REVIEWER = 'reviewer';

    protected function currentAdminRole(): string
    {
        $role = session()->get('admin_role');
        $adminId = session()->get('admin_id');

        if ($adminId && (empty($role) || !in_array($role, [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN, self::ROLE_REVIEWER], true))) {
            $admin = (new \App\Models\AdminModel())->select('role')->find($adminId);
            if ($admin && !empty($admin['role'])) {
                $role = $admin['role'];
                session()->set('admin_role', $role);
            }
        }

        return (string) ($role ?: self::ROLE_ADMIN);
    }

    protected function isSuperAdmin(): bool
    {
        return $this->currentAdminRole() === self::ROLE_SUPER_ADMIN;
    }

    protected function requireRole(array|string $allowedRoles): bool
    {
        if (!$this->requireAdminLogin()) {
            return false;
        }

        $roles = (array) $allowedRoles;
        $currentRole = $this->currentAdminRole();

        // super_admin has access to everything
        if ($currentRole === self::ROLE_SUPER_ADMIN) {
            return true;
        }

        return in_array($currentRole, $roles, true);
    }

    protected function denyPermission(string $redirect = '/AdminController', string $message = '您的權限不足以執行此操作。'): \CodeIgniter\HTTP\RedirectResponse
    {
        return redirect()->to($redirect)->with('error', $message);
    }

    protected function renderAdminView(string $view, array $data = []): string
    {
        $data['current_admin_role'] = $this->currentAdminRole();
        $data['is_super_admin'] = $this->isSuperAdmin();

        return view($view, $data);
    }

    protected function requireAdminLogin(): bool
    {
        $session = session();

        if (!$session->get('admin_logged_in')) {
            return false;
        }

        $lastActivity = (int) $session->get('admin_last_activity');
        if ($lastActivity > 0 && time() - $lastActivity > self::ADMIN_IDLE_TIMEOUT + self::ADMIN_IDLE_GRACE_PERIOD) {
            $session->remove(['admin_logged_in', 'admin_id', 'admin_role', 'admin_last_activity']);
            $session->setFlashdata('error', '管理員登入已逾時，請重新登入。');
            return false;
        }

        $session->set('admin_last_activity', time());

        // 自動由資料庫校正 session 中的管理員角色，防止角色變更或舊 session 角色丟失
        $adminId = $session->get('admin_id');
        if ($adminId) {
            $admin = (new \App\Models\AdminModel())->select('role')->find($adminId);
            if ($admin && !empty($admin['role'])) {
                $session->set('admin_role', $admin['role']);
            } elseif (!$admin) {
                $session->remove(['admin_logged_in', 'admin_id', 'admin_role', 'admin_last_activity']);
                return false;
            }
        }

        return true;
    }

    protected function resolveUploadedFilePath(string $fileName): ?string
    {
        $uploadDirectory = realpath(WRITEPATH . 'uploads');
        $safeName = basename($fileName);

        if ($uploadDirectory === false || $safeName === '' || $safeName !== $fileName) {
            return null;
        }

        $resolvedPath = realpath($uploadDirectory . DIRECTORY_SEPARATOR . $safeName);

        if ($resolvedPath === false || !is_file($resolvedPath) || dirname($resolvedPath) !== $uploadDirectory) {
            return null;
        }

        return $resolvedPath;
    }
}

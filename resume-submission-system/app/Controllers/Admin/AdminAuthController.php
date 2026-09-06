<?php

namespace App\Controllers\Admin;

use App\Models\AdminModel;
use App\Models\AuditLogModel;

class AdminAuthController extends BaseAdminController
{
    public function login()
    {
        $error = session()->getFlashdata('error');
        return $this->renderAdminView('admin/login', $error ? ['error' => $error] : []);
    }

    public function register()
    {
        return $this->renderAdminView('admin/register');
    }

    private function generateAdminRegistrationCode(): string
    {
        $lettersAll = 'ABCDEFGHJKLMNOPQRSTUVWXYZ';
        $digitsAll = '0123456789';

        $codeChars = [];
        for ($i = 0; $i < 3; $i++) {
            $codeChars[] = $lettersAll[random_int(0, strlen($lettersAll) - 1)];
        }
        for ($i = 0; $i < 3; $i++) {
            $codeChars[] = $digitsAll[random_int(0, strlen($digitsAll) - 1)];
        }

        shuffle($codeChars);
        return implode('', $codeChars);
    }

    private function sendAdminRegistrationEmail(string $email, string $name, string $code): bool
    {
        $subject = '【學生甄選與志願媒合系統】管理員帳號註冊驗證碼與安全提醒通知';
        $message = "您好 {$name}，\n\n" .
            "系統偵測到剛才有人嘗試使用您的資料與此 Email（{$email}）進行管理員帳號註冊。\n\n" .
            "若這是您本人的操作，您的註冊驗證碼為：\n" .
            "------------------------\n" .
            "【 {$code} 】\n" .
            "------------------------\n" .
            "（此驗證碼由 3 位英文字母與 3 位數字組合而成，有效時間為 15 分鐘）\n\n" .
            "請在註冊頁面中輸入此驗證碼以完成管理員帳號建立。\n\n" .
            "⚠️ 安全提醒：若非您本人操作，代表有人嘗試使用您的資訊進行註冊，請提高警覺並留意帳號安全，您可以直接忽略此郵件。";

        $resend = new \App\Libraries\ResendMailer();
        if ($resend->isConfigured()) {
            $sent = $resend->send($email, $subject, $message);
            if ($sent) {
                return true;
            }
        }

        $emailService = \Config\Services::email();
        $emailConfig = config('Email');
        if (!empty($emailConfig->SMTPHost) || !empty($emailConfig->fromEmail)) {
            try {
                $emailService->setTo($email);
                $emailService->setSubject($subject);
                $emailService->setMessage($message);
                if ($emailService->send()) {
                    return true;
                }
            } catch (\Exception $e) {
                log_message('error', 'Admin registration email sending failed: ' . $e->getMessage());
            }
        }

        return false;
    }

    public function doRegister()
    {
        if (!$this->validateAdminCsrf()) {
            return $this->csrfFailure('/AdminController/register');
        }

        $name = trim($this->request->getVar('name'));
        $username = trim($this->request->getVar('username'));
        $email = trim($this->request->getVar('email'));
        $password = $this->request->getVar('password');
        $passwordConfirm = $this->request->getVar('password_confirm');
        $employeeId = trim($this->request->getVar('employee_id'));

        $error = null;

        if (empty($name) || empty($username) || empty($email) || empty($password) || empty($passwordConfirm) || empty($employeeId)) {
            $error = '所有欄位皆為必填項目。';
        } elseif ($password !== $passwordConfirm) {
            $error = '兩次輸入的密碼不一致。';
        } elseif (strlen($password) < 6) {
            $error = '密碼長度至少需為 6 個字元。';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = '請輸入正確的電子郵件格式。';
        } elseif (!preg_match('/^admin\d{2}$/', $employeeId)) {
            $error = '員工證錯誤。必須是 admin01 或 admin 後面接兩個數字。';
        } else {
            $model = new AdminModel();
            if ($model->where('username', $username)->first()) {
                $error = '此帳號已存在。';
            } elseif ($model->where('email', $email)->first()) {
                $error = '此 Email 已被註冊。';
            }
        }

        if ($error !== null) {
            return $this->renderAdminView('admin/register', ['error' => $error, 'old' => $this->request->getPost()]);
        }

        $verificationCode = $this->generateAdminRegistrationCode();
        $expiresAt = time() + 900;

        session()->set('admin_pending_register', [
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'employee_id' => $employeeId,
            'code' => $verificationCode,
            'expires_at' => $expiresAt,
        ]);

        $mailSent = $this->sendAdminRegistrationEmail($email, $name, $verificationCode);
        if (!$mailSent) {
            session()->setFlashdata('dev_admin_code', $verificationCode);
        }

        return redirect()->to('/AdminController/verifyRegistration')->with('success', '驗證碼與安全提醒已寄出至 ' . esc($email) . '，請前往信箱查收！');
    }

    public function verifyRegistration()
    {
        $pending = session()->get('admin_pending_register');
        if (!$pending) {
            return redirect()->to('/AdminController/register')->with('error', '請先填寫註冊資料。');
        }

        return $this->renderAdminView('admin/verify_registration', [
            'email' => $pending['email'],
        ]);
    }

    public function doVerifyRegistration()
    {
        if (!$this->validateAdminCsrf()) {
            return $this->csrfFailure('/AdminController/register');
        }

        $pending = session()->get('admin_pending_register');
        if (!$pending) {
            return redirect()->to('/AdminController/register')->with('error', '註冊階段已過期，請重新填寫註冊資料。');
        }

        $code = strtoupper(trim($this->request->getVar('code')));

        if (empty($code)) {
            return $this->renderAdminView('admin/verify_registration', [
                'email' => $pending['email'],
                'error' => '請輸入 6 位數英數驗證碼。'
            ]);
        }

        if (time() > $pending['expires_at']) {
            return $this->renderAdminView('admin/verify_registration', [
                'email' => $pending['email'],
                'error' => '驗證碼已過期，請點擊下方「重新發送驗證碼」。'
            ]);
        }

        if ($code !== strtoupper($pending['code'])) {
            return $this->renderAdminView('admin/verify_registration', [
                'email' => $pending['email'],
                'error' => '驗證碼錯誤，請重新確認信件中的 3 位英文與 3 位數字組合。'
            ]);
        }

        $model = new AdminModel();
        $model->save([
            'name' => $pending['name'],
            'username' => $pending['username'],
            'email' => $pending['email'],
            'password' => $pending['password'],
            'employee_id' => $pending['employee_id'],
        ]);

        session()->remove('admin_pending_register');

        return redirect()->to('/AdminController/login')->with('success', '管理員帳號註冊成功！請使用您的帳號與密碼登入。');
    }

    public function resendVerification()
    {
        $pending = session()->get('admin_pending_register');
        if (!$pending) {
            return redirect()->to('/AdminController/register')->with('error', '尚未填寫註冊資料，請重新註冊。');
        }

        $newCode = $this->generateAdminRegistrationCode();
        $pending['code'] = $newCode;
        $pending['expires_at'] = time() + 900;
        session()->set('admin_pending_register', $pending);

        $mailSent = $this->sendAdminRegistrationEmail($pending['email'], $pending['name'], $newCode);
        if (!$mailSent) {
            session()->setFlashdata('dev_admin_code', $newCode);
        }

        return redirect()->to('/AdminController/verifyRegistration')->with('success', '新的驗證碼已寄出至 ' . esc($pending['email']) . '，請前往信箱查收。');
    }

    public function doLogin()
    {
        if (!$this->validateAdminCsrf()) {
            return $this->csrfFailure('/AdminController/login');
        }

        $username = $this->request->getVar('username');
        $password = $this->request->getVar('password');

        $model = new AdminModel();
        $admin = $model->findByUsername($username);

        if ($admin && password_verify($password, $admin['password'])) {
            session()->regenerate();
            session()->set([
                'admin_logged_in' => true,
                'admin_id' => $admin['admin_id'],
                'admin_role' => $admin['role'] ?? AdminModel::ROLE_ADMIN,
                'admin_last_activity' => time(),
            ]);

            AuditLogModel::log(
                '系統認證',
                "管理員 [{$admin['username']}] 登入系統",
                '成功',
                '身分角色：' . (AdminModel::roleLabels()[$admin['role'] ?? 'admin'] ?? ($admin['role'] ?? 'admin')),
                (int) $admin['admin_id'],
                $admin['name'] ?: $admin['username']
            );

            return redirect()->to('/AdminController');
        }

        AuditLogModel::log(
            '系統認證',
            "嘗試以帳號 [{$username}] 登入失敗：帳號或密碼錯誤",
            '失敗'
        );

        return redirect()->to('/AdminController/login')->with('error', '帳號或密碼錯誤。');
    }

    public function profile()
    {
        if (!$this->requireAdminLogin()) {
            return redirect()->to('/AdminController/login');
        }

        $admin = (new AdminModel())
            ->select('admin_id, name, username, email, employee_id, role')
            ->find(session()->get('admin_id'));

        if (!$admin) {
            session()->destroy();
            return redirect()->to('/AdminController/login')->with('error', '找不到目前的管理員帳號。');
        }

        return $this->renderAdminView('admin/profile', ['admin' => $admin]);
    }

    public function updateProfile()
    {
        if (!$this->requireAdminLogin()) {
            return redirect()->to('/AdminController/login');
        }

        if (!$this->validateAdminCsrf()) {
            return $this->csrfFailure('/AdminController/profile');
        }

        $adminModel = new AdminModel();
        $admin = $adminModel->find(session()->get('admin_id'));

        if (!$admin) {
            session()->destroy();
            return redirect()->to('/AdminController/login')->with('error', '找不到目前的管理員帳號。');
        }

        $name = trim((string) $this->request->getPost('name'));
        $email = trim((string) $this->request->getPost('email'));
        $password = (string) $this->request->getPost('password');
        $passwordConfirm = (string) $this->request->getPost('password_confirm');
        $error = null;

        if ($name === '' || $email === '') {
            $error = '姓名與 Email 為必填項目。';
        } elseif (mb_strlen($name) > 50) {
            $error = '姓名不可超過 50 個字元。';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = '請輸入正確的 Email 格式。';
        } elseif (
            $adminModel
                ->where('email', $email)
                ->where('admin_id !=', $admin['admin_id'])
                ->first() !== null
        ) {
            $error = '此 Email 已被其他管理員使用。';
        } elseif ($password !== '') {
            if (strlen($password) < 6) {
                $error = '新密碼長度至少需為 6 個字元。';
            } elseif ($password !== $passwordConfirm) {
                $error = '兩次輸入的新密碼不一致。';
            }
        }

        if ($error !== null) {
            return redirect()->to('/AdminController/profile')->with('error', $error);
        }

        $updateData = [
            'name' => $name,
            'email' => $email,
        ];

        if ($password !== '') {
            $updateData['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $adminModel->update($admin['admin_id'], $updateData);

        return redirect()->to('/AdminController/profile')->with('success', '個人資料已成功更新。');
    }

    public function forgotPassword()
    {
        return $this->renderAdminView('admin/forgot_password');
    }

    public function sendResetLink()
    {
        if (!$this->validateAdminCsrf()) {
            return $this->csrfFailure('/AdminController/forgotPassword');
        }

        $name = trim($this->request->getVar('name'));
        $email = trim($this->request->getVar('email'));
        $employeeId = trim($this->request->getVar('employee_id'));

        if (empty($name) || empty($email) || empty($employeeId)) {
            return $this->renderAdminView('admin/forgot_password', [
                'error' => '請輸入姓名、電子郵件與員工證號。',
                'old' => ['name' => $name, 'email' => $email, 'employee_id' => $employeeId]
            ]);
        }

        if (!preg_match('/^admin\d{2}$/', $employeeId)) {
            return $this->renderAdminView('admin/forgot_password', [
                'error' => '員工證錯誤。必須是 admin01 或 admin 後面接兩個數字。',
                'old' => ['name' => $name, 'email' => $email, 'employee_id' => $employeeId]
            ]);
        }

        $model = new AdminModel();
        $admin = $model->where('name', $name)
            ->where('email', $email)
            ->where('employee_id', $employeeId)
            ->first();

        if (!$admin) {
            return $this->renderAdminView('admin/forgot_password', [
                'error' => '驗證失敗：找不到此姓名、Email 與員工證號完全對應的管理員。',
                'old' => ['name' => $name, 'email' => $email, 'employee_id' => $employeeId]
            ]);
        }

        $code = $this->generateAdminRegistrationCode();
        $expiresAt = time() + 900;

        session()->set('admin_pending_reset', [
            'admin_id' => $admin['admin_id'],
            'email' => $email,
            'name' => $name,
            'code' => $code,
            'expires_at' => $expiresAt,
        ]);

        $subject = '【學生甄選與志願媒合系統】管理員安全驗證碼';
        $message = "您好 {$name}，\n\n" .
            "您請求了管理員身分驗證。\n" .
            "您的驗證碼為：{$code}\n\n" .
            "請在 15 分鐘內輸入此驗證碼以完成身分驗證並重設密碼。\n" .
            "若非您本人操作，請忽略此信件。";

        $resend = new \App\Libraries\ResendMailer();
        $mailSent = false;

        if ($resend->isConfigured()) {
            $mailSent = $resend->send($email, $subject, $message);
        }

        if (!$mailSent) {
            $emailService = \Config\Services::email();
            $emailConfig = config('Email');

            if (!empty($emailConfig->SMTPHost) || !empty($emailConfig->fromEmail)) {
                try {
                    $emailService->setTo($email);
                    $emailService->setSubject($subject);
                    $emailService->setMessage($message);
                    $mailSent = $emailService->send();
                } catch (\Exception $e) {
                    log_message('error', 'Reset password email sending failed: ' . $e->getMessage());
                }
            }
        }

        if (!$mailSent) {
            session()->remove('admin_pending_reset');
            return redirect()->to('/AdminController/forgotPassword')->with('error', '驗證碼寄送失敗，請確認郵件服務設定後再試。');
        }

        return redirect()->to('/AdminController/verifyResetCode')->with('success', '密碼重設驗證碼已成功寄出至 ' . esc($email) . '，請前往信箱查收。');
    }

    public function verifyResetCode()
    {
        $pending = session()->get('admin_pending_reset');
        if (!$pending) {
            return redirect()->to('/AdminController/forgotPassword')->with('error', '請先填寫帳號資訊以索取驗證碼。');
        }

        return $this->renderAdminView('admin/verify_reset_code', [
            'email' => $pending['email'],
        ]);
    }

    public function doVerifyResetCode()
    {
        if (!$this->validateAdminCsrf()) {
            return $this->csrfFailure('/AdminController/forgotPassword');
        }

        $pending = session()->get('admin_pending_reset');
        if (!$pending) {
            return redirect()->to('/AdminController/forgotPassword')->with('error', '重設驗證已過期，請重新申請。');
        }

        $code = strtoupper(trim($this->request->getVar('code')));

        if (empty($code)) {
            return $this->renderAdminView('admin/verify_reset_code', [
                'email' => $pending['email'],
                'error' => '請輸入驗證碼。'
            ]);
        }

        if (time() > $pending['expires_at']) {
            return $this->renderAdminView('admin/verify_reset_code', [
                'email' => $pending['email'],
                'error' => '驗證碼已過期，請點擊下方「重新發送驗證碼」。'
            ]);
        }

        if ($code !== strtoupper($pending['code'])) {
            return $this->renderAdminView('admin/verify_reset_code', [
                'email' => $pending['email'],
                'error' => '驗證碼錯誤，請重新確認信件中的驗證碼。'
            ]);
        }

        $pending['verified'] = true;
        session()->set('admin_pending_reset', $pending);

        return redirect()->to('/AdminController/resetPassword');
    }

    public function resendResetCode()
    {
        $pending = session()->get('admin_pending_reset');
        if (!$pending) {
            return redirect()->to('/AdminController/forgotPassword')->with('error', '請重新申請重設密碼。');
        }

        $previousPending = $pending;
        $newCode = $this->generateAdminRegistrationCode();
        $pending['code'] = $newCode;
        $pending['expires_at'] = time() + 900;
        session()->set('admin_pending_reset', $pending);

        $subject = '【學生甄選與志願媒合系統】管理員安全驗證碼';
        $message = "您好 {$pending['name']}，\n\n" .
            "您重新索取了管理員安全驗證碼。\n" .
            "您的新驗證碼為：{$newCode}\n\n" .
            "請在 15 分鐘內輸入此驗證碼以完成身分驗證並重設密碼。\n" .
            "若非您本人操作，請忽略此信件。";

        $resend = new \App\Libraries\ResendMailer();
        $mailSent = false;
        if ($resend->isConfigured()) {
            $mailSent = $resend->send($pending['email'], $subject, $message);
        }

        if (!$mailSent) {
            session()->set('admin_pending_reset', $previousPending);
            return redirect()->to('/AdminController/verifyResetCode')->with('error', '新的驗證碼寄送失敗，請稍後再試。');
        }

        return redirect()->to('/AdminController/verifyResetCode')->with('success', '新的重設驗證碼已寄出至 ' . esc($pending['email']) . '，請前往信箱查收。');
    }

    public function resetPassword()
    {
        $pending = session()->get('admin_pending_reset');
        if (!$pending || empty($pending['verified'])) {
            return redirect()->to('/AdminController/forgotPassword')->with('error', '請先完成信箱驗證碼驗證。');
        }

        return $this->renderAdminView('admin/reset_password');
    }

    public function doResetPassword()
    {
        if (!$this->validateAdminCsrf()) {
            return $this->csrfFailure('/AdminController/forgotPassword');
        }

        $pending = session()->get('admin_pending_reset');
        if (!$pending || empty($pending['verified'])) {
            return redirect()->to('/AdminController/forgotPassword')->with('error', '重設階段已失效，請重新申請。');
        }

        $password = $this->request->getVar('password');
        $passwordConfirm = $this->request->getVar('password_confirm');

        if (empty($password) || empty($passwordConfirm)) {
            return $this->renderAdminView('admin/reset_password', ['error' => '請輸入新密碼與確認密碼。']);
        }

        if (strlen($password) < 6) {
            return $this->renderAdminView('admin/reset_password', ['error' => '密碼長度至少需為 6 個字元。']);
        }

        if ($password !== $passwordConfirm) {
            return $this->renderAdminView('admin/reset_password', ['error' => '兩次輸入的密碼不一致。']);
        }

        $model = new AdminModel();
        $model->update($pending['admin_id'], [
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'reset_token' => null,
            'reset_expires_at' => null,
        ]);

        session()->remove('admin_pending_reset');

        return redirect()->to('/AdminController/login')->with('success', '密碼重設成功！請使用新密碼登入。');
    }

    public function logout()
    {
        $adminId = (int) session()->get('admin_id');
        if ($adminId > 0) {
            AuditLogModel::log('系統認證', '管理員登出系統');
        }
        session()->destroy();
        return redirect()->to('/AdminController/login');
    }

    public function manageAdmins()
    {
        if (!$this->requireRole(self::ROLE_SUPER_ADMIN)) {
            return $this->denyPermission('/AdminController', '只有超級管理員（super_admin）有權存取管理員帳號管理。');
        }

        $model = new AdminModel();
        $admins = $model->orderBy('admin_id', 'ASC')->findAll();

        return $this->renderAdminView('admin/manage_admins', [
            'admins' => $admins,
            'roles' => AdminModel::roleLabels(),
            'current_admin_id' => (int) session()->get('admin_id'),
            'super_admin_count' => $model->countSuperAdmins(),
        ]);
    }

    public function updateAdminRole($adminId)
    {
        if (!$this->requireRole(self::ROLE_SUPER_ADMIN)) {
            return $this->denyPermission('/AdminController', '只有超級管理員有權變更管理員角色。');
        }

        if (!$this->validateAdminCsrf()) {
            return $this->csrfFailure('/AdminController/admins');
        }

        $adminId = (int) $adminId;
        $model = new AdminModel();
        $target = $model->find($adminId);

        if (!$target) {
            return redirect()->to('/AdminController/admins')->with('error', '找不到該管理員帳號。');
        }

        $newRole = trim((string) $this->request->getPost('role'));
        if (!in_array($newRole, AdminModel::validRoles(), true)) {
            return redirect()->to('/AdminController/admins')->with('error', '請選擇有效的角色。');
        }

        if ($target['role'] === AdminModel::ROLE_SUPER_ADMIN && $newRole !== AdminModel::ROLE_SUPER_ADMIN) {
            if ($model->countSuperAdmins() <= 1) {
                return redirect()->to('/AdminController/admins')->with('error', '系統至少需保留一名超級管理員，無法將其降級。');
            }
        }

        $model->update($adminId, ['role' => $newRole]);

        if ($adminId === (int) session()->get('admin_id')) {
            session()->set('admin_role', $newRole);
        }

        $oldRoleLabel = AdminModel::roleLabels()[$target['role']] ?? $target['role'];
        $newRoleLabel = AdminModel::roleLabels()[$newRole] ?? $newRole;
        AuditLogModel::log(
            '角色權限',
            "變更管理員 [{$target['username']}]（{$target['name']}）角色",
            '成功',
            "原角色：{$oldRoleLabel} -> 新角色：{$newRoleLabel}"
        );

        return redirect()->to('/AdminController/admins')->with('success', "已成功更新管理員「{$target['name']}」的角色為「" . AdminModel::roleLabels()[$newRole] . '」。');
    }

    public function deleteAdmin($adminId)
    {
        if (!$this->requireRole(self::ROLE_SUPER_ADMIN)) {
            return $this->denyPermission('/AdminController', '只有超級管理員有權刪除管理員帳號。');
        }

        if (!$this->validateAdminCsrf()) {
            return $this->csrfFailure('/AdminController/admins');
        }

        $adminId = (int) $adminId;
        if ($adminId === (int) session()->get('admin_id')) {
            return redirect()->to('/AdminController/admins')->with('error', '超級管理員不可刪除自己的帳號。');
        }

        $model = new AdminModel();
        $target = $model->find($adminId);

        if (!$target) {
            return redirect()->to('/AdminController/admins')->with('error', '找不到該管理員帳號。');
        }

        if ($target['role'] === AdminModel::ROLE_SUPER_ADMIN && $model->countSuperAdmins() <= 1) {
            return redirect()->to('/AdminController/admins')->with('error', '系統最後一名超級管理員無法刪除。');
        }

        $model->delete($adminId);

        AuditLogModel::log(
            '管理員帳號',
            "刪除管理員帳號 [{$target['username']}]（姓名：{$target['name']}）",
            '成功'
        );

        return redirect()->to('/AdminController/admins')->with('success', "已成功刪除管理員「{$target['name']}」（{$target['username']}）。");
    }
}

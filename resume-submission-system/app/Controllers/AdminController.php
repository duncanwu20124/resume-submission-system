<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AdminModel;
use App\Models\UserModel;

class AdminController extends BaseController
{
    public function login()
    {
        return view('admin/login');
    }

    public function register()
    {
        return view('admin/register');
    }

    /**
     * 生成隨機 6 碼驗證碼：包含 3 個隨機英文字母 (A-Z) 與 3 個隨機數字 (0-9)，並隨機洗牌
     */
    private function generateAdminRegistrationCode(): string
    {
        $letters = 'ABCDEFGHJKLMNPQRSTUVWXYZ'; // 排除易混淆的 I, O
        $digits  = '23456789';               // 排除易混淆的 0, 1 或可用 0123456789
        // 使用完整標準 A-Z 與 0-9
        $lettersAll = 'ABCDEFGHJKLMNOPQRSTUVWXYZ';
        $digitsAll  = '0123456789';

        $codeChars = [];
        for ($i = 0; $i < 3; $i++) {
            $codeChars[] = $lettersAll[random_int(0, strlen($lettersAll) - 1)];
        }
        for ($i = 0; $i < 3; $i++) {
            $codeChars[] = $digitsAll[random_int(0, strlen($digitsAll) - 1)];
        }

        // 隨機洗牌打散位置
        shuffle($codeChars);
        return implode('', $codeChars);
    }

    /**
     * 發送管理員註冊驗證碼與安全提醒郵件
     */
    private function sendAdminRegistrationEmail(string $email, string $name, string $code): bool
    {
        $subject = '【甄選行政系統】管理員帳號註冊驗證碼與安全提醒通知';
        $message = "您好 {$name}，\n\n" .
                   "系統偵測到剛才有人嘗試使用您的資料與此 Email（{$email}）進行管理員帳號註冊。\n\n" .
                   "若這是您本人的操作，您的註冊驗證碼為：\n" .
                   "------------------------\n" .
                   "【 {$code} 】\n" .
                   "------------------------\n" .
                   "（此驗證碼由 3 位英文字母與 3 位數字組合而成，有效時間為 15 分鐘）\n\n" .
                   "請在註冊頁面中輸入此驗證碼以完成管理員帳號建立。\n\n" .
                   "⚠️ 安全提醒：若非您本人操作，代表有人嘗試使用您的資訊進行註冊，請提高警覺並留意帳號安全，您可以直接忽略此郵件。";

        // 優先嘗試使用 Resend 郵件服務
        $resend = new \App\Libraries\ResendMailer();
        if ($resend->isConfigured()) {
            $sent = $resend->send($email, $subject, $message);
            if ($sent) {
                return true;
            }
        }

        // 次之嘗試 CodeIgniter 內建 Email 服務
        $emailService = \Config\Services::email();
        $emailConfig  = config('Email');
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
        $name            = trim($this->request->getVar('name'));
        $username        = trim($this->request->getVar('username'));
        $email           = trim($this->request->getVar('email'));
        $password        = $this->request->getVar('password');
        $passwordConfirm = $this->request->getVar('password_confirm');
        $employeeId      = trim($this->request->getVar('employee_id'));

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
            // 限制：必須是 admin01 或 admin 後面接兩位數字
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
            return view('admin/register', ['error' => $error, 'old' => $this->request->getPost()]);
        }

        // 生成 3 位英文 + 3 位數字隨機驗證碼
        $verificationCode = $this->generateAdminRegistrationCode();
        $expiresAt = time() + 900; // 15 分鐘

        // 存入 session
        session()->set('admin_pending_register', [
            'name'        => $name,
            'username'    => $username,
            'email'       => $email,
            'password'    => password_hash($password, PASSWORD_DEFAULT),
            'employee_id' => $employeeId,
            'code'        => $verificationCode,
            'expires_at'  => $expiresAt,
        ]);

        // 發送 Gmail / Email 通知與安全提醒
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

        return view('admin/verify_registration', [
            'email' => $pending['email'],
        ]);
    }

    public function doVerifyRegistration()
    {
        $pending = session()->get('admin_pending_register');
        if (!$pending) {
            return redirect()->to('/AdminController/register')->with('error', '註冊階段已過期，請重新填寫註冊資料。');
        }

        $code = strtoupper(trim($this->request->getVar('code')));

        if (empty($code)) {
            return view('admin/verify_registration', [
                'email' => $pending['email'],
                'error' => '請輸入 6 位數英數驗證碼。'
            ]);
        }

        if (time() > $pending['expires_at']) {
            return view('admin/verify_registration', [
                'email' => $pending['email'],
                'error' => '驗證碼已過期，請點擊下方「重新發送驗證碼」。'
            ]);
        }

        if ($code !== strtoupper($pending['code'])) {
            return view('admin/verify_registration', [
                'email' => $pending['email'],
                'error' => '驗證碼錯誤，請重新確認信件中的 3 位英文與 3 位數字組合。'
            ]);
        }

        // 驗證通過，寫入資料庫
        $model = new AdminModel();
        $model->save([
            'name'        => $pending['name'],
            'username'    => $pending['username'],
            'email'       => $pending['email'],
            'password'    => $pending['password'],
            'employee_id' => $pending['employee_id'],
        ]);

        // 清除暫存 session
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
        $pending['expires_at'] = time() + 900; // 重新設定 15 分鐘
        session()->set('admin_pending_register', $pending);

        $mailSent = $this->sendAdminRegistrationEmail($pending['email'], $pending['name'], $newCode);
        if (!$mailSent) {
            session()->setFlashdata('dev_admin_code', $newCode);
        }

        return redirect()->to('/AdminController/verifyRegistration')->with('success', '新的驗證碼已寄出至 ' . esc($pending['email']) . '，請前往信箱查收。');
    }

    public function doLogin()
    {
        $username = $this->request->getVar('username');
        $password = $this->request->getVar('password');

        $model = new AdminModel();
        $admin = $model->findByUsername($username);

        if ($admin && password_verify($password, $admin['password'])) {
            session()->set('admin_logged_in', true);
            session()->set('admin_id', $admin['admin_id']);
            return redirect()->to('/AdminController');
        }

        return view('admin/login', ['error' => '帳號或密碼錯誤']);
    }

    public function index()
    {
        if (!session()->get('admin_logged_in')) {
            return redirect()->to('/AdminController/login');
        }

        $model = new UserModel();
        $users = $model->findAll();

        return view('admin/index', ['users' => $users, 'selected_user' => null]);
    }

    public function search()
    {
        if (!session()->get('admin_logged_in')) {
            return redirect()->to('/AdminController/login');
        }

        $keyword = $this->request->getVar('keyword');
        $searchBy = $this->request->getVar('search_by');
        $model = new UserModel();

        if ($searchBy === 'id') {
            $users = $model->searchByUserId($keyword);
        } elseif ($searchBy === 'email') {
            $users = $model->searchByEmail($keyword);
        } else {
            $users = $model->searchByName($keyword);
        }

        return view('admin/index', [
            'users' => $users, 
            'selected_user' => null, 
            'keyword' => $keyword,
            'search_by' => $searchBy
        ]);
    }

    public function show($user_id)
    {
        if (!session()->get('admin_logged_in')) {
            return redirect()->to('/AdminController/login');
        }

        $userModel = new UserModel();
        $selected  = $userModel->find($user_id);

        if (!$selected) {
            echo '找不到指定使用者';
            return;
        }

        return view('admin/show', ['user' => $selected]);
    }

    public function viewFile($user_id)
    {
        if (!session()->get('admin_logged_in')) {
            return redirect()->to('/AdminController/login');
        }

        $userModel = new UserModel();
        $user = $userModel->find($user_id);

        if (!$user || empty($user['file_name'])) {
            echo '找不到指定檔案';
            return;
        }

        $content = null;
        if (!empty($user['file_content'])) {
            $content = base64_decode($user['file_content']);
        } else {
            $filePath = WRITEPATH . 'uploads/' . $user['file_name'];
            if (file_exists($filePath) && is_file($filePath)) {
                $content = file_get_contents($filePath);
            }
        }

        if ($content === null) {
            echo '找不到指定檔案';
            return;
        }

        $ext = strtolower(pathinfo($user['file_name'], PATHINFO_EXTENSION));
        $mimeType = match ($ext) {
            'pdf'   => 'application/pdf',
            'doc'   => 'application/msword',
            'docx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            default => 'application/octet-stream',
        };

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Disposition', 'inline; filename="' . $user['file_name'] . '"')
            ->setBody($content);
    }

    public function download($user_id)
    {
        if (!session()->get('admin_logged_in')) {
            return redirect()->to('/AdminController/login');
        }

        $userModel = new UserModel();
        $user = $userModel->find($user_id);

        if (!$user || empty($user['file_name'])) {
            echo '找不到指定檔案';
            return;
        }

        if (!empty($user['file_content'])) {
            return $this->response->download($user['file_name'], base64_decode($user['file_content']));
        }

        $filePath = WRITEPATH . 'uploads/' . $user['file_name'];
        if (file_exists($filePath) && is_file($filePath)) {
            return $this->response->download($filePath, null)->setFileName($user['file_name']);
        }

        echo '找不到指定檔案';
        return;
    }

    public function forgotPassword()
    {
        return view('admin/forgot_password');
    }

    public function sendResetLink()
    {
        $name       = trim($this->request->getVar('name'));
        $email      = trim($this->request->getVar('email'));
        $employeeId = trim($this->request->getVar('employee_id'));

        if (empty($name) || empty($email) || empty($employeeId)) {
            return view('admin/forgot_password', [
                'error' => '請輸入姓名、電子郵件與員工證號。',
                'old'   => ['name' => $name, 'email' => $email, 'employee_id' => $employeeId]
            ]);
        }

        if (!preg_match('/^admin\d{2}$/', $employeeId)) {
            return view('admin/forgot_password', [
                'error' => '員工證錯誤。必須是 admin01 或 admin 後面接兩個數字。',
                'old'   => ['name' => $name, 'email' => $email, 'employee_id' => $employeeId]
            ]);
        }

        $model = new AdminModel();
        $admin = $model->where('name', $name)
                       ->where('email', $email)
                       ->where('employee_id', $employeeId)
                       ->first();

        if (!$admin) {
            return view('admin/forgot_password', [
                'error' => '驗證失敗：找不到此姓名、Email 與員工證號完全對應的管理員。',
                'old'   => ['name' => $name, 'email' => $email, 'employee_id' => $employeeId]
            ]);
        }

        // 生成 3 位英文 + 3 位數字隨機驗證碼 (純文字代碼，不含任何外部超連結，大幅降低垃圾信判定)
        $code = $this->generateAdminRegistrationCode();
        $expiresAt = time() + 900; // 15 分鐘

        // 存入 Session
        session()->set('admin_pending_reset', [
            'admin_id'   => $admin['admin_id'],
            'email'      => $email,
            'name'       => $name,
            'code'       => $code,
            'expires_at' => $expiresAt,
        ]);

        $subject = '【甄選行政系統】管理員安全驗證碼';
        $message = "您好 {$name}，\n\n" .
                   "您請求了管理員身分驗證。\n" .
                   "您的驗證碼為：{$code}\n\n" .
                   "請在 15 分鐘內輸入此驗證碼以完成身分驗證並重設密碼。\n" .
                   "若非您本人操作，請忽略此信件。";

        // 發送真實郵件
        $resend = new \App\Libraries\ResendMailer();
        $mailSent = false;

        if ($resend->isConfigured()) {
            $mailSent = $resend->send($email, $subject, $message);
        }

        if (!$mailSent) {
            $emailService = \Config\Services::email();
            $emailConfig  = config('Email');

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

        return redirect()->to('/AdminController/verifyResetCode')->with('success', '密碼重設驗證碼已成功寄出至 ' . esc($email) . '，請前往信箱查收。');
    }

    public function verifyResetCode()
    {
        $pending = session()->get('admin_pending_reset');
        if (!$pending) {
            return redirect()->to('/AdminController/forgotPassword')->with('error', '請先填寫帳號資訊以索取驗證碼。');
        }

        return view('admin/verify_reset_code', [
            'email' => $pending['email'],
        ]);
    }

    public function doVerifyResetCode()
    {
        $pending = session()->get('admin_pending_reset');
        if (!$pending) {
            return redirect()->to('/AdminController/forgotPassword')->with('error', '重設驗證已過期，請重新申請。');
        }

        $code = strtoupper(trim($this->request->getVar('code')));

        if (empty($code)) {
            return view('admin/verify_reset_code', [
                'email' => $pending['email'],
                'error' => '請輸入驗證碼。'
            ]);
        }

        if (time() > $pending['expires_at']) {
            return view('admin/verify_reset_code', [
                'email' => $pending['email'],
                'error' => '驗證碼已過期，請點擊下方「重新發送驗證碼」。'
            ]);
        }

        if ($code !== strtoupper($pending['code'])) {
            return view('admin/verify_reset_code', [
                'email' => $pending['email'],
                'error' => '驗證碼錯誤，請重新確認信件中的驗證碼。'
            ]);
        }

        // 驗證碼正確，標記通過並跳轉至設定新密碼頁
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

        $newCode = $this->generateAdminRegistrationCode();
        $pending['code'] = $newCode;
        $pending['expires_at'] = time() + 900;
        session()->set('admin_pending_reset', $pending);

        $subject = '【甄選行政系統】管理員安全驗證碼';
        $message = "您好 {$pending['name']}，\n\n" .
                   "您重新索取了管理員安全驗證碼。\n" .
                   "您的新驗證碼為：{$newCode}\n\n" .
                   "請在 15 分鐘內輸入此驗證碼以完成身分驗證並重設密碼。\n" .
                   "若非您本人操作，請忽略此信件。";

        $resend = new \App\Libraries\ResendMailer();
        if ($resend->isConfigured()) {
            $resend->send($pending['email'], $subject, $message);
        }

        return redirect()->to('/AdminController/verifyResetCode')->with('success', '新的重設驗證碼已寄出至 ' . esc($pending['email']) . '，請前往信箱查收。');
    }

    public function resetPassword()
    {
        $pending = session()->get('admin_pending_reset');
        if (!$pending || empty($pending['verified'])) {
            return redirect()->to('/AdminController/forgotPassword')->with('error', '請先完成信箱驗證碼驗證。');
        }

        return view('admin/reset_password');
    }

    public function doResetPassword()
    {
        $pending = session()->get('admin_pending_reset');
        if (!$pending || empty($pending['verified'])) {
            return redirect()->to('/AdminController/forgotPassword')->with('error', '重設階段已失效，請重新申請。');
        }

        $password        = $this->request->getVar('password');
        $passwordConfirm = $this->request->getVar('password_confirm');

        if (empty($password) || empty($passwordConfirm)) {
            return view('admin/reset_password', ['error' => '請輸入新密碼與確認密碼。']);
        }

        if (strlen($password) < 6) {
            return view('admin/reset_password', ['error' => '密碼長度至少需為 6 個字元。']);
        }

        if ($password !== $passwordConfirm) {
            return view('admin/reset_password', ['error' => '兩次輸入的密碼不一致。']);
        }

        // 更新密碼並清除 Session
        $model = new AdminModel();
        $model->update($pending['admin_id'], [
            'password'         => password_hash($password, PASSWORD_DEFAULT),
            'reset_token'      => null,
            'reset_expires_at' => null,
        ]);

        session()->remove('admin_pending_reset');

        return redirect()->to('/AdminController/login')->with('success', '密碼重設成功！請使用新密碼登入。');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/AdminController/login');
    }
}

<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AdminModel;
use App\Models\AnnouncementModel;
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

    public function doRegister()
    {
        $username        = trim($this->request->getVar('username'));
        $email           = trim($this->request->getVar('email'));
        $password        = $this->request->getVar('password');
        $passwordConfirm = $this->request->getVar('password_confirm');
        $employeeId      = trim($this->request->getVar('employee_id'));

        $error = null;

        if (empty($username) || empty($email) || empty($password) || empty($passwordConfirm) || empty($employeeId)) {
            $error = '所有欄位皆為必填項目。';
        } elseif ($password !== $passwordConfirm) {
            $error = '兩次輸入的密碼不一致。';
        } elseif (strlen($password) < 6) {
            $error = '密碼長度至少需為 6 個字元。';
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

        $model = new AdminModel();
        $model->save([
            'username'    => $username,
            'email'       => $email,
            'password'    => password_hash($password, PASSWORD_DEFAULT),
            'employee_id' => $employeeId,
        ]);

        return redirect()->to('/AdminController/login')->with('success', '註冊成功，請登入。');
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
        $email      = trim($this->request->getVar('email'));
        $employeeId = trim($this->request->getVar('employee_id'));

        if (empty($email) || empty($employeeId)) {
            return view('admin/forgot_password', [
                'error' => '請輸入電子郵件與員工證號。',
                'old'   => ['email' => $email, 'employee_id' => $employeeId]
            ]);
        }

        if (!preg_match('/^admin\d{2}$/', $employeeId)) {
            return view('admin/forgot_password', [
                'error' => '員工證錯誤。必須是 admin01 或 admin 後面接兩個數字。',
                'old'   => ['email' => $email, 'employee_id' => $employeeId]
            ]);
        }

        $model = new AdminModel();
        $admin = $model->where('email', $email)
                       ->where('employee_id', $employeeId)
                       ->first();

        if (!$admin) {
            return view('admin/forgot_password', [
                'error' => '驗證失敗：找不到此 Email 與員工證號對應的管理員。',
                'old'   => ['email' => $email, 'employee_id' => $employeeId]
            ]);
        }

        // 生成唯一 token (30 分鐘有效)
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 1800);

        $model->update($admin['admin_id'], [
            'reset_token'      => $token,
            'reset_expires_at' => $expiresAt,
        ]);

        $resetUrl = site_url('AdminController/resetPassword?token=' . $token);

        // 嘗試使用 CodeIgniter Email 服務發送信件
        $emailService = \Config\Services::email();
        $emailConfig = config('Email');

        $mailSent = false;
        // 如果 SMTPHost 已設定則嘗試發送真實郵件
        if (!empty($emailConfig->SMTPHost) || !empty($emailConfig->fromEmail)) {
            try {
                $emailService->setTo($email);
                $emailService->setSubject('【甄選行政系統】管理員密碼重設通知');
                $emailService->setMessage(
                    "您好，\n\n您最近請求了重設管理員帳號密碼。\n請點擊以下連結進行重設（30分鐘內有效）：\n\n" .
                    $resetUrl . "\n\n若非您本人操作，請忽略此郵件。"
                );
                $mailSent = $emailService->send();
            } catch (\Exception $e) {
                log_message('error', 'Email reset sending failed: ' . $e->getMessage());
            }
        }

        if ($mailSent) {
            return redirect()->to('/AdminController/forgotPassword')->with('success', '密碼重設信件已成功寄出至 ' . esc($email) . '，請前往收信。');
        } else {
            // 本地測試或尚未設定 SMTP 時，提供提示並帶有模擬連結
            session()->setFlashdata('dev_reset_link', $resetUrl);
            return redirect()->to('/AdminController/forgotPassword')->with('success', '重設驗證已生成！（若已設定 SMTP 系統將直接寄至 Gmail；本地開發可直接點擊下方模擬連結）');
        }
    }

    public function resetPassword()
    {
        $token = $this->request->getVar('token');

        if (empty($token)) {
            return redirect()->to('/AdminController/login')->with('error', '無效的重設連結。');
        }

        $model = new AdminModel();
        $admin = $model->where('reset_token', $token)
                       ->where('reset_expires_at >=', date('Y-m-d H:i:s'))
                       ->first();

        if (!$admin) {
            return redirect()->to('/AdminController/forgotPassword')->with('error', '密碼重設連結已失效或過期，請重新申請。');
        }

        return view('admin/reset_password', ['token' => $token]);
    }

    public function doResetPassword()
    {
        $token           = $this->request->getVar('token');
        $password        = $this->request->getVar('password');
        $passwordConfirm = $this->request->getVar('password_confirm');

        if (empty($token)) {
            return redirect()->to('/AdminController/login')->with('error', '無效的請求。');
        }

        $model = new AdminModel();
        $admin = $model->where('reset_token', $token)
                       ->where('reset_expires_at >=', date('Y-m-d H:i:s'))
                       ->first();

        if (!$admin) {
            return redirect()->to('/AdminController/forgotPassword')->with('error', '密碼重設連結已失效或過期，請重新申請。');
        }

        if (empty($password) || empty($passwordConfirm)) {
            return view('admin/reset_password', ['token' => $token, 'error' => '請輸入新密碼與確認密碼。']);
        }

        if (strlen($password) < 6) {
            return view('admin/reset_password', ['token' => $token, 'error' => '密碼長度至少需為 6 個字元。']);
        }

        if ($password !== $passwordConfirm) {
            return view('admin/reset_password', ['token' => $token, 'error' => '兩次輸入的密碼不一致。']);
        }

        // 更新密碼並清除 token
        $model->update($admin['admin_id'], [
            'password'         => password_hash($password, PASSWORD_DEFAULT),
            'reset_token'      => null,
            'reset_expires_at' => null,
        ]);

        return redirect()->to('/AdminController/login')->with('success', '密碼重設成功，請使用新密碼登入。');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/AdminController/login');
    }

    public function announcements()
    {
        if (!session()->get('admin_logged_in')) {
            return redirect()->to('/AdminController/login');
        }

        $model = new AnnouncementModel();
        $announcements = $model->orderBy('created_at', 'DESC')->findAll();

        return view('admin/announcements', ['announcements' => $announcements]);
    }

    public function createAnnouncement()
    {
        if (!session()->get('admin_logged_in')) {
            return redirect()->to('/AdminController/login');
        }

        $title       = trim((string) $this->request->getVar('title'));
        $content     = trim((string) $this->request->getVar('content'));
        $displayType = $this->request->getVar('display_type');

        if (!in_array($displayType, ['list', 'marquee'], true)) {
            $displayType = 'list';
        }

        if (empty($title) || empty($content)) {
            return redirect()->to('/AdminController/announcements')->with('error', '標題與內容皆為必填項目。');
        }

        $model = new AnnouncementModel();
        $model->save([
            'title'        => $title,
            'content'      => $content,
            'display_type' => $displayType,
            'is_active'    => 1,
            'admin_id'     => session()->get('admin_id'),
        ]);

        return redirect()->to('/AdminController/announcements')->with('success', '公告已成功發布。');
    }

    public function toggleAnnouncement($id)
    {
        if (!session()->get('admin_logged_in')) {
            return redirect()->to('/AdminController/login');
        }

        $model = new AnnouncementModel();
        $announcement = $model->find($id);

        if ($announcement) {
            $model->update($id, ['is_active' => $announcement['is_active'] ? 0 : 1]);
        }

        return redirect()->to('/AdminController/announcements')->with('success', '公告狀態已更新。');
    }

    public function deleteAnnouncement($id)
    {
        if (!session()->get('admin_logged_in')) {
            return redirect()->to('/AdminController/login');
        }

        $model = new AnnouncementModel();
        $model->delete($id);

        return redirect()->to('/AdminController/announcements')->with('success', '公告已刪除。');
    }
}

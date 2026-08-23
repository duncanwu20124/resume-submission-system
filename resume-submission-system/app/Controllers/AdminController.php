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

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/AdminController/login');
    }
}

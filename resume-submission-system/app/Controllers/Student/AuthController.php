<?php

namespace App\Controllers\Student;

use App\Controllers\BaseController;
use App\Models\StudentModel;

class AuthController extends BaseController
{
    public function login()
    {
        if (session()->get('is_student_logged_in')) {
            return redirect()->to('/student/dashboard');
        }

        return view('student/login');
    }

    public function processLogin()
    {
        $rules = [
            'student_id' => [
                'rules'  => 'required',
                'errors' => [
                    'required' => '請輸入學號。',
                ],
            ],
            'password' => [
                'rules'  => 'required',
                'errors' => [
                    'required' => '請輸入密碼。',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return view('student/login', [
                'validation' => $this->validator,
            ]);
        }

        $studentId = trim($this->request->getPost('student_id'));
        $password  = $this->request->getPost('password');

        $studentModel = new StudentModel();
        $student      = $studentModel->where('student_id', $studentId)->first();

        if ($student && password_verify($password, $student['password'])) {
            session()->set([
                'student_db_id'        => $student['id'],
                'student_id'           => $student['student_id'],
                'student_name'         => $student['name'],
                'student_email'        => $student['email'],
                'is_student_logged_in' => true,
            ]);

            return redirect()->to('/student/dashboard')->with('success', '登入成功！歡迎回來，' . esc($student['name']) . '。');
        }

        return redirect()->back()->withInput()->with('error', '學號或密碼錯誤，請重新嘗試。');
    }

    public function register()
    {
        if (session()->get('is_student_logged_in')) {
            return redirect()->to('/student/dashboard');
        }

        return view('student/register');
    }

    public function processRegister()
    {
        $rules = [
            'student_id' => [
                'rules'  => 'required|min_length[3]|max_length[50]|is_unique[students.student_id]',
                'errors' => [
                    'required'   => '請輸入學號。',
                    'min_length' => '學號長度至少需為 3 個字元。',
                    'max_length' => '學號長度不能超過 50 個字元。',
                    'is_unique'  => '此學號已經被註冊過。',
                ],
            ],
            'name' => [
                'rules'  => 'required|min_length[2]|max_length[100]',
                'errors' => [
                    'required'   => '請輸入姓名。',
                    'min_length' => '姓名長度至少需為 2 個字元。',
                    'max_length' => '姓名長度不能超過 100 個字元。',
                ],
            ],
            'email' => [
                'rules'  => 'required|valid_email|is_unique[students.email]',
                'errors' => [
                    'required'    => '請輸入電子郵件。',
                    'valid_email' => '請輸入有效的電子郵件格式。',
                    'is_unique'   => '此電子郵件已經被註冊過。',
                ],
            ],
            'password' => [
                'rules'  => 'required|min_length[6]',
                'errors' => [
                    'required'   => '請設定密碼。',
                    'min_length' => '密碼長度至少需為 6 個字元。',
                ],
            ],
            'password_confirm' => [
                'rules'  => 'required|matches[password]',
                'errors' => [
                    'required' => '請再次輸入密碼以進行確認。',
                    'matches'  => '兩次輸入的密碼不一致。',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return view('student/register', [
                'validation' => $this->validator,
            ]);
        }

        $studentModel = new StudentModel();
        $studentModel->insert([
            'student_id' => trim($this->request->getPost('student_id')),
            'name'       => trim($this->request->getPost('name')),
            'email'      => trim($this->request->getPost('email')),
            'password'   => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
        ]);

        return redirect()->to('/student/login')->with('success', '帳號建立成功！請使用您的學號與密碼進行登入。');
    }

    public function logout()
    {
        session()->remove(['student_db_id', 'student_id', 'student_name', 'student_email', 'is_student_logged_in']);
        return redirect()->to('/student/login')->with('success', '您已成功登出。');
    }
}

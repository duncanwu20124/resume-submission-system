<?php

namespace App\Controllers\Student;

use App\Controllers\BaseController;
use App\Libraries\ResendMailer;
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

    public function forgotPassword()
    {
        return view('student/forgot_password');
    }

    public function sendResetCode()
    {
        $email = trim($this->request->getPost('email'));

        if (empty($email)) {
            return view('student/forgot_password', ['error' => '請輸入您註冊的電子郵件。']);
        }

        $studentModel = new StudentModel();
        $student = $studentModel->where('email', $email)->first();

        if (!$student) {
            return view('student/forgot_password', ['error' => '找不到該電子郵件對應的學生帳號。']);
        }

        // Generate 6-digit code (valid for 15 minutes)
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', time() + 900);

        $studentModel->update($student['id'], [
            'reset_code'       => $code,
            'reset_expires_at' => $expiresAt,
        ]);

        // Send Email via Resend
        $resend = new ResendMailer();
        $mailSent = false;

        if ($resend->isConfigured()) {
            $mailSent = $resend->send(
                $email,
                '【學生履歷系統】密碼重設驗證碼',
                "您好 " . $student['name'] . "，\n\n" .
                "您請求了重設學生帳號密碼。\n您的驗證碼為： " . $code . "\n\n" .
                "請在 15 分鐘內輸入此驗證碼以重設您的密碼。\n若非您本人操作，請忽略此信件。"
            );
        }

        session()->set('reset_email', $email);

        if ($mailSent) {
            return redirect()->to('/student/verify-code')->with('success', '驗證碼已發送至您的信箱，請查收並輸入。');
        } else {
            // Local dev fallback
            session()->setFlashdata('dev_reset_code', $code);
            return redirect()->to('/student/verify-code')->with('success', '【開發模式】驗證碼已生成，若未配置信件伺服器可直接使用下方測試驗證碼。');
        }
    }

    public function verifyCode()
    {
        if (!session()->get('reset_email')) {
            return redirect()->to('/student/forgot-password');
        }
        return view('student/verify_code');
    }

    public function processVerifyCode()
    {
        $code = trim($this->request->getPost('code'));
        $email = session()->get('reset_email');

        if (empty($code) || empty($email)) {
            return view('student/verify_code', ['error' => '請輸入驗證碼。']);
        }

        $studentModel = new StudentModel();
        $student = $studentModel->where('email', $email)
                                ->where('reset_code', $code)
                                ->where('reset_expires_at >=', date('Y-m-d H:i:s'))
                                ->first();

        if (!$student) {
            return view('student/verify_code', ['error' => '驗證碼無效或已過期，請重新確認。']);
        }

        // Code verified, save token to session to allow reset
        session()->set('reset_verified', true);
        return redirect()->to('/student/reset-password');
    }

    public function resetPassword()
    {
        if (!session()->get('reset_verified') || !session()->get('reset_email')) {
            return redirect()->to('/student/forgot-password');
        }
        return view('student/reset_password');
    }

    public function processResetPassword()
    {
        if (!session()->get('reset_verified') || !session()->get('reset_email')) {
            return redirect()->to('/student/forgot-password');
        }

        $rules = [
            'password' => [
                'rules'  => 'required|min_length[6]',
                'errors' => [
                    'required'   => '請輸入新密碼。',
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
            return view('student/reset_password', [
                'validation' => $this->validator,
            ]);
        }

        $email = session()->get('reset_email');
        $password = $this->request->getPost('password');

        $studentModel = new StudentModel();
        $student = $studentModel->where('email', $email)->first();

        if ($student) {
            $studentModel->update($student['id'], [
                'password'         => password_hash($password, PASSWORD_DEFAULT),
                'reset_code'       => null,
                'reset_expires_at' => null,
            ]);
        }

        // Clear session reset states
        session()->remove(['reset_email', 'reset_verified']);

        return redirect()->to('/student/login')->with('success', '密碼重設成功！請使用新密碼重新登入。');
    }

    public function logout()
    {
        session()->remove(['student_db_id', 'student_id', 'student_name', 'student_email', 'is_student_logged_in']);
        return redirect()->to('/student/login')->with('success', '您已成功登出。');
    }
}

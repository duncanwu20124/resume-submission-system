<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AdminModel;
use App\Models\FormalApplicationModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class AdminController extends BaseController
{
    private const ADMIN_IDLE_TIMEOUT = 300;
    private const ADMIN_IDLE_GRACE_PERIOD = 30;
    private const ADMIN_CSRF_COOKIE_NAME = 'admin_csrf_token';

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->applyAdminSecurityHeaders();
    }

    private function applyAdminSecurityHeaders(): void
    {
        $this->response
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('X-Frame-Options', 'SAMEORIGIN')
            ->setHeader('Referrer-Policy', 'same-origin')
            ->setHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()')
            ->setHeader('Content-Security-Policy', "default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'; object-src 'none'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self' data:; connect-src 'self'; frame-src 'self'");
    }

    private function validateAdminCsrf(): bool
    {
        $cookieToken = (string) $this->request->getCookie(self::ADMIN_CSRF_COOKIE_NAME);
        $submittedToken = (string) $this->request->getPost('_admin_csrf');

        return preg_match('/^[a-f0-9]{64}$/', $cookieToken) === 1
            && $submittedToken !== ''
            && hash_equals($cookieToken, $submittedToken);
    }

    private function csrfFailure(string $redirect): \CodeIgniter\HTTP\RedirectResponse
    {
        return redirect()->to($redirect)->with('error', '表單驗證已失效，請重新整理頁面後再試。');
    }

    private function renderAdminView(string $view, array $data = []): string
    {
        return view($view, $data);
    }

    private function requireAdminLogin(): bool
    {
        $session = session();

        if (!$session->get('admin_logged_in')) {
            return false;
        }

        $lastActivity = (int) $session->get('admin_last_activity');
        if ($lastActivity > 0 && time() - $lastActivity > self::ADMIN_IDLE_TIMEOUT + self::ADMIN_IDLE_GRACE_PERIOD) {
            $session->remove(['admin_logged_in', 'admin_id', 'admin_last_activity']);
            $session->setFlashdata('error', '管理員登入已逾時，請重新登入。');
            return false;
        }

        $session->set('admin_last_activity', time());
        return true;
    }

    public function login()
    {
        $error = session()->getFlashdata('error');
        return $this->renderAdminView('admin/login', $error ? ['error' => $error] : []);
    }

    public function register()
    {
        return $this->renderAdminView('admin/register');
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
        if (!$this->validateAdminCsrf()) {
            return $this->csrfFailure('/AdminController/register');
        }

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
            return $this->renderAdminView('admin/register', ['error' => $error, 'old' => $this->request->getPost()]);
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
                'admin_logged_in'     => true,
                'admin_id'            => $admin['admin_id'],
                'admin_last_activity' => time(),
            ]);
            return redirect()->to('/AdminController');
        }

        return $this->renderAdminView('admin/login', ['error' => '帳號或密碼錯誤']);
    }

    public function index()
    {
        if (!$this->requireAdminLogin()) {
            return redirect()->to('/AdminController/login');
        }

        return $this->renderAdminIndex();
    }

    public function search()
    {
        if (!$this->requireAdminLogin()) {
            return redirect()->to('/AdminController/login');
        }

        return $this->renderAdminIndex();
    }

    public function keepAlive()
    {
        if (!$this->requireAdminLogin()) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON(['success' => false]);
        }

        if (!$this->validateAdminCsrf()) {
            return $this->response
                ->setStatusCode(419)
                ->setJSON(['success' => false]);
        }

        return $this->response->setJSON(['success' => true]);
    }

    public function applications()
    {
        if (!$this->requireAdminLogin()) {
            return redirect()->to('/AdminController/login');
        }

        $applicationModel = new FormalApplicationModel();
        $applicationModel->ensureInitialData();
        $filters = $this->getFormalApplicationFilters();
        $applications = $applicationModel
            ->select('student_id as id, name')
            ->findAll();
        $applications = $this->filterAndSortFormalApplications($applications, $filters);

        $total = count($applications);
        $pageCount = max(1, (int) ceil($total / $filters['per_page']));
        $page = max(1, min((int) $this->request->getGet('page'), $pageCount));

        return $this->renderAdminView('admin/applications', [
            'applications' => array_slice($applications, ($page - 1) * $filters['per_page'], $filters['per_page']),
            'filters'      => $filters,
            'page'         => $page,
            'page_count'   => $pageCount,
            'total'        => $total,
            'total_all'    => $applicationModel->countAll(),
        ]);
    }

    private function getFormalApplicationFilters(): array
    {
        $searchBy = $this->request->getGet('search_by');
        $sort = $this->request->getGet('sort');
        $direction = strtoupper((string) $this->request->getGet('direction'));
        $perPage = (int) $this->request->getGet('per_page');

        return [
            'search_by' => in_array($searchBy, ['id', 'name'], true) ? $searchBy : 'name',
            'keyword'   => trim((string) $this->request->getGet('keyword')),
            'sort'      => in_array($sort, ['id', 'name'], true) ? $sort : 'id',
            'direction' => in_array($direction, ['ASC', 'DESC'], true) ? $direction : 'ASC',
            'per_page'  => in_array($perPage, [10, 20, 50, 100], true) ? $perPage : 20,
        ];
    }

    private function filterAndSortFormalApplications(array $applications, array $filters): array
    {
        if ($filters['keyword'] !== '') {
            $applications = array_values(array_filter(
                $applications,
                static fn (array $application): bool => mb_stripos($application[$filters['search_by']], $filters['keyword']) !== false
            ));
        }

        usort($applications, static function (array $left, array $right) use ($filters): int {
            $result = $left[$filters['sort']] <=> $right[$filters['sort']];
            return $filters['direction'] === 'DESC' ? -$result : $result;
        });

        return $applications;
    }

    public function applicationsExport()
    {
        if (!$this->requireAdminLogin()) {
            return redirect()->to('/AdminController/login');
        }

        $applicationModel = new FormalApplicationModel();
        $applicationModel->ensureInitialData();
        $applications = $applicationModel
            ->select('student_id as id, name')
            ->findAll();
        $applications = $this->filterAndSortFormalApplications($applications, $this->getFormalApplicationFilters());
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, ['學號 Student ID', '使用者姓名']);

        foreach ($applications as $application) {
            fputcsv($stream, [$application['id'], $application['name']]);
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return $this->response
            ->download('applications-export-' . date('Ymd_His') . '.csv', $csv)
            ->setContentType('text/csv; charset=UTF-8');
    }

    public function applicationDetail(string $applicationId)
    {
        if (!$this->requireAdminLogin()) {
            return redirect()->to('/AdminController/login');
        }

        $applicationModel = new FormalApplicationModel();
        $applicationModel->ensureInitialData();
        $application = $applicationModel
            ->select('student_id as id, name')
            ->where('student_id', $applicationId)
            ->first();

        if ($application) {
            return $this->renderAdminView('admin/application_detail', ['application' => $application]);
        }

        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('找不到正式報名資料。');
    }

    public function profile()
    {
        if (!$this->requireAdminLogin()) {
            return redirect()->to('/AdminController/login');
        }

        $admin = (new AdminModel())
            ->select('admin_id, name, username, email, employee_id')
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

        $name            = trim((string) $this->request->getPost('name'));
        $email           = trim((string) $this->request->getPost('email'));
        $password        = (string) $this->request->getPost('password');
        $passwordConfirm = (string) $this->request->getPost('password_confirm');
        $error            = null;

        if ($name === '' || $email === '') {
            $error = '姓名與 Email 為必填項目。';
        } elseif (mb_strlen($name) > 50) {
            $error = '姓名不可超過 50 個字元。';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = '請輸入正確的 Email 格式。';
        } elseif ($adminModel
            ->where('email', $email)
            ->where('admin_id !=', $admin['admin_id'])
            ->first()) {
            $error = '此 Email 已被其他管理員使用。';
        } elseif ($password !== '' || $passwordConfirm !== '') {
            if (strlen($password) < 6) {
                $error = '新密碼長度至少需為 6 個字元。';
            } elseif ($password !== $passwordConfirm) {
                $error = '兩次輸入的新密碼不一致。';
            }
        }

        if ($error !== null) {
            return $this->renderAdminView('admin/profile', [
                'admin' => array_merge($admin, ['name' => $name, 'email' => $email]),
                'error' => $error,
            ]);
        }

        $data = [
            'name'  => $name,
            'email' => $email,
        ];

        if ($password !== '') {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        if (!$adminModel->update($admin['admin_id'], $data)) {
            return $this->renderAdminView('admin/profile', [
                'admin' => array_merge($admin, ['name' => $name, 'email' => $email]),
                'error' => '管理員資料更新失敗，請稍後再試。',
            ]);
        }

        session()->set('admin_name', $name);
        return redirect()->to('/AdminController/profile')->with('success', '管理員個人資料更新成功。');
    }

    private function renderAdminIndex()
    {
        $model = new UserModel();
        $filters = $this->getAdminFilters();

        $countModel = new UserModel();
        $totalFiltered = $countModel->applyAdminFilters($filters)->countAllResults();

        if ($filters['sort'] === 'name') {
            $users = $model
                ->select('id, student_id, name, email, file_name, uploaded_at, created_at')
                ->applyAdminFilters($filters)
                ->findAll();
            $users = $this->sortUsersByNameStroke($users, $filters['direction']);

            $page = max(1, (int) $this->request->getGet('page'));
            $pager = service('pager')->store('default', $page, $filters['per_page'], $totalFiltered);
            $offset = ($pager->getCurrentPage() - 1) * $filters['per_page'];
            $users = array_slice($users, $offset, $filters['per_page']);
        } else {
            $users = $model
                ->select('id, student_id, name, email, file_name, uploaded_at, created_at')
                ->applyAdminFilters($filters)
                ->orderBy($filters['sort'], $filters['direction'])
                ->paginate($filters['per_page']);
            $pager = $model->pager;
        }

        return $this->renderAdminView('admin/index', [
            'users'          => $users,
            'selected_user'  => null,
            'filters'        => $filters,
            'statistics'     => (new UserModel())->getAdminStatistics(),
            'total_filtered' => $totalFiltered,
            'pager'          => $pager,
        ]);
    }

    private function getAdminFilters(): array
    {
        $searchBy = $this->request->getGet('search_by');
        $status = $this->request->getGet('upload_status');
        $sort = $this->request->getGet('sort');
        $direction = strtoupper((string) $this->request->getGet('direction'));
        $perPage = (int) $this->request->getGet('per_page');

        return [
            'keyword'       => trim((string) $this->request->getGet('keyword')),
            'search_by'     => in_array($searchBy, ['id', 'name', 'email'], true) ? $searchBy : 'name',
            'upload_status' => in_array($status, ['all', 'uploaded', 'missing'], true) ? $status : 'all',
            'uploaded_from' => $this->normalizeAdminDate($this->request->getGet('uploaded_from')),
            'uploaded_to'   => $this->normalizeAdminDate($this->request->getGet('uploaded_to')),
            'sort'          => in_array($sort, ['student_id', 'name', 'email', 'uploaded_at', 'created_at'], true) ? $sort : 'uploaded_at',
            'direction'     => in_array($direction, ['ASC', 'DESC'], true) ? $direction : 'DESC',
            'per_page'      => in_array($perPage, [10, 20, 50, 100], true) ? $perPage : 20,
        ];
    }

    private function sortUsersByNameStroke(array $users, string $direction): array
    {
        $collator = new \Collator('zh_TW@collation=stroke');

        usort($users, static function (array $left, array $right) use ($collator, $direction): int {
            $comparison = $collator->compare((string) $left['name'], (string) $right['name']);

            if ($comparison === 0) {
                $comparison = strcmp((string) $left['student_id'], (string) $right['student_id']);
            }

            return $direction === 'DESC' ? -$comparison : $comparison;
        });

        return $users;
    }

    private function normalizeAdminDate($value): string
    {
        $date = \DateTime::createFromFormat('!Y-m-d', (string) $value);
        $errors = \DateTime::getLastErrors();

        if (!$date || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            return '';
        }

        return $date->format('Y-m-d');
    }

    public function export()
    {
        if (!$this->requireAdminLogin()) {
            return redirect()->to('/AdminController/login');
        }

        $filters = $this->getAdminFilters();
        $query = (new UserModel())
            ->select('student_id, name, email, file_name, uploaded_at')
            ->applyAdminFilters($filters);

        $users = $filters['sort'] === 'name'
            ? $this->sortUsersByNameStroke($query->findAll(), $filters['direction'])
            : $query->orderBy($filters['sort'], $filters['direction'])->findAll();

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, ['學生 ID', '姓名', 'Email', '履歷狀態', '檔案名稱', '上傳時間']);

        foreach ($users as $user) {
            fputcsv($stream, [
                $user['student_id'],
                $user['name'],
                $user['email'],
                empty($user['file_name']) ? '尚未上傳' : '已上傳',
                $user['file_name'] ?? '',
                $user['uploaded_at'] ?? '',
            ]);
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return $this->response
            ->download('students-export-' . date('Ymd_His') . '.csv', $csv)
            ->setContentType('text/csv; charset=UTF-8');
    }

    public function batchDownload()
    {
        if (!$this->requireAdminLogin()) {
            return redirect()->to('/AdminController/login');
        }

        if (!$this->validateAdminCsrf()) {
            return $this->csrfFailure('/AdminController');
        }

        $selectedIds = $this->request->getPost('selected_ids');
        $selectedIds = is_array($selectedIds) ? array_map('intval', $selectedIds) : [];
        $selectedIds = array_values(array_unique(array_filter($selectedIds, static fn ($id) => $id > 0)));

        if (empty($selectedIds)) {
            return redirect()->to('/AdminController')->with('error', '請至少選擇一份履歷檔案。');
        }

        if (!class_exists('ZipArchive')) {
            return redirect()->to('/AdminController')->with('error', '目前環境不支援批次下載功能。');
        }

        $users = (new UserModel())->whereIn('id', $selectedIds)->findAll();
        $zipPath = tempnam(WRITEPATH, 'admin_batch_');
        $zip = new \ZipArchive();

        if ($zipPath === false || $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            if ($zipPath !== false && is_file($zipPath)) {
                @unlink($zipPath);
            }

            return redirect()->to('/AdminController')->with('error', '批次下載檔案建立失敗。');
        }

        $usedNames = [];
        $addedCount = 0;

        foreach ($users as $user) {
            if (empty($user['file_name'])) {
                continue;
            }

            $content = !empty($user['file_content'])
                ? base64_decode($user['file_content'], true)
                : null;

            if ($content === false || $content === null) {
                $filePath = WRITEPATH . 'uploads/' . $user['file_name'];
                $content = is_file($filePath) ? file_get_contents($filePath) : null;
            }

            if ($content === false || $content === null) {
                continue;
            }

            $studentId = preg_replace('/[^a-zA-Z0-9_-]/', '_', $user['student_id']);
            $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($user['file_name']));
            $entryName = $studentId . '_' . $fileName;
            $nameIndex = 2;

            while (isset($usedNames[$entryName])) {
                $entryName = $studentId . '_' . $nameIndex . '_' . $fileName;
                $nameIndex++;
            }

            $usedNames[$entryName] = true;
            $zip->addFromString($entryName, $content);
            $addedCount++;
        }

        $zip->close();

        if ($addedCount === 0) {
            @unlink($zipPath);
            return redirect()->to('/AdminController')->with('error', '選取的學生沒有可下載的履歷檔案。');
        }

        register_shutdown_function(static function () use ($zipPath): void {
            if (is_file($zipPath)) {
                @unlink($zipPath);
            }
        });

        return $this->response
            ->download($zipPath, null)
            ->setFileName('student-resumes-' . date('Ymd_His') . '.zip');
    }

    public function show($user_id)
    {
        if (!$this->requireAdminLogin()) {
            return redirect()->to('/AdminController/login');
        }

        $userModel = new UserModel();
        $selected  = $userModel->find($user_id);

        if (!$selected) {
            echo '找不到指定使用者';
            return;
        }

        return $this->renderAdminView('admin/show', ['user' => $selected]);
    }

    public function viewFile($user_id)
    {
        if (!$this->requireAdminLogin()) {
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
        if (!$this->requireAdminLogin()) {
            return redirect()->to('/AdminController/login');
        }

        $userModel = new UserModel();
        $user = $userModel->find($user_id);

        if (!$user) {
            return redirect()->to('/AdminController')->with('error', '找不到指定學生資料，無法下載履歷。');
        }

        if (empty($user['file_name'])) {
            return redirect()->to('/AdminController')->with('error', '此學生尚未上傳履歷，無法下載。');
        }

        if (!empty($user['file_content'])) {
            $content = base64_decode($user['file_content'], true);
            if ($content === false || $content === '') {
                return redirect()->to('/AdminController')->with('error', '履歷內容異常，無法下載。');
            }

            return $this->response->download($user['file_name'], $content);
        }

        $filePath = WRITEPATH . 'uploads/' . $user['file_name'];
        if (is_file($filePath) && is_readable($filePath)) {
            $content = @file_get_contents($filePath);
            if ($content !== false && $content !== '') {
                return $this->response->download($filePath, null)->setFileName($user['file_name']);
            }

            return redirect()->to('/AdminController')->with('error', '履歷檔案內容異常，無法下載。');
        }

        return redirect()->to('/AdminController')->with('error', '履歷檔案不存在或無法讀取。');
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

        $name       = trim($this->request->getVar('name'));
        $email      = trim($this->request->getVar('email'));
        $employeeId = trim($this->request->getVar('employee_id'));

        if (empty($name) || empty($email) || empty($employeeId)) {
            return $this->renderAdminView('admin/forgot_password', [
                'error' => '請輸入姓名、電子郵件與員工證號。',
                'old'   => ['name' => $name, 'email' => $email, 'employee_id' => $employeeId]
            ]);
        }

        if (!preg_match('/^admin\d{2}$/', $employeeId)) {
            return $this->renderAdminView('admin/forgot_password', [
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
            return $this->renderAdminView('admin/forgot_password', [
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

        $password        = $this->request->getVar('password');
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

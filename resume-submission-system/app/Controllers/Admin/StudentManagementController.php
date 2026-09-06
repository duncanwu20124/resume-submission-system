<?php

namespace App\Controllers\Admin;

use App\Models\AuditLogModel;
use App\Models\UserModel;
use ZipArchive;

class StudentManagementController extends BaseAdminController
{
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
        if (!$this->requireRole([self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN])) {
            return $this->denyPermission('/AdminController', '審查委員無權匯出學生清單。');
        }

        $filters = $this->getAdminFilters();
        $model = new UserModel();
        $users = $model
            ->select('id, student_id, name, email, file_name, uploaded_at, created_at')
            ->applyAdminFilters($filters);

        if ($filters['sort'] === 'name') {
            $rows = $this->sortUsersByNameStroke($users->findAll(), $filters['direction']);
        } else {
            $rows = $users->orderBy($filters['sort'], $filters['direction'])->findAll();
        }

        $filename = 'students_export_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");
        fputcsv($out, ['序號', '學號', '姓名', 'Email', '履歷狀態', '檔案名稱', '上傳時間'], ',', '"', "\\");

        foreach ($rows as $index => $u) {
            fputcsv($out, [
                $index + 1,
                $this->csvSafeValue($u['student_id']),
                $this->csvSafeValue($u['name']),
                $this->csvSafeValue($u['email']),
                !empty($u['file_name']) ? '已上傳' : '尚未上傳',
                $this->csvSafeValue($u['file_name'] ?? '—'),
                $this->normalizeAdminDate($u['uploaded_at']),
            ], ',', '"', "\\");
        }
        fclose($out);

        AuditLogModel::log(
            '履歷管理',
            '匯出學生履歷清單 CSV（共 ' . count($rows) . ' 筆）',
            '成功'
        );

        if (ENVIRONMENT === 'testing') {
            return;
        }
        exit;
    }

    public function batchDownload()
    {
        if (!$this->requireRole([self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN])) {
            return $this->denyPermission('/AdminController', '審查委員無權批次打包下載學生履歷。');
        }

        if (!$this->validateAdminCsrf()) {
            return $this->csrfFailure('/AdminController');
        }

        $studentIds = $this->request->getPost('student_ids');

        if (!is_array($studentIds) || count($studentIds) === 0) {
            return redirect()->to('/AdminController')->with('error', '請先勾選至少一位學生。');
        }

        $sanitizedIds = [];
        foreach ($studentIds as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $sanitizedIds[] = $id;
            }
        }

        if (count($sanitizedIds) === 0) {
            return redirect()->to('/AdminController')->with('error', '選取的學生資料無效。');
        }

        $userModel = new UserModel();
        $users = $userModel
            ->select('id, student_id, name, file_name, file_content')
            ->whereIn('id', $sanitizedIds)
            ->findAll();

        if (count($users) === 0) {
            return redirect()->to('/AdminController')->with('error', '找不到可下載的學生資料。');
        }

        $zip = new ZipArchive();
        $zipPath = tempnam(sys_get_temp_dir(), 'resumes_zip_');

        if ($zipPath === false || $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            if ($zipPath !== false && is_file($zipPath)) {
                @unlink($zipPath);
            }
            return redirect()->to('/AdminController')->with('error', '無法建立 ZIP 壓縮檔，請稍後再試。');
        }

        $addedCount = 0;
        $usedNames = [];

        foreach ($users as $user) {
            if (empty($user['file_name'])) {
                continue;
            }

            $content = !empty($user['file_content'])
                ? base64_decode($user['file_content'], true)
                : null;

            if ($content === false || $content === null) {
                $filePath = $this->resolveUploadedFilePath((string) $user['file_name']);
                $content = $filePath !== null ? @file_get_contents($filePath) : null;
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

        AuditLogModel::log(
            '履歷管理',
            "批次打包下載 {$addedCount} 份學生履歷檔案",
            '成功'
        );

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
            $content = base64_decode($user['file_content'], true);
            if ($content === false) {
                return redirect()->to('/AdminController')->with('error', '履歷內容異常，無法預覽。');
            }
        } else {
            $filePath = $this->resolveUploadedFilePath((string) $user['file_name']);
            if ($filePath !== null) {
                $content = @file_get_contents($filePath);
            }
        }

        if ($content === false || $content === null || $content === '') {
            return redirect()->to('/AdminController')->with('error', '履歷檔案不存在或內容異常，無法預覽。');
        }

        $ext = strtolower(pathinfo($user['file_name'], PATHINFO_EXTENSION));
        $allowedTypes = [
            'pdf'  => 'application/pdf',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
        $contentType = $allowedTypes[$ext] ?? 'application/octet-stream';
        $inlineFilename = $this->sanitizeDownloadFileName($user['file_name']);

        return $this->response
            ->setHeader('Content-Type', $contentType)
            ->setHeader('Content-Disposition', 'inline; filename="' . $inlineFilename . '"')
            ->setBody($content);
    }

    public function download($user_id)
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

        if (!empty($user['file_content'])) {
            $content = base64_decode($user['file_content'], true);
            if ($content === false || $content === '') {
                return redirect()->to('/AdminController')->with('error', '履歷內容異常，無法下載。');
            }
            return $this->response->download($user['file_name'], $content);
        }

        $filePath = $this->resolveUploadedFilePath((string) $user['file_name']);
        if ($filePath !== null) {
            $content = @file_get_contents($filePath);
            if ($content !== false && $content !== '') {
                return $this->response->download($filePath, null)->setFileName($user['file_name']);
            }

            return redirect()->to('/AdminController')->with('error', '履歷檔案內容異常，無法下載。');
        }

        return redirect()->to('/AdminController')->with('error', '履歷檔案不存在或無法讀取。');
    }

    private function sanitizeDownloadFileName(string $fileName): string
    {
        $safeName = basename($fileName);
        $safeName = preg_replace('/[\x00-\x1F\x7F"\\\\]/u', '_', $safeName) ?? '';

        return trim($safeName) !== '' ? $safeName : 'resume';
    }

    private function csvSafeValue($value): string
    {
        $value = (string) $value;

        return preg_match('/^\s*[=+\-@]/u', $value) === 1 ? "'" . $value : $value;
    }
}

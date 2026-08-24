<?php

namespace App\Controllers\Student;

use App\Controllers\BaseController;
use App\Models\StudentModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $studentModel = new StudentModel();
        $studentDbId  = session()->get('student_db_id');
        $student      = $studentModel->find($studentDbId);

        if (!$student) {
            // Fallback if student not in DB
            $student = [
                'id'            => $studentDbId,
                'student_id'    => session()->get('student_id'),
                'name'          => session()->get('student_name'),
                'email'         => session()->get('student_email'),
                'file_name'     => null,
                'uploaded_at'   => null,
            ];
        }

        $fileData = null;
        $files    = [];

        if (!empty($student['file_name'])) {
            $fileExists   = !empty($student['file_content']);
            $fileSize     = $fileExists ? $this->formatBytes(strlen(base64_decode($student['file_content']))) : '未知';
            $ext          = strtolower(pathinfo($student['file_name'], PATHINFO_EXTENSION));

            $fileData = [
                'name'          => $student['file_name'],
                'relative_path' => '資料庫儲存',
                'size'          => $fileSize,
                'uploaded_at'   => $student['uploaded_at'],
                'is_pdf'        => ($ext === 'pdf'),
                'extension'     => $ext,
                'exists'        => $fileExists,
            ];

            $files[] = $fileData;
        }

        return view('student/dashboard', [
            'student' => $student,
            'file'    => $fileData,
            'files'   => $files,
        ]);
    }

    public function upload()
    {
        $rules = [
            'resume' => [
                'label'  => '履歷檔案',
                'rules'  => [
                    'uploaded[resume]',
                    'max_size[resume,3072]',
                    'ext_in[resume,pdf,doc,docx]',
                ],
                'errors' => [
                    'uploaded' => '請選擇要上傳的履歷檔案。',
                    'max_size' => '檔案大小不能超過 3MB。',
                    'ext_in'   => '僅允許上傳 PDF、DOC 或 DOCX 格式的檔案。',
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            $error = $this->validator->getError('resume') ?: '檔案驗證失敗，請檢查檔案格式與大小。';
            return redirect()->to('/student/dashboard')->withInput()->with('error', $error);
        }

        $file = $this->request->getFile('resume');

        if (!$file || !$file->isValid()) {
            $error = $file ? $file->getErrorString() : '檔案上傳過程發生錯誤。';
            return redirect()->to('/student/dashboard')->withInput()->with('error', $error);
        }

        $uploadDir = WRITEPATH . 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $studentModel = new StudentModel();
        $studentDbId  = session()->get('student_db_id');
        $student      = $studentModel->find($studentDbId);

        // Remove old file if exists
        if ($student && !empty($student['file_name'])) {
            $oldPath = $uploadDir . $student['file_name'];
            if (file_exists($oldPath) && is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        // Generate unique and organized filename with student ID, timestamp and random string
        $studentId       = session()->get('student_id') ?? 'student';
        $cleanStudentId  = preg_replace('/[^a-zA-Z0-9_-]/', '', $studentId);
        $ext             = strtolower($file->getClientExtension());
        $randomSuffix    = bin2hex(random_bytes(4));
        $newFileName     = "{$cleanStudentId}_" . date('Ymd_His') . "_{$randomSuffix}.{$ext}";

        // Base64-encode before storage: CodeIgniter's SQLite3 driver interpolates
        // escaped values directly into the SQL text rather than binding a true
        // blob parameter, so raw binary containing NUL bytes gets truncated by
        // SQLite's string-literal parsing. Encoding avoids NUL bytes entirely.
        $fileContent     = base64_encode(file_get_contents($file->getTempName()));

        // Update database record
        $studentModel->update($studentDbId, [
            'file_name'    => $newFileName,
            'file_content' => $fileContent,
            'uploaded_at'  => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/student/dashboard')->with('success', '履歷檔案上傳成功！');
    }

    public function viewFile()
    {
        $studentModel = new StudentModel();
        $studentDbId  = session()->get('student_db_id');
        $student      = $studentModel->find($studentDbId);

        if (!$student || empty($student['file_name']) || empty($student['file_content'])) {
            return redirect()->to('/student/dashboard')->with('error', '目前尚無上傳任何檔案。');
        }

        $ext = strtolower(pathinfo($student['file_name'], PATHINFO_EXTENSION));
        $mimeType = match ($ext) {
            'pdf'   => 'application/pdf',
            'doc'   => 'application/msword',
            'docx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            default => 'application/octet-stream',
        };

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Disposition', 'inline; filename="' . $student['file_name'] . '"')
            ->setBody(base64_decode($student['file_content']));
    }

    public function download()
    {
        $studentModel = new StudentModel();
        $studentDbId  = session()->get('student_db_id');
        $student      = $studentModel->find($studentDbId);

        if (!$student || empty($student['file_name']) || empty($student['file_content'])) {
            return redirect()->to('/student/dashboard')->with('error', '目前尚無上傳任何檔案可供下載。');
        }

        return $this->response->download($student['file_name'], base64_decode($student['file_content']));
    }

    public function deleteFile()
    {
        $studentModel = new StudentModel();
        $studentDbId  = session()->get('student_db_id');
        $student      = $studentModel->find($studentDbId);

        if ($student && !empty($student['file_name'])) {
            $filePath = WRITEPATH . 'uploads/' . $student['file_name'];
            if (file_exists($filePath) && is_file($filePath)) {
                @unlink($filePath);
            }

            $studentModel->update($studentDbId, [
                'file_name'    => null,
                'file_content' => null,
                'uploaded_at'  => null,
            ]);
        }

        return redirect()->to('/student/dashboard')->with('success', '已成功刪除履歷檔案。');
    }

    private function formatBytes(int|float $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow   = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}


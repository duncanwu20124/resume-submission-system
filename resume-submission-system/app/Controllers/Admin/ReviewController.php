<?php

namespace App\Controllers\Admin;

use App\Models\UserModel;
use App\Support\PdfDuplicateDetector;

class ReviewController extends BaseAdminController
{
    public function pdfDuplicates()
    {
        if (!$this->requireAdminLogin()) {
            return redirect()->to('/AdminController/login');
        }

        $students = (new UserModel())
            ->select('id, student_id, name, file_name, file_content, uploaded_at')
            ->findAll();
        $studentsWithContent = [];

        foreach ($students as $student) {
            $content = null;
            if (!empty($student['file_content'])) {
                $content = base64_decode((string) $student['file_content'], true);
            }

            if ($content === false || $content === null) {
                $filePath = $this->resolveUploadedFilePath((string) ($student['file_name'] ?? ''));
                $content = $filePath !== null ? @file_get_contents($filePath) : false;
            }

            if (!is_string($content) || $content === '') {
                continue;
            }

            $student['pdf_content'] = $content;
            $studentsWithContent[] = $student;
        }

        return $this->renderAdminView('admin/pdf_duplicates', [
            'duplicate_groups' => PdfDuplicateDetector::findDuplicateGroups($studentsWithContent),
            'checked_count' => count($studentsWithContent),
        ]);
    }
}

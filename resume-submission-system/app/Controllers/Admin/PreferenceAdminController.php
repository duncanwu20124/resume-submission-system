<?php

namespace App\Controllers\Admin;

use App\Config\Universities;
use App\Models\AuditLogModel;
use App\Models\StudentPreferenceModel;
use App\Models\UserModel;
use App\Support\PreferenceAnalytics;
use CodeIgniter\Exceptions\PageNotFoundException;

class PreferenceAdminController extends BaseAdminController
{
    public function preferences()
    {
        if (!$this->requireRole([self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN])) {
            return $this->denyPermission('/AdminController', '審查委員無權存取志願序管理功能。');
        }

        $prefModel = new StudentPreferenceModel();
        $submittedRows = $prefModel->listWithStudents();

        $keyword = trim((string) $this->request->getGet('keyword'));
        $department = trim((string) $this->request->getGet('department'));
        $school = trim((string) $this->request->getGet('school'));
        $sort = PreferenceAnalytics::normalizeSort((string) $this->request->getGet('sort'));
        $perPage = (int) $this->request->getGet('per_page');
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }
        $page = max(1, (int) $this->request->getGet('page'));

        $rows = PreferenceAnalytics::filterAndSort($submittedRows, $keyword, $school, $sort, $department);
        $totalFiltered = count($rows);

        $schoolStats = PreferenceAnalytics::sortSchoolCounts(
            PreferenceAnalytics::schoolCounts($submittedRows),
            $sort
        );

        if ($school !== '') {
            $schoolStats = PreferenceAnalytics::filterSchoolStats($schoolStats, $school);
        }

        $pager = service('pager')->store('default', $page, $perPage, $totalFiltered);
        $offset = ($page - 1) * $perPage;
        $pagedPreferences = array_slice($rows, $offset, $perPage);

        return $this->renderAdminView('admin/preferences', [
            'preferences' => $pagedPreferences,
            'total_filtered' => $totalFiltered,
            'pager' => $pager,
            'per_page' => $perPage,
            'page' => $page,
            'school' => $school,
            'department' => $department,
            'keyword' => $keyword,
            'sort' => $sort,
            'universities' => Universities::names(),
            'sort_options' => PreferenceAnalytics::sortOptions(),
            'school_stats' => $schoolStats,
            'total_students' => (new UserModel())->countAll(),
            'submitted_count' => $prefModel->countSubmitted(),
        ]);
    }

    public function preferenceDetail($studentDbId)
    {
        if (!$this->requireRole([self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN])) {
            return $this->denyPermission('/AdminController', '審查委員無權存取志願序管理功能。');
        }

        $userModel = new UserModel();
        $student = $userModel->find($studentDbId);

        if (!$student) {
            throw PageNotFoundException::forPageNotFound('找不到指定學生。');
        }

        $prefModel = new StudentPreferenceModel();
        $preference = $prefModel->findByStudent((int) $studentDbId);

        if (!$preference || !$prefModel->isLocked($preference)) {
            throw PageNotFoundException::forPageNotFound('該學生尚未送出志願序。');
        }

        return $this->renderAdminView('admin/preference_detail', [
            'student' => $student,
            'preference' => $preference,
            'choices' => $prefModel->choicesOf($preference),
        ]);
    }

    public function resetPreference($studentDbId)
    {
        if (!$this->requireRole([self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN])) {
            return $this->denyPermission('/AdminController', '審查委員無權存取志願序管理功能。');
        }

        $prefModel = new StudentPreferenceModel();

        if (!$prefModel->resetByStudent((int) $studentDbId)) {
            return redirect()->to('/AdminController/preferences')->with('error', '該學生尚未送出志願序，無需重新開放。');
        }

        $student = (new UserModel())->find((int) $studentDbId);
        $studentLabel = $student ? "{$student['name']}（{$student['student_id']}）" : "學生 #{$studentDbId}";
        AuditLogModel::log(
            '志願序管理',
            "重新開放 {$studentLabel} 的志願序填寫權限",
            '成功'
        );

        return redirect()->to('/AdminController/preferences')->with('success', '已重新開放該學生的志願序，學生可再次登入填寫。');
    }

    public function export()
    {
        if (!$this->requireRole([self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN])) {
            return $this->denyPermission('/AdminController/preferences', '審查委員無權匯出志願序清單。');
        }

        $prefModel = new StudentPreferenceModel();
        $db = db_connect();
        $rows = $db->table('student_preferences')
            ->select('student_preferences.*, students.student_id as student_number, students.name as student_name, students.email as student_email')
            ->join('students', 'students.id = student_preferences.student_db_id')
            ->where('student_preferences.status', StudentPreferenceModel::STATUS_SUBMITTED)
            ->orderBy('student_preferences.submitted_at', 'ASC')
            ->get()->getResultArray();

        $school = trim((string) $this->request->getGet('school'));
        $keyword = trim((string) $this->request->getGet('keyword'));
        $department = trim((string) $this->request->getGet('department'));

        $filteredRows = [];
        foreach ($rows as $row) {
            $choices = $prefModel->choicesOf($row);
            $row['parsed_choices'] = $choices;

            if ($school !== '') {
                $hasSchool = false;
                foreach ($choices as $c) {
                    if (str_starts_with($c, $school . ' - ')) {
                        $hasSchool = true;
                        break;
                    }
                }
                if (!$hasSchool) continue;
            }

            if ($keyword !== '') {
                $match = str_contains((string) ($row['student_number'] ?? ''), $keyword)
                    || str_contains((string) ($row['student_name'] ?? ''), $keyword);
                if (!$match) continue;
            }

            if ($department !== '') {
                $hasDept = false;
                foreach ($choices as $c) {
                    if (str_contains($c, $department)) {
                        $hasDept = true;
                        break;
                    }
                }
                if (!$hasDept) continue;
            }

            $filteredRows[] = $row;
        }

        $filename = 'preferences_export_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");
        fputcsv($out, ['序號', '學號', '姓名', 'Email', '志願數', '第1志願', '第2志願', '第3志願', '第4志願', '第5志願', '第6志願', '送出時間'], ',', '"', "\\");

        foreach ($filteredRows as $index => $r) {
            $c = $r['parsed_choices'];
            fputcsv($out, [
                $index + 1,
                $r['student_number'],
                $r['student_name'],
                $r['student_email'] ?? '',
                count($c),
                $c[0] ?? '—',
                $c[1] ?? '—',
                $c[2] ?? '—',
                $c[3] ?? '—',
                $c[4] ?? '—',
                $c[5] ?? '—',
                $r['submitted_at'] ?? '—',
            ], ',', '"', "\\");
        }
        fclose($out);
        if (ENVIRONMENT === 'testing') {
            return;
        }
        exit;
    }
}

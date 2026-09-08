<?php

namespace App\Controllers\Admin;

use App\Models\AuditLogModel;
use App\Models\StudentPreferenceModel;
use App\Models\StudentScoreModel;
use App\Models\UserModel;

class ScoringController extends BaseAdminController
{
    public function scoring()
    {
        if (!$this->requireAdminLogin()) {
            return redirect()->to('/AdminController/login');
        }

        $searchBy = (string) $this->request->getGet('search_by');
        $keyword = trim((string) $this->request->getGet('keyword'));
        $scoreStatus = (string) $this->request->getGet('score_status');
        $sort = (string) $this->request->getGet('sort');
        $direction = strtoupper((string) $this->request->getGet('direction'));
        $perPage = (int) $this->request->getGet('per_page');
        $page = max(1, (int) $this->request->getGet('page'));

        $filters = [
            'search_by' => in_array($searchBy, ['name', 'id'], true) ? $searchBy : 'name',
            'keyword' => $keyword,
            'score_status' => in_array($scoreStatus, ['all', 'confirmed', 'draft', 'unscored'], true) ? $scoreStatus : 'all',
            'sort' => in_array($sort, ['student_id', 'name', 'total_score', 'submitted_at'], true) ? $sort : 'student_id',
            'direction' => in_array($direction, ['ASC', 'DESC'], true) ? $direction : 'ASC',
            'per_page' => in_array($perPage, [10, 20, 50, 100], true) ? $perPage : 20,
        ];

        $db = db_connect();

        // 全域統計人數
        $totalSubmitted = (new StudentPreferenceModel())->countSubmitted();
        $totalConfirmed = $db->table('student_scores')
            ->join('student_preferences', 'student_preferences.student_db_id = student_scores.student_db_id')
            ->where('student_preferences.status', StudentPreferenceModel::STATUS_SUBMITTED)
            ->where('student_scores.status', StudentScoreModel::STATUS_CONFIRMED)->countAllResults();
        $totalDraft = max(0, $totalSubmitted - $totalConfirmed);

        // 建構篩選查詢
        $builder = $db->table('student_preferences')
            ->select('student_preferences.student_db_id, student_preferences.submitted_at, students.student_id as student_number, students.name as student_name, student_scores.total_score, student_scores.status as score_status, student_scores.comment, student_scores.updated_at as scored_at')
            ->join('students', 'students.id = student_preferences.student_db_id')
            ->join('student_scores', 'student_scores.student_db_id = student_preferences.student_db_id', 'left')
            ->where('student_preferences.status', StudentPreferenceModel::STATUS_SUBMITTED);

        // 關鍵字搜尋
        if ($filters['keyword'] !== '') {
            if ($filters['search_by'] === 'id') {
                $builder->like('students.student_id', $filters['keyword']);
            } else {
                $builder->like('students.name', $filters['keyword']);
            }
        }

        // 評分狀態篩選
        if ($filters['score_status'] === 'confirmed') {
            $builder->where('student_scores.status', 'confirmed');
        } elseif ($filters['score_status'] === 'draft') {
            $builder->where('student_scores.status', 'draft');
        } elseif ($filters['score_status'] === 'unscored') {
            $builder->where('student_scores.status IS NULL');
        }

        // 計算符合條件總數（重設 select 但保留 where 條件）
        $countBuilder = clone $builder;
        $totalFiltered = $countBuilder->countAllResults();

        // 排序
        $sortField = match ($filters['sort']) {
            'name' => 'students.name',
            'total_score' => 'student_scores.total_score',
            'submitted_at' => 'student_preferences.submitted_at',
            default => 'students.student_id',
        };
        $builder->orderBy($sortField, $filters['direction']);

        // 分頁 Limit & Offset
        $offset = ($page - 1) * $filters['per_page'];
        $rows = $builder->limit($filters['per_page'], $offset)->get()->getResultArray();

        // 註冊 Pager 服務
        $pager = service('pager')->store('default', $page, $filters['per_page'], $totalFiltered);

        return $this->renderAdminView('admin/scoring', [
            'students' => $rows,
            'filters' => $filters,
            'total_filtered' => $totalFiltered,
            'total_submitted' => $totalSubmitted,
            'total_confirmed' => $totalConfirmed,
            'total_draft' => $totalDraft,
            'pager' => $pager,
        ]);
    }

    public function saveScore($studentDbId)
    {
        if (!$this->requireAdminLogin()) {
            return redirect()->to('/AdminController/login');
        }

        $scoreRaw = trim((string) $this->request->getPost('total_score'));
        $status = (string) $this->request->getPost('status');
        if (!is_numeric($scoreRaw) || (float) $scoreRaw < 0 || (float) $scoreRaw > 100 || !preg_match('/^\d{1,3}(?:\.\d{1,2})?$/', $scoreRaw)) {
            return redirect()->to('/AdminController/scoring')->with('error', '分數必須是 0～100，最多小數點後兩位。');
        }
        if (!in_array($status, [StudentScoreModel::STATUS_DRAFT, StudentScoreModel::STATUS_CONFIRMED], true)) {
            return redirect()->to('/AdminController/scoring')->with('error', '評分狀態不正確。');
        }
        $preference = (new StudentPreferenceModel())->findByStudent((int) $studentDbId);
        if (!$preference || $preference['status'] !== StudentPreferenceModel::STATUS_SUBMITTED) {
            return redirect()->to('/AdminController/scoring')->with('error', '該學生尚未正式送出志願序。');
        }

        $model = new StudentScoreModel();
        $existing = $model->findByStudent((int) $studentDbId);
        $data = [
            'student_db_id' => (int) $studentDbId,
            'total_score' => number_format((float) $scoreRaw, 2, '.', ''),
            'status' => $status,
            'comment' => trim((string) $this->request->getPost('comment')),
            'scored_by' => (int) session()->get('admin_id'),
            'confirmed_at' => $status === StudentScoreModel::STATUS_CONFIRMED ? date('Y-m-d H:i:s') : null,
        ];
        $existing ? $model->update($existing['id'], $data) : $model->insert($data);

        $student = (new UserModel())->find((int) $studentDbId);
        $studentLabel = $student ? "{$student['name']}（{$student['student_id']}）" : "學生 #{$studentDbId}";
        $statusText = $status === StudentScoreModel::STATUS_CONFIRMED ? '確認送出' : '暫存草稿';
        AuditLogModel::log(
            '學生評分',
            "評定 {$studentLabel} 成績：{$data['total_score']} 分（{$statusText}）",
            '成功'
        );

        return redirect()->to('/AdminController/scoring')->with('success', '學生評分已儲存。');
    }

    public function export()
    {
        if (!$this->requireRole([self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN])) {
            return $this->denyPermission('/AdminController/scoring', '審查委員無權匯出評分清單。');
        }

        $searchBy = (string) $this->request->getGet('search_by');
        $keyword = trim((string) $this->request->getGet('keyword'));
        $scoreStatus = (string) $this->request->getGet('score_status');
        $sort = (string) $this->request->getGet('sort');
        $direction = strtoupper((string) $this->request->getGet('direction'));

        $filters = [
            'search_by' => in_array($searchBy, ['name', 'id'], true) ? $searchBy : 'name',
            'keyword' => $keyword,
            'score_status' => in_array($scoreStatus, ['all', 'confirmed', 'draft', 'unscored'], true) ? $scoreStatus : 'all',
            'sort' => in_array($sort, ['student_id', 'name', 'total_score', 'submitted_at'], true) ? $sort : 'student_id',
            'direction' => in_array($direction, ['ASC', 'DESC'], true) ? $direction : 'ASC',
        ];

        $db = db_connect();
        $builder = $db->table('student_preferences')
            ->select('student_preferences.student_db_id, student_preferences.submitted_at, students.student_id as student_number, students.name as student_name, student_scores.total_score, student_scores.status as score_status, student_scores.comment, student_scores.updated_at as scored_at')
            ->join('students', 'students.id = student_preferences.student_db_id')
            ->join('student_scores', 'student_scores.student_db_id = student_preferences.student_db_id', 'left')
            ->where('student_preferences.status', StudentPreferenceModel::STATUS_SUBMITTED);

        if ($filters['keyword'] !== '') {
            if ($filters['search_by'] === 'id') {
                $builder->like('students.student_id', $filters['keyword']);
            } else {
                $builder->like('students.name', $filters['keyword']);
            }
        }

        if ($filters['score_status'] === 'confirmed') {
            $builder->where('student_scores.status', 'confirmed');
        } elseif ($filters['score_status'] === 'draft') {
            $builder->where('student_scores.status', 'draft');
        } elseif ($filters['score_status'] === 'unscored') {
            $builder->where('student_scores.status IS NULL');
        }

        $sortField = match ($filters['sort']) {
            'name' => 'students.name',
            'total_score' => 'student_scores.total_score',
            'submitted_at' => 'student_preferences.submitted_at',
            default => 'students.student_id',
        };
        $rows = $builder->orderBy($sortField, $filters['direction'])->get()->getResultArray();

        $filename = 'scores_export_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");
        fputcsv($out, ['序號', '學號', '姓名', '志願送出時間', '評分狀態', '總分', '審查評語', '評分時間'], ',', '"', "\\");

        foreach ($rows as $index => $r) {
            $statusText = match ($r['score_status']) {
                'confirmed' => '已確認',
                'draft' => '暫存中',
                default => '未評分',
            };
            fputcsv($out, [
                $index + 1,
                $r['student_number'],
                $r['student_name'],
                $r['submitted_at'] ?? '—',
                $statusText,
                $r['total_score'] !== null ? $r['total_score'] : '—',
                $r['comment'] ?? '—',
                $r['scored_at'] ?? '—',
            ], ',', '"', "\\");
        }
        fclose($out);
        if (ENVIRONMENT === 'testing') {
            return;
        }
        exit;
    }
}

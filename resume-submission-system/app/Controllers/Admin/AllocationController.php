<?php

namespace App\Controllers\Admin;

use App\Models\AllocationRunModel;
use App\Models\AuditLogModel;
use App\Models\StudentPreferenceModel;
use App\Models\StudentScoreModel;
use App\Services\AllocationService;

class AllocationController extends BaseAdminController
{
    public function allocation()
    {
        if (!$this->requireRole([self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN])) {
            return $this->denyPermission('/AdminController', '審查委員無權存取分發管理功能。');
        }

        $runId = (int) $this->request->getGet('run');
        $runModel = new AllocationRunModel();
        $run = $runId > 0 ? $runModel->find($runId) : $runModel->orderBy('id', 'DESC')->first();

        $perPage = (int) $this->request->getGet('per_page');
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }
        $page = max(1, (int) $this->request->getGet('page'));

        $results = [];
        $totalResults = 0;
        $pager = null;

        if ($run) {
            $db = db_connect();
            $baseBuilder = $db->table('allocation_results')
                ->where('allocation_results.allocation_run_id', $run['id']);
            $totalResults = $baseBuilder->countAllResults(false);

            $offset = ($page - 1) * $perPage;
            $results = $baseBuilder
                ->select('allocation_results.*, students.student_id as student_number, students.name as student_name')
                ->join('students', 'students.id = allocation_results.student_db_id')
                ->orderBy('allocation_results.overall_rank', 'ASC')
                ->limit($perPage, $offset)
                ->get()->getResultArray();

            $pager = service('pager')->store('default', $page, $perPage, $totalResults);
        }

        $submitted = (new StudentPreferenceModel())->countSubmitted();
        $confirmed = db_connect()->table('student_scores')
            ->join('student_preferences', 'student_preferences.student_db_id = student_scores.student_db_id')
            ->where('student_preferences.status', StudentPreferenceModel::STATUS_SUBMITTED)
            ->where('student_scores.status', StudentScoreModel::STATUS_CONFIRMED)->countAllResults();

        return $this->renderAdminView('admin/allocation', [
            'run' => $run,
            'results' => $results,
            'total_results' => $totalResults,
            'pager' => $pager,
            'per_page' => $perPage,
            'page' => $page,
            'submitted' => $submitted,
            'confirmed' => $confirmed,
            'runs' => $runModel->orderBy('id', 'DESC')->findAll(20),
        ]);
    }

    public function createAllocationPreview()
    {
        if (!$this->requireRole([self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN])) {
            return $this->denyPermission('/AdminController', '審查委員無權建立分發預覽。');
        }

        try {
            $runId = (new AllocationService())->createPreview((int) session()->get('admin_id'));
            AuditLogModel::log(
                '分發管理',
                "建立分發演算預覽批次 #{$runId}",
                '成功'
            );
            return redirect()->to('/AdminController/allocation?run=' . $runId)->with('success', '分發預覽已建立，確認結果後再正式發布。');
        } catch (\Throwable $e) {
            AuditLogModel::log(
                '分發管理',
                '建立分發演算預覽失敗：' . $e->getMessage(),
                '失敗'
            );
            return redirect()->to('/AdminController/allocation')->with('error', $e->getMessage());
        }
    }

    public function publishAllocation($runId)
    {
        if (!$this->requireRole(self::ROLE_SUPER_ADMIN)) {
            return $this->denyPermission('/AdminController/allocation?run=' . (int) $runId, '只有超級管理員（super_admin）有權正式發布分發結果。');
        }

        $note = trim((string) $this->request->getPost('revision_note'));
        if (!(new AllocationService())->publish((int) $runId, $note)) {
            AuditLogModel::log(
                '分發管理',
                "正式發布分發放榜失敗（批次 #{$runId}）",
                '失敗'
            );
            return redirect()->to('/AdminController/allocation?run=' . (int) $runId)->with('error', '只有尚未發布的預覽可以發布。');
        }

        AuditLogModel::log(
            '分發管理',
            "正式發布分發放榜結果（批次 #{$runId}）",
            '成功',
            $note !== '' ? "放榜版本備註：{$note}" : '正式公開分發錄取榜單'
        );
        return redirect()->to('/AdminController/allocation?run=' . (int) $runId)->with('success', '分發結果已正式發布，學生現在可以查榜。');
    }

    public function export()
    {
        if (!$this->requireRole([self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN])) {
            return $this->denyPermission('/AdminController/allocation', '審查委員無權匯出分發結果。');
        }

        $runId = (int) $this->request->getGet('run');
        $runModel = new AllocationRunModel();
        $run = $runId > 0 ? $runModel->find($runId) : $runModel->orderBy('id', 'DESC')->first();

        if (!$run) {
            return redirect()->to('/AdminController/allocation')->with('error', '找不到可匯出的分發批次。');
        }

        $db = db_connect();
        $results = $db->table('allocation_results')
            ->select('allocation_results.*, students.student_id as student_number, students.name as student_name')
            ->join('students', 'students.id = allocation_results.student_db_id')
            ->where('allocation_results.allocation_run_id', $run['id'])
            ->orderBy('allocation_results.overall_rank', 'ASC')
            ->get()->getResultArray();

        $filename = 'allocation_run_' . $run['id'] . '_export_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");
        fputcsv($out, ['排名', '學號', '姓名', '分數', '同分抽籤序號', '分發狀態', '錄取校系', '錄取志願順位'], ',', '"', "\\");

        foreach ($results as $r) {
            fputcsv($out, [
                $r['overall_rank'],
                $r['student_number'],
                $r['student_name'],
                $r['score_snapshot'],
                $r['lottery_order'],
                $r['result_status'] === 'admitted' ? '已錄取' : '未錄取',
                $r['university_name_snapshot'] ?: '無（未獲錄取）',
                $r['preference_rank'] ? '第 ' . $r['preference_rank'] . ' 志願' : '—',
            ], ',', '"', "\\");
        }
        fclose($out);
        if (ENVIRONMENT === 'testing') {
            return;
        }
        exit;
    }
}

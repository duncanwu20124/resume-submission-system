<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;

class AdminFeedbackController extends BaseController
{
    /**
     * 建立回饋清單查詢，讓畫面與 CSV 使用相同條件。
     */
    private function buildSubmissionQuery($db, array $filters)
    {
        $builder = $db
            ->table('feedback_submissions fs')
            ->select(
                'fs.id,
                 fs.student_db_id,
                 fs.status,
                 fs.average_score,
                 fs.submitted_at,
                 fs.created_at,
                 fs.updated_at,
                 s.student_id,
                 s.name,
                 s.email'
            )
            ->join(
                'students s',
                's.id = fs.student_db_id',
                'left'
            );

        if ($filters['keyword'] !== '') {
            $builder
                ->groupStart()
                ->like('s.name', $filters['keyword'])
                ->orLike('s.student_id', $filters['keyword'])
                ->orLike('s.email', $filters['keyword'])
                ->groupEnd();
        }

        if ($filters['score'] !== '') {
            $score = (int) $filters['score'];

            if ($score === 5) {
                $builder->where('fs.average_score >=', 4.5);
            } elseif ($score === 1) {
                $builder->where('fs.average_score >', 0);
                $builder->where('fs.average_score <', 1.5);
            } else {
                $builder->where('fs.average_score >=', $score - 0.5);
                $builder->where('fs.average_score <', $score + 0.5);
            }
        }

        switch ($filters['sort']) {
            case 'oldest':
                $builder->orderBy('fs.submitted_at', 'ASC');
                break;

            case 'score_high':
                $builder
                    ->orderBy('fs.average_score', 'DESC')
                    ->orderBy('fs.submitted_at', 'DESC');
                break;

            case 'score_low':
                $builder
                    ->orderBy('fs.average_score', 'ASC')
                    ->orderBy('fs.submitted_at', 'DESC');
                break;

            case 'name_asc':
                $builder
                    ->orderBy('s.name', 'ASC')
                    ->orderBy('fs.submitted_at', 'DESC');
                break;

            default:
                $builder->orderBy('fs.submitted_at', 'DESC');
                break;
        }

        return $builder;
    }

    /**
     * 取得並清理清單查詢條件。
     */
    private function getFilters(): array
    {
        $keyword = trim((string) $this->request->getGet('keyword'));
        $score = (string) $this->request->getGet('score');
        $sort = (string) $this->request->getGet('sort');

        if (!in_array($score, ['', '1', '2', '3', '4', '5'], true)) {
            $score = '';
        }

        $allowedSorts = [
            'latest',
            'oldest',
            'score_high',
            'score_low',
            'name_asc',
        ];

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'latest';
        }

        return [
            'keyword' => $keyword,
            'score'   => $score,
            'sort'    => $sort,
        ];
    }

    /**
     * 確認管理者是否已登入。
     */
    private function requireAdminLogin(): bool
    {
        if (session()->get('admin_logged_in') !== true) {
            session()->setFlashdata(
                'error',
                '請先登入管理員帳號。'
            );

            return false;
        }

        return true;
    }

    /**
     * 顯示所有學生回饋。
     */
    public function index()
    {
        if (!$this->requireAdminLogin()) {
            return redirect()->to('/AdminController/login');
        }

        $db = db_connect();
        $filters = $this->getFilters();

        /*
         * 取得所有回饋，並連接學生資料。
         */
        $submissions = $this
            ->buildSubmissionQuery($db, $filters)
            ->get()
            ->getResultArray();

        /*
         * 取得整體統計資料。
         */
        $statistics = $db
            ->table('feedback_submissions')
            ->select(
                'COUNT(id) AS total,
                 AVG(average_score) AS overall_average,
                 MAX(submitted_at) AS latest_submitted_at'
            )
            ->where('status', 'submitted')
            ->get()
            ->getRowArray();

        $statistics = [
            'total' => (int) ($statistics['total'] ?? 0),

            'overall_average' => isset($statistics['overall_average'])
                ? round((float) $statistics['overall_average'], 2)
                : 0,

            'latest_submitted_at' =>
                $statistics['latest_submitted_at'] ?? null,
        ];

        /*
         * 計算各分數區間的回饋數量。
         */
        $scoreDistribution = [
            5 => 0,
            4 => 0,
            3 => 0,
            2 => 0,
            1 => 0,
        ];

        foreach ($submissions as $submission) {
            $averageScore = (float) (
                $submission['average_score'] ?? 0
            );

            if ($averageScore <= 0) {
                continue;
            }

            $roundedScore = (int) round($averageScore);

            $roundedScore = max(
                1,
                min(5, $roundedScore)
            );

            $scoreDistribution[$roundedScore]++;
        }

        return view('admin/feedback_index', [
            'submissions'      => $submissions,
            'statistics'       => $statistics,
            'scoreDistribution' => $scoreDistribution,
            'filters'          => $filters,
        ]);
    }

    /**
     * 將目前搜尋、篩選及排序後的回饋清單匯出為 CSV。
     */
    public function export()
    {
        if (!$this->requireAdminLogin()) {
            return redirect()->to('/AdminController/login');
        }

        $db = db_connect();
        $filters = $this->getFilters();

        $submissions = $this
            ->buildSubmissionQuery($db, $filters)
            ->get()
            ->getResultArray();

        $stream = fopen('php://temp', 'w+');

        if ($stream === false) {
            throw new \RuntimeException('無法建立 CSV 檔案。');
        }

        // UTF-8 BOM，避免 Excel 開啟繁體中文時出現亂碼。
        fwrite($stream, "\xEF\xBB\xBF");

        fputcsv($stream, [
            '回饋編號',
            '學生姓名',
            '學號',
            'Email',
            '平均分數',
            '狀態',
            '送出時間',
        ]);

        foreach ($submissions as $submission) {
            fputcsv($stream, [
                $submission['id'] ?? '',
                $submission['name'] ?? '未知學生',
                $submission['student_id'] ?? '',
                $submission['email'] ?? '',
                number_format(
                    (float) ($submission['average_score'] ?? 0),
                    2,
                    '.',
                    ''
                ),
                ($submission['status'] ?? '') === 'submitted'
                    ? '已提交'
                    : ($submission['status'] ?? ''),
                $submission['submitted_at'] ?? '',
            ]);
        }

        rewind($stream);
        $csvContent = stream_get_contents($stream);
        fclose($stream);

        $filename = 'feedback_' . date('Ymd_His') . '.csv';

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader(
                'Content-Disposition',
                'attachment; filename="' . $filename . '"'
            )
            ->setBody($csvContent ?: '');
    }

    /**
     * 顯示一份回饋的完整 25 題答案。
     */
    public function show(int $id)
    {
        if (!$this->requireAdminLogin()) {
            return redirect()->to('/AdminController/login');
        }

        $db = db_connect();

        /*
         * 取得回饋基本資料與學生資料。
         */
        $submission = $db
            ->table('feedback_submissions fs')
            ->select(
                'fs.id,
                 fs.student_db_id,
                 fs.status,
                 fs.average_score,
                 fs.submitted_at,
                 fs.created_at,
                 fs.updated_at,
                 s.student_id,
                 s.name,
                 s.email'
            )
            ->join(
                'students s',
                's.id = fs.student_db_id',
                'left'
            )
            ->where('fs.id', $id)
            ->get()
            ->getRowArray();

        if (!$submission) {
            throw PageNotFoundException::forPageNotFound(
                '找不到這筆回饋資料。'
            );
        }

        /*
         * 取得該份回饋的所有答案。
         */
        $answers = $db
            ->table('feedback_answers')
            ->where('feedback_submission_id', $id)
            ->orderBy('question_number', 'ASC')
            ->get()
            ->getResultArray();

        /*
         * 將評分題與文字題分開，方便畫面呈現。
         */
        $ratingAnswers = [];
        $textAnswers = [];

        foreach ($answers as $answer) {
            if (($answer['answer_type'] ?? '') === 'rating') {
                $ratingAnswers[] = $answer;
            } else {
                $textAnswers[] = $answer;
            }
        }

        return view('admin/feedback_detail', [
            'submission'   => $submission,
            'answers'      => $answers,
            'ratingAnswers' => $ratingAnswers,
            'textAnswers'  => $textAnswers,
        ]);
    }
}

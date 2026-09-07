<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;

class AdminFeedbackController extends BaseController
{
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

        /*
         * 取得所有回饋，並連接學生資料。
         */
        $submissions = $db
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
            ->orderBy('fs.submitted_at', 'DESC')
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
        ]);
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
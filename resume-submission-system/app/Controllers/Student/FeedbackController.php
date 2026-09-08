<?php

namespace App\Controllers\Student;

use App\Controllers\BaseController;
use App\Models\FeedbackAnswerModel;
use App\Models\FeedbackSubmissionModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class FeedbackController extends BaseController
{
    /**
     * 顯示學生回饋表。
     */
    public function index()
    {
        return view('student/feedback', [
            'student' => [
                'id'         => session()->get('student_db_id'),
                'student_id' => session()->get('student_id'),
                'name'       => session()->get('student_name'),
                'email'      => session()->get('student_email'),
            ],
        ]);
    }

    /**
     * 儲存學生回饋。
     *
     * 同一位學生再次送出時，會更新主表，
     * 並以最新的25題回答取代先前內容。
     */
    public function save(): ResponseInterface
    {
        if (!$this->request->is('post')) {
            return $this->response
                ->setStatusCode(405)
                ->setJSON([
                    'success' => false,
                    'message' => '不允許的請求方式。',
                ]);
        }

        $studentDbId = (int) session()->get('student_db_id');

        if ($studentDbId <= 0) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON([
                    'success' => false,
                    'message' => '登入狀態已失效，請重新登入。',
                ]);
        }

        $ratingQuestions = $this->ratingQuestions();
        $textQuestions   = $this->textQuestions();

        $ratingAnswers = [];
        $errors        = [];

        foreach ($ratingQuestions as $key => $question) {
            $rawValue = $this->request->getPost($key);

            if (
                $rawValue === null
                || filter_var(
                    $rawValue,
                    FILTER_VALIDATE_INT,
                    [
                        'options' => [
                            'min_range' => 1,
                            'max_range' => 5,
                        ],
                    ]
                ) === false
            ) {
                $errors[$key] = $question['number']
                    . ' 題必須選擇 1 至 5 分。';

                continue;
            }

            $ratingAnswers[$key] = (int) $rawValue;
        }

        $textAnswers = [];

        foreach ($textQuestions as $key => $question) {
            $answer = trim((string) $this->request->getPost($key));

            if (mb_strlen($answer) > 500) {
                $errors[$key] = $question['number']
                    . ' 題不能超過 500 個字。';

                continue;
            }

            $textAnswers[$key] = $answer;
        }

        if ($errors !== []) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => '部分回饋內容格式不正確。',
                    'errors'  => $errors,
                ]);
        }

        $averageScore = round(
            array_sum($ratingAnswers) / count($ratingAnswers),
            2
        );

        $submissionModel = new FeedbackSubmissionModel();
        $answerModel     = new FeedbackAnswerModel();
        $database        = db_connect();

        $database->transBegin();

        try {
            $existingSubmission = $submissionModel
                ->findByStudentId($studentDbId);

            $submissionData = [
                'student_db_id' => $studentDbId,
                'status'        => 'submitted',
                'average_score' => $averageScore,
                'submitted_at'  => date('Y-m-d H:i:s'),
            ];

            if ($existingSubmission !== null) {
                $submissionId = (int) $existingSubmission['id'];

                if (
                    $submissionModel->update(
                        $submissionId,
                        $submissionData
                    ) === false
                ) {
                    throw new \RuntimeException(
                        '無法更新回饋主表資料。'
                    );
                }

                if (
                    $answerModel->deleteBySubmissionId(
                        $submissionId
                    ) === false
                ) {
                    throw new \RuntimeException(
                        '無法更新原有回饋答案。'
                    );
                }
            } else {
                $insertResult = $submissionModel->insert(
                    $submissionData,
                    true
                );

                if ($insertResult === false) {
                    throw new \RuntimeException(
                        '無法建立回饋主表資料。'
                    );
                }

                $submissionId = (int) $insertResult;
            }

            $answerRows = [];
            $now        = date('Y-m-d H:i:s');

            foreach ($ratingQuestions as $key => $question) {
                $answerRows[] = [
                    'feedback_submission_id' => $submissionId,
                    'question_key'            => $key,
                    'question_number'         => $question['number'],
                    'question_text'           => $question['text'],
                    'answer_type'             => 'rating',
                    'rating_value'            => $ratingAnswers[$key],
                    'text_value'              => null,
                    'created_at'              => $now,
                    'updated_at'              => $now,
                ];
            }

            foreach ($textQuestions as $key => $question) {
                $answerRows[] = [
                    'feedback_submission_id' => $submissionId,
                    'question_key'            => $key,
                    'question_number'         => $question['number'],
                    'question_text'           => $question['text'],
                    'answer_type'             => 'text',
                    'rating_value'            => null,
                    'text_value'              => $textAnswers[$key],
                    'created_at'              => $now,
                    'updated_at'              => $now,
                ];
            }

            if ($answerModel->insertBatch($answerRows) === false) {
                throw new \RuntimeException(
                    '無法儲存回饋答案。'
                );
            }

            if ($database->transStatus() === false) {
                throw new \RuntimeException(
                    '資料庫交易執行失敗。'
                );
            }

            $database->transCommit();

            return $this->response->setJSON([
                'success'       => true,
                'message'       => '回饋已成功送出，感謝您的協助！',
                'submission_id' => $submissionId,
                'average_score' => $averageScore,
            ]);
        } catch (Throwable $exception) {
            $database->transRollback();

            log_message(
                'error',
                'Feedback save failed: {message}',
                ['message' => $exception->getMessage()]
            );

            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'message' => '回饋儲存失敗，請稍後再試。',
                ]);
        }
    }

    /**
     * 第1至20題：五分量表。
     */
    private function ratingQuestions(): array
    {
        return [
            'overall_rating' => [
                'number' => 1,
                'text'   => '您對本系統的整體滿意程度如何？',
            ],
            'navigation_rating' => [
                'number' => 2,
                'text'   => '系統導覽與功能位置是否容易理解？',
            ],
            'clarity_rating' => [
                'number' => 3,
                'text'   => '頁面資訊與操作說明是否清楚？',
            ],
            'login_rating' => [
                'number' => 4,
                'text'   => '學生登入與帳號相關功能是否容易使用？',
            ],
            'resume_rating' => [
                'number' => 5,
                'text'   => '履歷上傳、預覽與下載功能是否順暢？',
            ],
            'preference_rating' => [
                'number' => 6,
                'text'   => '志願序填寫功能是否容易操作？',
            ],
            'speed_rating' => [
                'number' => 7,
                'text'   => '系統頁面載入與操作速度是否令人滿意？',
            ],
            'design_rating' => [
                'number' => 8,
                'text'   => '系統的版面設計與文字閱讀體驗是否令人滿意？',
            ],
            'stability_rating' => [
                'number' => 9,
                'text'   => '系統操作過程是否穩定，且少有錯誤或中斷？',
            ],
            'security_rating' => [
                'number' => 10,
                'text'   => '您對本系統保護個人資料與履歷內容是否有信心？',
            ],
            'confidence_rating' => [
                'number' => 11,
                'text'   => '本系統是否能讓您有信心完成履歷與志願填寫流程？',
            ],
            'recommend_rating' => [
                'number' => 12,
                'text'   => '您是否願意推薦其他學生使用本系統？',
            ],
            'mobile_rating' => [
                'number' => 13,
                'text'   => '使用手機或不同尺寸螢幕操作本系統是否方便？',
            ],
            'error_rating' => [
                'number' => 14,
                'text'   => '系統發生輸入錯誤時，提示訊息是否清楚且容易理解？',
            ],
            'help_rating' => [
                'number' => 15,
                'text'   => '系統提供的操作說明與引導是否足夠？',
            ],
            'workflow_rating' => [
                'number' => 16,
                'text'   => '從登入到完成各項作業的整體流程是否流暢？',
            ],
            'result_rating' => [
                'number' => 17,
                'text'   => '分發結果與相關資訊的呈現方式是否清楚？',
            ],
            'accessibility_rating' => [
                'number' => 18,
                'text'   => '按鈕、文字大小與色彩配置是否容易辨識與操作？',
            ],
            'reuse_rating' => [
                'number' => 19,
                'text'   => '若未來有類似需求，您是否願意再次使用本系統？',
            ],
            'value_rating' => [
                'number' => 20,
                'text'   => '您認為本系統對完成甄選相關作業是否具有實際幫助？',
            ],
        ];
    }

    /**
     * 第21至25題：文字回饋。
     */
    private function textQuestions(): array
    {
        return [
            'favorite_feature' => [
                'number' => 21,
                'text'   => '您最常使用或認為最實用的功能是什麼？',
            ],
            'problem_description' => [
                'number' => 22,
                'text'   => '使用過程中是否遇到任何問題？',
            ],
            'desired_feature' => [
                'number' => 23,
                'text'   => '您希望本系統未來新增什麼功能？',
            ],
            'suggestion' => [
                'number' => 24,
                'text'   => '您對本系統還有什麼改善建議？',
            ],
            'other_comment' => [
                'number' => 25,
                'text'   => '是否還有其他使用感受或意見想告訴我們？',
            ],
        ];
    }
}
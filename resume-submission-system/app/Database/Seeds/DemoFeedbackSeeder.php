<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use RuntimeException;

class DemoFeedbackSeeder extends Seeder
{
    public function run()
    {
        $db = db_connect();

        /*
         * 20 題評分題。
         */
        $ratingQuestions = [
            'overall_rating' =>
                '您對本系統的整體滿意程度如何？',

            'navigation_rating' =>
                '系統導覽與功能位置是否容易理解？',

            'clarity_rating' =>
                '頁面資訊與操作說明是否清楚？',

            'login_rating' =>
                '學生登入與帳號相關功能是否容易使用？',

            'resume_rating' =>
                '履歷上傳、預覽與下載功能是否順暢？',

            'preference_rating' =>
                '志願序填寫功能是否容易操作？',

            'speed_rating' =>
                '系統頁面載入與操作速度是否令人滿意？',

            'design_rating' =>
                '系統版面設計與文字閱讀體驗是否令人滿意？',

            'stability_rating' =>
                '系統操作過程是否穩定，且少有錯誤或中斷？',

            'security_rating' =>
                '您對本系統保護個人資料與履歷內容是否有信心？',

            'confidence_rating' =>
                '本系統是否能讓您有信心完成履歷與志願填寫流程？',

            'recommend_rating' =>
                '您是否願意推薦其他學生使用本系統？',

            'mobile_rating' =>
                '使用手機或不同尺寸螢幕操作本系統是否方便？',

            'error_rating' =>
                '系統發生輸入錯誤時，提示訊息是否清楚且容易理解？',

            'help_rating' =>
                '系統提供的操作說明與引導是否足夠？',

            'workflow_rating' =>
                '從登入到完成各項作業的整體流程是否流暢？',

            'result_rating' =>
                '分發結果與相關資訊的呈現方式是否清楚？',

            'accessibility_rating' =>
                '按鈕、文字大小與色彩配置是否容易辨識與操作？',

            'reuse_rating' =>
                '若未來有類似需求，您是否願意再次使用本系統？',

            'value_rating' =>
                '您認為本系統對完成甄選相關作業是否具有實際幫助？',
        ];

        /*
         * 5 題文字題。
         */
        $textQuestions = [
            'favorite_feature' =>
                '您最常使用或認為最實用的功能是什麼？',

            'problem_description' =>
                '使用過程中是否遇到任何問題？',

            'desired_feature' =>
                '您希望本系統未來新增什麼功能？',

            'suggestion' =>
                '您對本系統還有什麼改善建議？',

            'other_comment' =>
                '是否還有其他使用感受或意見想告訴我們？',
        ];

        /*
         * 示範文字回答。
         */
        $favoriteAnswers = [
            '履歷上傳與預覽功能最實用，可以很快確認檔案是否正確。',
            '最常使用志願序填寫功能，操作方式簡單清楚。',
            '履歷下載與查看功能很方便。',
            '喜歡系統的進度提示，可以知道自己還有哪些步驟未完成。',
            '學生控制台資訊集中，使用起來很方便。',
        ];

        $problemAnswers = [
            '目前沒有遇到明顯問題。',
            '第一次使用時不太確定資料是否已經儲存。',
            '部分頁面在手機上需要上下滑動比較久。',
            '上傳檔案後等待時間稍長，但仍可正常完成。',
            '偶爾會忘記功能的位置，熟悉後就沒有問題。',
        ];

        $desiredAnswers = [
            '希望未來可以加入 AI 履歷建議功能。',
            '希望增加提交截止日期提醒。',
            '可以加入即時客服或 AI 指引機器人。',
            '希望能夠顯示更完整的申請進度。',
            '希望未來提供 Email 通知與結果提醒。',
        ];

        $suggestionAnswers = [
            '建議重要按鈕可以使用更明顯的顏色。',
            '整體已經很清楚，希望繼續維持簡潔的設計。',
            '建議增加新手操作說明或導覽。',
            '希望手機版本的按鈕能夠再大一點。',
            '建議提交成功後顯示更明顯的確認訊息。',
        ];

        $otherAnswers = [
            '整體使用體驗不錯，功能也很完整。',
            '系統操作簡單，能減少填寫資料的時間。',
            '目前沒有其他意見，謝謝開發團隊。',
            '頁面設計清楚，第一次使用也能快速上手。',
            '期待未來加入更多智慧化功能。',
        ];

        /*
         * 找出已經有回饋的學生，避免覆蓋真實資料。
         */
        $existingRows = $db
            ->table('feedback_submissions')
            ->select('student_db_id')
            ->get()
            ->getResultArray();

        $existingStudentIds = array_map(
            'intval',
            array_column($existingRows, 'student_db_id')
        );

        /*
         * 從現有學生中選取尚未填寫回饋的 20 人。
         */
        $studentBuilder = $db
            ->table('students')
            ->select('id, student_id, name, email')
            ->where('student_id !=', '615415015')
            ->orderBy('id', 'ASC');

        if (!empty($existingStudentIds)) {
            $studentBuilder->whereNotIn(
                'id',
                $existingStudentIds
            );
        }

        $students = $studentBuilder
            ->limit(20)
            ->get()
            ->getResultArray();

        if (empty($students)) {
            echo "沒有可新增示範回饋的學生。\n";
            return;
        }

        $db->transStart();

        foreach ($students as $studentIndex => $student) {
            $ratingRows = [];
            $ratingTotal = 0;
            $questionNumber = 1;

            /*
             * 產生較自然的 2～5 分分布。
             */
            foreach ($ratingQuestions as $questionKey => $questionText) {
                $pattern = (
                    ($studentIndex * 7)
                    + ($questionNumber * 3)
                ) % 10;

                if ($pattern === 0) {
                    $ratingValue = 2;
                } elseif ($pattern <= 2) {
                    $ratingValue = 3;
                } elseif ($pattern <= 7) {
                    $ratingValue = 4;
                } else {
                    $ratingValue = 5;
                }

                $ratingTotal += $ratingValue;

                $ratingRows[] = [
                    'question_key'    => $questionKey,
                    'question_number' => $questionNumber,
                    'question_text'   => $questionText,
                    'answer_type'     => 'rating',
                    'rating_value'    => $ratingValue,
                    'text_value'      => null,
                ];

                $questionNumber++;
            }

            $averageScore = round(
                $ratingTotal / count($ratingQuestions),
                2
            );

            /*
             * 讓提交時間分散在最近數小時，看起來更自然。
             */
            $submittedTimestamp = strtotime(
                '-' . ($studentIndex + 1) . ' hours'
            );

            $submittedAt = date(
                'Y-m-d H:i:s',
                $submittedTimestamp
            );

            /*
             * 建立一筆回饋主紀錄。
             */
            $db->table('feedback_submissions')->insert([
                'student_db_id' => (int) $student['id'],
                'status'        => 'submitted',
                'average_score' => $averageScore,
                'submitted_at'  => $submittedAt,
                'created_at'    => $submittedAt,
                'updated_at'    => $submittedAt,
            ]);

            $submissionId = $db->insertID();

            /*
             * 加入 20 題評分答案。
             */
            $answerRows = [];

            foreach ($ratingRows as $ratingRow) {
                $answerRows[] = array_merge(
                    $ratingRow,
                    [
                        'feedback_submission_id' => $submissionId,
                        'created_at'              => $submittedAt,
                        'updated_at'              => $submittedAt,
                    ]
                );
            }

            /*
             * 加入 5 題文字回答。
             */
            $selectedTextAnswers = [
                'favorite_feature' =>
                    $favoriteAnswers[
                        $studentIndex % count($favoriteAnswers)
                    ],

                'problem_description' =>
                    $problemAnswers[
                        $studentIndex % count($problemAnswers)
                    ],

                'desired_feature' =>
                    $desiredAnswers[
                        $studentIndex % count($desiredAnswers)
                    ],

                'suggestion' =>
                    $suggestionAnswers[
                        $studentIndex % count($suggestionAnswers)
                    ],

                'other_comment' =>
                    $otherAnswers[
                        $studentIndex % count($otherAnswers)
                    ],
            ];

            foreach ($textQuestions as $questionKey => $questionText) {
                $answerRows[] = [
                    'feedback_submission_id' => $submissionId,
                    'question_key'            => $questionKey,
                    'question_number'         => $questionNumber,
                    'question_text'           => $questionText,
                    'answer_type'             => 'text',
                    'rating_value'            => null,
                    'text_value'              =>
                        $selectedTextAnswers[$questionKey],
                    'created_at'              => $submittedAt,
                    'updated_at'              => $submittedAt,
                ];

                $questionNumber++;
            }

            $db
                ->table('feedback_answers')
                ->insertBatch($answerRows);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new RuntimeException(
                '示範回饋資料寫入失敗，交易已回滾。'
            );
        }

        echo '成功新增 '
            . count($students)
            . " 份示範回饋。\n";
    }
}
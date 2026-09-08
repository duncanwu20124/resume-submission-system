<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use RuntimeException;

class DemoFeedbackSeeder extends Seeder
{
    public function run()
    {
        $db = db_connect();

        $ratingQuestions = [
            'overall_rating'       => '您對本系統的整體滿意程度如何？',
            'navigation_rating'    => '系統導覽與功能位置是否容易理解？',
            'clarity_rating'       => '頁面資訊與操作說明是否清楚？',
            'login_rating'         => '學生登入與帳號相關功能是否容易使用？',
            'resume_rating'        => '履歷上傳、預覽與下載功能是否順暢？',
            'preference_rating'    => '志願序填寫功能是否容易操作？',
            'speed_rating'         => '系統頁面載入與操作速度是否令人滿意？',
            'design_rating'        => '系統版面設計與文字閱讀體驗是否令人滿意？',
            'stability_rating'     => '系統操作過程是否穩定，且少有錯誤或中斷？',
            'security_rating'      => '您對本系統保護個人資料與履歷內容是否有信心？',
            'confidence_rating'    => '本系統是否能讓您有信心完成履歷與志願填寫流程？',
            'recommend_rating'     => '您是否願意推薦其他學生使用本系統？',
            'mobile_rating'        => '使用手機或不同尺寸螢幕操作本系統是否方便？',
            'error_rating'         => '系統發生輸入錯誤時，提示訊息是否清楚且容易理解？',
            'help_rating'          => '系統提供的操作說明與引導是否足夠？',
            'workflow_rating'      => '從登入到完成各項作業的整體流程是否流暢？',
            'result_rating'        => '分發結果與相關資訊的呈現方式是否清楚？',
            'accessibility_rating' => '按鈕、文字大小與色彩配置是否容易辨識與操作？',
            'reuse_rating'         => '若未來有類似需求，您是否願意再次使用本系統？',
            'value_rating'         => '您認為本系統對完成甄選相關作業是否具有實際幫助？',
        ];

        $textQuestions = [
            'favorite_feature'    => '您最常使用或認為最實用的功能是什麼？',
            'problem_description' => '使用過程中是否遇到任何問題？',
            'desired_feature'     => '您希望本系統未來新增什麼功能？',
            'suggestion'          => '您對本系統還有什麼改善建議？',
            'other_comment'       => '是否還有其他使用感受或意見想告訴我們？',
        ];

        $textAnswerSets = [
            [
                '履歷上傳與預覽功能最實用，可以快速確認檔案是否正確。',
                '目前沒有遇到明顯問題，整體操作很順暢。',
                '希望未來可以加入 AI 履歷建議功能。',
                '建議提交成功後顯示更明顯的確認訊息。',
                '整體使用體驗不錯，功能也很完整。',
            ],
            [
                '最常使用志願序填寫功能，操作方式簡單清楚。',
                '第一次使用時不太確定資料是否已經儲存。',
                '希望增加提交截止日期提醒。',
                '建議增加新手操作說明或導覽。',
                '系統操作簡單，能減少填寫資料的時間。',
            ],
            [
                '履歷下載與查看功能很方便。',
                '部分頁面在手機上需要上下滑動比較久。',
                '希望未來提供 Email 通知與結果提醒。',
                '希望手機版的按鈕能夠再大一點。',
                '頁面設計清楚，第一次使用也能快速上手。',
            ],
            [
                '系統的進度提示很實用，可以掌握尚未完成的步驟。',
                '上傳檔案後等待時間稍長，但仍可正常完成。',
                '希望能顯示更完整的申請進度。',
                '建議重要按鈕使用更明顯的顏色。',
                '期待未來加入更多智慧化功能。',
            ],
            [
                '學生控制台資訊集中，查找功能很方便。',
                '偶爾會忘記功能的位置，熟悉後就沒有問題。',
                '希望加入即時客服或智慧指引功能。',
                '整體已經很清楚，希望繼續維持簡潔設計。',
                '目前沒有其他意見，謝謝開發團隊。',
            ],
        ];

        /*
         * 七種評分輪廓，平均分數約為 4.80、4.10、3.90、3.30、
         * 3.00、2.20、1.30。再依學生位置輪替題目順序，使資料更自然。
         */
        $ratingProfiles = [
            'excellent' => [5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 4, 4, 4, 4],
            'good_high' => [5, 5, 5, 5, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 3, 3],
            'good_low'  => [5, 5, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 3, 3, 3, 3],
            'average'   => [4, 4, 4, 4, 4, 4, 4, 4, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 2, 2],
            'fair'      => [4, 4, 4, 4, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 2, 2, 2, 2],
            'low'       => [3, 3, 3, 3, 3, 3, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 1, 1],
            'very_low'  => [2, 2, 2, 2, 2, 2, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1],
        ];

        /*
         * 題目滿意度由高至低的順序（0 代表第 1 題）。
         * 每位學生仍使用原本評分輪廓中的同一組分數，只改變分數分配到哪一題，
         * 因此每位學生的平均分數與 1～5 分人數分布都不會改變。
         */
        $questionPriority = [
            0,  // 整體滿意度
            4,  // 履歷上傳、預覽與下載
            15, // 整體流程
            16, // 分發結果呈現
            19, // 實際幫助
            1,  // 系統導覽
            2,  // 資訊與說明清楚度
            3,  // 登入與帳號功能
            5,  // 志願序填寫
            7,  // 版面與閱讀體驗
            9,  // 個資保護信心
            10, // 完成流程信心
            18, // 再次使用意願
            11, // 推薦意願
            8,  // 穩定性
            17, // 按鈕、文字與色彩辨識
            14, // 操作說明與引導
            13, // 錯誤提示
            6,  // 載入速度
            12, // 手機與不同螢幕尺寸
        ];

        /*
         * 4000 份資料的分布：
         * 5 分 600 份、4 分 2000 份、3 分 1000 份、2 分 320 份、1 分 80 份。
         */
        $profilePlan = array_merge(
            array_fill(0, 600, 'excellent'),
            array_fill(0, 1000, 'good_high'),
            array_fill(0, 1000, 'good_low'),
            array_fill(0, 500, 'average'),
            array_fill(0, 500, 'fair'),
            array_fill(0, 320, 'low'),
            array_fill(0, 80, 'very_low')
        );

        /* 保留學號 615415015，讓 Demo 當天可以親自填寫。 */
        $students = $db
            ->table('students')
            ->select('id, student_id, name, email')
            ->where('student_id !=', '615415015')
            ->orderBy('id', 'ASC')
            ->limit(4000)
            ->get()
            ->getResultArray();

        if (count($students) < 4000) {
            throw new RuntimeException('可使用的學生不足 4000 人，無法建立完整示範資料。');
        }

        $db->transStart();

        /* 僅清除回饋資料，不影響學生、履歷、志願序及其他系統資料。 */
        $db->table('feedback_answers')->emptyTable();
        $db->table('feedback_submissions')->emptyTable();

        /* 固定種子，確保每次重建的日期分布一致，方便展示與測試。 */
        mt_srand(20260908);

        foreach ($students as $studentIndex => $student) {
            $profile = $ratingProfiles[$profilePlan[$studentIndex]];

            /*
             * 將較高分安排到較受肯定的題目，並對相鄰題目做少量交換，
             * 讓題目平均分數有層次，但不會顯得每份問卷完全相同。
             */
            $studentPriority = $questionPriority;

            for ($pairStart = 0; $pairStart < 20; $pairStart += 2) {
                if (($studentIndex + intdiv($pairStart, 2)) % 4 === 0) {
                    [$studentPriority[$pairStart], $studentPriority[$pairStart + 1]] = [
                        $studentPriority[$pairStart + 1],
                        $studentPriority[$pairStart],
                    ];
                }
            }

            $ratings = array_fill(0, 20, 1);

            foreach ($studentPriority as $rank => $questionIndex) {
                $ratings[$questionIndex] = $profile[$rank];
            }

            $averageScore = round(array_sum($ratings) / count($ratings), 2);

            /* 將回饋自然地分散在最近 120 天，而不是固定每小時一筆。 */
            $submittedAt = date(
                'Y-m-d H:i:s',
                time() - mt_rand(0, (120 * 24 * 60 * 60) - 1)
            );

            $db->table('feedback_submissions')->insert([
                'student_db_id' => (int) $student['id'],
                'status'        => 'submitted',
                'average_score' => $averageScore,
                'submitted_at'  => $submittedAt,
                'created_at'    => $submittedAt,
                'updated_at'    => $submittedAt,
            ]);

            $submissionId = $db->insertID();
            $answerRows = [];
            $questionNumber = 1;

            foreach ($ratingQuestions as $questionKey => $questionText) {
                $answerRows[] = [
                    'feedback_submission_id' => $submissionId,
                    'question_key'            => $questionKey,
                    'question_number'         => $questionNumber,
                    'question_text'           => $questionText,
                    'answer_type'             => 'rating',
                    'rating_value'            => $ratings[$questionNumber - 1],
                    'text_value'              => null,
                    'created_at'              => $submittedAt,
                    'updated_at'              => $submittedAt,
                ];

                $questionNumber++;
            }

            $selectedTexts = $textAnswerSets[$studentIndex % count($textAnswerSets)];
            $textIndex = 0;

            foreach ($textQuestions as $questionKey => $questionText) {
                $answerRows[] = [
                    'feedback_submission_id' => $submissionId,
                    'question_key'            => $questionKey,
                    'question_number'         => $questionNumber,
                    'question_text'           => $questionText,
                    'answer_type'             => 'text',
                    'rating_value'            => null,
                    'text_value'              => $selectedTexts[$textIndex],
                    'created_at'              => $submittedAt,
                    'updated_at'              => $submittedAt,
                ];

                $questionNumber++;
                $textIndex++;
            }

            $db->table('feedback_answers')->insertBatch($answerRows);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new RuntimeException('示範回饋資料寫入失敗，交易已回滾。');
        }

        echo "成功重新建立 4000 份繁體中文示範回饋。\n";
    }
}

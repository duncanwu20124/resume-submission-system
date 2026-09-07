<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>回饋內容 | 管理員系統</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/admin.css') ?>"
    >

    <style>
        .feedback-detail-page {
            padding-bottom: 60px;
        }

        .detail-topbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 24px;
        }

        .detail-topbar h2 {
            margin: 0 0 8px;
            color: #0f172a;
            font-size: 28px;
        }

        .detail-topbar p {
            margin: 0;
            color: #64748b;
        }

        .back-button {
            display: inline-block;
            padding: 10px 16px;
            color: #4338ca;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
            border: 1px solid #c7d2fe;
            border-radius: 10px;
            background: #eef2ff;
        }

        .back-button:hover {
            color: #ffffff;
            background: #4f46e5;
        }

        .student-summary {
            display: grid;
            grid-template-columns: 1.4fr 1fr 1fr;
            gap: 18px;
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
        }

        .summary-item {
            min-width: 0;
        }

        .summary-label {
            display: block;
            margin-bottom: 7px;
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
        }

        .summary-value {
            display: block;
            overflow-wrap: anywhere;
            color: #0f172a;
            font-size: 17px;
            font-weight: 700;
        }

        .average-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 18px;
            padding: 24px;
            margin-bottom: 24px;
            color: #ffffff;
            border-radius: 16px;
            background: linear-gradient(135deg, #4f46e5, #3730a3);
            box-shadow: 0 8px 24px rgba(79, 70, 229, 0.22);
        }

        .average-card h3 {
            margin: 0 0 6px;
            font-size: 18px;
        }

        .average-card p {
            margin: 0;
            opacity: 0.85;
        }

        .average-score {
            font-size: 34px;
            font-weight: 700;
            white-space: nowrap;
        }

        .answer-section {
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
        }

        .answer-section h3 {
            margin: 0 0 8px;
            color: #0f172a;
            font-size: 20px;
        }

        .section-description {
            margin: 0 0 22px;
            color: #64748b;
            font-size: 14px;
        }

        .rating-answer-list {
            display: grid;
            gap: 12px;
        }

        .rating-answer {
            display: grid;
            grid-template-columns: 52px 1fr 110px;
            align-items: center;
            gap: 14px;
            padding: 16px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #f8fafc;
        }

        .question-number {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            color: #4338ca;
            font-weight: 700;
            border-radius: 50%;
            background: #e0e7ff;
        }

        .question-text {
            color: #1e293b;
            font-weight: 600;
            line-height: 1.6;
        }

        .rating-score {
            padding: 8px 12px;
            text-align: center;
            color: #4338ca;
            font-weight: 700;
            border-radius: 999px;
            background: #eef2ff;
            white-space: nowrap;
        }

        .rating-label {
            display: block;
            margin-top: 4px;
            color: #64748b;
            font-size: 12px;
            font-weight: 500;
        }

        .text-answer-list {
            display: grid;
            gap: 16px;
        }

        .text-answer {
            overflow: hidden;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
        }

        .text-answer__question {
            padding: 14px 18px;
            color: #1e293b;
            font-weight: 700;
            line-height: 1.6;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .text-answer__content {
            min-height: 74px;
            padding: 18px;
            color: #334155;
            line-height: 1.8;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            background: #ffffff;
        }

        .empty-answer {
            color: #94a3b8;
            font-style: italic;
        }

        @media (max-width: 850px) {
            .student-summary {
                grid-template-columns: 1fr;
            }

            .rating-answer {
                grid-template-columns: 46px 1fr;
            }

            .rating-score {
                grid-column: 2;
                justify-self: start;
            }
        }

        @media (max-width: 600px) {
            .detail-topbar {
                flex-direction: column;
            }

            .average-card {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>

<body>

<header class="sys-navbar">
    <div class="sys-navbar__inner">
        <div class="sys-navbar__brand">

            <svg
                width="26"
                height="26"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                style="color: var(--sys-primary);"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 14l9-5-9-5-9 5 9 5z"
                ></path>

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"
                ></path>
            </svg>

            <h1 class="sys-navbar__title">
                管理員系統
            </h1>

            <span class="sys-navbar__badge">
                Admin Portal
            </span>
        </div>
    </div>
</header>

<main class="admin-shell admin-shell--with-sidebar feedback-detail-page">

    <?= view('partials/admin_sidebar') ?>

    <section class="detail-topbar">
        <div>
            <h2>學生回饋內容</h2>

            <p>
                查看學生提交的完整系統評分與文字建議。
            </p>
        </div>

        <a
            class="back-button"
            href="/AdminController/feedback"
        >
            ← 返回回饋清單
        </a>
    </section>

    <section class="student-summary">

        <div class="summary-item">
            <span class="summary-label">學生姓名</span>

            <strong class="summary-value">
                <?= esc($submission['name'] ?? '未知學生') ?>
            </strong>
        </div>

        <div class="summary-item">
            <span class="summary-label">學號</span>

            <strong class="summary-value">
                <?= esc($submission['student_id'] ?? '—') ?>
            </strong>
        </div>

        <div class="summary-item">
            <span class="summary-label">Email</span>

            <strong class="summary-value">
                <?= esc($submission['email'] ?? '—') ?>
            </strong>
        </div>

        <div class="summary-item">
            <span class="summary-label">回饋編號</span>

            <strong class="summary-value">
                #<?= esc($submission['id']) ?>
            </strong>
        </div>

        <div class="summary-item">
            <span class="summary-label">提交狀態</span>

            <strong class="summary-value">
                <?= ($submission['status'] ?? '') === 'submitted'
                    ? '已提交'
                    : esc($submission['status'] ?? '未知') ?>
            </strong>
        </div>

        <div class="summary-item">
            <span class="summary-label">送出時間</span>

            <strong class="summary-value">
                <?= !empty($submission['submitted_at'])
                    ? esc($submission['submitted_at'])
                    : '—' ?>
            </strong>
        </div>

    </section>

    <section class="average-card">
        <div>
            <h3>本次回饋平均分數</h3>
            <p>依照所有評分題計算</p>
        </div>

        <div class="average-score">
            <?= number_format(
                (float) ($submission['average_score'] ?? 0),
                2
            ) ?>
            / 5
        </div>
    </section>

    <section class="answer-section">
        <h3>評分題目</h3>

        <p class="section-description">
            共 <?= count($ratingAnswers ?? []) ?> 題評分題。
        </p>

        <div class="rating-answer-list">
            <?php foreach ($ratingAnswers as $answer): ?>
                <?php
                $ratingValue = (int) (
                    $answer['rating_value'] ?? 0
                );

                $ratingLabels = [
                    1 => '非常不滿意',
                    2 => '不滿意',
                    3 => '普通',
                    4 => '滿意',
                    5 => '非常滿意',
                ];
                ?>

                <article class="rating-answer">

                    <span class="question-number">
                        <?= esc($answer['question_number']) ?>
                    </span>

                    <div class="question-text">
                        <?= esc($answer['question_text']) ?>
                    </div>

                    <div class="rating-score">
                        <?= esc($ratingValue) ?> 分

                        <span class="rating-label">
                            <?= esc(
                                $ratingLabels[$ratingValue]
                                ?? '未評分'
                            ) ?>
                        </span>
                    </div>

                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="answer-section">
        <h3>文字回饋</h3>

        <p class="section-description">
            學生填寫的使用情況與改善建議。
        </p>

        <div class="text-answer-list">
            <?php foreach ($textAnswers as $answer): ?>
                <?php
                $textValue = trim(
                    (string) ($answer['text_value'] ?? '')
                );
                ?>

                <article class="text-answer">

                    <div class="text-answer__question">
                        <?= esc($answer['question_number']) ?>.
                        <?= esc($answer['question_text']) ?>
                    </div>

                    <div class="text-answer__content">
                        <?php if ($textValue !== ''): ?>
                            <?= nl2br(esc($textValue)) ?>
                        <?php else: ?>
                            <span class="empty-answer">
                                學生未填寫此題
                            </span>
                        <?php endif; ?>
                    </div>

                </article>
            <?php endforeach; ?>
        </div>
    </section>

</main>

</body>
</html>
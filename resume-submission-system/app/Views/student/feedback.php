<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>使用回饋表 | 學生履歷管理系統</title>

    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --primary-light: #eef2ff;
            --background: #f8fafc;
            --surface: #ffffff;
            --text: #0f172a;
            --muted: #64748b;
            --border: #e2e8f0;
            --success: #059669;
            --success-light: #ecfdf5;
            --warning: #d97706;
            --warning-light: #fffbeb;
            --danger: #dc2626;
            --danger-light: #fef2f2;
            --shadow: 0 4px 15px rgba(15, 23, 42, .05);
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, "Microsoft JhengHei", sans-serif;
            color: var(--text);
            background: var(--background);
        }

        .navbar {
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            padding: 18px 32px;
            background: rgba(255, 255, 255, .96);
            border-bottom: 1px solid var(--border);
            backdrop-filter: blur(10px);
        }

        .brand {
            color: var(--primary);
            font-size: 20px;
            font-weight: 700;
        }

        .back-link {
            color: var(--muted);
            font-weight: 600;
            text-decoration: none;
            transition: color .2s;
        }

        .back-link:hover {
            color: var(--primary);
        }

        .container {
            width: 100%;
            max-width: 1080px;
            margin: 36px auto;
            padding: 0 20px 60px;
        }

        .header-card {
            padding: 36px 40px;
            margin-bottom: 24px;
            color: white;
            border-radius: 20px;
            background: linear-gradient(135deg, #4f46e5, #3730a3);
            box-shadow: 0 12px 25px rgba(79, 70, 229, .22);
        }

        .header-card h1 {
            margin: 0 0 12px;
            font-size: 32px;
        }

        .header-card p {
            margin: 0;
            line-height: 1.7;
            opacity: .9;
        }

        .card {
            padding: 30px;
            margin-bottom: 24px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: var(--shadow);
        }

        .student-info {
            display: flex;
            flex-wrap: wrap;
            gap: 14px 32px;
            color: var(--muted);
        }

        .student-info strong {
            color: var(--text);
        }

        .progress-card {
            padding: 22px 30px;
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 12px;
        }

        .progress-header span {
            color: var(--muted);
            font-size: 14px;
        }

        .progress-track {
            width: 100%;
            height: 11px;
            overflow: hidden;
            background: #e2e8f0;
            border-radius: 999px;
        }

        .progress-bar {
            width: 0;
            height: 100%;
            background: linear-gradient(90deg, #4f46e5, #818cf8);
            border-radius: 999px;
            transition: width .3s ease;
        }

        .draft-status {
            margin: 12px 0 0;
            color: var(--muted);
            font-size: 13px;
        }

        .draft-status.saved {
            color: var(--success);
        }

        .section-heading {
            margin: 0 0 4px;
            font-size: 22px;
        }

        .section-description {
            margin: 0 0 18px;
            color: var(--muted);
            line-height: 1.6;
        }

        .required-mark {
            color: var(--danger);
        }

        .question {
            padding: 25px 0;
            border-bottom: 1px solid var(--border);
        }

        .question:last-of-type {
            border-bottom: 0;
        }

        .question-title {
            margin-bottom: 15px;
            font-weight: 700;
            line-height: 1.6;
        }

        .rating-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .rating-option {
            min-width: 118px;
            padding: 11px 15px;
            text-align: center;
            cursor: pointer;
            background: white;
            border: 1px solid var(--border);
            border-radius: 10px;
            transition:
                border-color .2s,
                background-color .2s,
                color .2s,
                transform .2s,
                box-shadow .2s;
        }

        .rating-option:hover {
            border-color: var(--primary);
            background: var(--primary-light);
            transform: translateY(-1px);
        }

        .rating-option:has(input:checked) {
            color: white;
            background: var(--primary);
            border-color: var(--primary);
            box-shadow: 0 5px 12px rgba(79, 70, 229, .22);
        }

        .rating-option input {
            margin-right: 5px;
            accent-color: var(--primary);
        }

        .text-area-wrapper {
            position: relative;
        }

        textarea {
            width: 100%;
            min-height: 120px;
            padding: 14px 14px 32px;
            resize: vertical;
            color: var(--text);
            background: white;
            border: 1px solid var(--border);
            border-radius: 10px;
            outline: none;
            font: inherit;
            line-height: 1.6;
            transition: border-color .2s, box-shadow .2s;
        }

        textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, .1);
        }

        .character-count {
            position: absolute;
            right: 12px;
            bottom: 10px;
            color: var(--muted);
            font-size: 12px;
        }

        .low-score-notice {
            display: none;
            padding: 14px 16px;
            margin-top: 20px;
            color: #92400e;
            line-height: 1.6;
            background: var(--warning-light);
            border: 1px solid #fde68a;
            border-radius: 10px;
        }

        .actions {
            display: flex;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 28px;
        }

        .btn {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 7px;
            padding: 12px 22px;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
            border-radius: 10px;
            transition:
                background-color .2s,
                border-color .2s,
                transform .2s;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-primary {
            color: white;
            background: var(--primary);
        }

        .btn-primary:hover {
            background: var(--primary-hover);
        }

        .btn-secondary {
            color: #334155;
            background: white;
            border-color: var(--border);
        }

        .btn-secondary:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .btn-danger {
            color: var(--danger);
            background: white;
            border-color: #fecaca;
        }

        .btn-danger:hover {
            background: var(--danger-light);
        }

        .notice {
            margin: 20px 0 0;
            color: var(--warning);
            font-size: 14px;
            line-height: 1.6;
        }

        .preview-card {
            display: none;
            border-color: #a7f3d0;
        }

        .preview-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 20px;
            padding-bottom: 20px;
            margin-bottom: 10px;
            border-bottom: 1px solid var(--border);
        }

        .preview-header h2 {
            margin: 0 0 8px;
            color: var(--success);
        }

        .preview-header p {
            margin: 0;
            color: var(--muted);
        }

        .average-score-box {
            min-width: 140px;
            padding: 16px 20px;
            text-align: center;
            color: #065f46;
            background: var(--success-light);
            border: 1px solid #a7f3d0;
            border-radius: 12px;
        }

        .average-score-value {
            display: block;
            margin-bottom: 4px;
            font-size: 28px;
            font-weight: 800;
        }

        .average-score-label {
            font-size: 13px;
        }

        .answer-row {
            padding: 16px 0;
            border-bottom: 1px solid var(--border);
        }

        .answer-row:last-child {
            border-bottom: 0;
        }

        .answer-title {
            margin-bottom: 7px;
            font-weight: 700;
            line-height: 1.5;
        }

        .answer-value {
            color: var(--muted);
            line-height: 1.6;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .score-badge {
            display: inline-block;
            min-width: 75px;
            padding: 5px 10px;
            color: white;
            text-align: center;
            font-size: 13px;
            font-weight: 700;
            background: var(--primary);
            border-radius: 999px;
        }

        .score-badge.low {
            background: var(--danger);
        }

        .preview-warning {
            display: none;
            padding: 16px;
            margin: 18px 0 5px;
            color: #92400e;
            line-height: 1.7;
            background: var(--warning-light);
            border: 1px solid #fde68a;
            border-radius: 10px;
        }

        @media (max-width: 760px) {
            .navbar {
                padding: 16px 18px;
            }

            .brand {
                font-size: 17px;
            }

            .container {
                margin-top: 22px;
                padding: 0 14px 40px;
            }

            .header-card,
            .card {
                padding: 24px 20px;
            }

            .header-card h1 {
                font-size: 27px;
            }

            .rating-group {
                display: grid;
                grid-template-columns: 1fr 1fr;
            }

            .rating-option {
                min-width: 0;
            }

            .actions {
                flex-direction: column-reverse;
            }

            .btn {
                width: 100%;
            }
        }

        @media print {
            body {
                background: white;
            }

            .navbar,
            .header-card,
            .progress-card,
            #feedbackForm,
            .preview-actions {
                display: none !important;
            }

            .container {
                max-width: none;
                margin: 0;
                padding: 0;
            }

            .preview-card {
                display: block !important;
                border: 0;
                box-shadow: none;
            }
        }
    </style>
</head>

<body>
<nav class="navbar">
    <div class="brand">學生履歷管理 Portal</div>

    <a href="<?= site_url('student/dashboard') ?>" class="back-link">
        ← 返回學生控制台
    </a>
</nav>

<main class="container">
    <section class="header-card">
        <h1>系統使用回饋表</h1>

        <p>
            您的意見將協助我們改善學生履歷與志願填寫系統。
            評分題採五分量表，1 分表示非常不滿意，5 分表示非常滿意。
        </p>
    </section>

    <section class="card">
        <div class="student-info">
            <span>
                姓名：
                <strong><?= esc($student['name'] ?? '') ?></strong>
            </span>

            <span>
                學號：
                <strong><?= esc($student['student_id'] ?? '') ?></strong>
            </span>

            <span>
                Email：
                <strong><?= esc($student['email'] ?? '') ?></strong>
            </span>
        </div>
    </section>

    <?php
    $questions = [
        'overall_rating'    => '1. 您對本系統的整體滿意程度如何？',
        'navigation_rating' => '2. 系統導覽與功能位置是否容易理解？',
        'clarity_rating'    => '3. 頁面資訊與操作說明是否清楚？',
        'login_rating'      => '4. 學生登入與帳號相關功能是否容易使用？',
        'resume_rating'     => '5. 履歷上傳、預覽與下載功能是否順暢？',
        'preference_rating' => '6. 志願序填寫功能是否容易操作？',
        'speed_rating'      => '7. 系統頁面載入與操作速度是否令人滿意？',
        'recommend_rating'  => '8. 您是否願意推薦其他學生使用本系統？',
    ];

    $ratingLabels = [
        1 => '非常不滿意',
        2 => '不滿意',
        3 => '普通',
        4 => '滿意',
        5 => '非常滿意',
    ];
    ?>

    <section class="card progress-card">
        <div class="progress-header">
            <strong>評分題填寫進度</strong>
            <span id="progressText">已完成 0 / 8 題（0%）</span>
        </div>

        <div class="progress-track">
            <div id="progressBar" class="progress-bar"></div>
        </div>

        <p id="draftStatus" class="draft-status">
            回答將自動暫存在此瀏覽器中。
        </p>
    </section>

    <form id="feedbackForm" class="card">
        <h2 class="section-heading">使用體驗評分</h2>

        <p class="section-description">
            標示 <span class="required-mark">*</span> 的評分題為必填。
        </p>

        <?php foreach ($questions as $name => $question): ?>
            <div class="question">
                <div class="question-title">
                    <?= esc($question) ?>
                    <span class="required-mark">*</span>
                </div>

                <div class="rating-group">
                    <?php foreach ($ratingLabels as $score => $label): ?>
                        <label class="rating-option">
                            <input
                                type="radio"
                                name="<?= esc($name) ?>"
                                value="<?= $score ?>"
                                required
                            >

                            <?= $score ?> - <?= esc($label) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div id="lowScoreNotice" class="low-score-notice">
            我們注意到您對部分項目的評分較低。如果方便，請在下方說明遇到的問題，這將有助於我們改善系統。
        </div>

        <div class="question">
            <div class="question-title">
                9. 您最常使用或認為最實用的功能是什麼？
            </div>

            <div class="text-area-wrapper">
                <textarea
                    name="favorite_feature"
                    maxlength="500"
                    placeholder="例如：履歷上傳、志願序填寫、分發結果查詢"
                ></textarea>

                <span class="character-count" data-count-for="favorite_feature">
                    0 / 500
                </span>
            </div>
        </div>

        <div class="question">
            <div class="question-title">
                10. 使用過程中是否遇到任何問題？
            </div>

            <div class="text-area-wrapper">
                <textarea
                    name="problem_description"
                    maxlength="500"
                    placeholder="若沒有遇到問題，可以填寫「無」"
                ></textarea>

                <span class="character-count" data-count-for="problem_description">
                    0 / 500
                </span>
            </div>
        </div>

        <div class="question">
            <div class="question-title">
                11. 您對本系統還有什麼改善建議？
            </div>

            <div class="text-area-wrapper">
                <textarea
                    name="suggestion"
                    maxlength="500"
                    placeholder="此題可以留白"
                ></textarea>

                <span class="character-count" data-count-for="suggestion">
                    0 / 500
                </span>
            </div>
        </div>

        <div class="actions">
            <button type="button" id="clearDraftButton" class="btn btn-danger">
                清除草稿
            </button>

            <button type="submit" class="btn btn-primary">
                預覽填寫結果
            </button>
        </div>

        <p class="notice">
            目前為前端預覽版本，回答只會暫存在此瀏覽器，尚未寫入系統資料庫。
        </p>
    </form>

    <section id="previewCard" class="card preview-card">
        <div class="preview-header">
            <div>
                <h2>回饋內容確認單</h2>

                <p>
                    請確認以下內容是否正確。目前尚未正式送出至資料庫。
                </p>
            </div>

            <div class="average-score-box">
                <span id="averageScoreValue" class="average-score-value">
                    0.0
                </span>

                <span class="average-score-label">
                    平均評分 / 5
                </span>
            </div>
        </div>

        <div class="student-info">
            <span>
                姓名：
                <strong><?= esc($student['name'] ?? '') ?></strong>
            </span>

            <span>
                學號：
                <strong><?= esc($student['student_id'] ?? '') ?></strong>
            </span>

            <span>
                產生時間：
                <strong id="previewTime"></strong>
            </span>
        </div>

        <div id="previewWarning" class="preview-warning"></div>

        <div id="previewContent"></div>

        <div class="actions preview-actions">
            <button type="button" id="editButton" class="btn btn-secondary">
                返回修改
            </button>

            <button type="button" id="printButton" class="btn btn-primary">
                列印確認單
            </button>
        </div>
    </section>
</main>

<script>
    const questionTitles = <?= json_encode(
        $questions,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    ) ?>;

    const ratingLabels = <?= json_encode(
        $ratingLabels,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    ) ?>;

    const studentStorageKey = <?= json_encode(
        (string) ($student['id'] ?? $student['student_id'] ?? 'guest'),
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    ) ?>;

    const storageKey = `studentFeedbackDraft_${studentStorageKey}`;

    const form = document.getElementById('feedbackForm');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const draftStatus = document.getElementById('draftStatus');
    const lowScoreNotice = document.getElementById('lowScoreNotice');

    const previewCard = document.getElementById('previewCard');
    const previewContent = document.getElementById('previewContent');
    const previewWarning = document.getElementById('previewWarning');
    const previewTime = document.getElementById('previewTime');
    const averageScoreValue = document.getElementById('averageScoreValue');

    const clearDraftButton = document.getElementById('clearDraftButton');
    const editButton = document.getElementById('editButton');
    const printButton = document.getElementById('printButton');

    const ratingNames = Object.keys(questionTitles);

    let savedMessageTimer = null;

    function getSelectedScore(name) {
        const selected = form.querySelector(
            `input[name="${name}"]:checked`
        );

        return selected ? Number(selected.value) : null;
    }

    function updateProgress() {
        const answeredCount = ratingNames.filter(name => {
            return getSelectedScore(name) !== null;
        }).length;

        const percentage = Math.round(
            (answeredCount / ratingNames.length) * 100
        );

        progressBar.style.width = `${percentage}%`;

        progressText.textContent =
            `已完成 ${answeredCount} / ${ratingNames.length} 題（${percentage}%）`;

        updateLowScoreNotice();
    }

    function updateLowScoreNotice() {
        const hasLowScore = ratingNames.some(name => {
            const score = getSelectedScore(name);

            return score !== null && score <= 2;
        });

        lowScoreNotice.style.display = hasLowScore ? 'block' : 'none';
    }

    function updateCharacterCount(textarea) {
        const counter = document.querySelector(
            `[data-count-for="${textarea.name}"]`
        );

        if (!counter) {
            return;
        }

        counter.textContent =
            `${textarea.value.length} / ${textarea.maxLength}`;
    }

    function collectFormData() {
        const data = {};

        new FormData(form).forEach((value, key) => {
            data[key] = value;
        });

        return data;
    }

    function saveDraft() {
        try {
            const draft = {
                answers: collectFormData(),
                savedAt: new Date().toISOString()
            };

            localStorage.setItem(storageKey, JSON.stringify(draft));

            draftStatus.textContent =
                `草稿已自動儲存：${new Date().toLocaleTimeString('zh-TW')}`;

            draftStatus.classList.add('saved');

            clearTimeout(savedMessageTimer);

            savedMessageTimer = setTimeout(() => {
                draftStatus.textContent =
                    '回答將自動暫存在此瀏覽器中。';

                draftStatus.classList.remove('saved');
            }, 2500);
        } catch (error) {
            draftStatus.textContent =
                '目前無法使用瀏覽器暫存功能。';
        }
    }

    function restoreDraft() {
        const savedDraft = localStorage.getItem(storageKey);

        if (!savedDraft) {
            return;
        }

        try {
            const draft = JSON.parse(savedDraft);
            const answers = draft.answers ?? {};

            form.querySelectorAll('[name]').forEach(control => {
                const savedValue = answers[control.name];

                if (savedValue === undefined) {
                    return;
                }

                if (control.type === 'radio') {
                    control.checked =
                        String(control.value) === String(savedValue);
                } else {
                    control.value = savedValue;
                }
            });

            form.querySelectorAll('textarea').forEach(textarea => {
                updateCharacterCount(textarea);
            });

            if (draft.savedAt) {
                draftStatus.textContent =
                    `已恢復先前草稿，最後儲存時間：${
                        new Date(draft.savedAt).toLocaleString('zh-TW')
                    }`;

                draftStatus.classList.add('saved');
            }
        } catch (error) {
            localStorage.removeItem(storageKey);
        }
    }

    function createAnswerRow(title, value, score = null) {
        const row = document.createElement('div');
        row.className = 'answer-row';

        const titleElement = document.createElement('div');
        titleElement.className = 'answer-title';
        titleElement.textContent = title;

        const valueElement = document.createElement('div');
        valueElement.className = 'answer-value';

        if (score !== null) {
            const scoreBadge = document.createElement('span');
            scoreBadge.className =
                score <= 2 ? 'score-badge low' : 'score-badge';

            scoreBadge.textContent =
                `${score} 分・${ratingLabels[score] ?? ''}`;

            valueElement.appendChild(scoreBadge);
        } else {
            valueElement.textContent = value || '未填寫';
        }

        row.appendChild(titleElement);
        row.appendChild(valueElement);

        return row;
    }

    function buildPreview(formData) {
        previewContent.replaceChildren();

        const scores = [];
        const lowScoreQuestions = [];

        Object.entries(questionTitles).forEach(([name, title]) => {
            const score = Number(formData.get(name));

            scores.push(score);

            if (score <= 2) {
                lowScoreQuestions.push(title);
            }

            previewContent.appendChild(
                createAnswerRow(title, '', score)
            );
        });

        const textAnswers = {
            favorite_feature:
                '9. 最常使用或認為最實用的功能',
            problem_description:
                '10. 使用過程中遇到的問題',
            suggestion:
                '11. 系統改善建議'
        };

        Object.entries(textAnswers).forEach(([name, title]) => {
            const answer = String(
                formData.get(name) ?? ''
            ).trim();

            previewContent.appendChild(
                createAnswerRow(title, answer)
            );
        });

        const averageScore =
            scores.reduce((total, score) => total + score, 0)
            / scores.length;

        averageScoreValue.textContent = averageScore.toFixed(1);

        previewTime.textContent =
            new Date().toLocaleString('zh-TW');

        if (lowScoreQuestions.length > 0) {
            previewWarning.style.display = 'block';

            previewWarning.textContent =
                `您有 ${lowScoreQuestions.length} 個項目評分為 2 分以下。`
                + '我們會特別關注您在問題說明及改善建議中提供的意見。';
        } else {
            previewWarning.style.display = 'none';
            previewWarning.textContent = '';
        }
    }

    form.addEventListener('input', event => {
        updateProgress();

        if (event.target.matches('textarea')) {
            updateCharacterCount(event.target);
        }

        saveDraft();
    });

    form.addEventListener('change', () => {
        updateProgress();
        saveDraft();
    });

    form.addEventListener('submit', event => {
        event.preventDefault();

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const formData = new FormData(form);

        buildPreview(formData);

        previewCard.style.display = 'block';

        previewCard.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    });

    clearDraftButton.addEventListener('click', () => {
        const confirmed = window.confirm(
            '確定要清除目前所有填寫內容嗎？'
        );

        if (!confirmed) {
            return;
        }

        form.reset();
        localStorage.removeItem(storageKey);

        form.querySelectorAll('textarea').forEach(textarea => {
            updateCharacterCount(textarea);
        });

        previewCard.style.display = 'none';
        previewContent.replaceChildren();

        draftStatus.textContent = '草稿已清除。';
        draftStatus.classList.remove('saved');

        updateProgress();

        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    editButton.addEventListener('click', () => {
        form.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    });

    printButton.addEventListener('click', () => {
        window.print();
    });

    restoreDraft();
    updateProgress();

    form.querySelectorAll('textarea').forEach(textarea => {
        updateCharacterCount(textarea);
    });
</script>
</body>
</html>
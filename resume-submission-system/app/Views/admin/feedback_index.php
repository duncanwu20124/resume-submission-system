<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>使用回饋管理 | 管理員系統</title>

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
        .feedback-page {
            padding-bottom: 60px;
        }

        .feedback-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 24px;
        }

        .feedback-header h2 {
            margin: 0 0 8px;
            font-size: 28px;
            color: #0f172a;
        }

        .feedback-header p {
            margin: 0;
            color: #64748b;
        }

        .feedback-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }

        .feedback-stat-card {
            padding: 22px;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
        }

        .feedback-stat-card__label {
            display: block;
            margin-bottom: 10px;
            color: #64748b;
            font-size: 14px;
            font-weight: 600;
        }

        .feedback-stat-card__value {
            display: block;
            color: #0f172a;
            font-size: 28px;
            font-weight: 700;
        }

        .feedback-stat-card--primary {
            border-left: 5px solid #4f46e5;
        }

        .feedback-stat-card--success {
            border-left: 5px solid #10b981;
        }

        .feedback-stat-card--time {
            border-left: 5px solid #6366f1;
        }

        .feedback-stat-card--time .feedback-stat-card__value {
            font-size: 18px;
        }

        .feedback-distribution {
            padding: 22px;
            margin-bottom: 24px;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #ffffff;
        }

        .feedback-filter-card {
            padding: 20px 22px;
            margin-bottom: 24px;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
        }

        .feedback-filter-card h3 {
            margin: 0 0 16px;
            color: #0f172a;
            font-size: 18px;
        }

        .feedback-filter-form {
            display: grid;
            grid-template-columns: minmax(240px, 2fr) repeat(2, minmax(150px, 1fr)) auto;
            gap: 12px;
            align-items: end;
        }

        .feedback-filter-field label {
            display: block;
            margin-bottom: 7px;
            color: #475569;
            font-size: 13px;
            font-weight: 700;
        }

        .feedback-filter-field input,
        .feedback-filter-field select {
            width: 100%;
            height: 42px;
            padding: 0 12px;
            color: #0f172a;
            border: 1px solid #cbd5e1;
            border-radius: 9px;
            background: #ffffff;
            outline: none;
        }

        .feedback-filter-field input:focus,
        .feedback-filter-field select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
        }

        .feedback-filter-actions {
            display: flex;
            gap: 8px;
        }

        .filter-button,
        .clear-button,
        .export-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 0 15px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
            border-radius: 9px;
            cursor: pointer;
        }

        .filter-button {
            color: #ffffff;
            border: 0;
            background: #4f46e5;
        }

        .filter-button:hover {
            background: #4338ca;
        }

        .clear-button {
            color: #475569;
            border: 1px solid #cbd5e1;
            background: #ffffff;
        }

        .clear-button:hover {
            background: #f8fafc;
        }

        .export-button {
            color: #047857;
            border: 1px solid #6ee7b7;
            background: #ecfdf5;
        }

        .export-button:hover {
            background: #d1fae5;
        }

        .feedback-table-heading__actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .feedback-distribution h3 {
            margin: 0 0 18px;
            color: #0f172a;
            font-size: 18px;
        }

        .score-list {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 12px;
        }

        .score-item {
            padding: 14px;
            text-align: center;
            border-radius: 12px;
            background: #f8fafc;
        }

        .score-item strong {
            display: block;
            margin-bottom: 5px;
            color: #4f46e5;
            font-size: 20px;
        }

        .score-item span {
            color: #64748b;
            font-size: 13px;
        }

        .feedback-table-card {
            overflow: hidden;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
        }

        .feedback-table-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 20px 22px;
            border-bottom: 1px solid #e2e8f0;
        }

        .feedback-table-heading h3 {
            margin: 0;
            color: #0f172a;
            font-size: 18px;
        }

        .feedback-count {
            color: #64748b;
            font-size: 14px;
        }

        .feedback-table-wrapper {
            overflow-x: auto;
        }

        .feedback-table {
            width: 100%;
            border-collapse: collapse;
        }

        .feedback-table th,
        .feedback-table td {
            padding: 15px 16px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .feedback-table th {
            color: #475569;
            font-size: 13px;
            font-weight: 700;
            background: #f8fafc;
            white-space: nowrap;
        }

        .feedback-table td {
            color: #334155;
            font-size: 14px;
        }

        .feedback-table tbody tr:hover {
            background: #f8fafc;
        }

        .feedback-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .student-name {
            display: block;
            margin-bottom: 4px;
            color: #0f172a;
            font-weight: 700;
        }

        .student-email {
            color: #64748b;
            font-size: 12px;
        }

        .score-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 68px;
            padding: 7px 11px;
            color: #4338ca;
            font-weight: 700;
            border-radius: 999px;
            background: #eef2ff;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 10px;
            color: #047857;
            font-size: 12px;
            font-weight: 700;
            border-radius: 999px;
            background: #d1fae5;
        }

        .view-feedback-button {
            display: inline-block;
            padding: 9px 14px;
            color: #ffffff;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
            border-radius: 9px;
            background: #4f46e5;
            transition: background 0.2s, transform 0.2s;
        }

        .view-feedback-button:hover {
            background: #4338ca;
            transform: translateY(-1px);
        }

        .feedback-empty {
            padding: 60px 20px;
            text-align: center;
            color: #64748b;
        }

        .feedback-empty__icon {
            display: block;
            margin-bottom: 12px;
            font-size: 40px;
        }

        @media (max-width: 900px) {
            .feedback-stats {
                grid-template-columns: 1fr;
            }

            .score-list {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .feedback-filter-form {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 600px) {
            .feedback-header {
                flex-direction: column;
            }

            .score-list {
                grid-template-columns: 1fr;
            }

            .feedback-filter-form {
                grid-template-columns: 1fr;
            }

            .feedback-filter-actions,
            .feedback-table-heading,
            .feedback-table-heading__actions {
                align-items: stretch;
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

<main class="admin-shell admin-shell--with-sidebar feedback-page">

    <?= view('partials/admin_sidebar') ?>

    <section class="feedback-header">
        <div>
            <h2>使用回饋管理</h2>
            <p>
                查看學生送出的系統評分、使用意見與改善建議。
            </p>
        </div>
    </section>

    <section class="feedback-stats">

        <article class="feedback-stat-card feedback-stat-card--primary">
            <span class="feedback-stat-card__label">
                已收到回饋
            </span>

            <strong class="feedback-stat-card__value">
                <?= esc($statistics['total'] ?? 0) ?>
            </strong>
        </article>

        <article class="feedback-stat-card feedback-stat-card--success">
            <span class="feedback-stat-card__label">
                整體平均分數
            </span>

            <strong class="feedback-stat-card__value">
                <?= number_format(
                    (float) ($statistics['overall_average'] ?? 0),
                    2
                ) ?>
                / 5
            </strong>
        </article>

        <article class="feedback-stat-card feedback-stat-card--time">
            <span class="feedback-stat-card__label">
                最近回饋時間
            </span>

            <strong class="feedback-stat-card__value">
                <?= !empty($statistics['latest_submitted_at'])
                    ? esc($statistics['latest_submitted_at'])
                    : '尚無紀錄' ?>
            </strong>
        </article>

    </section>

    <section class="feedback-distribution">
        <h3>平均分數分布</h3>

        <div class="score-list">
            <?php foreach ([5, 4, 3, 2, 1] as $score): ?>
                <div class="score-item">
                    <strong>
                        <?= esc($scoreDistribution[$score] ?? 0) ?>
                    </strong>

                    <span>
                        <?= $score ?> 分區間
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <?php
        $currentFilters = $filters ?? [
            'keyword' => '',
            'score'   => '',
            'sort'    => 'latest',
        ];

        $exportQuery = http_build_query([
            'keyword' => $currentFilters['keyword'],
            'score'   => $currentFilters['score'],
            'sort'    => $currentFilters['sort'],
        ]);
    ?>

    <section class="feedback-filter-card">
        <h3>搜尋、篩選與排序</h3>

        <form
            class="feedback-filter-form"
            method="get"
            action="<?= site_url('AdminController/feedback') ?>"
        >
            <div class="feedback-filter-field">
                <label for="keyword">搜尋學生</label>
                <input
                    id="keyword"
                    name="keyword"
                    type="search"
                    value="<?= esc($currentFilters['keyword']) ?>"
                    placeholder="輸入姓名、學號或 Email"
                >
            </div>

            <div class="feedback-filter-field">
                <label for="score">平均分數區間</label>
                <select id="score" name="score">
                    <option value="">全部分數</option>
                    <?php foreach ([5, 4, 3, 2, 1] as $score): ?>
                        <option
                            value="<?= $score ?>"
                            <?= (string) $currentFilters['score'] === (string) $score
                                ? 'selected'
                                : '' ?>
                        >
                            <?= $score ?> 分區間
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="feedback-filter-field">
                <label for="sort">排序方式</label>
                <select id="sort" name="sort">
                    <?php
                        $sortOptions = [
                            'latest'     => '送出時間：最新優先',
                            'oldest'     => '送出時間：最舊優先',
                            'score_high' => '平均分數：高至低',
                            'score_low'  => '平均分數：低至高',
                            'name_asc'   => '學生姓名：筆畫排序',
                        ];
                    ?>

                    <?php foreach ($sortOptions as $value => $label): ?>
                        <option
                            value="<?= esc($value) ?>"
                            <?= $currentFilters['sort'] === $value
                                ? 'selected'
                                : '' ?>
                        >
                            <?= esc($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="feedback-filter-actions">
                <button class="filter-button" type="submit">
                    套用條件
                </button>

                <a
                    class="clear-button"
                    href="<?= site_url('AdminController/feedback') ?>"
                >
                    清除
                </a>
            </div>
        </form>
    </section>

    <section class="feedback-table-card">

        <div class="feedback-table-heading">
            <h3>學生回饋清單</h3>

            <div class="feedback-table-heading__actions">
                <span class="feedback-count">
                    查詢結果共 <?= count($submissions ?? []) ?> 筆
                </span>

                <a
                    class="export-button"
                    href="<?= site_url('AdminController/feedback/export')
                        . ($exportQuery !== '' ? '?' . esc($exportQuery) : '') ?>"
                >
                    匯出 CSV
                </a>
            </div>
        </div>

        <?php if (!empty($submissions)): ?>

            <div class="feedback-table-wrapper">
                <table class="feedback-table">
                    <thead>
                        <tr>
                            <th>編號</th>
                            <th>學生資料</th>
                            <th>學號</th>
                            <th>平均分數</th>
                            <th>狀態</th>
                            <th>送出時間</th>
                            <th>操作</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($submissions as $submission): ?>
                            <tr>
                                <td>
                                    #<?= esc($submission['id']) ?>
                                </td>

                                <td>
                                    <span class="student-name">
                                        <?= esc(
                                            $submission['name']
                                            ?? '未知學生'
                                        ) ?>
                                    </span>

                                    <span class="student-email">
                                        <?= esc(
                                            $submission['email']
                                            ?? '無 Email'
                                        ) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= esc(
                                        $submission['student_id']
                                        ?? '—'
                                    ) ?>
                                </td>

                                <td>
                                    <span class="score-badge">
                                        <?= number_format(
                                            (float) (
                                                $submission['average_score']
                                                ?? 0
                                            ),
                                            2
                                        ) ?>
                                        分
                                    </span>
                                </td>

                                <td>
                                    <span class="status-badge">
                                        已提交
                                    </span>
                                </td>

                                <td>
                                    <?= !empty($submission['submitted_at'])
                                        ? esc($submission['submitted_at'])
                                        : '—' ?>
                                </td>

                                <td>
                                    <a
                                        class="view-feedback-button"
                                        href="/AdminController/feedback/<?= esc($submission['id']) ?>"
                                    >
                                        查看內容
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>

            <div class="feedback-empty">
                <span class="feedback-empty__icon">💬</span>
                找不到符合條件的學生回饋。
            </div>

        <?php endif; ?>

    </section>

</main>

</body>
</html>

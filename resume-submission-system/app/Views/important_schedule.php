<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>重要時程 | 學生甄選與志願媒合系統</title>
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #312e81;
            --background: #f8fafc;
            --surface: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --notice: #fffbeb;
            --notice-border: #fde68a;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--background);
            color: var(--text-main);
            font-family: Inter, system-ui, -apple-system, sans-serif;
        }

        header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 1rem 1.5rem;
        }

        .header-inner,
        main {
            width: min(960px, calc(100% - 2rem));
            margin: 0 auto;
        }

        .header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .brand {
            color: var(--primary);
            font-size: 1.05rem;
            font-weight: 700;
            text-decoration: none;
        }

        .back-link {
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
        }

        .back-link:hover,
        .back-link:focus-visible,
        .brand:hover,
        .brand:focus-visible {
            text-decoration: underline;
        }

        main { padding: 2rem 0 3rem; }

        .page-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            padding: clamp(1.25rem, 4vw, 2.5rem);
        }

        .eyebrow {
            margin: 0 0 0.5rem;
            color: var(--primary);
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.08em;
        }

        h1 {
            margin: 0;
            color: var(--primary-dark);
            font-size: clamp(1.6rem, 4vw, 2.25rem);
        }

        .intro {
            margin: 0.75rem 0 1.5rem;
            color: var(--text-muted);
            line-height: 1.7;
        }

        .official-notice {
            margin-bottom: 1.5rem;
            padding: 0.9rem 1rem;
            background: var(--notice);
            border: 1px solid var(--notice-border);
            border-radius: 0.5rem;
            line-height: 1.6;
        }

        .official-notice a {
            color: var(--primary-dark);
            font-weight: 600;
        }

        .schedule-table-wrap { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 560px;
        }

        th,
        td {
            padding: 0.9rem 1rem;
            border-bottom: 1px solid var(--border);
            text-align: left;
            vertical-align: top;
            line-height: 1.6;
        }

        th {
            background: #f1f5f9;
            color: var(--primary-dark);
            font-size: 0.85rem;
        }

        td:first-child {
            width: 190px;
            white-space: nowrap;
            font-weight: 700;
        }

        .empty-state {
            margin: 1.5rem 0 0;
            color: var(--text-muted);
        }

        @media (max-width: 640px) {
            header { padding: 0.9rem 1rem; }
            .header-inner { align-items: flex-start; flex-direction: column; }
            main { width: min(100% - 1rem, 960px); padding-top: 1rem; }
            .page-card { padding: 1rem; }
        }
    </style>
</head>
<body>
<header>
    <div class="header-inner">
        <a class="brand" href="<?= site_url('/') ?>">學生甄選與志願媒合系統</a>
        <a class="back-link" href="<?= site_url('/') ?>">← 回到首頁</a>
    </div>
</header>

<main>
    <article class="page-card">
        <p class="eyebrow">招生資訊</p>
        <h1>重要時程</h1>
        <p class="intro">實際招生日期請以承辦單位最新公告為準。</p>

        <?php if (($officialHasData ?? false) === false): ?>
            <div class="official-notice" role="status">
                官方 116 申請入學時程頁目前顯示「目前尚無資料」。
                <a href="<?= esc($officialSource ?? '') ?>" target="_blank" rel="noopener noreferrer">前往官方時程頁查看</a>
            </div>
        <?php endif; ?>

        <?php if (!empty($scheduleItems)): ?>
            <div class="schedule-table-wrap">
                <table>
                    <caption class="sr-only">本機練習系統重要時程</caption>
                    <thead>
                    <tr>
                        <th scope="col">日期／時間</th>
                        <th scope="col">事項</th>
                        <th scope="col">說明</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($scheduleItems as $item): ?>
                        <tr>
                            <td><?= esc($item['date']) ?></td>
                            <td><?= esc($item['title']) ?></td>
                            <td><?= esc($item['description']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="empty-state" role="status">目前尚無本機時程資料。</p>
        <?php endif; ?>
    </article>
</main>
</body>
</html>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($announcement['title']) ?> | 學生甄選與志願媒合系統</title>
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --surface: #ffffff;
            --background: #f8fafc;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--background);
            color: var(--text-main);
            font-family: Inter, system-ui, -apple-system, sans-serif;
        }

        header {
            padding: 1.5rem 2rem;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
        }

        .brand {
            color: var(--primary);
            font-weight: 700;
            text-decoration: none;
        }

        main {
            flex: 1;
            width: min(840px, calc(100% - 2rem));
            margin: 0 auto;
            padding: 3rem 0;
        }

        .announcement-detail {
            padding: clamp(1.5rem, 4vw, 3rem);
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 1rem;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.06);
        }

        .back-link {
            color: var(--text-muted);
            font-size: 0.9rem;
            text-decoration: none;
        }

        .back-link:hover,
        .back-link:focus-visible {
            color: var(--primary);
        }

        .eyebrow {
            margin: 2rem 0 0.5rem;
            color: var(--primary);
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.08em;
        }

        h1 {
            margin: 0;
            font-size: clamp(1.6rem, 4vw, 2.3rem);
            line-height: 1.35;
        }

        .announcement-content {
            margin-top: 1.75rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
            color: #475569;
            font-size: 1rem;
            line-height: 1.9;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .announcement-content a {
            color: var(--primary);
            font-weight: 700;
            text-decoration: underline;
            text-underline-offset: 3px;
        }

        .announcement-date {
            margin: 1.5rem 0 0;
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        footer {
            padding: 1.5rem;
            color: var(--text-muted);
            font-size: 0.85rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <header>
        <a class="brand" href="<?= site_url('/') ?>">學生甄選與志願媒合系統</a>
    </header>

    <main>
        <article class="announcement-detail">
            <a class="back-link" href="<?= site_url('/') ?>">&larr; 回到首頁</a>
            <p class="eyebrow">公告內容</p>
            <h1><?= esc($announcement['title']) ?></h1>
            <div class="announcement-content"><?= \App\Support\AnnouncementLink::renderContent((string) $announcement['content']) ?></div>
            <?php if (!empty($announcement['created_at'])): ?>
                <p class="announcement-date">發布時間：<?= esc($announcement['created_at']) ?></p>
            <?php endif; ?>
        </article>
    </main>

    <footer>&copy; <?= date('Y') ?> Selection &amp; Preference Matching System</footer>
</body>
</html>

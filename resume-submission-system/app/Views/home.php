<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>學生甄選與志願媒合系統 | Selection & Preference Matching System</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --primary-light: #eef2ff;
            --surface: #ffffff;
            --background: #f8fafc;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            --radius-xl: 1rem;
            --radius-lg: 0.75rem;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: radial-gradient(circle at 50% 0%, #e0e7ff 0%, #f8fafc 65%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: var(--text-main);
        }

        header {
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--primary);
        }

        .logo svg {
            width: 32px;
            height: 32px;
        }

        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .hero-container {
            max-width: 960px;
            width: 100%;
            text-align: center;
        }

        .hero-title {
            font-size: 2.75rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            color: #1e1b4b;
            margin-bottom: 1rem;
        }

        .hero-subtitle {
            font-size: 1.125rem;
            color: var(--text-muted);
            margin-bottom: 3rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .portal-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
            padding: 0 1rem;
        }

        .portal-card {
            background: var(--surface);
            border-radius: var(--radius-xl);
            padding: 2.5rem 2rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .portal-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 25px 30px -5px rgba(79, 70, 229, 0.12);
        }

        .card-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .student-icon {
            background-color: var(--primary-light);
            color: var(--primary);
        }

        .professor-icon {
            background-color: #f0fdf4;
            color: #16a34a;
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }

        .card-desc {
            font-size: 0.95rem;
            color: var(--text-muted);
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.875rem 1.5rem;
            border-radius: var(--radius-lg);
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            box-shadow: 0 6px 16px rgba(79, 70, 229, 0.35);
        }

        .btn-secondary {
            background-color: #f1f5f9;
            color: #475569;
            cursor: not-allowed;
        }

        .tag-placeholder {
            display: inline-block;
            margin-top: 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.6rem;
            background: #f1f5f9;
            color: #64748b;
            border-radius: 9999px;
        }


        .info-panel {
            margin: 0 auto 2rem;
            width: calc(100% - 2rem);
            max-width: none;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            padding: 1.25rem 1.5rem;
            text-align: left;
        }

        .info-panel-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.05rem;
            font-weight: 700;
            color: #312e81;
            margin-bottom: 1rem;
        }

        .schedule-link {
            margin-left: auto;
            color: var(--primary);
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
        }

        .schedule-link:hover,
        .schedule-link:focus-visible {
            text-decoration: underline;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }

        .info-item {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 1rem;
        }

        .info-label {
            display: block;
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-bottom: 0.35rem;
        }

        .info-value {
            font-size: 0.98rem;
            font-weight: 700;
            color: var(--text-main);
        }

        .notice-box {
            margin-top: 1rem;
            padding: 0.9rem 1rem;
            border-left: 4px solid #f59e0b;
            background: #fffbeb;
            color: #92400e;
            border-radius: 0.5rem;
            line-height: 1.6;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }

        footer {
            padding: 1.5rem;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
            </svg>
            <span>學生甄選與志願媒合系統</span>
        </div>
    </header>

    <main>
        <div class="hero-container">
            <h1 class="hero-title">歡迎使用履歷上傳與管理系統</h1>

            <?= $this->include('partials/announcement_board') ?>

            <!-- 繳交資訊：日期可依實際時程修改 -->
            <section class="info-panel" aria-label="履歷繳交資訊">
                <div class="info-panel-title">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    履歷繳交資訊
                    <a class="schedule-link" href="<?= site_url('schedule') ?>">查看重要時程 →</a>
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">繳交開始</span>
                        <span class="info-value">2026 / 08 / 15</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">繳交期限</span>
                        <span class="info-value">2026 / 08 / 31 23:59</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">檔案規範</span>
                        <span class="info-value">PDF，3 MB 以下</span>
                    </div>
                </div>

                <div class="notice-box">
                    <strong>注意事項：</strong>請確認履歷內容與個人資料正確後再上傳；如需更新履歷，可於學生端刪除舊檔後重新上傳。
                </div>
            </section>

            <div class="portal-grid" style="max-width: 480px; margin: 0 auto;">
                <!-- 學生登入區塊 -->
                <div class="portal-card">
                    <div class="card-icon student-icon">
                        <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <h2 class="card-title">學生入口 (Student Portal)</h2>
                    <p class="card-desc">學生可在此建立專屬帳號、進行身分驗證登入，並上傳與管理履歷檔案。</p>
                    <a href="<?= site_url('student/login') ?>" class="btn btn-primary">
                        進入學生登入 / 註冊
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <p>
            &copy; <?= date('Y') ?> Selection & Preference Matching System — Team Collaboration Project
        </p>
    </footer>
</body>
</html>

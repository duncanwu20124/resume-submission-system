<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>學生控制台 | 履歷管理系統</title>
    
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
            --danger: #ef4444;
            --success: #10b981;
            --radius-xl: 1rem;
            --radius-lg: 0.75rem;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--background);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar Styling */
        .navbar {
            background-color: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 700;
            font-size: 1.15rem;
            color: var(--primary);
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }

        .user-info-chip {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--background);
            padding: 0.4rem 0.85rem;
            border-radius: 9999px;
            border: 1px solid var(--border);
            font-size: 0.875rem;
        }

        .avatar {
            width: 28px;
            height: 28px;
            background-color: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .btn-logout {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.45rem 0.9rem;
            border-radius: var(--radius-lg);
            background-color: #fef2f2;
            color: var(--danger);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 600;
            border: 1px solid #fecaca;
            transition: all 0.2s;
        }

        .btn-logout:hover {
            background-color: #fee2e2;
        }

        /* Main Layout */
        .container {
            max-width: 1080px;
            width: 100%;
            margin: 2rem auto;
            padding: 0 1.5rem;
            flex: 1;
        }

        .welcome-card {
            background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
            color: white;
            border-radius: var(--radius-xl);
            padding: 2rem 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .welcome-text h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .welcome-text p {
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .profile-badges {
            display: flex;
            gap: 0.75rem;
        }

        .badge-item {
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(10px);
            padding: 0.5rem 1rem;
            border-radius: var(--radius-lg);
            font-size: 0.85rem;
            font-weight: 500;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        .card {
            background: var(--surface);
            border-radius: var(--radius-xl);
            border: 1px solid var(--border);
            padding: 1.75rem 2rem;
            box-shadow: var(--shadow-sm);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Files Table / List */
        .file-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .file-table th {
            padding: 0.75rem 1rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
            background-color: var(--background);
            border-bottom: 1px solid var(--border);
        }

        .file-table td {
            padding: 1rem;
            font-size: 0.9rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1.5rem;
            color: var(--text-muted);
        }

        .empty-icon {
            width: 48px;
            height: 48px;
            margin-bottom: 0.75rem;
            color: #cbd5e1;
        }

        .empty-text {
            font-size: 1rem;
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .empty-subtext {
            font-size: 0.85rem;
            color: #94a3b8;
        }

        .alert-flash {
            padding: 1rem 1.25rem;
            border-radius: var(--radius-lg);
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .alert-success {
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        /* Upload Area Placeholder */
        .upload-dropzone {
            border: 2px dashed #cbd5e1;
            border-radius: var(--radius-lg);
            padding: 2.5rem 1.5rem;
            text-align: center;
            background-color: #fafafa;
            transition: border-color 0.2s, background-color 0.2s;
            cursor: pointer;
        }

        .upload-dropzone:hover {
            border-color: var(--primary);
            background-color: var(--primary-light);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-brand">
            <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
            </svg>
            <span>學生履歷管理 Portal</span>
        </div>

        <div class="user-menu">
            <div class="user-info-chip">
                <div class="avatar"><?= mb_substr(esc($student['student_name']), 0, 1) ?></div>
                <span><?= esc($student['student_name']) ?> (<?= esc($student['student_id']) ?>)</span>
            </div>
            <a href="<?= site_url('student/logout') ?>" class="btn-logout">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                登出 (Logout)
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert-flash alert-success">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <!-- Student Profile Welcome Banner -->
        <div class="welcome-card">
            <div class="welcome-text">
                <h1>你好，<?= esc($student['student_name']) ?> 同學！</h1>
                <p>歡迎登入學生履歷管理系統，您可以在此檢視與管理上傳的履歷檔案。</p>
            </div>
            <div class="profile-badges">
                <div class="badge-item">學號: <?= esc($student['student_id']) ?></div>
                <div class="badge-item">信箱: <?= esc($student['student_email']) ?></div>
            </div>
        </div>

        <div class="content-grid">
            <!-- 已上傳履歷檔案列表 (View Uploaded Resumes) -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        檢視已上傳的檔案 (My Uploaded Resumes)
                    </h2>
                </div>

                <?php if (empty($files)): ?>
                    <div class="empty-state">
                        <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <div class="empty-text">目前尚無上傳任何履歷檔案</div>
                        <div class="empty-subtext">請使用下方上傳區域上傳您的 PDF 或 Word 履歷檔案（大小限制 3MB 以內）</div>
                    </div>
                <?php else: ?>
                    <table class="file-table">
                        <thead>
                            <tr>
                                <th>檔案名稱</th>
                                <th>檔案大小</th>
                                <th>上傳時間</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($files as $file): ?>
                                <tr>
                                    <td><?= esc($file['name']) ?></td>
                                    <td><?= esc($file['size']) ?></td>
                                    <td><?= esc($file['created_at']) ?></td>
                                    <td>
                                        <a href="#" style="color: var(--primary); font-weight: 600; text-decoration: none;">檢視 / 下載</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- 上傳區域預留 (Upload Area - Ready for S-04 Upload integration) -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        上傳新履歷檔案 (Upload Resume File)
                    </h2>
                </div>

                <div class="upload-dropzone">
                    <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #64748b; margin-bottom: 0.75rem;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <p style="font-weight: 600; color: #334155; margin-bottom: 0.25rem;">點擊選擇檔案或將履歷檔案拖曳至此</p>
                    <p style="font-size: 0.8rem; color: #94a3b8;">支援 PDF, DOC, DOCX 格式（單一檔案上限 3MB）</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

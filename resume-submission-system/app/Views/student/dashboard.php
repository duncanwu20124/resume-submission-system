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
            --danger-hover: #dc2626;
            --danger-light: #fef2f2;
            --success: #10b981;
            --success-light: #ecfdf5;
            --radius-xl: 1rem;
            --radius-lg: 0.75rem;
            --radius-md: 0.5rem;
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

        /* Alerts */
        .alert-flash {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.25rem;
            border-radius: var(--radius-lg);
            margin-bottom: 1.5rem;
            font-size: 0.925rem;
            animation: fadeIn 0.3s ease;
        }

        .alert-success {
            background-color: var(--success-light);
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-danger {
            background-color: var(--danger-light);
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Welcome Hero Banner */
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
            flex-wrap: wrap;
        }

        .badge-item {
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(10px);
            padding: 0.5rem 1rem;
            border-radius: var(--radius-lg);
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* Content Grid & Cards */
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
            flex-wrap: wrap;
            gap: 1rem;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .badge-status-active {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.8rem;
            font-weight: 600;
            background-color: #ecfdf5;
            color: #059669;
            padding: 0.3rem 0.75rem;
            border-radius: 9999px;
            border: 1px solid #a7f3d0;
        }

        /* Files Table / List */
        .file-table-container {
            overflow-x: auto;
        }

        .file-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .file-table th {
            padding: 0.875rem 1rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
            background-color: var(--background);
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        .file-table td {
            padding: 1.125rem 1rem;
            font-size: 0.9rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .file-name-cell {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .file-type-icon {
            width: 36px;
            height: 36px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .icon-pdf {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .icon-doc {
            background-color: #dbeafe;
            color: #1d4ed8;
        }

        .icon-file {
            background-color: #f1f5f9;
            color: #475569;
        }

        .file-meta-name {
            font-weight: 600;
            color: var(--text-main);
            word-break: break-all;
        }

        .file-meta-path {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-family: monospace;
            margin-top: 0.15rem;
        }

        .path-badge {
            background: #f1f5f9;
            color: #475569;
            padding: 0.15rem 0.4rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-family: monospace;
            display: inline-block;
        }

        /* Action Buttons */
        .action-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            padding: 0.5rem 0.9rem;
            font-size: 0.85rem;
            font-weight: 600;
            border-radius: var(--radius-md);
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .btn-secondary {
            background-color: #ffffff;
            color: #334155;
            border-color: var(--border);
        }

        .btn-secondary:hover {
            background-color: #f8fafc;
            border-color: #cbd5e1;
        }

        .btn-danger-outline {
            background-color: #ffffff;
            color: var(--danger);
            border-color: #fecaca;
        }

        .btn-danger-outline:hover {
            background-color: var(--danger-light);
            border-color: var(--danger);
        }

        .empty-state {
            text-align: center;
            padding: 3.5rem 1.5rem;
            color: var(--text-muted);
        }

        .empty-icon {
            width: 52px;
            height: 52px;
            margin-bottom: 0.75rem;
            color: #cbd5e1;
        }

        .empty-text {
            font-size: 1.05rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.35rem;
        }

        .empty-subtext {
            font-size: 0.875rem;
            color: #94a3b8;
            max-width: 460px;
            margin: 0 auto;
        }

        /* Upload Area */
        .upload-section-desc {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 1.25rem;
        }

        .upload-dropzone {
            border: 2px dashed #cbd5e1;
            border-radius: var(--radius-lg);
            padding: 2.5rem 1.5rem;
            text-align: center;
            background-color: #fbfcfe;
            transition: all 0.25s ease;
            cursor: pointer;
            position: relative;
        }

        .upload-dropzone:hover,
        .upload-dropzone.dragover {
            border-color: var(--primary);
            background-color: var(--primary-light);
        }

        .upload-icon {
            width: 48px;
            height: 48px;
            color: var(--primary);
            margin-bottom: 0.85rem;
            transition: transform 0.2s ease;
        }

        .upload-dropzone:hover .upload-icon {
            transform: translateY(-2px);
        }

        .upload-title {
            font-weight: 600;
            font-size: 1rem;
            color: #1e293b;
            margin-bottom: 0.35rem;
        }

        .upload-hint {
            font-size: 0.825rem;
            color: var(--text-muted);
        }

        .file-selected-box {
            display: none;
            background: #ffffff;
            border: 1px solid #c7d2fe;
            border-radius: var(--radius-lg);
            padding: 1rem 1.25rem;
            margin-top: 1.25rem;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            box-shadow: var(--shadow-sm);
        }

        .selected-file-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            overflow: hidden;
        }

        .selected-file-details {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .selected-file-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-main);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .selected-file-size {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .btn-remove-selected {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 0.4rem;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .btn-remove-selected:hover {
            color: var(--danger);
            background-color: var(--danger-light);
        }

        .upload-actions {
            margin-top: 1.5rem;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        /* PDF Viewer Section */
        .preview-box {
            margin-top: 1.5rem;
            border-top: 1px solid var(--border);
            padding-top: 1.5rem;
        }

        .preview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .preview-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .pdf-frame {
            width: 100%;
            height: 520px;
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            background-color: #f1f5f9;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar {
                padding: 1rem;
                flex-direction: column;
                gap: 0.75rem;
                align-items: flex-start;
            }
            .user-menu {
                width: 100%;
                justify-content: space-between;
            }
            .welcome-card {
                padding: 1.5rem;
            }
            .card {
                padding: 1.25rem 1rem;
            }
            .action-group {
                flex-direction: column;
                align-items: stretch;
            }
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <nav class="navbar">
        <div class="nav-brand">
            <svg width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
            </svg>
            <span>學生履歷管理 Portal</span>
        </div>

        <div class="user-menu">
            <div class="user-info-chip">
                <div class="avatar"><?= mb_substr(esc($student['name'] ?? $student['student_name'] ?? '學'), 0, 1) ?></div>
                <span><?= esc($student['name'] ?? $student['student_name'] ?? '') ?> (<?= esc($student['student_id'] ?? '') ?>)</span>
            </div>
            <a href="<?= site_url('student/logout') ?>" class="btn-logout">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                登出 (Logout)
            </a>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container">
        <!-- Flash Messages -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert-flash alert-success">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span><?= esc(session()->getFlashdata('success')) ?></span>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert-flash alert-danger">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span><?= esc(session()->getFlashdata('error')) ?></span>
            </div>
        <?php endif; ?>

        <!-- Welcome Banner -->
        <div class="welcome-card">
            <div class="welcome-text">
                <h1>你好，<?= esc($student['name'] ?? $student['student_name'] ?? '同學') ?>！</h1>
                <p>歡迎進入學生專屬履歷上傳與管理中心，您可以上傳、線上檢視並管理您的履歷資料。</p>
            </div>
            <div class="profile-badges">
                <div class="badge-item">學號：<?= esc($student['student_id'] ?? '-') ?></div>
                <div class="badge-item">信箱：<?= esc($student['email'] ?? $student['student_email'] ?? '-') ?></div>
            </div>
        </div>

        <?= $this->include('partials/announcement_board') ?>

        <div class="content-grid">
            <!-- 1. 已上傳檔案清單 (Uploaded Resumes List) -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        檢視已上傳的履歷 (My Uploaded Resume)
                    </h2>
                    <?php if (!empty($file) && $file['exists']): ?>
                        <span class="badge-status-active">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            已完成繳交
                        </span>
                    <?php endif; ?>
                </div>

                <?php if (empty($file) || !$file['exists']): ?>
                    <div class="empty-state">
                        <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <div class="empty-text">目前尚無上傳任何履歷檔案</div>
                        <div class="empty-subtext">請使用下方上傳區域上傳您的 PDF 格式履歷（單檔大小限制 3MB 以內）。</div>
                    </div>
                <?php else: ?>
                    <div class="file-table-container">
                        <table class="file-table">
                            <thead>
                                <tr>
                                    <th>檔案名稱 / 本地路徑</th>
                                    <th>檔案大小</th>
                                    <th>上傳時間</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="file-name-cell">
                                            <div class="file-type-icon <?= $file['is_pdf'] ? 'icon-pdf' : ($file['extension'] === 'docx' || $file['extension'] === 'doc' ? 'icon-doc' : 'icon-file') ?>">
                                                <?= strtoupper($file['extension']) ?>
                                            </div>
                                            <div>
                                                <div class="file-meta-name"><?= esc($file['name']) ?></div>
                                                <div class="file-meta-path">
                                                    本地相對路徑: <span class="path-badge"><?= esc($file['relative_path']) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= esc($file['size']) ?></td>
                                    <td><?= esc($file['uploaded_at'] ?? '未知') ?></td>
                                    <td>
                                        <div class="action-group">
                                            <a href="<?= site_url('student/viewFile') ?>" target="_blank" class="btn btn-primary" title="線上檢視 / 預覽">
                                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                檢視
                                            </a>
                                            <a href="<?= site_url('student/download') ?>" class="btn btn-secondary" title="下載檔案到電腦">
                                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                下載
                                            </a>
                                            <form action="<?= site_url('student/deleteFile') ?>" method="post" onsubmit="return confirm('確定要刪除已上傳的履歷檔案嗎？此操作將自本地資料夾中移除該檔案。');" style="display:inline;">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-danger-outline" title="刪除此檔案">
                                                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    刪除
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- PDF 在線預覽區塊 (PDF Inline Viewer) -->
                    <?php if ($file['is_pdf']): ?>
                        <div class="preview-box">
                            <div class="preview-header">
                                <div class="preview-title">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    PDF 檔案預覽 (Preview)
                                </div>
                                <a href="<?= site_url('student/viewFile') ?>" target="_blank" class="btn btn-secondary" style="font-size: 0.8rem; padding: 0.35rem 0.75rem;">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                    開啟新分頁全螢幕檢視
                                </a>
                            </div>
                            <iframe src="<?= site_url('student/viewFile') ?>" class="pdf-frame" title="PDF Preview"></iframe>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- 2. 上傳履歷檔案區塊 (Upload Resume File) -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        <?= (!empty($file) && $file['exists']) ? '重新上傳 / 替換履歷檔案' : '上傳新履歷檔案 (Upload Resume File)' ?>
                    </h2>
                </div>

                <p class="section-desc">
                    支援 PDF 格式，檔案大小限 3MB 以下。檔案將以安全加密機制直接寫入系統資料庫 (SQLite) 供審閱使用。
                    <span style="color: #d97706; font-weight: 500;">（注意：重新上傳將會自動覆蓋您先前繳交的履歷檔案）</span>
                </p>

                <form action="<?= site_url('student/upload') ?>" method="post" enctype="multipart/form-data" id="resumeUploadForm">
                    <?= csrf_field() ?>

                    <input type="file" name="resume" id="fileInputElement" accept=".pdf,application/pdf" style="display: none;">

                    <div class="upload-dropzone" id="dropzoneElement">
                        <svg class="upload-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <div class="upload-title">點擊選擇檔案 或 將履歷檔案拖曳至此處</div>
                        <div class="upload-hint">支援格式：PDF（單檔最大上限 3MB）</div>
                    </div>

                    <!-- 選取檔案資訊顯示卡片 -->
                    <div class="file-selected-box" id="fileSelectedBox">
                        <div class="selected-file-info">
                            <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--primary); flex-shrink: 0;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <div class="selected-file-details">
                                <span class="selected-file-name" id="selectedFileName">resume.pdf</span>
                                <span class="selected-file-size" id="selectedFileSize">1.2 MB</span>
                            </div>
                        </div>
                        <button type="button" class="btn-remove-selected" id="btnCancelFile" title="取消選擇">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="upload-actions">
                        <button type="submit" class="btn btn-primary" id="btnSubmitUpload" style="padding: 0.65rem 1.5rem; font-size: 0.95rem;" disabled>
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            確認上傳檔案 (Confirm Upload)
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dropzone = document.getElementById('dropzoneElement');
            const fileInput = document.getElementById('fileInputElement');
            const selectedBox = document.getElementById('fileSelectedBox');
            const selectedName = document.getElementById('selectedFileName');
            const selectedSize = document.getElementById('selectedFileSize');
            const btnCancel = document.getElementById('btnCancelFile');
            const btnSubmit = document.getElementById('btnSubmitUpload');
            const form = document.getElementById('resumeUploadForm');

            const MAX_FILE_SIZE = 3 * 1024 * 1024; // 3MB
            const ALLOWED_EXTS = ['pdf'];

            // Click to select
            dropzone.addEventListener('click', () => {
                fileInput.click();
            });

            // Drag & Drop events
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.add('dragover');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.remove('dragover');
                }, false);
            });

            dropzone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const files = dt.files;
                if (files.length > 0) {
                    handleFileSelection(files[0]);
                }
            });

            fileInput.addEventListener('change', (e) => {
                if (fileInput.files.length > 0) {
                    handleFileSelection(fileInput.files[0]);
                }
            });

            btnCancel.addEventListener('click', () => {
                resetFileInput();
            });

            function formatBytes(bytes, decimals = 2) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const dm = decimals < 0 ? 0 : decimals;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
            }

            function handleFileSelection(file) {
                const ext = file.name.split('.').pop().toLowerCase();

                if (!ALLOWED_EXTS.includes(ext)) {
                    alert('檔案格式不符！僅允許上傳 PDF 檔案。');
                    resetFileInput();
                    return;
                }

                if (file.size > MAX_FILE_SIZE) {
                    alert('檔案大小超過上限（最大 3MB）！目前大小為 ' + formatBytes(file.size));
                    resetFileInput();
                    return;
                }

                // If user dragged a file, assign to input files via DataTransfer
                if (fileInput.files[0] !== file) {
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    fileInput.files = dataTransfer.files;
                }

                selectedName.textContent = file.name;
                selectedSize.textContent = formatBytes(file.size);
                selectedBox.style.display = 'flex';
                btnSubmit.disabled = false;
            }

            function resetFileInput() {
                fileInput.value = '';
                selectedBox.style.display = 'none';
                btnSubmit.disabled = true;
            }

            form.addEventListener('submit', () => {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = `
                    <svg class="animate-spin" width="18" height="18" fill="none" viewBox="0 0 24 24" style="animation: spin 1s linear infinite; display: inline-block;">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="opacity: 0.25;"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" style="opacity: 0.75;"></path>
                    </svg>
                    正在上傳中...
                `;
            });
        });
    </script>
    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</body>
</html>

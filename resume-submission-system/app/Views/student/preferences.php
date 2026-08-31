<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>志願序填寫 | 學生甄選與志願媒合系統</title>

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
            --warning: #d97706;
            --warning-light: #fffbeb;
            --radius-xl: 1rem;
            --radius-lg: 0.75rem;
            --radius-md: 0.5rem;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', system-ui, -apple-system, "PingFang TC", "Microsoft JhengHei", "Noto Sans TC", sans-serif;
            background-color: var(--background);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

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

        .nav-brand { display: flex; align-items: center; gap: 0.75rem; font-weight: 700; font-size: 1.15rem; color: var(--primary); }

        .user-menu { display: flex; align-items: center; gap: 1rem; }

        .nav-link {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-muted);
            text-decoration: none;
            padding: 0.45rem 0.9rem;
            border-radius: var(--radius-lg);
            transition: all 0.2s;
        }
        .nav-link:hover { background-color: var(--background); color: var(--text-main); }

        .btn-logout {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.45rem 0.9rem; border-radius: var(--radius-lg);
            background-color: #fef2f2; color: var(--danger); text-decoration: none;
            font-size: 0.875rem; font-weight: 600; border: 1px solid #fecaca;
            transition: all 0.2s;
        }
        .btn-logout:hover { background-color: #fee2e2; }

        .container { max-width: 1080px; width: 100%; margin: 2rem auto; padding: 0 1.5rem; flex: 1; }

        .alert-flash {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 1rem 1.25rem; border-radius: var(--radius-lg);
            margin-bottom: 1.5rem; font-size: 0.925rem;
        }
        .alert-success { background-color: var(--success-light); color: #065f46; border: 1px solid #a7f3d0; }
        .alert-danger { background-color: var(--danger-light); color: #991b1b; border: 1px solid #fecaca; }

        .page-hero { margin-bottom: 1.5rem; }
        .page-hero h1 { font-size: 1.6rem; font-weight: 700; color: #1e1b4b; margin-bottom: 0.4rem; }
        .page-hero p { color: var(--text-muted); font-size: 0.95rem; }

        .card {
            background: var(--surface); border-radius: var(--radius-xl);
            border: 1px solid var(--border); padding: 1.75rem 2rem;
            box-shadow: var(--shadow-sm); margin-bottom: 1.5rem;
        }

        /* ---- Countdown banner ---- */
        .countdown-banner {
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;
            background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); color: #fff;
            border-radius: var(--radius-xl); padding: 1.1rem 1.5rem; margin-bottom: 1.5rem;
            box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.3);
        }
        .countdown-banner.is-urgent { background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); box-shadow: 0 10px 20px -5px rgba(220, 38, 38, 0.35); }
        .countdown-banner.is-over { background: #64748b; box-shadow: none; }
        .countdown-label { font-size: 0.85rem; opacity: 0.9; margin-bottom: 0.25rem; }
        .countdown-value { font-size: 1.5rem; font-weight: 700; font-variant-numeric: tabular-nums; letter-spacing: 0.02em; }
        .countdown-deadline { font-size: 0.8rem; opacity: 0.85; }

        /* ---- Locked / submitted view ---- */
        .locked-banner {
            display: flex; align-items: center; gap: 0.75rem;
            background: var(--success-light); color: #065f46;
            border: 1px solid #a7f3d0; border-radius: var(--radius-lg);
            padding: 1rem 1.25rem; margin-bottom: 1.5rem; font-size: 0.925rem;
        }

        .rank-list { list-style: none; display: flex; flex-direction: column; gap: 0.75rem; }

        .rank-row {
            display: flex; align-items: center; gap: 1rem;
            padding: 0.9rem 1.1rem; border: 1px solid var(--border);
            border-radius: var(--radius-lg); background: var(--surface);
        }

        .rank-badge {
            flex-shrink: 0; width: 32px; height: 32px; border-radius: 50%;
            background: var(--primary); color: #fff; font-weight: 700; font-size: 0.9rem;
            display: flex; align-items: center; justify-content: center;
        }
        .rank-row--locked .rank-badge { background: #94a3b8; }
        .rank-row:first-child .rank-badge { background: var(--warning); }

        .rank-name { font-weight: 600; font-size: 1rem; }

        .receipt-cta { display: flex; justify-content: flex-end; margin-top: 1.5rem; }

        /* ---- Editable picker ---- */
        .picker-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }

        .picker-col-title {
            font-size: 0.95rem; font-weight: 700; color: var(--text-main);
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 0.9rem;
        }

        .count-pill {
            font-size: 0.8rem; font-weight: 700; color: var(--primary);
            background: var(--primary-light); padding: 0.2rem 0.65rem; border-radius: 9999px;
        }

        .search-input {
            width: 100%; padding: 0.65rem 0.9rem; border: 1px solid var(--border);
            border-radius: var(--radius-md); font-size: 0.9rem; margin-bottom: 0.75rem;
            font-family: inherit; outline: none; transition: border-color 0.2s, box-shadow 0.2s;
        }
        .search-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15); }

        .uni-list {
            list-style: none; border: 1px solid var(--border); border-radius: var(--radius-lg);
            max-height: 420px; overflow-y: auto; background: var(--background);
        }

        .uni-item {
            display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;
            padding: 0.7rem 0.9rem; border-bottom: 1px solid var(--border);
            font-size: 0.9rem; background: var(--surface);
        }
        .uni-item:last-child { border-bottom: none; }
        .uni-item.is-picked { opacity: 0.45; }

        .uni-item-name { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

        .uni-item-actions { display: flex; align-items: center; gap: 0.4rem; flex-shrink: 0; }

        .uni-info-btn, .uni-add-btn {
            flex-shrink: 0; width: 26px; height: 26px; border-radius: 50%;
            font-size: 0.9rem; line-height: 1; cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: all 0.15s; border: 1px solid var(--border); background: #fff; color: var(--text-muted);
        }
        .uni-info-btn:hover { background: var(--background); color: var(--text-main); }

        .uni-add-btn { border-color: var(--primary); background: var(--primary-light); color: var(--primary); font-size: 1rem; }
        .uni-add-btn:hover:not(:disabled) { background: var(--primary); color: #fff; }
        .uni-add-btn:disabled { opacity: 0.4; cursor: not-allowed; }

        .uni-empty { padding: 1.5rem; text-align: center; color: var(--text-muted); font-size: 0.875rem; }

        .selected-list { list-style: none; display: flex; flex-direction: column; gap: 0.6rem; min-height: 120px; }

        .selected-item {
            display: flex; align-items: center; gap: 0.7rem;
            padding: 0.65rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius-lg);
            background: var(--surface); cursor: grab;
        }
        .selected-item.dragging { opacity: 0.4; }
        .selected-item.drag-over { border-color: var(--primary); background: var(--primary-light); }

        .drag-handle { color: #cbd5e1; font-size: 1.1rem; line-height: 1; user-select: none; flex-shrink: 0; }

        .selected-item .rank-badge { width: 26px; height: 26px; font-size: 0.8rem; }

        .selected-name { flex: 1; font-weight: 600; font-size: 0.9rem; word-break: break-all; }

        .selected-actions { display: flex; gap: 0.25rem; flex-shrink: 0; }

        .icon-btn {
            width: 26px; height: 26px; border-radius: var(--radius-md); border: 1px solid var(--border);
            background: #fff; color: var(--text-muted); cursor: pointer; font-size: 0.8rem;
            display: flex; align-items: center; justify-content: center; transition: all 0.15s;
        }
        .icon-btn:hover:not(:disabled) { background: var(--background); color: var(--text-main); }
        .icon-btn:disabled { opacity: 0.35; cursor: not-allowed; }
        .icon-btn.icon-remove:hover:not(:disabled) { background: var(--danger-light); color: var(--danger); border-color: #fecaca; }

        .selected-empty {
            border: 2px dashed var(--border); border-radius: var(--radius-lg);
            padding: 2rem 1rem; text-align: center; color: var(--text-muted); font-size: 0.875rem;
        }

        .picker-footer {
            display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;
            margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--border);
        }

        .picker-hint { font-size: 0.8rem; color: var(--text-muted); }

        .footer-actions { display: flex; gap: 0.75rem; }

        .btn-draft {
            padding: 0.75rem 1.5rem; background-color: #fff; color: var(--text-main);
            border: 1px solid var(--border); border-radius: var(--radius-lg); font-weight: 600; font-size: 0.95rem;
            cursor: pointer; transition: all 0.2s;
        }
        .btn-draft:hover { background-color: var(--background); }

        .btn-submit {
            padding: 0.75rem 1.75rem; background-color: var(--primary); color: white;
            border: none; border-radius: var(--radius-lg); font-weight: 600; font-size: 0.95rem;
            cursor: pointer; transition: background-color 0.2s;
        }
        .btn-submit:hover:not(:disabled) { background-color: var(--primary-hover); }
        .btn-submit:disabled { background-color: #c7c9f5; cursor: not-allowed; }

        .rules-box {
            background: var(--primary-light); border: 1px solid #c7d2fe; border-radius: var(--radius-lg);
            padding: 0.9rem 1.1rem; margin-bottom: 1.5rem; font-size: 0.85rem; color: #3730a3; line-height: 1.6;
        }
        .rules-box strong { color: #312e81; }

        .draft-banner {
            display: flex; align-items: center; gap: 0.75rem;
            background: var(--warning-light); color: #92400e;
            border: 1px solid #fde68a; border-radius: var(--radius-lg);
            padding: 0.9rem 1.1rem; margin-bottom: 1.5rem; font-size: 0.875rem;
        }

        .past-deadline-banner {
            display: flex; align-items: center; gap: 0.75rem;
            background: #f1f5f9; color: var(--text-muted);
            border: 1px solid var(--border); border-radius: var(--radius-lg);
            padding: 0.9rem 1.1rem; margin-bottom: 1.5rem; font-size: 0.875rem;
        }

        /* ---- Info modal ---- */
        .modal-overlay {
            display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.5);
            align-items: center; justify-content: center; z-index: 1000; padding: 1rem;
        }
        .modal-overlay.is-open { display: flex; }
        .modal-box {
            background: #fff; border-radius: var(--radius-xl); box-shadow: var(--shadow-md);
            max-width: 420px; width: 100%; padding: 1.75rem;
        }
        .modal-title { font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem; color: #1e1b4b; }
        .modal-row { display: flex; justify-content: space-between; padding: 0.6rem 0; border-bottom: 1px solid var(--border); font-size: 0.9rem; }
        .modal-row:last-of-type { border-bottom: none; }
        .modal-row-label { color: var(--text-muted); }
        .modal-row-value { font-weight: 600; }
        .modal-note { margin-top: 1rem; font-size: 0.8rem; color: var(--text-muted); line-height: 1.6; }
        .modal-close {
            margin-top: 1.25rem; width: 100%; padding: 0.65rem; background: var(--primary-light);
            color: var(--primary); border: none; border-radius: var(--radius-md); font-weight: 600;
            cursor: pointer; font-size: 0.9rem;
        }

        @media (max-width: 860px) {
            .picker-grid { grid-template-columns: 1fr; }
            .navbar { padding: 1rem; }
            .card { padding: 1.25rem 1.25rem; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <svg width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
            </svg>
            <span>學生履歷管理 Portal</span>
        </div>
        <div class="user-menu">
            <a href="<?= site_url('student/dashboard') ?>" class="nav-link">返回控制台</a>
            <a href="<?= site_url('student/logout') ?>" class="btn-logout">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                登出 (Logout)
            </a>
        </div>
    </nav>

    <div class="container">
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

        <div class="page-hero">
            <h1>志願序填寫</h1>
            <p>從下方大學列表中選擇 6 個志願，並自由拖曳或使用上下鍵調整排序。</p>
        </div>

        <?php if (!$isLocked): ?>
            <div class="countdown-banner" id="countdownBanner">
                <div>
                    <div class="countdown-label">距離志願選填截止還有</div>
                    <div class="countdown-value" id="countdownValue">計算中...</div>
                </div>
                <div class="countdown-deadline">截止時間：<?= esc(date('Y/m/d H:i', strtotime($deadline))) ?></div>
            </div>
        <?php endif; ?>

        <?php if ($isLocked): ?>
            <div class="locked-banner">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                <span>您的志願序已於 <?= esc($preference['submitted_at']) ?> 送出，內容已鎖定，無法再修改。</span>
            </div>

            <div class="card">
                <ol class="rank-list">
                    <?php foreach ($choices as $index => $choice): ?>
                        <li class="rank-row rank-row--locked">
                            <span class="rank-badge"><?= $index + 1 ?></span>
                            <span class="rank-name"><?= esc($choice) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ol>

                <div class="receipt-cta">
                    <a href="<?= site_url('student/preferences/receipt') ?>" class="btn-submit" style="text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H8a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        下載 / 列印志願確認單
                    </a>
                </div>
            </div>
        <?php elseif ($pastDeadline): ?>
            <div class="past-deadline-banner">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>志願選填已於 <?= esc(date('Y/m/d H:i', strtotime($deadline))) ?> 截止，目前無法儲存草稿或送出志願序。</span>
            </div>

            <?php if (!empty($filledChoices)): ?>
                <div class="card">
                    <p class="picker-hint" style="margin-bottom: 1rem;">以下為您截止前最後儲存的草稿內容（未送出）：</p>
                    <ol class="rank-list">
                        <?php foreach ($filledChoices as $index => $choice): ?>
                            <li class="rank-row rank-row--locked">
                                <span class="rank-badge"><?= $index + 1 ?></span>
                                <span class="rank-name"><?= esc($choice) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <?php if (!empty($filledChoices)): ?>
                <div class="draft-banner">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    <span>您有尚未送出的草稿（已存 <?= count($filledChoices) ?> 個志願），可繼續編輯或直接送出。</span>
                </div>
            <?php endif; ?>

            <div class="rules-box">
                <strong>填寫規則：</strong>請從左側列表選擇恰好 6 所學校加入右側志願序，可拖曳排序卡片或使用上下鍵調整順序。可先點擊「儲存草稿」保留進度，之後隨時回來繼續編輯；確認無誤後點擊「送出志願序」，<strong>送出後即無法再修改</strong>，請務必確認排序正確。
            </div>

            <form id="preferenceForm" class="card" action="<?= site_url('student/preferences') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" id="actionField" value="draft">
                <div id="hiddenChoiceInputs"></div>

                <div class="picker-grid">
                    <div>
                        <div class="picker-col-title">
                            <span>大學列表</span>
                        </div>
                        <input type="text" class="search-input" id="uniSearch" placeholder="搜尋學校名稱...">
                        <ul class="uni-list" id="uniList"></ul>
                    </div>

                    <div>
                        <div class="picker-col-title">
                            <span>我的志願序</span>
                            <span class="count-pill" id="countPill">已選擇 0 / 6</span>
                        </div>
                        <ul class="selected-list" id="selectedList"></ul>
                        <div class="selected-empty" id="selectedEmpty">尚未選擇任何志願，請從左側列表加入。</div>
                    </div>
                </div>

                <div class="picker-footer">
                    <span class="picker-hint">草稿可儲存 0～6 個志願；最終送出需恰好 6 個，且送出後無法修改。</span>
                    <div class="footer-actions">
                        <button type="button" class="btn-draft" id="draftBtn">儲存草稿</button>
                        <button type="button" class="btn-submit" id="submitBtn" disabled>送出志願序</button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <div class="modal-overlay" id="infoModal">
        <div class="modal-box">
            <div class="modal-title" id="infoModalTitle">學校名稱</div>
            <div class="modal-row">
                <span class="modal-row-label">學校類型</span>
                <span class="modal-row-value" id="infoModalType">-</span>
            </div>
            <div class="modal-row">
                <span class="modal-row-label">所在地區</span>
                <span class="modal-row-value" id="infoModalCity">-</span>
            </div>
            <p class="modal-note">實際招生名額、學系資訊與報名資格請以校方最新公告之招生簡章為準，本系統僅提供基本參考資訊。</p>
            <button type="button" class="modal-close" id="infoModalClose">關閉</button>
        </div>
    </div>

    <?php if (!$isLocked): ?>
    <script>
        (function () {
            const DEADLINE_TS = <?= (int) strtotime($deadline) ?> * 1000;
            const countdownValue = document.getElementById('countdownValue');
            const countdownBanner = document.getElementById('countdownBanner');

            if (countdownValue && countdownBanner) {
                function renderCountdown() {
                    const remaining = DEADLINE_TS - Date.now();

                    if (remaining <= 0) {
                        countdownValue.textContent = '已截止';
                        countdownBanner.classList.add('is-over');
                        return;
                    }

                    const totalSeconds = Math.floor(remaining / 1000);
                    const days = Math.floor(totalSeconds / 86400);
                    const hours = Math.floor((totalSeconds % 86400) / 3600);
                    const minutes = Math.floor((totalSeconds % 3600) / 60);
                    const seconds = totalSeconds % 60;

                    countdownValue.textContent = days + ' 天 ' + hours + ' 小時 ' + minutes + ' 分 ' + seconds + ' 秒';
                    countdownBanner.classList.toggle('is-urgent', remaining < 24 * 3600 * 1000);
                }

                renderCountdown();
                setInterval(renderCountdown, 1000);
            }
        })();
    </script>
    <?php endif; ?>

    <?php if (!$isLocked && !$pastDeadline): ?>
    <script>
        (function () {
            const ALL_UNIVERSITIES = <?= json_encode($universities, JSON_UNESCAPED_UNICODE) ?>;
            const MAX_CHOICES = 6;

            let selected = <?= json_encode($filledChoices, JSON_UNESCAPED_UNICODE) ?>;
            let dragIndex = null;

            const uniList = document.getElementById('uniList');
            const uniSearch = document.getElementById('uniSearch');
            const selectedList = document.getElementById('selectedList');
            const selectedEmpty = document.getElementById('selectedEmpty');
            const countPill = document.getElementById('countPill');
            const submitBtn = document.getElementById('submitBtn');
            const draftBtn = document.getElementById('draftBtn');
            const actionField = document.getElementById('actionField');
            const hiddenInputs = document.getElementById('hiddenChoiceInputs');
            const form = document.getElementById('preferenceForm');

            const infoModal = document.getElementById('infoModal');
            const infoModalTitle = document.getElementById('infoModalTitle');
            const infoModalType = document.getElementById('infoModalType');
            const infoModalCity = document.getElementById('infoModalCity');
            const infoModalClose = document.getElementById('infoModalClose');

            function findUniversity(name) {
                return ALL_UNIVERSITIES.find((u) => u.name === name);
            }

            function openInfoModal(name) {
                const uni = findUniversity(name);
                if (!uni) {
                    return;
                }
                infoModalTitle.textContent = uni.name;
                infoModalType.textContent = uni.type;
                infoModalCity.textContent = uni.city;
                infoModal.classList.add('is-open');
            }

            infoModalClose.addEventListener('click', () => infoModal.classList.remove('is-open'));
            infoModal.addEventListener('click', (e) => {
                if (e.target === infoModal) {
                    infoModal.classList.remove('is-open');
                }
            });

            function renderUniList() {
                const keyword = uniSearch.value.trim();
                uniList.innerHTML = '';

                const matches = ALL_UNIVERSITIES.filter((u) => !keyword || u.name.includes(keyword));

                if (matches.length === 0) {
                    const empty = document.createElement('li');
                    empty.className = 'uni-empty';
                    empty.textContent = '找不到符合的學校';
                    uniList.appendChild(empty);
                    return;
                }

                matches.forEach((uni) => {
                    const picked = selected.includes(uni.name);
                    const li = document.createElement('li');
                    li.className = 'uni-item' + (picked ? ' is-picked' : '');

                    const label = document.createElement('span');
                    label.className = 'uni-item-name';
                    label.textContent = uni.name;
                    li.appendChild(label);

                    const actions = document.createElement('div');
                    actions.className = 'uni-item-actions';

                    const infoBtn = document.createElement('button');
                    infoBtn.type = 'button';
                    infoBtn.className = 'uni-info-btn';
                    infoBtn.textContent = 'ℹ';
                    infoBtn.setAttribute('aria-label', '查看 ' + uni.name + ' 詳細資訊');
                    infoBtn.addEventListener('click', () => openInfoModal(uni.name));
                    actions.appendChild(infoBtn);

                    const addBtn = document.createElement('button');
                    addBtn.type = 'button';
                    addBtn.className = 'uni-add-btn';
                    addBtn.textContent = '+';
                    addBtn.disabled = picked || selected.length >= MAX_CHOICES;
                    addBtn.setAttribute('aria-label', '加入 ' + uni.name);
                    addBtn.addEventListener('click', () => addChoice(uni.name));
                    actions.appendChild(addBtn);

                    li.appendChild(actions);
                    uniList.appendChild(li);
                });
            }

            function addChoice(name) {
                if (selected.includes(name) || selected.length >= MAX_CHOICES) {
                    return;
                }
                selected.push(name);
                renderAll();
            }

            function removeChoice(index) {
                selected.splice(index, 1);
                renderAll();
            }

            function moveChoice(index, delta) {
                const target = index + delta;
                if (target < 0 || target >= selected.length) {
                    return;
                }
                const [item] = selected.splice(index, 1);
                selected.splice(target, 0, item);
                renderAll();
            }

            function renderSelectedList() {
                selectedList.innerHTML = '';
                selectedEmpty.style.display = selected.length === 0 ? 'block' : 'none';

                selected.forEach((name, index) => {
                    const li = document.createElement('li');
                    li.className = 'selected-item';
                    li.draggable = true;
                    li.dataset.index = String(index);

                    li.addEventListener('dragstart', () => {
                        dragIndex = index;
                        li.classList.add('dragging');
                    });
                    li.addEventListener('dragend', () => {
                        li.classList.remove('dragging');
                    });
                    li.addEventListener('dragover', (e) => {
                        e.preventDefault();
                        li.classList.add('drag-over');
                    });
                    li.addEventListener('dragleave', () => {
                        li.classList.remove('drag-over');
                    });
                    li.addEventListener('drop', (e) => {
                        e.preventDefault();
                        li.classList.remove('drag-over');
                        if (dragIndex === null || dragIndex === index) {
                            return;
                        }
                        const [item] = selected.splice(dragIndex, 1);
                        selected.splice(index, 0, item);
                        dragIndex = null;
                        renderAll();
                    });

                    const handle = document.createElement('span');
                    handle.className = 'drag-handle';
                    handle.textContent = '⠿';
                    li.appendChild(handle);

                    const badge = document.createElement('span');
                    badge.className = 'rank-badge';
                    badge.textContent = String(index + 1);
                    li.appendChild(badge);

                    const nameEl = document.createElement('span');
                    nameEl.className = 'selected-name';
                    nameEl.textContent = name;
                    li.appendChild(nameEl);

                    const actions = document.createElement('div');
                    actions.className = 'selected-actions';

                    const infoBtn = document.createElement('button');
                    infoBtn.type = 'button';
                    infoBtn.className = 'icon-btn';
                    infoBtn.textContent = 'ℹ';
                    infoBtn.setAttribute('aria-label', '查看詳細資訊');
                    infoBtn.addEventListener('click', () => openInfoModal(name));
                    actions.appendChild(infoBtn);

                    const upBtn = document.createElement('button');
                    upBtn.type = 'button';
                    upBtn.className = 'icon-btn';
                    upBtn.innerHTML = '&uarr;';
                    upBtn.disabled = index === 0;
                    upBtn.setAttribute('aria-label', '上移');
                    upBtn.addEventListener('click', () => moveChoice(index, -1));
                    actions.appendChild(upBtn);

                    const downBtn = document.createElement('button');
                    downBtn.type = 'button';
                    downBtn.className = 'icon-btn';
                    downBtn.innerHTML = '&darr;';
                    downBtn.disabled = index === selected.length - 1;
                    downBtn.setAttribute('aria-label', '下移');
                    downBtn.addEventListener('click', () => moveChoice(index, 1));
                    actions.appendChild(downBtn);

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'icon-btn icon-remove';
                    removeBtn.innerHTML = '&times;';
                    removeBtn.setAttribute('aria-label', '移除');
                    removeBtn.addEventListener('click', () => removeChoice(index));
                    actions.appendChild(removeBtn);

                    li.appendChild(actions);
                    selectedList.appendChild(li);
                });
            }

            function renderHiddenInputs() {
                hiddenInputs.innerHTML = '';
                selected.forEach((name) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'choices[]';
                    input.value = name;
                    hiddenInputs.appendChild(input);
                });
            }

            function renderAll() {
                renderUniList();
                renderSelectedList();
                renderHiddenInputs();
                countPill.textContent = '已選擇 ' + selected.length + ' / ' + MAX_CHOICES;
                submitBtn.disabled = selected.length !== MAX_CHOICES;
            }

            uniSearch.addEventListener('input', renderUniList);

            draftBtn.addEventListener('click', () => {
                actionField.value = 'draft';
                draftBtn.disabled = true;
                draftBtn.textContent = '儲存中...';
                form.submit();
            });

            submitBtn.addEventListener('click', () => {
                if (selected.length !== MAX_CHOICES) {
                    alert('請選擇恰好 6 個志願後再送出。');
                    return;
                }
                if (!confirm('送出後將無法再修改志願序，確定要送出嗎？')) {
                    return;
                }
                actionField.value = 'submit';
                submitBtn.disabled = true;
                submitBtn.textContent = '送出中...';
                form.submit();
            });

            renderAll();
        })();
    </script>
    <?php endif; ?>
</body>
</html>

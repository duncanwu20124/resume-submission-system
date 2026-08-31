<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($user['name']) ?> - 履歷詳細資料 | 學生履歷繳交系統</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
</head>
<body>

<header class="sys-navbar">
    <div class="sys-navbar__inner">
        <div class="sys-navbar__brand">
            <svg width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--sys-primary);">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
            </svg>
            <h1 class="sys-navbar__title">管理員系統</h1>
            <span class="sys-navbar__badge">Admin Portal</span>
        </div>
        <div class="sys-navbar__user">
            <a class="sys-navbar__link" href="/AdminController">返回資料清單</a>
            <a class="sys-navbar__link sys-navbar__link--btn" href="/AdminController/logout">登出</a>
        </div>
    </div>
</header>

<main class="admin-shell">
    <div class="page-header">
        <h2 class="page-header__title">申請者詳細資料：<?= esc($user['name']) ?></h2>
        <div class="page-header__actions">
            <a class="btn btn--secondary" href="/AdminController" onclick="if(document.referrer && document.referrer.includes('/AdminController')) { history.back(); return false; }">返回上一頁</a>
        </div>
    </div>

    <div class="detail-layout">
        <!-- 左側：申請者與檔案基本資訊 -->
        <section class="detail-card" aria-labelledby="applicant-info-heading">
            <div class="detail-card__header" id="applicant-info-heading">基本資料與檔案狀態</div>
            
            <table class="detail-table">
                <tbody>
                    <tr>
                        <th scope="row">使用者 ID</th>
                        <td><?= esc($user['student_id']) ?></td>
                    </tr>
                    <tr>
                        <th scope="row">使用者姓名</th>
                        <td><?= esc($user['name']) ?></td>
                    </tr>
                    <tr>
                        <th scope="row">Email</th>
                        <td><?= esc($user['email']) ?></td>
                    </tr>
                    <tr>
                        <th scope="row">履歷狀態</th>
                        <td>
                            <?php if (!empty($user['file_name'])): ?>
                                <span class="tag-status tag-status--success">已上傳</span>
                            <?php else: ?>
                                <span class="tag-status tag-status--muted">尚未上傳</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">檔案名稱</th>
                        <td><?= !empty($user['file_name']) ? esc($user['file_name']) : '尚無檔案' ?></td>
                    </tr>
                    <tr>
                        <th scope="row">上傳時間</th>
                        <td><?= !empty($user['uploaded_at']) ? esc($user['uploaded_at']) : '尚無紀錄' ?></td>
                    </tr>
                </tbody>
            </table>

            <?php if (!empty($user['file_name'])): ?>
                <div class="detail-card__footer">
                    <a class="btn btn--primary btn--block" href="/AdminController/download/<?= $user['id'] ?>">下載履歷檔案</a>
                </div>
            <?php endif; ?>
        </section>

        <!-- 右側：PDF 預覽區塊 -->
        <section class="preview-card" aria-labelledby="preview-heading">
            <div class="preview-card__header">
                <h3 class="preview-card__title" id="preview-heading">履歷檔案預覽</h3>
                <?php if (!empty($user['file_name'])): ?>
                    <span style="font-size: 0.8rem; color: var(--sys-text-secondary);"><?= esc($user['file_name']) ?></span>
                <?php endif; ?>
            </div>

            <?php 
            $ext = strtolower(pathinfo($user['file_name'] ?? '', PATHINFO_EXTENSION));
            if ($ext === 'pdf'): 
            ?>
                <iframe class="preview-card__frame" src="/AdminController/viewFile/<?= $user['id'] ?>" title="<?= esc($user['name']) ?> 的 PDF 履歷預覽"></iframe>
            <?php elseif (!empty($user['file_name'])): ?>
                <div class="preview-empty">
                    <p>此檔案格式非 PDF，無法於瀏覽器內直接預覽。</p>
                    <p style="margin-top: 8px;">請點擊左側「下載履歷檔案」於本機開啟檢視。</p>
                </div>
            <?php else: ?>
                <div class="preview-empty">
                    <p>該申請者尚未上傳履歷檔案。</p>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

</body>
</html>

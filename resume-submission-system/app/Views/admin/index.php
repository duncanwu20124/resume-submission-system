<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>履歷繳交資料管理 | 學生履歷繳交系統</title>

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
            <a class="sys-navbar__link" href="/AdminController/announcements">公告管理</a>
            <a class="sys-navbar__link sys-navbar__link--btn" href="/AdminController/logout">登出</a>
        </div>
    </div>
</header>

<main class="admin-shell">
    <div class="page-header">
        <h2 class="page-header__title">履歷繳交資料管理</h2>
    </div>

    <!-- 搜尋區塊 -->
    <section class="search-box" aria-label="資料搜尋">
        <form class="search-box__form" action="/AdminController/search" method="GET">
            <div class="search-group search-group--select">
                <label for="search_by">搜尋欄位</label>
                <select id="search_by" name="search_by">
                    <option value="name" <?= (!isset($search_by) || $search_by === 'name') ? 'selected' : '' ?>>使用者姓名</option>
                    <option value="id" <?= (isset($search_by) && $search_by === 'id') ? 'selected' : '' ?>>使用者 ID</option>
                    <option value="email" <?= (isset($search_by) && $search_by === 'email') ? 'selected' : '' ?>>Email</option>
                </select>
            </div>

            <div class="search-group search-group--input">
                <label for="keyword">關鍵字</label>
                <input id="keyword" type="search" name="keyword" value="<?= isset($keyword) ? esc($keyword) : '' ?>" placeholder="請輸入姓名、ID 或 Email">
            </div>

            <div class="search-actions">
                <button class="btn btn--primary" type="submit">搜尋</button>
                <?php if (isset($keyword) && $keyword !== ''): ?>
                    <a class="btn btn--secondary" href="/AdminController">清除搜尋</a>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <!-- 資料表格區塊 -->
    <section aria-label="資料清單">
        <div class="table-meta">
            <span>
                <?php if (isset($keyword) && $keyword !== ''): ?>
                    搜尋條件：<strong><?= esc($keyword) ?></strong>（
                    <?= ($search_by === 'id') ? '使用者 ID' : (($search_by === 'email') ? 'Email' : '使用者姓名') ?>
                    ）
                <?php else: ?>
                    資料狀態：全部清單
                <?php endif; ?>
            </span>
            <span class="table-meta__count">共 <?= count($users ?? []) ?> 筆資料</span>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 140px;">使用者 ID</th>
                        <th style="width: 130px;">使用者姓名</th>
                        <th>Email</th>
                        <th style="width: 260px;">履歷檔案</th>
                        <th style="width: 160px;">上傳時間</th>
                        <th style="width: 130px; text-align: center;">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="col-id" data-label="使用者 ID"><?= esc($user['student_id']) ?></td>
                                <td class="col-name" data-label="使用者姓名"><?= esc($user['name']) ?></td>
                                <td class="col-email" data-label="Email"><?= esc($user['email']) ?></td>
                                <td class="col-file" data-label="履歷檔案">
                                    <?php if (!empty($user['file_name'])): ?>
                                        <span class="tag-status tag-status--success">已上傳</span>
                                        <span class="file-name-text"><?= esc($user['file_name']) ?></span>
                                    <?php else: ?>
                                        <span class="tag-status tag-status--muted">尚未上傳</span>
                                    <?php endif; ?>
                                </td>
                                <td class="col-time" data-label="上傳時間"><?= !empty($user['uploaded_at']) ? esc($user['uploaded_at']) : '尚無紀錄' ?></td>
                                <td class="col-actions" data-label="操作">
                                    <div class="table-actions">
                                        <a class="btn btn--secondary btn--sm" href="/AdminController/show/<?= $user['id'] ?>">查看</a>
                                        <?php if (!empty($user['file_name'])): ?>
                                            <a class="btn btn--primary btn--sm" href="/AdminController/download/<?= $user['id'] ?>">下載</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td class="table-empty" colspan="6">查無符合條件之資料。請調整搜尋條件後重試。</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

</body>
</html>

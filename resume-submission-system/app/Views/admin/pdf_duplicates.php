<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF 重複檢查 | 學生甄選與志願媒合系統</title>
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
            <a class="sys-navbar__link sys-navbar__link--btn" href="/AdminController/logout">登出</a>
        </div>
    </div>
</header>

<main class="admin-shell admin-shell--with-sidebar">
    <?= view('partials/admin_sidebar') ?>
    <div class="page-header">
        <h2 class="page-header__title">PDF 內容重複檢查</h2>
    </div>

    <section class="admin-panel" aria-labelledby="duplicate-summary-heading">
        <div class="admin-panel__header">
            <h3 class="admin-panel__title" id="duplicate-summary-heading">檢查結果</h3>
            <span class="table-meta__count">已檢查 <?= esc($checked_count) ?> 份 PDF</span>
        </div>
        <div class="admin-panel__body">
            <?php if (empty($duplicate_groups)): ?>
                <p class="table-empty">目前沒有發現內容完全相同的 PDF。</p>
            <?php else: ?>
                <p class="alert alert--error" role="alert">發現 <?= esc(count($duplicate_groups)) ?> 組 PDF 內容完全相同，請確認是否為重複上傳。</p>
                <?php foreach ($duplicate_groups as $group): ?>
                    <section class="admin-panel" aria-labelledby="duplicate-group-<?= esc($group['hash']) ?>">
                        <div class="admin-panel__header">
                            <h4 class="admin-panel__title" id="duplicate-group-<?= esc($group['hash']) ?>">重複群組（<?= esc(count($group['students'])) ?> 人）</h4>
                            <span class="table-meta__count">檔案大小 <?= esc(number_format($group['file_size'])) ?> bytes</span>
                        </div>
                        <div class="table-container table-container--flat">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>學號</th>
                                        <th>姓名</th>
                                        <th>檔名</th>
                                        <th>上傳時間</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($group['students'] as $student): ?>
                                        <tr>
                                            <td data-label="學號"><?= esc($student['student_id']) ?></td>
                                            <td data-label="姓名"><?= esc($student['name']) ?></td>
                                            <td data-label="檔名"><?= esc($student['file_name']) ?></td>
                                            <td data-label="上傳時間"><?= esc($student['uploaded_at'] ?? '') ?></td>
                                            <td data-label="操作"><a class="btn btn--secondary btn--sm" href="/AdminController/viewFile/<?= esc($student['id']) ?>" target="_blank" rel="noopener">查看 PDF</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</main>

</body>
</html>

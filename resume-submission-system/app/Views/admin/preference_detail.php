<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($student['name']) ?> 的志願序 | 學生甄選與志願媒合系統</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
    <style>
        .pref-rank-list { list-style: none; display: flex; flex-direction: column; gap: 10px; padding: 18px; }
        .pref-rank-row {
            display: flex; align-items: center; gap: 14px;
            padding: 12px 14px; border: 1px solid var(--sys-border); border-radius: var(--sys-radius);
            background: var(--sys-surface-alt);
        }
        .pref-rank-badge {
            flex-shrink: 0; width: 30px; height: 30px; border-radius: 50%;
            background: var(--sys-primary); color: #fff; font-weight: 700; font-size: 0.85rem;
            display: flex; align-items: center; justify-content: center;
        }
        .pref-rank-row:first-child .pref-rank-badge { background: var(--sys-warning); }
        .pref-rank-name { font-weight: 600; color: var(--sys-text); }
    </style>
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
            <a class="sys-navbar__link" href="/AdminController/preferences">返回志願序清單</a>
            <a class="sys-navbar__link sys-navbar__link--btn" href="/AdminController/logout">登出</a>
        </div>
    </div>
</header>

<main class="admin-shell admin-shell--narrow">
    <div class="page-header">
        <h2 class="page-header__title">志願序：<?= esc($student['name']) ?></h2>
        <div class="page-header__actions">
            <a class="btn btn--secondary" href="/AdminController/preferences">返回清單</a>
        </div>
    </div>

    <section class="detail-card" aria-labelledby="pref-student-heading" style="margin-bottom: 20px;">
        <div class="detail-card__header" id="pref-student-heading">學生基本資料</div>
        <table class="detail-table">
            <tbody>
                <tr>
                    <th scope="row">學號</th>
                    <td><?= esc($student['student_id']) ?></td>
                </tr>
                <tr>
                    <th scope="row">姓名</th>
                    <td><?= esc($student['name']) ?></td>
                </tr>
                <tr>
                    <th scope="row">Email</th>
                    <td><?= esc($student['email']) ?></td>
                </tr>
                <tr>
                    <th scope="row">送出時間</th>
                    <td><?= esc($preference['submitted_at']) ?></td>
                </tr>
            </tbody>
        </table>
    </section>

    <section class="detail-card" aria-labelledby="pref-order-heading">
        <div class="detail-card__header" id="pref-order-heading">志願序（共 <?= count($choices) ?> 項）</div>
        <ol class="pref-rank-list">
            <?php foreach ($choices as $index => $choice): ?>
                <li class="pref-rank-row">
                    <span class="pref-rank-badge"><?= $index + 1 ?></span>
                    <span class="pref-rank-name"><?= esc($choice) ?></span>
                </li>
            <?php endforeach; ?>
        </ol>
    </section>

    <section class="admin-panel" aria-labelledby="pref-reset-heading">
        <div class="admin-panel__header">
            <h3 class="admin-panel__title" id="pref-reset-heading">重新開放填寫</h3>
        </div>
        <div class="admin-panel__body">
            <p class="form-hint" style="margin-bottom: 12px;">重新開放後，將刪除此筆已送出的志願序，該學生登入後可重新選擇並排序 6 個志願。此操作無法復原，請確認後再執行。</p>
            <form action="/AdminController/preferences/<?= esc($student['id']) ?>/reset" method="post" onsubmit="return confirm('確定要重新開放這位學生的志願序嗎？目前已送出的排序將被清除，此操作無法復原。');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn--danger">重新開放此學生的志願序</button>
            </form>
        </div>
    </section>
</main>

</body>
</html>

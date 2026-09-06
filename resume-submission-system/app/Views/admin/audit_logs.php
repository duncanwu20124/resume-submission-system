<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>操作紀錄與審計 | 學生甄選與志願媒合系統</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
</head>
<body>

<?php
$currentRole = $current_admin_role ?? (string) (session()->get('admin_role') ?: 'super_admin');
$roleLabelMap = [
    'super_admin' => '超級管理員',
    'admin' => '一般管理員',
    'reviewer' => '審查委員',
];
$roleName = $roleLabelMap[$currentRole] ?? '管理員';
?>

<header class="sys-navbar">
    <div class="sys-navbar__inner">
        <div class="sys-navbar__brand">
            <svg width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--sys-primary);">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
            </svg>
            <h1 class="sys-navbar__title">管理員系統</h1>
            <span class="role-badge role-badge--<?= esc($currentRole) ?>"><?= esc($roleName) ?></span>
        </div>
        <div class="sys-navbar__user">
            <span id="admin-session-nav-countdown" class="admin-session-nav-countdown" aria-label="管理員登入剩餘時間">05:00</span>
            <a class="sys-navbar__link" href="/AdminController/admins">管理員帳號</a>
            <a class="sys-navbar__link sys-navbar__link--active" href="/AdminController/auditLogs">操作日誌</a>
            <a class="sys-navbar__link" href="/AdminController/preferences">志願序管理</a>
            <a class="sys-navbar__link" href="/AdminController/scoring">學生評分</a>
            <a class="sys-navbar__link" href="/AdminController/allocation">分發管理</a>
            <a class="sys-navbar__link" href="/AdminController/announcements">公告管理</a>
            <a class="sys-navbar__link" href="/AdminController">履歷資料管理</a>
            <a class="sys-navbar__link" href="/AdminController/profile">我的帳號</a>
            <a class="sys-navbar__link sys-navbar__link--btn" href="/AdminController/logout">登出</a>
        </div>
    </div>
</header>

<main class="admin-shell admin-shell--with-sidebar">
    <?= view('partials/admin_sidebar') ?>
    <div class="page-header">
        <div>
            <h2 class="page-header__title">系統操作審計紀錄 (Audit Logs)</h2>
            <p class="page-header__description">超級管理員專屬日誌：記錄所有管理員敏感操作、登入安全活動與系統核心異動歷程。</p>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert--success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert--error"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <section class="admin-panel">
        <div class="admin-panel__header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <h3 class="admin-panel__title">操作安全稽核紀錄</h3>
            <form method="get" action="/AdminController/auditLogs" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <?php if (!empty($modules)): ?>
                    <label for="filter_module" style="font-size: 0.85rem; color: var(--sys-text-muted); margin: 0;">模組篩選：</label>
                    <select id="filter_module" name="module" onchange="this.form.submit()" style="padding: 4px 8px; font-size: 0.85rem; border: 1px solid var(--sys-border); border-radius: var(--sys-radius-sm); background: #fff;">
                        <option value="">全部模組</option>
                        <?php foreach ($modules as $mod): ?>
                            <option value="<?= esc($mod) ?>" <?= ($selected_module ?? '') === $mod ? 'selected' : '' ?>><?= esc($mod) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>

                <label for="filter_per_page" style="font-size: 0.85rem; color: var(--sys-text-muted); margin: 0;">每頁筆數：</label>
                <select id="filter_per_page" name="per_page" onchange="this.form.submit()" style="padding: 4px 8px; font-size: 0.85rem; border: 1px solid var(--sys-border); border-radius: var(--sys-radius-sm); background: #fff;">
                    <option value="10" <?= ($per_page ?? 20) === 10 ? 'selected' : '' ?>>10 筆</option>
                    <option value="20" <?= ($per_page ?? 20) === 20 ? 'selected' : '' ?>>20 筆</option>
                    <option value="50" <?= ($per_page ?? 20) === 50 ? 'selected' : '' ?>>50 筆</option>
                    <option value="100" <?= ($per_page ?? 20) === 100 ? 'selected' : '' ?>>100 筆</option>
                </select>

                <?php if (!empty($selected_module)): ?>
                    <a href="/AdminController/auditLogs" class="btn btn--secondary btn--sm">清除篩選</a>
                <?php endif; ?>
            </form>
        </div>
        <div class="admin-panel__body">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 170px;">時間</th>
                            <th style="width: 140px;">操作人員</th>
                            <th style="width: 120px;">模組</th>
                            <th>動作說明</th>
                            <th style="width: 120px;">IP 位址</th>
                            <th style="width: 80px; text-align: center;">狀態</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--sys-text-muted); padding: 2rem;">
                                    目前尚無符合篩選之審計紀錄。
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td style="font-size: 0.85rem; color: var(--sys-text-muted);"><?= esc($log['created_at'] ?? '-') ?></td>
                                    <td><strong><?= esc($log['admin_name'] ?? '-') ?></strong></td>
                                    <td>
                                        <span class="role-badge" style="background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe; font-size: 0.75rem;">
                                            <?= esc($log['module'] ?? '-') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= esc($log['action'] ?? '-') ?>
                                        <?php if (!empty($log['details'])): ?>
                                            <div style="font-size: 0.8rem; color: var(--sys-text-muted); margin-top: 3px;">
                                                <?= esc($log['details']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><code><?= esc($log['ip_address'] ?? '-') ?></code></td>
                                    <td style="text-align: center;">
                                        <?php if (($log['status'] ?? '成功') === '成功'): ?>
                                            <span class="tag-status tag-status--success">成功</span>
                                        <?php else: ?>
                                            <span class="tag-status tag-status--danger"><?= esc($log['status'] ?? '失敗') ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (isset($pager) && $pager->getPageCount() > 1): ?>
                <div class="pagination" style="margin: 1.5rem 0; display: flex; align-items: center; justify-content: center; gap: 1rem;">
                    <?php if ($pager->getCurrentPage() > 1): ?>
                        <a class="btn btn--secondary btn--sm" href="/AdminController/auditLogs?page=<?= $pager->getCurrentPage() - 1 ?>&per_page=<?= (int) ($per_page ?? 20) ?><?= !empty($selected_module) ? '&module=' . urlencode($selected_module) : '' ?>">上一頁</a>
                    <?php endif; ?>
                    <span>第 <?= (int) $pager->getCurrentPage() ?> / <?= (int) $pager->getPageCount() ?> 頁（共 <?= (int) ($total_logs ?? count($logs)) ?> 筆）</span>
                    <?php if ($pager->getCurrentPage() < $pager->getPageCount()): ?>
                        <a class="btn btn--secondary btn--sm" href="/AdminController/auditLogs?page=<?= $pager->getCurrentPage() + 1 ?>&per_page=<?= (int) ($per_page ?? 20) ?><?= !empty($selected_module) ? '&module=' . urlencode($selected_module) : '' ?>">下一頁</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<script src="<?= base_url('assets/js/admin.js') ?>"></script>
</body>
</html>

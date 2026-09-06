<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理員帳號與權限管理 | 學生甄選與志願媒合系統</title>

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
            <span class="sys-navbar__badge">Super Admin</span>
        </div>
        <div class="sys-navbar__user">
            <a class="sys-navbar__link" href="/AdminController">管理首頁</a>
            <a class="sys-navbar__link sys-navbar__link--btn" href="/AdminController/logout">登出</a>
        </div>
    </div>
</header>

<main class="admin-shell admin-shell--with-sidebar">
    <?= view('partials/admin_sidebar') ?>
    <div class="page-header">
        <div>
            <h2 class="page-header__title">管理員帳號與角色權限</h2>
            <p class="page-header__description">超級管理員專屬控制台：分配管理員角色（超級管理員 / 一般管理員 / 審查委員）與管理帳號狀態。</p>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert--success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert--error"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <!-- 角色權限對照說明 -->
    <section class="admin-panel" style="margin-bottom: 1.5rem;">
        <div class="admin-panel__header">
            <h3 class="admin-panel__title">角色職能規範說明</h3>
        </div>
        <div class="admin-panel__body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem;">
                <div style="background: #fdfaf0; border: 1px solid #fcd34d; border-radius: var(--sys-radius); padding: 1rem;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 0.5rem;">
                        <span class="role-badge role-badge--super_admin">超級管理員 (super_admin)</span>
                    </div>
                    <ul style="margin: 0; padding-left: 1.2rem; font-size: 0.88rem; color: #78350f; line-height: 1.6;">
                        <li>具備系統全功能最高權限</li>
                        <li>管理員帳號與角色授權管理</li>
                        <li>正式發布分發放榜權限</li>
                        <li>系統操作審計紀錄查核</li>
                    </ul>
                </div>

                <div style="background: #f5f7ff; border: 1px solid #c7d2fe; border-radius: var(--sys-radius); padding: 1rem;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 0.5rem;">
                        <span class="role-badge role-badge--admin">一般管理員 (admin)</span>
                    </div>
                    <ul style="margin: 0; padding-left: 1.2rem; font-size: 0.88rem; color: #3730a3; line-height: 1.6;">
                        <li>學生履歷管理與名冊匯出</li>
                        <li>志願序統計與重填解鎖</li>
                        <li>建立成績分發預覽批次</li>
                        <li>全站公告發布與管理</li>
                    </ul>
                </div>

                <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: var(--sys-radius); padding: 1rem;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 0.5rem;">
                        <span class="role-badge role-badge--reviewer">審查委員 (reviewer)</span>
                    </div>
                    <ul style="margin: 0; padding-left: 1.2rem; font-size: 0.88rem; color: #334155; line-height: 1.6;">
                        <li>檢視與下載學生履歷檔案</li>
                        <li>PDF 重複防弊檢查</li>
                        <li>學生評分與成績確認</li>
                        <li>🚫 禁止修改全站設定或發布分發結果</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- 管理員清單表格 -->
    <section class="admin-panel">
        <div class="admin-panel__header" style="display: flex; align-items: center; justify-content: space-between;">
            <h3 class="admin-panel__title">目前管理員名冊（共 <?= count($admins) ?> 位）</h3>
            <span style="font-size: 0.85rem; color: var(--sys-text-muted);">
                超級管理員：<strong><?= (int) $super_admin_count ?></strong> 位
            </span>
        </div>
        <div class="admin-panel__body">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>管理員姓名</th>
                            <th>帳號</th>
                            <th>員工編號</th>
                            <th>Email</th>
                            <th>目前角色</th>
                            <th style="min-width: 200px;">調整角色</th>
                            <th style="width: 100px;">帳號操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admins as $item): ?>
                            <?php
                            $isSelf = ((int) $item['admin_id'] === (int) $current_admin_id);
                            $isLastSuper = ($item['role'] === \App\Models\AdminModel::ROLE_SUPER_ADMIN && (int) $super_admin_count <= 1);
                            ?>
                            <tr>
                                <td><?= (int) $item['admin_id'] ?></td>
                                <td>
                                    <strong><?= esc($item['name']) ?></strong>
                                    <?php if ($isSelf): ?>
                                        <span style="font-size: 0.75rem; background: #e0f2fe; color: #0369a1; padding: 2px 6px; border-radius: 4px; margin-left: 4px;">本人</span>
                                    <?php endif; ?>
                                </td>
                                <td><code><?= esc($item['username']) ?></code></td>
                                <td><?= esc($item['employee_id'] ?? '—') ?></td>
                                <td><?= esc($item['email']) ?></td>
                                <td>
                                    <span class="role-badge role-badge--<?= esc($item['role']) ?>">
                                        <?= esc($roles[$item['role']] ?? $item['role']) ?>
                                    </span>
                                </td>
                                <td>
                                    <form action="/AdminController/admins/<?= (int) $item['admin_id'] ?>/role" method="post" style="display: flex; gap: 6px; align-items: center;">
                                        <input type="hidden" name="_admin_csrf" value="">
                                        <select name="role" class="form-control" style="padding: 4px 8px; font-size: 0.85rem; border: 1px solid var(--sys-border); border-radius: var(--sys-radius-sm); background: #fff;">
                                            <?php foreach ($roles as $roleKey => $roleTitle): ?>
                                                <option value="<?= esc($roleKey) ?>" <?= $item['role'] === $roleKey ? 'selected' : '' ?>>
                                                    <?= esc($roleTitle) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn--secondary btn--sm" <?= ($isLastSuper && $item['role'] === \App\Models\AdminModel::ROLE_SUPER_ADMIN) ? '' : '' ?>>
                                            儲存
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <?php if ($isSelf): ?>
                                        <button class="btn btn--secondary btn--sm" disabled title="無法刪除自己正在使用的帳號" style="opacity: 0.5; cursor: not-allowed;">
                                            刪除
                                        </button>
                                    <?php elseif ($isLastSuper): ?>
                                        <button class="btn btn--secondary btn--sm" disabled title="系統至少需保留一名超級管理員" style="opacity: 0.5; cursor: not-allowed;">
                                            刪除
                                        </button>
                                    <?php else: ?>
                                        <form action="/AdminController/admins/<?= (int) $item['admin_id'] ?>/delete" method="post" onsubmit="return confirm('確定要刪除管理員「<?= esc($item['name']) ?>」（<?= esc($item['username']) ?>）嗎？此動作無法復原。');">
                                            <input type="hidden" name="_admin_csrf" value="">
                                            <button type="submit" class="btn btn--danger btn--sm" style="color: #ef4444; border-color: #fecaca; background: #fef2f2;">
                                                刪除
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</main>

<script src="<?= base_url('assets/js/admin-security.js') ?>"></script>
</body>
</html>

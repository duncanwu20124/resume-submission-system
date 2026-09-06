<?php
$currentPath = trim((string) parse_url(current_url(), PHP_URL_PATH), '/');
$currentPath = preg_replace('#^index\.php/?#', '', $currentPath) ?? $currentPath;
$isActive = static function (string $path) use ($currentPath): bool {
    $target = trim($path, '/');

    return $currentPath === $target || ($target !== 'AdminController' && str_starts_with($currentPath, $target . '/'));
};

$currentRole = $current_admin_role ?? (string) (session()->get('admin_role') ?: '');
if (empty($currentRole) || !in_array($currentRole, ['super_admin', 'admin', 'reviewer'], true)) {
    if ($adminId = session()->get('admin_id')) {
        $dbAdmin = (new \App\Models\AdminModel())->select('role')->find($adminId);
        if ($dbAdmin && !empty($dbAdmin['role'])) {
            $currentRole = $dbAdmin['role'];
            session()->set('admin_role', $currentRole);
        }
    }
}
if (empty($currentRole)) {
    $currentRole = 'admin';
}

$roleLabelMap = [
    'super_admin' => '超級管理員',
    'admin' => '一般管理員',
    'reviewer' => '審查委員',
];
$roleName = $roleLabelMap[$currentRole] ?? '管理員';
$isSuper = ($currentRole === 'super_admin');
$isAdminOrAbove = in_array($currentRole, ['super_admin', 'admin'], true);
?>

<button class="admin-menu-toggle" id="admin-menu-toggle" type="button" aria-controls="admin-sidebar" aria-expanded="false">
    <span class="admin-menu-toggle__icon" aria-hidden="true"><span></span><span></span><span></span></span>
    <span class="sr-only">開啟管理員功能選單</span>
</button>
<div class="admin-sidebar-backdrop" id="admin-sidebar-backdrop" hidden></div>

<aside class="admin-sidebar" id="admin-sidebar" aria-label="管理員功能選單" aria-hidden="true">
    <div class="admin-sidebar__heading" style="display: flex; align-items: center; justify-content: space-between; gap: 6px;">
        <span>管理功能</span>
        <span class="role-badge role-badge--<?= esc($currentRole) ?>"><?= esc($roleName) ?></span>
    </div>
    <nav class="admin-sidebar__nav">
        <a class="admin-sidebar__link <?= $isActive('AdminController') ? 'admin-sidebar__link--active' : '' ?>" href="/index.php/AdminController">管理首頁</a>
        <?php if ($isAdminOrAbove): ?>
            <a class="admin-sidebar__link <?= $isActive('AdminController/preferences') ? 'admin-sidebar__link--active' : '' ?>" href="/AdminController/preferences">志願序管理</a>
        <?php endif; ?>
        <a class="admin-sidebar__link <?= $isActive('AdminController/pdfDuplicates') ? 'admin-sidebar__link--active' : '' ?>" href="/AdminController/pdfDuplicates">PDF 重複檢查</a>
        <a class="admin-sidebar__link <?= $isActive('AdminController/scoring') ? 'admin-sidebar__link--active' : '' ?>" href="/AdminController/scoring">學生評分</a>
        <?php if ($isAdminOrAbove): ?>
            <a class="admin-sidebar__link <?= $isActive('AdminController/allocation') ? 'admin-sidebar__link--active' : '' ?>" href="/AdminController/allocation">分發管理</a>
            <a class="admin-sidebar__link <?= $isActive('AdminController/announcements') ? 'admin-sidebar__link--active' : '' ?>" href="/AdminController/announcements">公告管理</a>
        <?php endif; ?>
        <?php if ($isSuper): ?>
            <a class="admin-sidebar__link <?= $isActive('AdminController/admins') ? 'admin-sidebar__link--active' : '' ?>" href="/AdminController/admins">管理員帳號</a>
            <a class="admin-sidebar__link <?= $isActive('AdminController/auditLogs') ? 'admin-sidebar__link--active' : '' ?>" href="/AdminController/auditLogs">操作日誌</a>
        <?php endif; ?>
        <a class="admin-sidebar__link <?= $isActive('AdminController/profile') ? 'admin-sidebar__link--active' : '' ?>" href="/AdminController/profile">我的帳號</a>
    </nav>
    <div class="admin-sidebar__footer">
        <a class="admin-sidebar__link admin-sidebar__link--logout" href="/AdminController/logout">登出</a>
    </div>
</aside>

<script>
(() => {
    const toggle = document.getElementById('admin-menu-toggle');
    const sidebar = document.getElementById('admin-sidebar');
    const backdrop = document.getElementById('admin-sidebar-backdrop');
    if (!toggle || !sidebar || !backdrop) return;

    const setOpen = (open) => {
        document.body.classList.toggle('admin-sidebar-open', open);
        toggle.setAttribute('aria-expanded', String(open));
        toggle.querySelector('.sr-only').textContent = open ? '關閉管理員功能選單' : '開啟管理員功能選單';
        sidebar.setAttribute('aria-hidden', String(!open));
        backdrop.hidden = !open;
    };

    toggle.addEventListener('click', () => setOpen(!document.body.classList.contains('admin-sidebar-open')));
    backdrop.addEventListener('click', () => setOpen(false));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') setOpen(false);
    });
})();
</script>

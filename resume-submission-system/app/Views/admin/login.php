<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理員登入 | 學生履歷繳交系統</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
</head>
<body>

<main class="admin-shell admin-shell--narrow">
    <div class="auth-box">
        <a href="<?= site_url('/') ?>" class="back-link">&larr; 返回系統首頁</a>

        <div class="auth-header">
            <span class="auth-header__sub">Admin Portal</span>
            <h1 class="auth-header__title">管理員登入</h1>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert--success" role="status"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert--error" role="alert"><?= esc($error) ?></div>
        <?php endif; ?>

        <form action="/AdminController/doLogin" method="POST">
            <input type="hidden" name="_admin_csrf" value="">
            <div class="form-field">
                <label for="username">管理員帳號</label>
                <input id="username" type="text" name="username" autocomplete="username" required autofocus>
            </div>

            <div class="form-field">
                <label for="password">密碼</label>
                <input id="password" type="password" name="password" autocomplete="current-password" required>
            </div>

            <button class="btn btn--primary btn--block" type="submit" style="margin-top: 8px;">登入系統</button>
        </form>

        <div class="auth-footer">
            <a href="/AdminController/forgotPassword">忘記密碼？</a>
            <a href="/AdminController/register">註冊管理員帳號</a>
            <a href="<?= site_url('/') ?>">前往首頁</a>
        </div>
    </div>
</main>

<script src="<?= base_url('assets/js/admin-security.js') ?>"></script>

</body>
</html>

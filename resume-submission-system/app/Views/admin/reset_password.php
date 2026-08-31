<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理員重設密碼 | 學生甄選與志願媒合系統</title>

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
            <h1 class="auth-header__title">設定新密碼</h1>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert--error" role="alert"><?= esc($error) ?></div>
        <?php endif; ?>

        <form action="/AdminController/doResetPassword" method="POST">
            <input type="hidden" name="_admin_csrf" value="">
            <div class="form-field">
                <label for="password">新密碼 (至少 6 個字元)</label>
                <input id="password" type="password" name="password" minlength="6" required autofocus>
            </div>

            <div class="form-field">
                <label for="password_confirm">確認新密碼</label>
                <input id="password_confirm" type="password" name="password_confirm" minlength="6" required>
            </div>

            <button class="btn btn--primary btn--block" type="submit" style="margin-top: 8px;">確認更新密碼</button>
        </form>

        <div class="auth-footer">
            <a href="/AdminController/login">返回登入</a>
        </div>
    </div>
</main>

<script src="<?= base_url('assets/js/admin-security.js') ?>"></script>

</body>
</html>

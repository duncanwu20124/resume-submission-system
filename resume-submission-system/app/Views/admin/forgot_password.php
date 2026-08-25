<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理員忘記密碼 - 甄選行政系統</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
</head>
<body>

<main class="admin-shell admin-shell--narrow">
    <div class="auth-box">
        <div class="auth-header">
            <p class="auth-header__sub">管理員系統</p>
            <h1 class="auth-header__title">忘記密碼</h1>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert--success" role="status"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert--error" role="alert"><?= esc($error) ?></div>
        <?php endif; ?>

        <p style="color: var(--text-muted, #64748b); font-size: 0.9rem; margin-bottom: 1rem; line-height: 1.5;">
            請輸入您註冊管理員時填寫的姓名、電子郵件與員工證號，驗證身分後系統將發送重設密碼信件給您。
        </p>

        <form action="/AdminController/sendResetLink" method="POST">
            <div class="form-field">
                <label for="name">管理員姓名</label>
                <input id="name" type="text" name="name" value="<?= esc($old['name'] ?? '') ?>" required autofocus placeholder="請輸入管理員姓名">
            </div>

            <div class="form-field">
                <label for="email">管理員電子郵件 (Email)</label>
                <input id="email" type="email" name="email" value="<?= esc($old['email'] ?? '') ?>" required placeholder="example@gmail.com">
            </div>

            <div class="form-field">
                <label for="employee_id">管理員員工證號 (例如: admin01)</label>
                <input id="employee_id" type="text" name="employee_id" value="<?= esc($old['employee_id'] ?? '') ?>" required placeholder="admin01">
            </div>

            <button class="btn btn--primary btn--block" type="submit" style="margin-top: 8px;">發送重設密碼信件</button>
        </form>

        <div class="auth-footer">
            <a href="/AdminController/login">返回登入</a>
            <a href="<?= site_url('/') ?>">返回系統首頁</a>
        </div>
    </div>
</main>

</body>
</html>

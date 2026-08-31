<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理員註冊驗證 - 甄選行政系統</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
</head>
<body>

<main class="admin-shell admin-shell--narrow">
    <div class="auth-box">
        <div class="auth-header">
            <p class="auth-header__sub">管理員註冊驗證</p>
            <h1 class="auth-header__title">輸入電子郵件驗證碼</h1>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert--error" role="alert"><?= esc($error) ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert--error" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert--success" role="alert"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('dev_admin_code')): ?>
            <div class="alert alert--success" style="font-size: 0.85rem; line-height: 1.5;">
                【本機開發測試】驗證碼：<strong style="letter-spacing: 2px; font-size: 1.1rem; color: #047857;"><?= esc(session()->getFlashdata('dev_admin_code')) ?></strong>
            </div>
        <?php endif; ?>

        <p style="font-size: 0.85rem; color: var(--sys-text-secondary); margin-bottom: 16px; line-height: 1.5;">
            系統已向 <strong><?= esc($email ?? '') ?></strong> 發送安全提醒通知與驗證碼，有效時間為 15 分鐘。
        </p>

        <form action="/AdminController/doVerifyRegistration" method="POST">
            <input type="hidden" name="_admin_csrf" value="">
            <div class="form-field">
                <label for="code">註冊驗證碼</label>
                <input id="code" type="text" name="code" maxlength="6" style="text-transform: uppercase; font-size: 1.1rem; letter-spacing: 2px; text-align: center;" required autofocus>
            </div>

            <button class="btn btn--primary btn--block" type="submit" style="margin-top: 8px;">確認並完成註冊</button>
        </form>

        <div class="auth-footer" style="margin-top: 20px;">
            <a href="/AdminController/resendVerification">重新發送驗證碼</a>
            <a href="/AdminController/register">返回修改註冊資料</a>
        </div>
    </div>
</main>

<script src="<?= base_url('assets/js/admin-security.js') ?>"></script>

</body>
</html>

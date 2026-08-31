<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>密碼重設驗證 - 學生甄選與志願媒合系統</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
</head>
<body>

<main class="admin-shell admin-shell--narrow">
    <div class="auth-box">
        <div class="auth-header">
            <p class="auth-header__sub">管理員密碼重設</p>
            <h1 class="auth-header__title">輸入重設驗證碼</h1>
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

        <p style="font-size: 0.85rem; color: var(--sys-text-secondary); margin-bottom: 16px; line-height: 1.5;">
            系統已向 <strong><?= esc($email ?? '') ?></strong> 發送密碼重設驗證碼，有效時間為 15 分鐘。
        </p>

        <form action="/AdminController/doVerifyResetCode" method="POST">
            <input type="hidden" name="_admin_csrf" value="">
            <div class="form-field">
                <label for="code">重設驗證碼</label>
                <input id="code" type="text" name="code" maxlength="6" style="text-transform: uppercase; font-size: 1.1rem; letter-spacing: 2px; text-align: center;" required autofocus>
            </div>

            <button class="btn btn--primary btn--block" type="submit" style="margin-top: 8px;">驗證並前往設定新密碼</button>
        </form>

        <div class="auth-footer" style="margin-top: 20px;">
            <a href="/AdminController/resendResetCode">重新發送驗證碼</a>
            <a href="/AdminController/forgotPassword">返回重新填寫</a>
        </div>
    </div>
</main>

<script src="<?= base_url('assets/js/admin-security.js') ?>"></script>

</body>
</html>

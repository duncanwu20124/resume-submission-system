<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理員帳號註冊 | 學生履歷繳交系統</title>

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
            <h1 class="auth-header__title">管理員帳號註冊</h1>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert--error" role="alert"><?= esc($error) ?></div>
        <?php endif; ?>

        <form action="/AdminController/doRegister" method="POST">
            <div class="form-field">
                <label for="username">管理員帳號</label>
                <input id="username" type="text" name="username" value="<?= isset($old['username']) ? esc($old['username']) : '' ?>" autocomplete="username" required autofocus>
            </div>

            <div class="form-field">
                <label for="email">電子郵件</label>
                <input id="email" type="email" name="email" value="<?= isset($old['email']) ? esc($old['email']) : '' ?>" autocomplete="email" inputmode="email" required>
            </div>

            <div class="form-field">
                <label for="password">密碼</label>
                <input id="password" type="password" name="password" autocomplete="new-password" minlength="6" aria-describedby="password-hint" required>
                <p class="form-hint" id="password-hint">長度需至少 6 個字元。</p>
            </div>

            <div class="form-field">
                <label for="password_confirm">確認密碼</label>
                <input id="password_confirm" type="password" name="password_confirm" autocomplete="new-password" minlength="6" required>
            </div>

            <div class="form-field">
                <label for="employee_id">員工證編號</label>
                <input id="employee_id" type="text" name="employee_id" placeholder="例如 admin01" value="<?= isset($old['employee_id']) ? esc($old['employee_id']) : '' ?>" pattern="admin[0-9]{2}" aria-describedby="employee-id-hint" required>
                <p class="form-hint" id="employee-id-hint">格式為 admin 加上兩位數字（例如 admin01）。</p>
            </div>

            <button class="btn btn--primary btn--block" type="submit" style="margin-top: 8px;">建立帳號</button>
        </form>

        <div class="auth-footer">
            <a href="/AdminController/login">已有帳號？返回登入</a>
        </div>
    </div>
</main>

</body>
</html>

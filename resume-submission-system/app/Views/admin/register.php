<!DOCTYPE html>
<html lang="zh-TW">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>管理員註冊</title>
<link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
</head>
<body>
<main class="admin-shell admin-shell--narrow">
<section class="admin-panel register-panel" aria-labelledby="register-title">
<header class="admin-header">
<div>
<p class="admin-kicker">甄選會管理系統</p>
<h1 id="register-title">註冊管理員帳號</h1>
<p class="admin-lead">建立供甄選會工作人員使用的管理帳號。所有欄位皆為必填。</p>
</div>
</header>

<?php if (!empty($error)): ?>
<div class="notice notice--error" role="alert"><?= esc($error) ?></div>
<?php endif; ?>

<form action="/AdminController/doRegister" method="POST">
<div class="field">
<label for="username">管理員帳號</label>
<input id="username" type="text" name="username" value="<?= isset($old['username']) ? esc($old['username']) : '' ?>" autocomplete="username" required autofocus>
</div>

<div class="field">
<label for="email">電子郵件</label>
<input id="email" type="email" name="email" value="<?= isset($old['email']) ? esc($old['email']) : '' ?>" autocomplete="email" inputmode="email" required>
</div>

<div class="field">
<label for="password">密碼</label>
<input id="password" type="password" name="password" autocomplete="new-password" minlength="6" aria-describedby="password-hint" required>
<p class="field-hint" id="password-hint">至少 6 個字元。</p>
</div>

<div class="field">
<label for="password_confirm">確認密碼</label>
<input id="password_confirm" type="password" name="password_confirm" autocomplete="new-password" minlength="6" required>
</div>

<div class="field">
<label for="employee_id">員工證編號</label>
<input id="employee_id" type="text" name="employee_id" placeholder="例如 admin01" value="<?= isset($old['employee_id']) ? esc($old['employee_id']) : '' ?>" pattern="admin[0-9]{2}" aria-describedby="employee-id-hint" required>
<p class="field-hint" id="employee-id-hint">格式為 admin 加上兩位數字，例如 admin01。</p>
</div>

<button class="button button--full" type="submit">建立管理員帳號</button>
</form>

<nav class="login-links" aria-label="註冊頁選項">
<a class="text-link" href="/AdminController/login">返回管理員登入</a>
</nav>
</section>
</main>
</body>
</html>

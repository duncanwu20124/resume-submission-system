<!DOCTYPE html>
<html lang="zh-TW">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>管理員登入</title>
<link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
</head>
<body>
<main class="admin-shell admin-shell--narrow">
<section class="admin-panel login-panel" aria-labelledby="login-title">
<header class="admin-header">
<div>
<p class="admin-kicker">甄選會管理系統</p>
<h1 id="login-title">管理員登入</h1>
<p class="admin-lead">登入後可查詢申請者資料、確認履歷上傳狀態並下載檔案。</p>
</div>
</header>

<?php if (session()->getFlashdata('success')): ?>
<div class="notice notice--success" role="status"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
<div class="notice notice--error" role="alert"><?= esc($error) ?></div>
<?php endif; ?>

<form action="/AdminController/doLogin" method="POST">
<div class="field">
<label for="username">管理員帳號</label>
<input id="username" type="text" name="username" autocomplete="username" required autofocus>
</div>
<div class="field">
<label for="password">密碼</label>
<input id="password" type="password" name="password" autocomplete="current-password" required>
</div>
<button class="button button--full" type="submit">登入管理系統</button>
</form>

<nav class="login-links" aria-label="其他登入選項">
<a class="text-link" href="/AdminController/register">註冊管理員帳號</a>
<a class="text-link" href="<?= site_url('/') ?>">返回上一頁</a>
</nav>
</section>
</main>
</body>
</html>

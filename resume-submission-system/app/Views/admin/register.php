<!DOCTYPE html>
<html lang="zh-TW">
<head>
<meta charset="UTF-8">
<title>管理員註冊</title>
<style>
body { font-family: Arial, sans-serif; margin: 20px; }
input, button { margin: 3px; padding: 3px; }
</style>
</head>
<body>
<h1>管理員註冊</h1>

<?php if (!empty($error)): ?>
<p style="color:red;"><?= $error ?></p>
<?php endif; ?>

<form action="/AdminController/doRegister" method="POST">
<label>帳號：<br>
<input type="text" name="username" value="<?= isset($old['username']) ? htmlspecialchars($old['username']) : '' ?>">
</label><br><br>

<label>電子郵件：<br>
<input type="email" name="email" value="<?= isset($old['email']) ? htmlspecialchars($old['email']) : '' ?>">
</label><br><br>

<label>密碼：<br>
<input type="password" name="password">
</label><br><br>

<label>確認密碼：<br>
<input type="password" name="password_confirm">
</label><br><br>

<label>員工證：<br>
<input type="text" name="employee_id" placeholder="例如 admin01" value="<?= isset($old['employee_id']) ? htmlspecialchars($old['employee_id']) : '' ?>">
</label><br><br>

<button type="submit">註冊</button>
</form>

<br>
<a href="/AdminController/login">返回登入頁面</a>
</body>
</html>

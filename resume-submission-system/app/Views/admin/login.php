<!DOCTYPE html>
<html lang="zh-TW">
<head>
<meta charset="UTF-8">
<title>管理員登入</title>
<style>
body {
  font-family: Arial, sans-serif;
  margin: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: 20px;
}
input, button { margin: 3px; padding: 3px; }
</style>
</head>
<body>
<h1>管理員登入</h1>

<?php if (session()->getFlashdata('success')): ?>
<p style="color:green;"><?= session()->getFlashdata('success') ?></p>
<?php endif; ?>

<?php if (!empty($error)): ?>
<p style="color:red;"><?= $error ?></p>
<?php endif; ?>

<form action="/AdminController/doLogin" method="POST">
<label>帳號：<br>
<input type="text" name="username">
</label><br><br>
<label>密碼：<br>
<input type="password" name="password">
</label><br><br>
<button type="submit">登入</button>
</form>

<br>
<a href="/AdminController/register">註冊管理員帳號</a>
<a href="javascript:history.back()" class="btn btn-secondary" style="margin-left:10px;">返回上一頁</a>
</body>
</html>

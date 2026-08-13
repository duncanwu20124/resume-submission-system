<!DOCTYPE html>
<html lang="zh-TW">
<head>
<meta charset="UTF-8">
<title>管理員頁面</title>
<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { border-collapse: collapse; }
th, td { border: 1px solid black; padding: 5px; }
input, button { margin: 3px; padding: 3px; }
</style>
</head>
<body>
<h1>管理員頁面</h1>

<form action="/AdminController/search" method="GET">
搜尋方式：
<select name="search_by">
    <option value="name" <?= (!isset($search_by) || $search_by === 'name') ? 'selected' : '' ?>>使用者姓名</option>
    <option value="id" <?= (isset($search_by) && $search_by === 'id') ? 'selected' : '' ?>>使用者 ID</option>
    <option value="email" <?= (isset($search_by) && $search_by === 'email') ? 'selected' : '' ?>>Email</option>
</select>
搜尋關鍵字：
<input type="text" name="keyword" value="<?= isset($keyword) ? htmlspecialchars($keyword) : '' ?>">
<button type="submit">搜尋</button>
</form>

<br>

<table>
<tr>
<th>使用者 ID</th>
<th>使用者姓名</th>
<th>Email</th>
<th>檔案名稱</th>
<th>上傳時間</th>
<th>操作</th>
</tr>
<?php if (!empty($users)): ?>
<?php foreach ($users as $user): ?>
<tr>
<td><?= htmlspecialchars($user['student_id']) ?></td>
<td><?= htmlspecialchars($user['name']) ?></td>
<td><?= htmlspecialchars($user['email']) ?></td>
<td><?= htmlspecialchars($user['file_name']) ?></td>
<td><?= $user['uploaded_at'] ?></td>
<td>
<a href="/AdminController/show/<?= $user['id'] ?>" target="_blank">查看</a>
<a href="/AdminController/download/<?= $user['id'] ?>">下載</a>
</td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="6">查無使用者資料</td></tr>
<?php endif; ?>
</table>

<br><br>
<a href="/AdminController/logout">登出</a>
</body>
</html>

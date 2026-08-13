<!DOCTYPE html>
<html lang="zh-TW">
<head>
<meta charset="UTF-8">
<title>使用者詳細資料</title>
<style>
body { font-family: Arial, sans-serif; margin: 20px; }
input, button { margin: 3px; padding: 3px; }
</style>
</head>
<body>
<h1>使用者詳細資料</h1>

<p>使用者 ID：<?= htmlspecialchars($user['student_id']) ?></p>
<p>使用者姓名：<?= htmlspecialchars($user['name']) ?></p>
<p>Email：<?= htmlspecialchars($user['email']) ?></p>
<p>檔案名稱：<?= htmlspecialchars($user['file_name']) ?></p>
<p>上傳時間：<?= $user['uploaded_at'] ?></p>

<br>
<a href="/AdminController/download/<?= $user['id'] ?>">下載</a>
<br><br>

<?php 
$ext = strtolower(pathinfo($user['file_name'], PATHINFO_EXTENSION));
if ($ext === 'pdf'): 
?>
<h3>PDF 預覽：</h3>
<iframe src="/AdminController/viewFile/<?= $user['id'] ?>" width="100%" height="600px" style="border: 1px solid black;"></iframe>
<?php endif; ?>

<br><br>
<a href="/AdminController" onclick="if(document.referrer && document.referrer.includes('/AdminController')) { history.back(); return false; }">返回上一頁</a>
</body>
</html>

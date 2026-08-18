<!DOCTYPE html>
<html lang="zh-TW">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>申請資料管理</title>
<link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
</head>
<body>
<main class="admin-shell">
<header class="admin-header">
<div>
<p class="admin-kicker">甄選會管理系統</p>
<h1>申請資料管理</h1>
<p class="admin-lead">查詢申請者、確認履歷繳交狀態，或進入詳細頁檢視上傳內容。</p>
</div>
<a class="button button--secondary" href="/AdminController/logout">登出</a>
</header>

<section class="toolbar" aria-label="申請資料搜尋">
<form class="search-form" action="/AdminController/search" method="GET">
<div class="search-field">
<label for="search_by">搜尋欄位</label>
<select id="search_by" name="search_by">
    <option value="name" <?= (!isset($search_by) || $search_by === 'name') ? 'selected' : '' ?>>使用者姓名</option>
    <option value="id" <?= (isset($search_by) && $search_by === 'id') ? 'selected' : '' ?>>使用者 ID</option>
    <option value="email" <?= (isset($search_by) && $search_by === 'email') ? 'selected' : '' ?>>Email</option>
</select>
</div>
<div class="search-field">
<label for="keyword">搜尋關鍵字</label>
<input id="keyword" type="search" name="keyword" value="<?= isset($keyword) ? esc($keyword) : '' ?>" placeholder="輸入姓名、使用者 ID 或 Email">
</div>
<button class="button" type="submit">搜尋資料</button>
</form>
</section>

<section aria-labelledby="results-title">
<div class="results-summary">
<h2 id="results-title"><?= isset($keyword) && $keyword !== '' ? '搜尋結果' : '全部申請資料' ?></h2>
<p class="results-count">共 <?= count($users ?? []) ?> 筆資料</p>
</div>

<div class="table-wrap">
<table class="data-table">
<thead>
<tr>
<th>使用者 ID</th>
<th>使用者姓名</th>
<th>Email</th>
<th>履歷檔案</th>
<th>上傳時間</th>
<th>操作</th>
</tr>
</thead>
<tbody>
<?php if (!empty($users)): ?>
<?php foreach ($users as $user): ?>
<tr>
<td class="cell-primary" data-label="使用者 ID"><?= esc($user['student_id']) ?></td>
<td data-label="姓名"><?= esc($user['name']) ?></td>
<td class="cell-secondary" data-label="Email"><?= esc($user['email']) ?></td>
<td class="file-name" data-label="履歷檔案">
<?php if (!empty($user['file_name'])): ?>
<span class="status">已上傳</span><br><?= esc($user['file_name']) ?>
<?php else: ?>
<span class="status status--muted">尚未上傳</span>
<?php endif; ?>
</td>
<td class="cell-secondary" data-label="上傳時間"><?= !empty($user['uploaded_at']) ? esc($user['uploaded_at']) : '尚無紀錄' ?></td>
<td class="row-actions-cell" data-label="操作">
<div class="row-actions">
<a class="button button--small" href="/AdminController/show/<?= $user['id'] ?>">查看</a>
<?php if (!empty($user['file_name'])): ?>
<a class="button button--small button--secondary" href="/AdminController/download/<?= $user['id'] ?>">下載</a>
<?php endif; ?>
</div>
</td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr><td class="empty-state" colspan="6">查無符合條件的申請資料。請調整搜尋欄位或關鍵字後再試一次。</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</section>

</main>
</body>
</html>

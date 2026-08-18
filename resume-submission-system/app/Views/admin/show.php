<!DOCTYPE html>
<html lang="zh-TW">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($user['name']) ?>的申請資料</title>
<link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
</head>
<body>
<main class="admin-shell">
<header class="admin-header">
<div>
<p class="admin-kicker">申請資料詳細內容</p>
<h1><?= esc($user['name']) ?></h1>
<p class="admin-lead">使用者 ID：<?= esc($user['student_id']) ?></p>
</div>
<a class="button button--secondary" href="/AdminController" onclick="if(document.referrer && document.referrer.includes('/AdminController')) { history.back(); return false; }">返回資料列表</a>
</header>

<div class="detail-grid">
<section class="admin-panel detail-panel" aria-labelledby="applicant-info-title">
<h2 id="applicant-info-title">申請者與檔案資訊</h2>
<dl class="detail-list">
<div class="detail-row"><dt>使用者 ID</dt><dd><?= esc($user['student_id']) ?></dd></div>
<div class="detail-row"><dt>使用者姓名</dt><dd><?= esc($user['name']) ?></dd></div>
<div class="detail-row"><dt>Email</dt><dd><?= esc($user['email']) ?></dd></div>
<div class="detail-row"><dt>履歷狀態</dt><dd><?= !empty($user['file_name']) ? '<span class="status">已上傳</span>' : '<span class="status status--muted">尚未上傳</span>' ?></dd></div>
<div class="detail-row"><dt>檔案名稱</dt><dd><?= !empty($user['file_name']) ? esc($user['file_name']) : '尚無檔案' ?></dd></div>
<div class="detail-row"><dt>上傳時間</dt><dd><?= !empty($user['uploaded_at']) ? esc($user['uploaded_at']) : '尚無紀錄' ?></dd></div>
</dl>
<?php if (!empty($user['file_name'])): ?>
<div class="file-actions">
<a class="button" href="/AdminController/download/<?= $user['id'] ?>">下載履歷</a>
</div>
<?php endif; ?>
</section>

<?php 
$ext = strtolower(pathinfo($user['file_name'] ?? '', PATHINFO_EXTENSION));
if ($ext === 'pdf'): 
?>
<section class="admin-panel preview-panel" aria-labelledby="preview-title">
<header class="preview-header"><h2 id="preview-title">PDF 履歷預覽</h2></header>
<iframe class="preview-frame" src="/AdminController/viewFile/<?= $user['id'] ?>" title="<?= esc($user['name']) ?>的 PDF 履歷預覽"></iframe>
</section>
<?php elseif (!empty($user['file_name'])): ?>
<section class="admin-panel preview-panel" aria-labelledby="preview-title">
<header class="preview-header"><h2 id="preview-title">履歷預覽</h2></header>
<p class="preview-unavailable">此檔案格式無法在瀏覽器內預覽，請下載後查看完整內容。</p>
</section>
<?php else: ?>
<section class="admin-panel preview-panel" aria-labelledby="preview-title">
<header class="preview-header"><h2 id="preview-title">履歷預覽</h2></header>
<p class="preview-unavailable">申請者尚未上傳履歷檔案。</p>
</section>
<?php endif; ?>
</div>
</main>
</body>
</html>

<!DOCTYPE html>
<html lang="zh-TW"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>學生評分管理</title><link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>"></head><body>
<header class="sys-navbar"><div class="sys-navbar__inner"><div class="sys-navbar__brand"><h1 class="sys-navbar__title">學生評分管理</h1><span class="sys-navbar__badge">Admin Portal</span></div><div class="sys-navbar__user"><a class="sys-navbar__link" href="/AdminController/preferences">志願序</a><a class="sys-navbar__link" href="/AdminController/allocation">分發管理</a><a class="sys-navbar__link" href="/AdminController">返回後台</a></div></div></header>
<main class="admin-shell">
<div class="page-header"><div><h2 class="page-header__title">學生評分</h2><p>僅列出已正式送出志願序的學生；分數確認後才可參與分發。</p></div></div>
<?php if (session()->getFlashdata('success')): ?><div class="alert alert--success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
<?php if (session()->getFlashdata('error')): ?><div class="alert alert--error"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
<section class="admin-panel"><div class="admin-panel__body"><div class="table-responsive"><table class="data-table"><thead><tr><th>學生</th><th>分數</th><th>狀態</th><th>內部備註</th><th>操作</th></tr></thead><tbody>
<?php if (!$students): ?><tr><td colspan="5" class="table-empty">尚無已送出志願序的學生。</td></tr><?php endif; ?>
<?php foreach ($students as $row): ?><?php $formId = 'score-form-' . (int) $row['student_db_id']; ?><tr>
<td><strong><?= esc($row['student_name']) ?></strong><br><small><?= esc($row['student_number']) ?></small></td>
<td><input form="<?= $formId ?>" name="total_score" type="number" min="0" max="100" step="0.01" required value="<?= esc($row['total_score'] ?? '') ?>" style="width:100px"></td>
<td><select form="<?= $formId ?>" name="status"><option value="draft" <?= ($row['score_status'] ?? '') === 'draft' ? 'selected' : '' ?>>草稿</option><option value="confirmed" <?= ($row['score_status'] ?? '') === 'confirmed' ? 'selected' : '' ?>>已確認</option></select></td>
<td><input form="<?= $formId ?>" name="comment" maxlength="1000" value="<?= esc($row['comment'] ?? '') ?>" placeholder="僅管理員可見"></td>
<td><form id="<?= $formId ?>" method="post" action="/AdminController/scoring/<?= (int) $row['student_db_id'] ?>"><?= csrf_field() ?><button class="btn btn--primary btn--sm" type="submit">儲存</button></form></td></tr><?php endforeach; ?>
</tbody></table></div></div></section></main></body></html>

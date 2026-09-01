<!DOCTYPE html>
<html lang="zh-TW"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>學生評分管理</title><link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>"></head><body>
<header class="sys-navbar"><div class="sys-navbar__inner"><div class="sys-navbar__brand"><h1 class="sys-navbar__title">學生評分管理</h1><span class="sys-navbar__badge">Admin Portal</span></div><div class="sys-navbar__user"><a class="sys-navbar__link" href="/AdminController/preferences">志願序</a><a class="sys-navbar__link" href="/AdminController/allocation">分發管理</a><a class="sys-navbar__link" href="/AdminController">返回後台</a></div></div></header>
<main class="admin-shell admin-shell--with-sidebar">
<?= view('partials/admin_sidebar') ?>
<?php
$studentCount = count($students);
$confirmedCount = count(array_filter($students, static fn (array $row): bool => ($row['score_status'] ?? '') === 'confirmed'));
$draftCount = $studentCount - $confirmedCount;
?>
<div class="page-header"><div><h2 class="page-header__title">學生評分</h2><p class="page-header__description">確認每位學生的總分與備註；只有「已確認」的評分可以進入分發。</p></div><a class="btn btn--secondary btn--sm" href="/AdminController/allocation">前往分發管理</a></div>
<?php if (session()->getFlashdata('success')): ?><div class="alert alert--success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
<?php if (session()->getFlashdata('error')): ?><div class="alert alert--error"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
<section class="stats-grid scoring-stats" aria-label="評分進度"><div class="stat-card"><strong class="stat-card__value"><?= $studentCount ?></strong><span class="stat-card__label">待處理學生</span></div><div class="stat-card stat-card--success"><strong class="stat-card__value"><?= $confirmedCount ?></strong><span class="stat-card__label">已確認評分</span></div><div class="stat-card stat-card--muted"><strong class="stat-card__value"><?= $draftCount ?></strong><span class="stat-card__label">尚待確認</span></div></section>
<section class="admin-panel"><div class="admin-panel__header"><div><h3 class="admin-panel__title">評分清單</h3><span class="table-meta__count">共 <?= $studentCount ?> 位</span></div><button class="btn btn--primary btn--sm" id="save-all-scores" type="button" <?= $studentCount === 0 ? 'disabled' : '' ?>>全部儲存</button></div><div class="admin-panel__body admin-panel__body--flush"><div class="table-responsive"><table class="data-table scoring-table"><thead><tr><th>學生</th><th>總分（0～100）</th><th>評分狀態</th><th>內部備註</th><th>操作</th></tr></thead><tbody>
<?php if (!$students): ?><tr><td colspan="5" class="table-empty">尚無已送出志願序的學生。</td></tr><?php endif; ?>
<?php foreach ($students as $row): ?><?php $formId = 'score-form-' . (int) $row['student_db_id']; ?><tr>
<td><strong><?= esc($row['student_name']) ?></strong><br><small><?= esc($row['student_number']) ?></small></td>
<td data-label="總分"><label class="sr-only" for="score-<?= (int) $row['student_db_id'] ?>"> <?= esc($row['student_name']) ?>的總分</label><input id="score-<?= (int) $row['student_db_id'] ?>" class="score-input" form="<?= $formId ?>" name="total_score" type="number" min="0" max="100" step="0.01" required value="<?= esc($row['total_score'] ?? '') ?>" aria-describedby="score-hint-<?= (int) $row['student_db_id'] ?>"><small class="field-hint" id="score-hint-<?= (int) $row['student_db_id'] ?>">0～100</small></td>
<td data-label="評分狀態"><label class="sr-only" for="status-<?= (int) $row['student_db_id'] ?>"> <?= esc($row['student_name']) ?>的評分狀態</label><select id="status-<?= (int) $row['student_db_id'] ?>" class="score-status" form="<?= $formId ?>" name="status"><option value="draft" <?= ($row['score_status'] ?? '') === 'draft' ? 'selected' : '' ?>>草稿</option><option value="confirmed" <?= ($row['score_status'] ?? '') === 'confirmed' ? 'selected' : '' ?>>已確認</option></select></td>
<td data-label="內部備註"><label class="sr-only" for="comment-<?= (int) $row['student_db_id'] ?>"> <?= esc($row['student_name']) ?>的內部備註</label><input id="comment-<?= (int) $row['student_db_id'] ?>" class="score-comment" form="<?= $formId ?>" name="comment" maxlength="1000" value="<?= esc($row['comment'] ?? '') ?>" placeholder="僅管理員可見"></td>
<td data-label="操作"><form id="<?= $formId ?>" method="post" action="/AdminController/scoring/<?= (int) $row['student_db_id'] ?>"><?= csrf_field() ?><button class="btn btn--primary btn--sm" type="submit">儲存評分</button></form></td></tr><?php endforeach; ?>
</tbody></table></div></div></section>
<p class="form-hint scoring-save-status" id="scoring-save-status" role="status" aria-live="polite"></p>
<script>
(() => {
    const button = document.getElementById('save-all-scores');
    const status = document.getElementById('scoring-save-status');
    if (!button) return;

    button.addEventListener('click', async () => {
        const forms = Array.from(document.querySelectorAll('.scoring-table form[id^="score-form-"]'));
        if (!forms.length) return;

        button.disabled = true;
        button.textContent = '全部儲存中…';
        status.textContent = `準備儲存 ${forms.length} 筆評分，請稍候。`;

        let saved = 0;
        try {
            for (const form of forms) {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    credentials: 'same-origin',
                    redirect: 'follow',
                });

                if (!response.ok || !response.url.includes('/AdminController/scoring')) {
                    throw new Error('評分儲存失敗');
                }
                saved += 1;
                status.textContent = `已儲存 ${saved} / ${forms.length} 筆評分。`;
            }

            window.location.reload();
        } catch (error) {
            button.disabled = false;
            button.textContent = '全部儲存';
            status.textContent = `已儲存 ${saved} / ${forms.length} 筆；請檢查後再重試。`;
        }
    });
})();
</script>
</main></body></html>

<!DOCTYPE html>
<html lang="zh-TW"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>分發管理</title><link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>"></head><body>
<header class="sys-navbar"><div class="sys-navbar__inner"><div class="sys-navbar__brand"><h1 class="sys-navbar__title">分發管理</h1><span class="sys-navbar__badge">Admin Portal</span></div><div class="sys-navbar__user"><a class="sys-navbar__link" href="/AdminController/scoring">學生評分</a><a class="sys-navbar__link" href="/AdminController/preferences">志願序</a><a class="sys-navbar__link" href="/AdminController">返回後台</a></div></div></header>
<main class="admin-shell admin-shell--with-sidebar"><?= view('partials/admin_sidebar') ?><div class="page-header"><div><h2 class="page-header__title">成績分發</h2><p class="page-header__description">依總分排序，再依學生志願序分配尚有名額的校系。先建立預覽，確認後才發布。</p></div><a class="btn btn--secondary btn--sm" href="/AdminController/scoring">返回學生評分</a></div>
<?php if (session()->getFlashdata('success')): ?><div class="alert alert--success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
<?php if (session()->getFlashdata('error')): ?><div class="alert alert--error"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
<section class="stats-grid allocation-stats" aria-label="分發資料狀態"><div class="stat-card"><strong class="stat-card__value"><?= (int) $submitted ?></strong><span class="stat-card__label">已送出志願</span></div><div class="stat-card stat-card--success"><strong class="stat-card__value"><?= (int) $confirmed ?></strong><span class="stat-card__label">已確認評分</span></div><div class="stat-card stat-card--muted"><strong class="stat-card__value"><?= max(0, (int)$submitted-(int)$confirmed) ?></strong><span class="stat-card__label">尚未確認</span></div></section>
<section class="admin-panel allocation-workflow" aria-labelledby="allocation-workflow-heading"><div class="admin-panel__header"><h3 class="admin-panel__title" id="allocation-workflow-heading">分發流程</h3><span class="tag-status <?= $submitted > 0 && $submitted === $confirmed ? 'tag-status--success' : 'tag-status--muted' ?>"><?= $submitted > 0 && $submitted === $confirmed ? '資料已備妥' : '請先完成評分' ?></span></div><div class="admin-panel__body"><ol class="workflow-steps"><li class="workflow-step workflow-step--done"><span class="workflow-step__number">1</span><div><strong>確認資料</strong><small><?= (int) $submitted ?> 位已送出志願、<?= (int) $confirmed ?> 位已確認評分</small></div></li><li class="workflow-step"><span class="workflow-step__number">2</span><div><strong>建立預覽</strong><small>產生本次排名與分發結果</small></div></li><li class="workflow-step"><span class="workflow-step__number">3</span><div><strong>檢視結果</strong><small>確認排名、錄取校系與志願順位</small></div></li><li class="workflow-step"><span class="workflow-step__number">4</span><div><strong>正式發布</strong><small>發布後學生即可查詢結果</small></div></li></ol><form class="allocation-action" method="post" action="/AdminController/allocation/preview"><?= csrf_field() ?><button class="btn btn--primary" type="submit" <?= $submitted === 0 || $submitted !== $confirmed ? 'disabled' : '' ?>>建立分發預覽</button><?php if ($submitted !== $confirmed): ?><p class="form-hint">所有已送出志願的學生都完成確認評分後才能執行。</p><?php else: ?><p class="form-hint">這會建立新的預覽批次，不會立即發布給學生。</p><?php endif; ?></form></div></section>
<?php if ($run): ?>
<section class="admin-panel">
    <div class="admin-panel__header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <h3 class="admin-panel__title">批次 #<?= (int)$run['id'] ?>（<?= esc($run['status']) ?>）</h3>
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <span class="table-meta__count">共 <?= (int) ($total_results ?? count($results)) ?> 筆結果（本頁顯示 <?= count($results) ?> 筆）</span>
            <form method="get" action="/AdminController/allocation" style="display: inline-flex; align-items: center; gap: 6px;">
                <input type="hidden" name="run" value="<?= (int) $run['id'] ?>">
                <label for="alloc_per_page" style="font-size: 0.85rem; color: var(--sys-text-muted); margin: 0;">每頁筆數：</label>
                <select id="alloc_per_page" name="per_page" onchange="this.form.submit()" style="padding: 3px 8px; font-size: 0.85rem; border: 1px solid var(--sys-border); border-radius: var(--sys-radius-sm); background: #fff;">
                    <option value="10" <?= ($per_page ?? 20) === 10 ? 'selected' : '' ?>>10 筆</option>
                    <option value="20" <?= ($per_page ?? 20) === 20 ? 'selected' : '' ?>>20 筆</option>
                    <option value="50" <?= ($per_page ?? 20) === 50 ? 'selected' : '' ?>>50 筆</option>
                    <option value="100" <?= ($per_page ?? 20) === 100 ? 'selected' : '' ?>>100 筆</option>
                </select>
            </form>
            <a class="btn btn--secondary btn--sm" href="/AdminController/allocation/export?run=<?= (int)$run['id'] ?>">匯出資料</a>
        </div>
    </div>
    <div class="admin-panel__body">
<p class="form-hint">建立時間：<?= esc($run['started_at']) ?>　抽籤 seed：<code><?= esc($run['random_seed']) ?></code></p>
<?php if ($run['status'] === 'preview'): ?>
    <?php if (!empty($is_super_admin)): ?>
        <form method="post" action="/AdminController/allocation/<?= (int)$run['id'] ?>/publish" onsubmit="return confirm('發布後學生將立即看到結果，確定發布？')">
            <?= csrf_field() ?>
            <div class="form-field"><label>版本說明</label><input name="revision_note" maxlength="1000" placeholder="例如：第一次正式分發"></div>
            <button class="btn btn--primary">正式發布結果</button>
        </form>
    <?php else: ?>
        <div class="alert alert--warning" style="margin-top: 1rem;">
            <strong>權限提示：</strong>目前為預覽狀態。依系統安全權限規範，正式發布放榜需由<strong>超級管理員（super_admin）</strong>審核後執行。
        </div>
    <?php endif; ?>
<?php endif; ?>
<div class="table-responsive"><table class="data-table allocation-table"><thead><tr><th>排名</th><th>學生</th><th>分數</th><th>同分序號</th><th>錄取校系</th><th>志願順位</th></tr></thead><tbody><?php foreach($results as $row): ?><tr><td data-label="排名"><?= (int)$row['overall_rank'] ?></td><td data-label="學生"><?= esc($row['student_name']) ?><br><small><?= esc($row['student_number']) ?></small></td><td data-label="分數"><?= esc($row['score_snapshot']) ?></td><td data-label="同分序號"><code><?= esc(substr($row['lottery_order'],0,10)) ?></code></td><td data-label="錄取校系"><?php if ($row['result_status'] === 'admitted'): ?><?php $admParts = explode(' - ', (string) $row['university_name_snapshot'], 2); $schoolPart = $admParts[0] ?? $row['university_name_snapshot']; $deptPart = $admParts[1] ?? ''; ?><?php if ($deptPart): ?><span style="font-weight: 700; color: #0f172a;"><?= esc($schoolPart) ?></span><span style="color: #2563eb; font-weight: 600;"> - <?= esc($deptPart) ?></span><?php else: ?><span style="font-weight: 700; color: #0f172a;"><?= esc($schoolPart) ?></span><?php endif; ?><?php else: ?><span style="color: var(--sys-text-muted);">未分發</span><?php endif; ?></td><td data-label="志願順位"><?= $row['preference_rank'] ? '第 '.(int)$row['preference_rank'].' 志願' : '—' ?></td></tr><?php endforeach; ?></tbody></table></div>

<?php if (isset($pager) && $pager->getPageCount() > 1): ?>
    <div class="pagination" style="margin: 1.5rem 0; display: flex; align-items: center; justify-content: center; gap: 1rem;">
        <?php if ($pager->getCurrentPage() > 1): ?>
            <a class="btn btn--secondary btn--sm" href="/AdminController/allocation?run=<?= (int)$run['id'] ?>&per_page=<?= (int)($per_page ?? 20) ?>&page=<?= $pager->getCurrentPage() - 1 ?>">上一頁</a>
        <?php endif; ?>
        <span style="font-weight: 600; color: var(--sys-text-muted);">第 <?= esc($pager->getCurrentPage()) ?> / <?= esc($pager->getPageCount()) ?> 頁（每頁 <?= (int) ($per_page ?? 20) ?> 筆，共 <?= (int) ($total_results ?? 0) ?> 筆）</span>
        <?php if ($pager->getCurrentPage() < $pager->getPageCount()): ?>
            <a class="btn btn--secondary btn--sm" href="/AdminController/allocation?run=<?= (int)$run['id'] ?>&per_page=<?= (int)($per_page ?? 20) ?>&page=<?= $pager->getCurrentPage() + 1 ?>">下一頁</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

</div></section><?php endif; ?>

<?php if ($runs): ?><section class="admin-panel"><div class="admin-panel__header"><h3 class="admin-panel__title">歷史批次</h3></div><div class="admin-panel__body"><?php foreach($runs as $item): ?><a class="btn btn--secondary btn--sm" href="?run=<?= (int)$item['id'] ?>">#<?= (int)$item['id'] ?> <?= esc($item['status']) ?></a> <?php endforeach; ?></div></section><?php endif; ?>
</main></body></html>

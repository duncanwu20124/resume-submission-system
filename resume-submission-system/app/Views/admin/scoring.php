<!DOCTYPE html>
<html lang="zh-TW"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>學生評分管理</title><link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>"></head><body>
<header class="sys-navbar"><div class="sys-navbar__inner"><div class="sys-navbar__brand"><h1 class="sys-navbar__title">學生評分管理</h1><span class="sys-navbar__badge">Admin Portal</span></div><div class="sys-navbar__user"><a class="sys-navbar__link" href="/AdminController/preferences">志願序</a><a class="sys-navbar__link" href="/AdminController/allocation">分發管理</a><a class="sys-navbar__link" href="/AdminController">返回後台</a></div></div></header>
<main class="admin-shell admin-shell--with-sidebar">
<?= view('partials/admin_sidebar') ?>
<?php
$filterQuery = array_filter($filters, static fn ($value) => $value !== '' && $value !== null);
$hasFilters = $filters['keyword'] !== '' || $filters['score_status'] !== 'all' || $filters['sort'] !== 'student_id' || $filters['direction'] !== 'ASC' || $filters['per_page'] !== 20;
?>
<div class="page-header"><div><h2 class="page-header__title">學生評分</h2><p class="page-header__description">確認每位學生的總分與備註；只有「已確認」的評分可以進入分發。</p></div><a class="btn btn--secondary btn--sm" href="/AdminController/allocation">前往分發管理</a></div>
<?php if (session()->getFlashdata('success')): ?><div class="alert alert--success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
<?php if (session()->getFlashdata('error')): ?><div class="alert alert--error"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>

<section class="stats-grid scoring-stats" aria-label="評分進度">
    <div class="stat-card"><strong class="stat-card__value"><?= (int) $total_submitted ?></strong><span class="stat-card__label">待處理學生總數</span></div>
    <div class="stat-card stat-card--success"><strong class="stat-card__value"><?= (int) $total_confirmed ?></strong><span class="stat-card__label">已確認評分</span></div>
    <div class="stat-card stat-card--muted"><strong class="stat-card__value"><?= (int) $total_draft ?></strong><span class="stat-card__label">尚待確認</span></div>
</section>

<!-- 搜尋與篩選區塊 -->
<section class="search-box" aria-label="評分資料搜尋與篩選">
    <form class="search-box__form" action="/AdminController/scoring" method="GET">
        <div class="search-group search-group--select">
            <label for="search_by">搜尋欄位</label>
            <select id="search_by" name="search_by">
                <option value="name" <?= $filters['search_by'] === 'name' ? 'selected' : '' ?>>使用者姓名</option>
                <option value="id" <?= $filters['search_by'] === 'id' ? 'selected' : '' ?>>學號 Student ID</option>
            </select>
        </div>

        <div class="search-group search-group--input">
            <label for="keyword">關鍵字</label>
            <input id="keyword" type="search" name="keyword" value="<?= esc($filters['keyword']) ?>" placeholder="請輸入姓名或學號...">
        </div>

        <div class="search-group search-group--select">
            <label for="score_status">評分狀態</label>
            <select id="score_status" name="score_status">
                <option value="all" <?= $filters['score_status'] === 'all' ? 'selected' : '' ?>>全部狀態</option>
                <option value="confirmed" <?= $filters['score_status'] === 'confirmed' ? 'selected' : '' ?>>已確認</option>
                <option value="draft" <?= $filters['score_status'] === 'draft' ? 'selected' : '' ?>>草稿 / 待確認</option>
                <option value="unscored" <?= $filters['score_status'] === 'unscored' ? 'selected' : '' ?>>尚未評分</option>
            </select>
        </div>

        <div class="search-group search-group--select">
            <label for="sort">排序欄位</label>
            <select id="sort" name="sort">
                <option value="student_id" <?= $filters['sort'] === 'student_id' ? 'selected' : '' ?>>學號</option>
                <option value="name" <?= $filters['sort'] === 'name' ? 'selected' : '' ?>>使用者姓名</option>
                <option value="total_score" <?= $filters['sort'] === 'total_score' ? 'selected' : '' ?>>評分總分</option>
                <option value="submitted_at" <?= $filters['sort'] === 'submitted_at' ? 'selected' : '' ?>>送出志願時間</option>
            </select>
        </div>

        <div class="search-group search-group--select">
            <label for="direction">排序方向</label>
            <select id="direction" name="direction">
                <option value="ASC" <?= $filters['direction'] === 'ASC' ? 'selected' : '' ?>>由小到大 / 升冪</option>
                <option value="DESC" <?= $filters['direction'] === 'DESC' ? 'selected' : '' ?>>由大到小 / 降冪</option>
            </select>
        </div>

        <div class="search-group search-group--select">
            <label for="per_page">每頁筆數</label>
            <select id="per_page" name="per_page">
                <option value="10" <?= $filters['per_page'] === 10 ? 'selected' : '' ?>>10 筆</option>
                <option value="20" <?= $filters['per_page'] === 20 ? 'selected' : '' ?>>20 筆</option>
                <option value="50" <?= $filters['per_page'] === 50 ? 'selected' : '' ?>>50 筆</option>
                <option value="100" <?= $filters['per_page'] === 100 ? 'selected' : '' ?>>100 筆</option>
            </select>
        </div>

        <div class="search-actions">
            <button class="btn btn--primary" type="submit">搜尋</button>
            <?php if ($hasFilters): ?>
                <a class="btn btn--secondary" href="/AdminController/scoring">清除搜尋</a>
            <?php endif; ?>
            <a class="btn btn--secondary" href="/AdminController/scoring/export?<?= esc(http_build_query($filters)) ?>">匯出資料</a>
        </div>
    </form>
</section>

<section class="admin-panel">
    <div class="admin-panel__header">
        <div>
            <h3 class="admin-panel__title">評分清單</h3>
            <span class="table-meta__count">符合篩選共 <?= (int) $total_filtered ?> 位（本頁顯示 <?= count($students) ?> 位）</span>
        </div>
        <button class="btn btn--primary btn--sm" id="save-all-scores" type="button" <?= empty($students) ? 'disabled' : '' ?>>儲存本頁所有評分</button>
    </div>
    <div class="admin-panel__body admin-panel__body--flush">
        <div class="table-responsive">
            <table class="data-table scoring-table">
                <thead>
                    <tr>
                        <th style="width: 220px;">學生</th>
                        <th style="width: 140px;">總分（0～100）</th>
                        <th style="width: 140px;">評分狀態</th>
                        <th>內部備註</th>
                        <th style="width: 110px;">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$students): ?>
                        <tr><td colspan="5" class="table-empty">查無符合條件的學生資料。</td></tr>
                    <?php endif; ?>
                    <?php foreach ($students as $row): ?>
                        <?php $formId = 'score-form-' . (int) $row['student_db_id']; ?>
                        <tr>
                            <td>
                                <strong><?= esc($row['student_name']) ?></strong><br>
                                <small style="color: var(--sys-text-muted);"><?= esc($row['student_number']) ?></small>
                            </td>
                            <td data-label="總分">
                                <label class="sr-only" for="score-<?= (int) $row['student_db_id'] ?>"> <?= esc($row['student_name']) ?>的總分</label>
                                <input id="score-<?= (int) $row['student_db_id'] ?>" class="score-input" form="<?= $formId ?>" name="total_score" type="number" min="0" max="100" step="1" required value="<?= esc($row['total_score'] !== null ? (int)$row['total_score'] : '') ?>" style="width: 90px; font-weight: 700; text-align: center;">
                            </td>
                            <td data-label="評分狀態">
                                <label class="sr-only" for="status-<?= (int) $row['student_db_id'] ?>"> <?= esc($row['student_name']) ?>的評分狀態</label>
                                <select id="status-<?= (int) $row['student_db_id'] ?>" class="score-status" form="<?= $formId ?>" name="status" style="width: 100px;">
                                    <option value="draft" <?= ($row['score_status'] ?? '') === 'draft' ? 'selected' : '' ?>>草稿</option>
                                    <option value="confirmed" <?= ($row['score_status'] ?? '') === 'confirmed' ? 'selected' : '' ?>>已確認</option>
                                </select>
                            </td>
                            <td data-label="內部備註">
                                <label class="sr-only" for="comment-<?= (int) $row['student_db_id'] ?>"> <?= esc($row['student_name']) ?>的內部備註</label>
                                <input id="comment-<?= (int) $row['student_db_id'] ?>" class="score-comment" form="<?= $formId ?>" name="comment" maxlength="1000" value="<?= esc($row['comment'] ?? '') ?>" placeholder="僅管理員可見">
                            </td>
                            <td data-label="操作">
                                <form id="<?= $formId ?>" method="post" action="/AdminController/scoring/<?= (int) $row['student_db_id'] ?>">
                                    <?= csrf_field() ?>
                                    <button class="btn btn--primary btn--sm" type="submit">儲存</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- 分頁導覽列 -->
<?php if (isset($pager) && $pager->getPageCount() > 1): ?>
    <div class="pagination" style="margin: 1.5rem 0; display: flex; align-items: center; justify-content: center; gap: 1rem;">
        <?php if ($pager->getCurrentPage() > 1): ?>
            <a class="btn btn--secondary btn--sm" href="/AdminController/scoring?<?= esc(http_build_query(array_merge($filterQuery, ['page' => $pager->getCurrentPage() - 1]))) ?>">上一頁</a>
        <?php endif; ?>
        <span style="font-weight: 600; color: var(--sys-text-muted);">第 <?= esc($pager->getCurrentPage()) ?> / <?= esc($pager->getPageCount()) ?> 頁（每頁 <?= (int)$filters['per_page'] ?> 筆）</span>
        <?php if ($pager->getCurrentPage() < $pager->getPageCount()): ?>
            <a class="btn btn--secondary btn--sm" href="/AdminController/scoring?<?= esc(http_build_query(array_merge($filterQuery, ['page' => $pager->getCurrentPage() + 1]))) ?>">下一頁</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<p class="form-hint scoring-save-status" id="scoring-save-status" role="status" aria-live="polite" style="text-align: center; font-weight: 600; color: #2563eb;"></p>

<script>
(() => {
    const button = document.getElementById('save-all-scores');
    const status = document.getElementById('scoring-save-status');
    if (!button) return;

    button.addEventListener('click', async () => {
        const forms = Array.from(document.querySelectorAll('.scoring-table form[id^="score-form-"]'));
        if (!forms.length) return;

        button.disabled = true;
        button.textContent = '儲存本頁中…';
        status.textContent = `準備儲存本頁 ${forms.length} 筆評分，請稍候。`;

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
            button.textContent = '儲存本頁所有評分';
            status.textContent = `已儲存 ${saved} / ${forms.length} 筆；請檢查後再重試。`;
        }
    });
})();
</script>
</main></body></html>

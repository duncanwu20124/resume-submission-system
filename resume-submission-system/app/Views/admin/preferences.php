<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>學生志願序管理 | 學生甄選與志願媒合系統</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
    <style>
        .stats-accordion summary {
            cursor: pointer;
            user-select: none;
            list-style: none;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .stats-accordion summary::-webkit-details-marker { display: none; }
        .stats-accordion summary::after {
            content: '⌄';
            font-size: 1.25rem;
            line-height: 1;
            color: var(--sys-text-muted, #64748b);
            margin-left: auto;
            padding-left: 0.5rem;
            font-weight: 700;
        }
        .stats-accordion[open] summary::after {
            content: '⌃';
        }
    </style>
</head>
<body>

<header class="sys-navbar">
    <div class="sys-navbar__inner">
        <div class="sys-navbar__brand">
            <svg width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--sys-primary);">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
            </svg>
            <h1 class="sys-navbar__title">管理員系統</h1>
            <span class="sys-navbar__badge">Admin Portal</span>
        </div>
        <div class="sys-navbar__user">
            <a class="sys-navbar__link" href="/AdminController">返回資料清單</a>
            <a class="sys-navbar__link" href="/AdminController/profile">我的帳號</a>
            <a class="sys-navbar__link sys-navbar__link--btn" href="/AdminController/logout">登出</a>
        </div>
    </div>
</header>

<main class="admin-shell admin-shell--with-sidebar">
    <?= view('partials/admin_sidebar') ?>
    <div class="page-header">
        <h2 class="page-header__title">學生志願序管理</h2>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert--success" role="status"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert--error" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <section class="stats-grid" aria-label="志願序統計" style="grid-template-columns: repeat(3, minmax(0, 1fr));">
        <div class="stat-card">
            <span class="stat-card__label">學生總數</span>
            <strong class="stat-card__value"><?= esc($total_students) ?></strong>
        </div>
        <div class="stat-card stat-card--success">
            <span class="stat-card__label">已送出志願序</span>
            <strong class="stat-card__value"><?= esc($submitted_count) ?></strong>
        </div>
        <div class="stat-card stat-card--muted">
            <span class="stat-card__label">尚未送出</span>
            <strong class="stat-card__value"><?= esc(max(0, $total_students - $submitted_count)) ?></strong>
        </div>
    </section>

    <section class="search-box" aria-label="志願序搜尋">
        <form class="search-box__form" action="/AdminController/preferences" method="GET">
            <div class="search-group search-group--input">
                <label for="keyword">搜尋學生姓名 / 學號</label>
                <input type="text" id="keyword" name="keyword" value="<?= esc($keyword ?? '') ?>" placeholder="輸入學生姓名或學號...">
            </div>
            <div class="search-group search-group--select">
                <label for="school">篩選學校（任一志願）</label>
                <select id="school" name="school">
                    <option value="">全部學校</option>
                    <?php foreach ($universities as $university): ?>
                        <option value="<?= esc($university) ?>" <?= $school === $university ? 'selected' : '' ?>><?= esc($university) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="search-group search-group--input">
                <label for="department">篩選學系關鍵字（任一志願）</label>
                <input type="text" id="department" name="department" value="<?= esc($department ?? '') ?>" placeholder="例如：資訊工程、中文、電機...">
            </div>
            <div class="search-group search-group--select">
                <label for="sort">排序方式</label>
                <select id="sort" name="sort">
                    <?php foreach ($sort_options as $value => $label): ?>
                        <option value="<?= esc($value) ?>" <?= $sort === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="search-group search-group--select">
                <label for="per_page">每頁筆數</label>
                <select id="per_page" name="per_page">
                    <option value="10" <?= ($per_page ?? 20) === 10 ? 'selected' : '' ?>>10 筆</option>
                    <option value="20" <?= ($per_page ?? 20) === 20 ? 'selected' : '' ?>>20 筆</option>
                    <option value="50" <?= ($per_page ?? 20) === 50 ? 'selected' : '' ?>>50 筆</option>
                    <option value="100" <?= ($per_page ?? 20) === 100 ? 'selected' : '' ?>>100 筆</option>
                </select>
            </div>
            <div class="search-actions">
                <button class="btn btn--primary" type="submit">搜尋</button>
                <?php if ($school !== '' || !empty($keyword) || !empty($department) || $sort !== 'school_count_desc' || ($per_page ?? 20) !== 20): ?>
                    <a class="btn btn--secondary" href="/AdminController/preferences">清除搜尋</a>
                <?php endif; ?>
                <a class="btn btn--secondary" href="/AdminController/preferences/export?<?= esc(http_build_query([
                    'school' => $school ?? '',
                    'keyword' => $keyword ?? '',
                    'department' => $department ?? '',
                    'sort' => $sort ?? 'school_count_desc',
                ])) ?>">匯出資料</a>
            </div>
        </form>
    </section>


    <details class="admin-panel stats-accordion" aria-labelledby="school-stats-heading" <?= $school !== '' ? 'open' : '' ?>>
        <summary class="admin-panel__header">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <h3 class="admin-panel__title" id="school-stats-heading" style="margin: 0;">
                    <?= $school !== '' ? '【' . esc($school) . '】志願填寫統計' : '各校志願填寫統計' ?>
                </h3>
            </div>
            <span class="table-meta__count" style="margin-left: 0.75rem;">
                <?= $school !== '' ? '已篩選指定學校（共 1 筆）' : '以所有已送出志願序計算' ?>
            </span>
        </summary>
        <div class="admin-panel__body admin-panel__body--flush">
            <?php if (!empty($school_stats)): ?>
                <div class="table-container table-container--flat">
                    <table class="data-table school-stats-table">
                        <thead>
                            <tr>
                                <th>學校</th>
                                <th>第 1 志願</th>
                                <th>第 2 志願</th>
                                <th>第 3 志願</th>
                                <th>第 4 志願</th>
                                <th>第 5 志願</th>
                                <th>第 6 志願</th>
                                <th>填寫總人數</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($school_stats as $stat): ?>
                                <tr>
                                    <td class="col-school" data-label="學校"><?= esc($stat['school']) ?></td>
                                    <?php for ($rank = 1; $rank <= 6; $rank++): ?>
                                        <td data-label="第 <?= $rank ?> 志願"><?= esc($stat['rank_' . $rank]) ?></td>
                                    <?php endfor; ?>
                                    <td class="col-total" data-label="填寫總人數"><?= esc($stat['total']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="table-empty">目前尚無已送出的志願序統計資料。</p>
            <?php endif; ?>
        </div>
    </details>

    <section aria-label="志願序清單">
        <div class="table-meta">
            <span>
                <?php
                    $filterLabels = [];
                    if (!empty($keyword)) $filterLabels[] = '姓名/學號包含【' . esc($keyword) . '】';
                    if (!empty($school)) $filterLabels[] = '學校【' . esc($school) . '】';
                    if (!empty($department)) $filterLabels[] = '學系包含【' . esc($department) . '】';
                    echo !empty($filterLabels) ? '已篩選 ' . implode(' 且 ', $filterLabels) . ' 之學生' : '僅列出已成功送出志願序的學生';
                ?>
            </span>
            <span class="table-meta__count">符合條件共 <?= (int) ($total_filtered ?? count($preferences)) ?> 筆（本頁顯示 <?= count($preferences) ?> 筆）</span>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 140px;">學號</th>
                        <th style="width: 130px;">姓名</th>
                        <th>完整志願序（1～6）</th>
                        <th style="width: 160px;">送出時間</th>
                        <th style="width: 110px; text-align: center;">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($preferences)): ?>
                        <?php foreach ($preferences as $row): ?>
                            <tr>
                                <td class="col-id" data-label="學號"><?= esc($row['student_number']) ?></td>
                                <td class="col-name" data-label="姓名"><?= esc($row['student_name']) ?></td>
                                <td data-label="完整志願序">
                                    <ol class="preference-ranks">
                                        <?php for ($rank = 1; $rank <= 6; $rank++): ?>
                                            <?php $choice = trim((string) $row['choice_' . $rank]); ?>
                                            <?php if ($choice === ''): ?>
                                                <?php continue; ?>
                                            <?php endif; ?>
                                            <?php 
                                                $isSchoolMatch = ($school !== '' && (\App\Config\Universities::extractSchool($choice) === $school || $choice === $school));
                                                $isDeptMatch = (!empty($department) && mb_strpos(mb_strtolower(\App\Config\Universities::extractDepartment($choice)), mb_strtolower($department)) !== false);
                                                $isMatch = $isSchoolMatch || $isDeptMatch;
                                            ?>

                                            <li style="<?= $isMatch ? 'background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 3px 8px; font-weight: 600;' : '' ?>">
                                                <span class="preference-rank-number" style="<?= $isMatch ? 'background: #2563eb; color: #ffffff;' : '' ?>"><?= $rank ?></span>
                                                <span style="<?= $isMatch ? 'color: #1d4ed8;' : '' ?>"><?= esc($choice) ?></span>
                                            </li>
                                        <?php endfor; ?>


                                    </ol>
                                </td>
                                <td class="col-time" data-label="送出時間"><?= esc($row['submitted_at']) ?></td>
                                <td class="col-actions" data-label="操作">
                                    <div class="table-actions">
                                        <a class="btn btn--secondary btn--sm" href="/AdminController/preferences/<?= esc($row['student_db_id']) ?>">查看</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td class="table-empty" colspan="5">
                                <?= $school !== '' ? '目前無任何已送出志願序的學生將【' . esc($school) . '】列入志願。' : '目前查無符合條件的志願序資料。' ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php
        $queryParam = [
            'keyword' => $keyword ?? '',
            'school' => $school ?? '',
            'department' => $department ?? '',
            'sort' => $sort ?? '',
            'per_page' => $per_page ?? 20,
        ];
        $queryParam = array_filter($queryParam, fn($v) => $v !== '' && $v !== null);
        ?>
        <?php if (isset($pager) && $pager->getPageCount() > 1): ?>
            <div class="pagination" style="margin: 1.5rem 0; display: flex; align-items: center; justify-content: center; gap: 1rem;">
                <?php if ($pager->getCurrentPage() > 1): ?>
                    <a class="btn btn--secondary btn--sm" href="/AdminController/preferences?<?= esc(http_build_query(array_merge($queryParam, ['page' => $pager->getCurrentPage() - 1]))) ?>">上一頁</a>
                <?php endif; ?>
                <span style="font-weight: 600; color: var(--sys-text-muted);">第 <?= esc($pager->getCurrentPage()) ?> / <?= esc($pager->getPageCount()) ?> 頁（每頁 <?= (int) ($per_page ?? 20) ?> 筆，共 <?= (int) ($total_filtered ?? 0) ?> 筆）</span>
                <?php if ($pager->getCurrentPage() < $pager->getPageCount()): ?>
                    <a class="btn btn--secondary btn--sm" href="/AdminController/preferences?<?= esc(http_build_query(array_merge($queryParam, ['page' => $pager->getCurrentPage() + 1]))) ?>">下一頁</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

</body>
</html>

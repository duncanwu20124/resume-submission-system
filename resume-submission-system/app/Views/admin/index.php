<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>履歷繳交資料管理 - 甄選行政系統</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
</head>
<body>

<header class="sys-navbar">
    <div class="sys-navbar__inner">
        <div class="sys-navbar__brand">
            <h1 class="sys-navbar__title">管理員系統</h1>
        </div>
        <div class="sys-navbar__user">
            <span id="admin-session-nav-countdown" class="admin-session-nav-countdown" aria-label="管理員登入剩餘時間">05:00</span>
            <a class="sys-navbar__link" href="/AdminController/profile">我的帳號</a>
            <a class="sys-navbar__link sys-navbar__link--btn" href="/AdminController/logout">登出</a>
        </div>
    </div>
</header>

<main class="admin-shell">
    <div class="page-header">
        <h2 class="page-header__title">履歷繳交資料管理</h2>
    </div>

    <?php
    $filters = $filters ?? [
        'keyword' => '', 'search_by' => 'name', 'upload_status' => 'all',
        'uploaded_from' => '', 'uploaded_to' => '', 'sort' => 'uploaded_at',
        'direction' => 'DESC', 'per_page' => 20,
    ];
    $statistics = $statistics ?? ['total' => 0, 'uploaded' => 0, 'missing' => 0, 'latest_uploaded_at' => null];
    $filterQuery = array_filter($filters, static fn ($value) => $value !== '' && $value !== null);
    $filterQueryString = http_build_query($filterQuery);
    $hasFilters = $filters['keyword'] !== ''
        || $filters['upload_status'] !== 'all'
        || $filters['uploaded_from'] !== ''
        || $filters['uploaded_to'] !== '';
    $directionLabels = match ($filters['sort']) {
        'name' => [
            'ASC'  => '姓名筆畫少到多',
            'DESC' => '姓名筆畫多到少',
        ],
        'student_id', 'email' => [
            'ASC'  => '升冪（小到大／A-Z）',
            'DESC' => '降冪（大到小／Z-A）',
        ],
        default => [
            'ASC'  => '由舊到新',
            'DESC' => '由新到舊',
        ],
    };
    ?>

    <?php if ($error = session()->getFlashdata('error')): ?>
        <div class="admin-alert admin-alert--error" role="alert"><?= esc($error) ?></div>
    <?php endif; ?>
    <?php if ($success = session()->getFlashdata('success')): ?>
        <div class="admin-alert admin-alert--success" role="status"><?= esc($success) ?></div>
    <?php endif; ?>

    <section class="stats-grid" aria-label="履歷資料統計">
        <div class="stat-card">
            <span class="stat-card__label">學生總數</span>
            <strong class="stat-card__value"><?= esc($statistics['total']) ?></strong>
        </div>
        <div class="stat-card stat-card--success">
            <span class="stat-card__label">目前已上傳</span>
            <strong class="stat-card__value"><?= esc($statistics['uploaded']) ?></strong>
        </div>
        <div class="stat-card stat-card--muted">
            <span class="stat-card__label">尚未上傳</span>
            <strong class="stat-card__value"><?= esc($statistics['missing']) ?></strong>
        </div>
        <div class="stat-card">
            <span class="stat-card__label">最近上傳時間</span>
            <strong class="stat-card__value stat-card__value--time">
                <?= !empty($statistics['latest_uploaded_at']) ? esc($statistics['latest_uploaded_at']) : '尚無紀錄' ?>
            </strong>
        </div>
    </section>

    <section class="admin-panel" aria-labelledby="mock-applications-heading">
        <div class="admin-panel__header">
            <h3 class="admin-panel__title" id="mock-applications-heading">正式報名資料</h3>
            <span class="tag-status tag-status--muted">230 筆</span>
        </div>
        <div class="admin-panel__body">
            <p class="form-hint">此清單獨立於目前已註冊使用者，只包含學號 Student ID 與使用者姓名。</p>
            <a class="btn btn--primary" href="/AdminController/applications">進入報名資料管理</a>
        </div>
    </section>

    <!-- 搜尋區塊 -->
    <section class="search-box" aria-label="資料搜尋與篩選">
        <form class="search-box__form" action="/AdminController/search" method="GET">
            <div class="search-group search-group--select">
                <label for="search_by">搜尋欄位</label>
                <select id="search_by" name="search_by">
                    <option value="name" <?= $filters['search_by'] === 'name' ? 'selected' : '' ?>>使用者姓名</option>
                    <option value="id" <?= $filters['search_by'] === 'id' ? 'selected' : '' ?>>學號 Student ID</option>
                    <option value="email" <?= $filters['search_by'] === 'email' ? 'selected' : '' ?>>Email</option>
                </select>
            </div>

            <div class="search-group search-group--input">
                <label for="keyword">關鍵字</label>
                <input id="keyword" type="search" name="keyword" value="<?= esc($filters['keyword']) ?>" placeholder="請輸入姓名、ID 或 Email">
            </div>

            <div class="search-group search-group--select">
                <label for="upload_status">履歷狀態</label>
                <select id="upload_status" name="upload_status">
                    <option value="all" <?= $filters['upload_status'] === 'all' ? 'selected' : '' ?>>全部</option>
                    <option value="uploaded" <?= $filters['upload_status'] === 'uploaded' ? 'selected' : '' ?>>已上傳</option>
                    <option value="missing" <?= $filters['upload_status'] === 'missing' ? 'selected' : '' ?>>尚未上傳</option>
                </select>
            </div>

            <div class="search-group">
                <label for="uploaded_from">上傳日期起</label>
                <input id="uploaded_from" type="date" name="uploaded_from" value="<?= esc($filters['uploaded_from']) ?>">
            </div>

            <div class="search-group">
                <label for="uploaded_to">上傳日期迄</label>
                <input id="uploaded_to" type="date" name="uploaded_to" value="<?= esc($filters['uploaded_to']) ?>">
            </div>

            <div class="search-group search-group--select">
                <label for="sort">排序欄位</label>
                <select id="sort" name="sort">
                    <option value="uploaded_at" <?= $filters['sort'] === 'uploaded_at' ? 'selected' : '' ?>>上傳時間</option>
                    <option value="student_id" <?= $filters['sort'] === 'student_id' ? 'selected' : '' ?>>學生 ID</option>
                    <option value="name" <?= $filters['sort'] === 'name' ? 'selected' : '' ?>>姓名</option>
                    <option value="email" <?= $filters['sort'] === 'email' ? 'selected' : '' ?>>Email</option>
                    <option value="created_at" <?= $filters['sort'] === 'created_at' ? 'selected' : '' ?>>註冊時間</option>
                </select>
            </div>

            <div class="search-group search-group--select">
                <label for="direction">排序方向</label>
                <select id="direction" name="direction">
                    <option value="DESC" <?= $filters['direction'] === 'DESC' ? 'selected' : '' ?>><?= esc($directionLabels['DESC']) ?></option>
                    <option value="ASC" <?= $filters['direction'] === 'ASC' ? 'selected' : '' ?>><?= esc($directionLabels['ASC']) ?></option>
                </select>
            </div>

            <div class="search-group search-group--select">
                <label for="per_page">每頁筆數</label>
                <select id="per_page" name="per_page">
                    <?php foreach ([10, 20, 50, 100] as $pageSize): ?>
                        <option value="<?= $pageSize ?>" <?= $filters['per_page'] === $pageSize ? 'selected' : '' ?>><?= $pageSize ?> 筆</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="search-actions">
                <button class="btn btn--primary" type="submit">搜尋</button>
                <?php if ($hasFilters): ?>
                    <a class="btn btn--secondary" href="/AdminController">清除搜尋</a>
                <?php endif; ?>
                <a class="btn btn--secondary" href="/AdminController/export?<?= esc($filterQueryString) ?>">匯出資料</a>
            </div>
        </form>
    </section>

    <!-- 資料表格區塊 -->
    <section aria-label="資料清單">
        <div class="table-meta">
            <span>
                <?php if ($filters['keyword'] !== '' || $filters['upload_status'] !== 'all' || $filters['uploaded_from'] !== '' || $filters['uploaded_to'] !== ''): ?>
                    已套用篩選條件
                <?php else: ?>
                    資料狀態：全部清單
                <?php endif; ?>
            </span>
            <span class="table-meta__count">目前顯示 <?= count($users ?? []) ?> 筆，共 <?= esc($total_filtered ?? 0) ?> 筆</span>
        </div>

        <form action="/AdminController/batchDownload" method="POST" id="batch-download-form" onsubmit="return confirmBatchDownload(this);">
            <input type="hidden" name="_admin_csrf" value="">
            <div class="batch-toolbar">
                <span>勾選已上傳履歷後，可批次下載</span>
                <div class="batch-toolbar__actions">
                    <label class="select-all-label"><input type="checkbox" id="select-all-resumes"> 全選本頁</label>
                    <button class="btn btn--primary" type="submit">批次下載</button>
                </div>
            </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 42px; text-align: center;">選取</th>
                        <th style="width: 140px;">學號 Student ID</th>
                        <th style="width: 130px;">使用者姓名</th>
                        <th>Email</th>
                        <th style="width: 260px;">履歷檔案</th>
                        <th style="width: 160px;">上傳時間</th>
                        <th style="width: 130px; text-align: center;">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="col-select" data-label="選取">
                                    <?php if (!empty($user['file_name'])): ?>
                                        <input type="checkbox" name="selected_ids[]" value="<?= esc($user['id']) ?>" class="resume-checkbox" aria-label="選取 <?= esc($user['name']) ?> 的履歷">
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td class="col-id" data-label="學號 Student ID"><?= esc($user['student_id']) ?></td>
                                <td class="col-name" data-label="使用者姓名"><?= esc($user['name']) ?></td>
                                <td class="col-email" data-label="Email"><?= esc($user['email']) ?></td>
                                <td class="col-file" data-label="履歷檔案">
                                    <?php if (!empty($user['file_name'])): ?>
                                        <span class="tag-status tag-status--success">已上傳</span>
                                        <span class="file-name-text"><?= esc($user['file_name']) ?></span>
                                    <?php else: ?>
                                        <span class="tag-status tag-status--muted">尚未上傳</span>
                                    <?php endif; ?>
                                </td>
                                <td class="col-time" data-label="上傳時間"><?= !empty($user['uploaded_at']) ? esc($user['uploaded_at']) : '尚無紀錄' ?></td>
                                <td class="col-actions" data-label="操作">
                                    <div class="table-actions">
                                        <a class="btn btn--secondary btn--sm" href="/AdminController/show/<?= $user['id'] ?>">查看</a>
                                        <?php if (!empty($user['file_name'])): ?>
                                            <a class="btn btn--primary btn--sm" href="/AdminController/download/<?= $user['id'] ?>">下載</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td class="table-empty" colspan="7">查無符合條件之資料。請調整搜尋條件後重試。</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        </form>

        <?php if (isset($pager) && $pager->getPageCount() > 1): ?>
            <nav class="pagination" aria-label="資料分頁">
                <?php if ($pager->getCurrentPage() > 1): ?>
                    <a class="btn btn--secondary btn--sm" href="/AdminController?<?= esc(http_build_query(array_merge($filterQuery, ['page' => $pager->getCurrentPage() - 1]))) ?>">上一頁</a>
                <?php endif; ?>
                <span>第 <?= esc($pager->getCurrentPage()) ?> / <?= esc($pager->getPageCount()) ?> 頁</span>
                <?php if ($pager->getCurrentPage() < $pager->getPageCount()): ?>
                    <a class="btn btn--secondary btn--sm" href="/AdminController?<?= esc(http_build_query(array_merge($filterQuery, ['page' => $pager->getCurrentPage() + 1]))) ?>">下一頁</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </section>
</main>

<script src="<?= base_url('assets/js/admin-security.js') ?>"></script>

<div id="admin-session-warning" class="admin-session-warning" hidden role="alertdialog" aria-modal="true" aria-labelledby="admin-session-warning-title" tabindex="-1">
    <div class="admin-session-warning__dialog">
        <h2 id="admin-session-warning-title">管理員登入即將逾時</h2>
        <p>已經 5 分鐘沒有操作，系統將在 <strong id="admin-session-countdown">30</strong> 秒後返回管理員登入畫面。</p>
        <button class="btn btn--primary" type="button" id="admin-session-continue">繼續使用</button>
    </div>
</div>

<script>
    const sortSelect = document.getElementById('sort');
    const directionSelect = document.getElementById('direction');
    const directionLabels = {
        time: {
            ASC: '由舊到新',
            DESC: '由新到舊',
        },
        name: {
            ASC: '姓名筆畫少到多',
            DESC: '姓名筆畫多到少',
        },
        text: {
            ASC: '升冪（小到大／A-Z）',
            DESC: '降冪（大到小／Z-A）',
        },
    };

    function updateDirectionLabels() {
        const labels = sortSelect.value === 'name'
            ? directionLabels.name
            : ['student_id', 'email'].includes(sortSelect.value)
                ? directionLabels.text
                : directionLabels.time;

        Array.from(directionSelect.options).forEach(function (option) {
            option.textContent = labels[option.value];
        });
    }

    sortSelect.addEventListener('change', updateDirectionLabels);
    updateDirectionLabels();

    const selectAllResumes = document.getElementById('select-all-resumes');
    if (selectAllResumes) {
        selectAllResumes.addEventListener('change', function () {
            document.querySelectorAll('.resume-checkbox').forEach(function (checkbox) {
                checkbox.checked = selectAllResumes.checked;
            });
        });
    }

    function confirmBatchDownload(form) {
        const selected = form.querySelectorAll('input[name="selected_ids[]"]:checked');
        if (selected.length === 0) {
            alert('請至少選擇一份履歷檔案。');
            return false;
        }
        return true;
    }

    (function () {
        const lastActivity = <?= (int) session()->get('admin_last_activity') ?>;
        const warning = document.getElementById('admin-session-warning');
        const countdown = document.getElementById('admin-session-countdown');
        const continueButton = document.getElementById('admin-session-continue');
        const navCountdown = document.getElementById('admin-session-nav-countdown');

        if (!lastActivity || !warning || !countdown || !continueButton || !navCountdown) {
            return;
        }

        const showWarningAfter = Math.max(0, ((lastActivity + 300) * 1000) - Date.now());
        let countdownTimer;
        let redirectTimer;

        const formatTime = function (seconds) {
            const minutes = Math.floor(seconds / 60).toString().padStart(2, '0');
            const remainder = (seconds % 60).toString().padStart(2, '0');
            return minutes + ':' + remainder;
        };

        const continueSession = function () {
            continueButton.disabled = true;
            window.clearInterval(countdownTimer);
            window.clearTimeout(redirectTimer);

            fetch('/AdminController/keepAlive', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                body: '_admin_csrf=' + encodeURIComponent((document.querySelector('input[name="_admin_csrf"]') || {}).value || ''),
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('session-expired');
                    }
                    return response.json();
                })
                .then(function (result) {
                    if (!result.success) {
                        throw new Error('session-expired');
                    }
                    window.location.reload();
                })
                .catch(function () {
                    window.location.replace('/AdminController/login');
                });
        };

        continueButton.addEventListener('click', continueSession);
        warning.addEventListener('click', function (event) {
            if (event.target === warning) {
                continueSession();
            }
        });

        window.setTimeout(function () {
            warning.hidden = false;
            warning.focus();

            let seconds = 30;
            countdown.textContent = seconds;
            navCountdown.textContent = formatTime(seconds);
            countdownTimer = window.setInterval(function () {
                seconds -= 1;
                countdown.textContent = seconds;
                navCountdown.textContent = formatTime(Math.max(0, seconds));

                if (seconds <= 0) {
                    window.clearInterval(countdownTimer);
                }
            }, 1000);

            redirectTimer = window.setTimeout(function () {
                window.location.replace('/AdminController/login');
            }, 30000);
        }, showWarningAfter);

        const updateNavCountdown = function () {
            const remaining = Math.max(0, (lastActivity + 300) - Math.floor(Date.now() / 1000));
            navCountdown.textContent = formatTime(remaining);

            if (remaining <= 0) {
                return;
            }

            window.setTimeout(updateNavCountdown, 1000);
        };

        updateNavCountdown();
    }());
</script>

</body>
</html>

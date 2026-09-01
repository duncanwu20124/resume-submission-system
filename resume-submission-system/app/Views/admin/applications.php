<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>正式報名資料管理 - 甄選行政系統</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
</head>
<body>

<header class="sys-navbar">
    <div class="sys-navbar__inner">
        <div class="sys-navbar__brand">
            <h1 class="sys-navbar__title">管理員系統</h1>
        </div>
        <div class="sys-navbar__user">
            <a class="sys-navbar__link" href="/AdminController">返回資料清單</a>
            <span id="admin-session-nav-countdown" class="admin-session-nav-countdown" aria-label="管理員登入剩餘時間">05:00</span>
            <a class="sys-navbar__link" href="/AdminController/profile">我的帳號</a>
            <a class="sys-navbar__link sys-navbar__link--btn" href="/AdminController/logout">登出</a>
        </div>
    </div>
</header>

<main class="admin-shell">
    <div class="page-header">
        <h2 class="page-header__title">正式報名資料管理</h2>
    </div>

    <section class="search-box" aria-label="正式報名資料搜尋">
        <form class="search-box__form" action="/AdminController/applications" method="GET">
            <div class="search-group search-group--select">
                <label for="search_by">搜尋欄位</label>
                <select id="search_by" name="search_by">
                    <option value="name" <?= $filters['search_by'] === 'name' ? 'selected' : '' ?>>使用者姓名</option>
                    <option value="id" <?= $filters['search_by'] === 'id' ? 'selected' : '' ?>>學號 Student ID</option>
                </select>
            </div>
            <div class="search-group search-group--input">
                <label for="keyword">搜尋姓名或學號 Student ID</label>
                <input id="keyword" type="search" name="keyword" value="<?= esc($filters['keyword']) ?>" placeholder="請輸入關鍵字">
            </div>
            <div class="search-group search-group--select">
                <label for="sort">排序欄位</label>
                <select id="sort" name="sort">
                    <option value="id" <?= $filters['sort'] === 'id' ? 'selected' : '' ?>>學號 Student ID</option>
                    <option value="name" <?= $filters['sort'] === 'name' ? 'selected' : '' ?>>使用者姓名</option>
                </select>
            </div>
            <div class="search-group search-group--select">
                <label for="direction">排序方向</label>
                <select id="direction" name="direction">
                    <option value="ASC" <?= $filters['direction'] === 'ASC' ? 'selected' : '' ?>>升冪（小到大／A-Z）</option>
                    <option value="DESC" <?= $filters['direction'] === 'DESC' ? 'selected' : '' ?>>降冪（大到小／Z-A）</option>
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
                <a class="btn btn--secondary" href="/AdminController/applications">清除搜尋</a>
                <a class="btn btn--secondary" href="/AdminController/applications/export?<?= esc(http_build_query($filters)) ?>">匯出資料</a>
            </div>
        </form>
    </section>

    <section aria-label="正式報名資料清單">
        <div class="table-meta">
            <span>資料只包含姓名與學號 Student ID，其餘欄位保留空白</span>
            <span class="table-meta__count">目前顯示 <?= count($applications) ?> 筆，共 <?= esc($total) ?> 筆／全部 <?= esc($total_all) ?> 筆</span>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 220px;">學號 Student ID</th>
                        <th>使用者姓名</th>
                        <th style="width: 130px; text-align: center;">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($applications)): ?>
                        <?php foreach ($applications as $application): ?>
                            <tr>
                                <td data-label="學號 Student ID"><?= esc($application['id']) ?></td>
                                <td data-label="使用者姓名"><?= esc($application['name']) ?></td>
                                <td class="col-actions" data-label="操作">
                                    <div class="table-actions">
                                        <a class="btn btn--secondary btn--sm" href="/AdminController/applications/<?= esc($application['id']) ?>">查看</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td class="table-empty" colspan="3">查無符合條件的正式報名資料。</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($page_count > 1): ?>
            <?php $query = $filters; ?>
            <nav class="pagination" aria-label="正式報名資料分頁">
                <?php if ($page > 1): ?>
                    <a class="btn btn--secondary btn--sm" href="/AdminController/applications?<?= esc(http_build_query(array_merge($query, ['page' => $page - 1]))) ?>">上一頁</a>
                <?php endif; ?>
                <span>第 <?= esc($page) ?> / <?= esc($page_count) ?> 頁</span>
                <?php if ($page < $page_count): ?>
                    <a class="btn btn--secondary btn--sm" href="/AdminController/applications?<?= esc(http_build_query(array_merge($query, ['page' => $page + 1]))) ?>">下一頁</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </section>
</main>

<script src="<?= base_url('assets/js/admin-security.js') ?>"></script>
<input type="hidden" name="_admin_csrf" value="">

<div id="admin-session-warning" class="admin-session-warning" hidden role="alertdialog" aria-modal="true" aria-labelledby="admin-session-warning-title" tabindex="-1">
    <div class="admin-session-warning__dialog">
        <h2 id="admin-session-warning-title">管理員登入即將逾時</h2>
        <p>已經 5 分鐘沒有操作，系統將在 <strong id="admin-session-countdown">30</strong> 秒後返回管理員登入畫面。</p>
        <button class="btn btn--primary" type="button" id="admin-session-continue">繼續使用</button>
    </div>
</div>

<script>
    (function () {
        const lastActivity = <?= (int) session()->get('admin_last_activity') ?>;
        const warning = document.getElementById('admin-session-warning');
        const countdown = document.getElementById('admin-session-countdown');
        const continueButton = document.getElementById('admin-session-continue');
        const navCountdown = document.getElementById('admin-session-nav-countdown');

        if (!lastActivity || !warning || !countdown || !continueButton || !navCountdown) {
            return;
        }

        const formatTime = function (seconds) {
            const minutes = Math.floor(seconds / 60).toString().padStart(2, '0');
            const remainder = (seconds % 60).toString().padStart(2, '0');
            return minutes + ':' + remainder;
        };
        const showWarningAfter = Math.max(0, ((lastActivity + 300) * 1000) - Date.now());
        let countdownTimer;
        let redirectTimer;

        const continueSession = function () {
            continueButton.disabled = true;
            window.clearInterval(countdownTimer);
            window.clearTimeout(redirectTimer);

            fetch('/AdminController/keepAlive', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                },
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

        const updateNavCountdown = function () {
            const remaining = Math.max(0, (lastActivity + 300) - Math.floor(Date.now() / 1000));
            navCountdown.textContent = formatTime(remaining);
            if (remaining > 0) {
                window.setTimeout(updateNavCountdown, 1000);
            }
        };
        updateNavCountdown();

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
    }());
</script>

</body>
</html>

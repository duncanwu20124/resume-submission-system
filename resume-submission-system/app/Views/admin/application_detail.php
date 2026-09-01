<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($application['name']) ?> - 報名資料</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
</head>
<body>

<header class="sys-navbar">
    <div class="sys-navbar__inner">
        <div class="sys-navbar__brand">
            <h1 class="sys-navbar__title">管理員系統</h1>
        </div>
        <div class="sys-navbar__user">
            <a class="sys-navbar__link" href="/AdminController/applications">返回報名資料</a>
            <span id="admin-session-nav-countdown" class="admin-session-nav-countdown" aria-label="管理員登入剩餘時間">05:00</span>
            <a class="sys-navbar__link" href="/AdminController/profile">我的帳號</a>
            <a class="sys-navbar__link sys-navbar__link--btn" href="/AdminController/logout">登出</a>
        </div>
    </div>
</header>

<main class="admin-shell admin-shell--narrow">
    <div class="page-header">
        <h2 class="page-header__title">單筆報名資料</h2>
    </div>

    <section class="detail-card" aria-labelledby="application-detail-heading">
        <div class="detail-card__header" id="application-detail-heading">基本資料</div>
        <table class="detail-table">
            <tbody>
                <tr>
                    <th scope="row">學號 Student ID</th>
                    <td><?= esc($application['id']) ?></td>
                </tr>
                <tr>
                    <th scope="row">使用者姓名</th>
                    <td><?= esc($application['name']) ?></td>
                </tr>
                <tr>
                    <th scope="row">Email</th>
                    <td>—</td>
                </tr>
                <tr>
                    <th scope="row">電話</th>
                    <td>—</td>
                </tr>
                <tr>
                    <th scope="row">選填校系資料</th>
                    <td>—</td>
                </tr>
            </tbody>
        </table>
        <div class="detail-card__footer">
            <p class="form-hint">此資料目前只有姓名與學號 Student ID，其餘報名內容尚未建立。</p>
            <a class="btn btn--secondary" href="/AdminController/applications">返回清單</a>
        </div>
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

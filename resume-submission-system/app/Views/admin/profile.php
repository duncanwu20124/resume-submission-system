<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>我的帳號 - 學生甄選與志願媒合系統</title>
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
            <a class="sys-navbar__link sys-navbar__link--btn" href="/AdminController/logout">登出</a>
        </div>
    </div>
</header>

<main class="admin-shell admin-shell--narrow">
    <div class="page-header">
        <h2 class="page-header__title">我的帳號</h2>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert--error" role="alert"><?= esc($error) ?></div>
    <?php endif; ?>
    <?php if ($success = session()->getFlashdata('success')): ?>
        <div class="alert alert--success" role="status"><?= esc($success) ?></div>
    <?php endif; ?>

    <div class="auth-box">
        <form action="/AdminController/profile" method="POST">
            <input type="hidden" name="_admin_csrf" value="">
            <div class="form-field">
                <label for="name">管理員姓名</label>
                <input id="name" type="text" name="name" value="<?= esc($admin['name']) ?>" maxlength="50" autocomplete="name" required autofocus>
            </div>

            <div class="form-field">
                <label for="username">管理員帳號</label>
                <input id="username" type="text" value="<?= esc($admin['username']) ?>" autocomplete="username" disabled>
            </div>

            <div class="form-field">
                <label for="employee_id">員工證編號</label>
                <input id="employee_id" type="text" value="<?= esc($admin['employee_id']) ?>" disabled>
            </div>

            <div class="form-field">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="<?= esc($admin['email']) ?>" autocomplete="email" required>
            </div>

            <div class="form-field">
                <label for="password">新密碼</label>
                <input id="password" type="password" name="password" minlength="6" autocomplete="new-password" aria-describedby="password-hint">
                <p class="form-hint" id="password-hint">若不修改密碼請留白，修改時至少需要 6 個字元。</p>
            </div>

            <div class="form-field">
                <label for="password_confirm">確認新密碼</label>
                <input id="password_confirm" type="password" name="password_confirm" minlength="6" autocomplete="new-password">
            </div>

            <button class="btn btn--primary btn--block" type="submit">儲存變更</button>
        </form>
    </div>
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
    (function () {
        const lastActivity = <?= (int) session()->get('admin_last_activity') ?>;
        const warning = document.getElementById('admin-session-warning');
        const countdown = document.getElementById('admin-session-countdown');
        const continueButton = document.getElementById('admin-session-continue');

        if (!lastActivity || !warning || !countdown || !continueButton) {
            return;
        }

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

        const showWarning = function () {
            warning.hidden = false;
            warning.focus();
            let seconds = 30;
            countdown.textContent = seconds;
            countdownTimer = window.setInterval(function () {
                seconds -= 1;
                countdown.textContent = seconds;

                if (seconds <= 0) {
                    window.clearInterval(countdownTimer);
                }
            }, 1000);

            redirectTimer = window.setTimeout(function () {
                window.location.replace('/AdminController/login');
            }, 30000);
        };

        continueButton.addEventListener('click', continueSession);
        warning.addEventListener('click', function (event) {
            if (event.target === warning) {
                continueSession();
            }
        });

        window.setTimeout(showWarning, showWarningAfter);
    }());
</script>

</body>
</html>

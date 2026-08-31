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

</body>
</html>

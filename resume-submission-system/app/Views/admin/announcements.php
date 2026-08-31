<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>公告管理 | 學生履歷繳交系統</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
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
            <a class="sys-navbar__link" href="/AdminController">履歷資料管理</a>
            <a class="sys-navbar__link sys-navbar__link--btn" href="/AdminController/logout">登出</a>
        </div>
    </div>
</header>

<main class="admin-shell">
    <div class="page-header">
        <h2 class="page-header__title">公告管理</h2>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert--success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert--error"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <!-- 發布新公告 -->
    <section class="admin-panel">
        <div class="admin-panel__header">
            <h3 class="admin-panel__title">發布新公告</h3>
        </div>
        <div class="admin-panel__body">
            <form action="/AdminController/createAnnouncement" method="post">
                <?= csrf_field() ?>

                <div class="form-field">
                    <label for="title">公告標題</label>
                    <input type="text" id="title" name="title" maxlength="150" required placeholder="請輸入公告標題">
                </div>

                <div class="form-field">
                    <label for="content">公告內容</label>
                    <textarea id="content" name="content" required placeholder="請輸入公告內容"></textarea>
                </div>

                <div class="form-field">
                    <label for="display_type">顯示方式</label>
                    <select id="display_type" name="display_type">
                        <option value="list">條列式（一般清單）</option>
                        <option value="marquee">跑馬燈式（滾動顯示）</option>
                    </select>
                    <p class="form-hint">條列式適合較長或多筆並列的公告；跑馬燈式適合簡短且需要引起注意的即時訊息。</p>
                </div>

                <button type="submit" class="btn btn--primary">發布公告</button>
            </form>
        </div>
    </section>

    <!-- 公告清單 -->
    <section class="admin-panel">
        <div class="admin-panel__header">
            <h3 class="admin-panel__title">已發布的公告（共 <?= count($announcements ?? []) ?> 則）</h3>
        </div>
        <div class="admin-panel__body">
            <?php if (empty($announcements)): ?>
                <p class="form-hint">尚未發布任何公告。</p>
            <?php else: ?>
                <div class="announcement-list">
                    <?php foreach ($announcements as $item): ?>
                        <div class="announcement-item <?= $item['is_active'] ? '' : 'announcement-item--inactive' ?>">
                            <div class="announcement-item__header">
                                <div class="announcement-item__title">
                                    <?= esc($item['title']) ?>
                                    <?php if ($item['display_type'] === 'marquee'): ?>
                                        <span class="tag-status tag-status--info">跑馬燈</span>
                                    <?php else: ?>
                                        <span class="tag-status tag-status--muted">條列式</span>
                                    <?php endif; ?>
                                    <?php if ($item['is_active']): ?>
                                        <span class="tag-status tag-status--success">顯示中</span>
                                    <?php else: ?>
                                        <span class="tag-status tag-status--muted">已隱藏</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="announcement-item__content"><?= esc($item['content']) ?></div>
                            <div class="announcement-item__meta">
                                發布時間：<?= esc($item['created_at'] ?? '-') ?>
                            </div>
                            <div class="announcement-item__actions">
                                <form action="/AdminController/toggleAnnouncement/<?= (int) $item['id'] ?>" method="post" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn--secondary btn--sm">
                                        <?= $item['is_active'] ? '隱藏公告' : '重新顯示' ?>
                                    </button>
                                </form>
                                <form action="/AdminController/deleteAnnouncement/<?= (int) $item['id'] ?>" method="post" style="display:inline;" onsubmit="return confirm('確定要刪除此則公告嗎？此操作無法復原。');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn--danger btn--sm">刪除</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

</body>
</html>

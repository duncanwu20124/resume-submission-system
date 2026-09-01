<?php
/**
 * @var array $announcements  Active announcements, each with title/content/display_type/created_at.
 */
$listAnnouncements    = array_values(array_filter($announcements ?? [], fn ($a) => ($a['display_type'] ?? 'list') !== 'marquee'));
$marqueeAnnouncements = array_values(array_filter($announcements ?? [], fn ($a) => ($a['display_type'] ?? 'list') === 'marquee'));
?>
<?php if (!empty($listAnnouncements) || !empty($marqueeAnnouncements)): ?>
<style>
    .announce-board { margin-bottom: 1.5rem; }

    .announce-marquee {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        background: linear-gradient(90deg, #4f46e5, #6366f1);
        color: #ffffff;
        border-radius: 0.75rem;
        padding: 0.65rem 1rem;
        margin-bottom: 1rem;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
    }

    .announce-marquee__label {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.8rem;
        font-weight: 700;
        background: rgba(255, 255, 255, 0.2);
        padding: 0.3rem 0.65rem;
        border-radius: 9999px;
        white-space: nowrap;
    }

    .announce-marquee__track {
        flex: 1;
        overflow: hidden;
        position: relative;
    }

    .announce-marquee__track ul {
        display: inline-flex;
        list-style: none;
        margin: 0;
        padding: 0;
        white-space: nowrap;
        animation: announce-scroll 22s linear infinite;
    }

    .announce-marquee__track li {
        padding: 0 2.5rem 0 0;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .announce-marquee__track li strong {
        font-weight: 700;
        margin-right: 0.4rem;
    }

    .announce-marquee__track li a {
        color: inherit;
        text-decoration: none;
    }

    .announce-marquee__track a,
    .announce-list__item-content a {
        color: inherit;
        font-weight: 700;
        text-decoration: underline;
        text-underline-offset: 2px;
    }

    @keyframes announce-scroll {
        0%   { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }

    @media (prefers-reduced-motion: reduce) {
        .announce-marquee__track ul { animation: none; }
        .announce-marquee__track { overflow-x: auto; }
    }

    .announce-list {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        padding: 1.25rem 1.5rem;
        margin-bottom: 1rem;
        text-align: left;
    }

    .announce-list__title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1.05rem;
        font-weight: 700;
        color: #312e81;
        margin-bottom: 0.9rem;
    }

    .announce-list ul {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }

    .announce-list li {
        border-left: 3px solid #4f46e5;
        padding: 0.15rem 0 0.15rem 0.85rem;
    }

    .announce-list__item-link {
        display: block;
        color: inherit;
        text-decoration: none;
        border-radius: 0.35rem;
        padding: 0.15rem 0.35rem 0.15rem 0;
    }

    .announce-list__item-link:hover,
    .announce-list__item-link:focus-visible {
        background: #f8fafc;
        outline: none;
    }

    .announce-list__item-title {
        font-weight: 700;
        color: #0f172a;
        font-size: 0.95rem;
        margin-bottom: 0.15rem;
    }

    .announce-list__item-content {
        color: #475569;
        font-size: 0.875rem;
        line-height: 1.6;
        white-space: pre-wrap;
    }

    .announce-list__item-date {
        color: #94a3b8;
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }
</style>

<div class="announce-board">
    <?php if (!empty($marqueeAnnouncements)): ?>
        <div class="announce-marquee" role="region" aria-label="跑馬燈公告">
            <span class="announce-marquee__label">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                公告
            </span>
            <div class="announce-marquee__track">
                    <ul>
                        <?php foreach (array_merge($marqueeAnnouncements, $marqueeAnnouncements) as $item): ?>
                        <li>
                            <a href="<?= site_url('announcement/' . (int) $item['id']) ?>">
                                <strong><?= esc($item['title']) ?></strong><?= esc($item['content']) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($listAnnouncements)): ?>
        <section class="announce-list" aria-label="公告事項">
            <div class="announce-list__title">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l9.586-9.586z"></path></svg>
                最新公告
            </div>
                <ul>
                    <?php foreach ($listAnnouncements as $item): ?>
                        <li>
                            <a class="announce-list__item-link" href="<?= site_url('announcement/' . (int) $item['id']) ?>">
                                <div class="announce-list__item-title"><?= esc($item['title']) ?></div>
                                <div class="announce-list__item-content"><?= esc($item['content']) ?></div>
                                <?php if (!empty($item['created_at'])): ?>
                                    <div class="announce-list__item-date"><?= esc($item['created_at']) ?></div>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>
</div>
<?php endif; ?>

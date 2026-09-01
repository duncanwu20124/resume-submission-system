<?php

namespace App\Config;

final class ImportantSchedule
{
    public const OFFICIAL_SOURCE = 'https://www.cac.edu.tw/apply116/schedule.php';

    /**
     * 本機練習系統目前使用的時程；正式日期應以承辦單位公告為準。
     */
    public const ITEMS = [
        [
            'date'        => '2026/08/15',
            'title'       => '履歷上傳開始',
            'description' => '開放學生上傳履歷 PDF。',
        ],
        [
            'date'        => '2026/08/31 23:59',
            'title'       => '履歷上傳截止',
            'description' => '履歷上傳期限截止。',
        ],
        [
            'date'        => '2026/09/15 23:59',
            'title'       => '志願序填寫截止',
            'description' => '逾時後無法儲存或送出志願序。',
        ],
    ];
}

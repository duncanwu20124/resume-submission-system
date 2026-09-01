<?php

use CodeIgniter\Test\CIUnitTestCase;

final class ImportantScheduleViewTest extends CIUnitTestCase
{
    public function testRendersLocalScheduleAndOfficialSourceNotice(): void
    {
        $html = view('important_schedule', [
            'scheduleItems' => [
                [
                    'date'        => '2026/08/15',
                    'title'       => '履歷上傳開始',
                    'description' => '開放學生上傳履歷 PDF。',
                ],
                [
                    'date'        => '2026/09/15 23:59',
                    'title'       => '志願序填寫截止',
                    'description' => '逾時後無法儲存或送出志願序。',
                ],
            ],
            'officialSource' => 'https://www.cac.edu.tw/apply116/schedule.php',
            'officialHasData' => false,
        ]);

        $this->assertStringContainsString('重要時程', $html);
        $this->assertStringContainsString('履歷上傳開始', $html);
        $this->assertStringContainsString('2026/09/15 23:59', $html);
        $this->assertStringContainsString('目前尚無資料', $html);
        $this->assertStringContainsString('https://www.cac.edu.tw/apply116/schedule.php', $html);
    }
}

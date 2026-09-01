<?php

use CodeIgniter\Test\CIUnitTestCase;

final class AnnouncementDetailViewTest extends CIUnitTestCase
{
    public function testRendersOneAnnouncementWithItsBodyAndDocumentLink(): void
    {
        $html = view('announcement_detail', [
            'announcement' => [
                'id'         => 7,
                'title'      => '招生公告',
                'content'    => "請參閱：\nhttps://example.com/document.pdf",
                'created_at' => '2026-09-01 10:00:00',
            ],
        ]);

        $this->assertStringContainsString('招生公告', $html);
        $this->assertStringContainsString('請參閱：', $html);
        $this->assertStringContainsString(
            '<a href="https://example.com/document.pdf" target="_blank" rel="noopener noreferrer">https://example.com/document.pdf</a>',
            $html
        );
        $this->assertStringContainsString('回到首頁', $html);
    }
}

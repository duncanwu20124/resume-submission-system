<?php

use App\Support\AnnouncementLink;
use PHPUnit\Framework\TestCase;

final class AnnouncementLinkTest extends TestCase
{
    public function testAcceptsHttpAndHttpsDocumentLinks(): void
    {
        $url = 'https://www.cac.edu.tw/star116/document/TelcLowest_Star116_asdf_20260831.pdf';

        $this->assertSame($url, AnnouncementLink::normalizeUrl($url));
    }

    public function testRejectsUnsafeOrMalformedLinks(): void
    {
        $this->assertNull(AnnouncementLink::normalizeUrl('javascript:alert(1)'));
        $this->assertNull(AnnouncementLink::normalizeUrl('not-a-url'));
    }

    public function testUsesUrlWhenAttachmentTitleIsBlank(): void
    {
        $url = 'https://example.com/document.pdf';

        $this->assertSame($url, AnnouncementLink::displayTitle('', $url));
    }

    public function testRendersMarkdownDocumentLinkAlongsideAnnouncementText(): void
    {
        $content = "請參閱文件：\n[招生檢定科目校系一覽表](https://example.com/document.pdf)";

        $rendered = AnnouncementLink::renderContent($content);

        $this->assertStringContainsString('請參閱文件：', $rendered);
        $this->assertStringContainsString(
            '<a href="https://example.com/document.pdf" target="_blank" rel="noopener noreferrer">招生檢定科目校系一覽表</a>',
            $rendered
        );
    }

    public function testEscapesUnsafeMarkdownLinksAsPlainText(): void
    {
        $rendered = AnnouncementLink::renderContent('[危險連結](javascript:alert(1))');

        $this->assertStringNotContainsString('<a ', $rendered);
        $this->assertStringNotContainsString('javascript:', $rendered);
    }
}

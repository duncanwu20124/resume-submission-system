<?php

namespace Tests\Unit;

use App\Support\PdfDuplicateDetector;
use PHPUnit\Framework\TestCase;

final class PdfDuplicateDetectorTest extends TestCase
{
    public function testGroupsStudentsWithIdenticalPdfContentEvenWhenFileNamesDiffer(): void
    {
        $groups = PdfDuplicateDetector::findDuplicateGroups([
            ['id' => 1, 'student_id' => 'S112001', 'name' => '王小明', 'file_name' => 'one.pdf', 'pdf_content' => '%PDF-same'],
            ['id' => 2, 'student_id' => 'S112002', 'name' => '李小華', 'file_name' => 'different-name.pdf', 'pdf_content' => '%PDF-same'],
            ['id' => 3, 'student_id' => 'S112003', 'name' => '陳小美', 'file_name' => 'three.pdf', 'pdf_content' => '%PDF-other'],
        ]);

        self::assertCount(1, $groups);
        self::assertSame(['S112001', 'S112002'], array_column($groups[0]['students'], 'student_id'));
        self::assertSame(hash('sha256', '%PDF-same'), $groups[0]['hash']);
        self::assertSame(strlen('%PDF-same'), $groups[0]['file_size']);
    }

    public function testIgnoresStudentsWithoutPdfContent(): void
    {
        self::assertSame([], PdfDuplicateDetector::findDuplicateGroups([
            ['id' => 1, 'student_id' => 'S112001', 'name' => '王小明', 'file_name' => '', 'pdf_content' => ''],
            ['id' => 2, 'student_id' => 'S112002', 'name' => '李小華', 'file_name' => 'resume.pdf', 'pdf_content' => '%PDF-only'],
        ]));
    }
}

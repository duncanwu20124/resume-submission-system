#!/usr/bin/env php
<?php

require_once __DIR__ . '/TestPdfGenerator.php';

use App\Tools\TestPdfGenerator;

$outputDirectory = $argv[1] ?? dirname(__DIR__) . '/writable/test-pdfs';
$studentCount = isset($argv[2]) ? (int) $argv[2] : 50;

try {
    $paths = TestPdfGenerator::generate($outputDirectory, $studentCount, [1, 2, 3]);
    fwrite(STDOUT, sprintf("已產生 %d 份測試 PDF：%s\n", count($paths), $outputDirectory));
    fwrite(STDOUT, "第 1、2、3 份 PDF 使用完全相同的內容。\n");
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
}

#!/usr/bin/env php
<?php

declare(strict_types=1);

use CodeIgniter\Boot;
use Config\Paths;

define('FCPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
chdir(FCPATH);
$_SERVER['CI_ENVIRONMENT'] ??= 'development';
defined('ENVIRONMENT') || define('ENVIRONMENT', $_SERVER['CI_ENVIRONMENT']);
defined('CI_DEBUG') || define('CI_DEBUG', true);
require dirname(__DIR__) . '/app/Config/Paths.php';

$paths = new Paths();
require $paths->systemDirectory . '/Boot.php';
Boot::bootConsole($paths);

/**
 * 快速生成支援繁體中文的輕量單頁 PDF（PDF 1.4 + Type0 / UniCNS-UTF16-H）
 */
function buildChineseMockPdf(string $title, string $studentId, string $studentName): string
{
    $lines = [
        $title,
        "姓名：{$studentName}",
        "學號：{$studentId}",
        "建立時間：" . date('Y-m-d H:i:s'),
        "本檔案為模擬系統之測試履歷檔案，內容由資料庫安全管理。"
    ];

    $stream = "BT\n/F1 14 Tf\n72 720 Td\n";
    foreach ($lines as $line) {
        $encoded = mb_convert_encoding($line, 'UTF-16BE', 'UTF-8');
        $hex = bin2hex($encoded);
        $stream .= "<{$hex}> Tj\n0 -28 Td\n";
    }
    $stream .= "ET\n";

    $objects = [
        '<< /Type /Catalog /Pages 2 0 R >>',
        '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
        '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>',
        '<< /Length ' . strlen($stream) . " >>\nstream\n" . $stream . 'endstream',
        '<< /Type /Font /Subtype /Type0 /BaseFont /MSungStd-Light-Acro /Encoding /UniCNS-UTF16-H /DescendantFonts [6 0 R] >>',
        '<< /Type /Font /Subtype /CIDFontType0 /BaseFont /MSungStd-Light /CIDSystemInfo << /Registry (Adobe) /Ordering (CNS1) /Supplement 4 >> >>',
    ];

    $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
    $offsets = [0];
    foreach ($objects as $index => $object) {
        $offsets[] = strlen($pdf);
        $pdf .= ($index + 1) . " 0 obj\n" . $object . "\nendobj\n";
    }

    $xrefOffset = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
    for ($index = 1; $index < count($offsets); $index++) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$index]);
    }
    $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
    $pdf .= "startxref\n" . $xrefOffset . "\n%%EOF\n";

    return $pdf;
}

try {
    $db = db_connect();

    // 取得所有測試學生（S112 開頭）
    $students = $db->table('students')
        ->select('id, student_id, name')
        ->like('student_id', 'S112', 'after')
        ->get()
        ->getResultArray();

    $total = count($students);
    echo "========================================================\n";
    echo "開始為 {$total} 位學生重新生成支援繁體中文的 PDF 履歷\n";
    echo "========================================================\n";

    $batchSize = 200;
    for ($i = 0; $i < $total; $i++) {
        if ($i % $batchSize === 0) {
            $db->transStart();
        }

        $student = $students[$i];
        $pdfContent = buildChineseMockPdf('學生履歷表 (Resume)', $student['student_id'], $student['name']);
        $base64 = base64_encode($pdfContent);

        $db->table('students')
            ->where('id', $student['id'])
            ->update([
                'file_content' => $base64,
                'file_name' => $student['student_id'] . '_resume.pdf'
            ]);

        if (($i + 1) % $batchSize === 0 || ($i + 1) === $total) {
            $db->transComplete();
            if (!$db->transStatus()) {
                throw new RuntimeException("寫入至第 " . ($i + 1) . " 筆時交易失敗！");
            }
            $percent = number_format((($i + 1) / $total) * 100, 1);
            echo "[ " . ($i + 1) . " / {$total} ] ({$percent}%) 覆蓋完成...\n";
        }
    }

    echo "\n資料庫 students.file_content 已全數覆蓋為無亂碼中文 PDF！\n";

    // 清理 writable/uploads 中的測試檔案，只保留空目錄或非測試檔
    $uploadsDir = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads';
    $removedFiles = 0;
    foreach (glob($uploadsDir . '/*_resume.pdf') ?: [] as $f) {
        if (is_file($f)) {
            unlink($f);
            $removedFiles++;
        }
    }
    echo "已自 writable/uploads 移除 {$removedFiles} 個實體檔案，實體檔案完全清空。\n";

} catch (Throwable $e) {
    if (isset($db) && $db->transStatus() === false) {
        $db->transRollback();
    }
    fwrite(STDERR, "執行出錯：" . $e->getMessage() . "\n");
    exit(1);
}

#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Config\Universities;
use App\Models\StudentModel;
use App\Models\StudentPreferenceModel;
use App\Models\AllocationRunModel;
use App\Services\AllocationService;
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

echo "========================================================\n";
echo "更新志願集中偏好（熱門科系）與設定 10 份相同重複 PDF\n";
echo "========================================================\n";

$allChoices = array_keys(Universities::allCapacities());

// 前 28 個熱門指標科系庫
$candidatePopular = [
    '國立臺灣大學 - 資訊工程學系',
    '國立臺灣大學 - 電機工程學系',
    '國立臺灣大學 - 醫學系',
    '國立臺灣大學 - 財務金融學系',
    '國立臺灣大學 - 外國語文學系',
    '國立臺灣大學 - 法律學系',
    '國立臺灣大學 - 經濟學系',
    '國立清華大學 - 資訊工程學系',
    '國立清華大學 - 電機工程學系',
    '國立清華大學 - 動力機械工程學系',
    '國立陽明交通大學 - 資訊工程學系',
    '國立陽明交通大學 - 電機工程學系',
    '國立成功大學 - 資訊工程學系',
    '國立成功大學 - 電機工程學系',
    '國立成功大學 - 機械工程學系',
    '國立政治大學 - 資訊管理學系',
    '國立政治大學 - 金融學系',
    '國立臺灣師範大學 - 資訊工程學系',
    '國立臺灣師範大學 - 電機工程學系',
    '國立中央大學 - 資訊工程學系',
    '國立中央大學 - 電機工程學系',
    '國立中興大學 - 資訊工程學系',
    '國立中正大學 - 資訊工程學系',
    '國立臺北大學 - 資訊工程學系',
    '東吳大學 - 法律學系',
    '國立臺灣大學 - 牙醫學系',
    '國立中興大學 - 電機工程學系',
    '國立中興大學 - 獸醫學系',
];

$popularChoices = array_values(array_filter($candidatePopular, fn($c) => in_array($c, $allChoices, true)));
if (count($popularChoices) < 25) {
    foreach ($allChoices as $c) {
        if (!in_array($c, $popularChoices, true) && (str_contains($c, '資訊工程') || str_contains($c, '電機工程') || str_contains($c, '醫學系'))) {
            $popularChoices[] = $c;
            if (count($popularChoices) >= 28) break;
        }
    }
}

echo "熱門科系清單總數：" . count($popularChoices) . " 個\n";

$db = db_connect();

// 1. 設定 10 位學生的 PDF 為完全相同
echo "正在設定 10 位學生的 PDF 為完全相同檔案（供重複檢查）...\n";
function buildIdenticalMockPdf(): string
{
    $title = "【全國高中科技專案競賽】專題成果總結報告";
    $body = "本文件為 2026 年度高中生智慧科技研究專案競賽之完整成果證明，包含專案架構、演算法設計與實驗成果。";
    $lines = [
        $title,
        $body,
        "報告版本：v1.0.4 正式版",
        "備註：此份成果報告由參賽隊員共同繳交。"
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

$identicalPdfBytes = buildIdenticalMockPdf();
$identicalBase64 = base64_encode($identicalPdfBytes);

// 選出 10 位有上傳履歷的測試學生（嚴格排除真實學生 ID 1, 3, 4）
$studentsToMakeDuplicate = $db->table('students')
    ->select('id, student_id, name')
    ->where('file_name IS NOT NULL')
    ->whereNotIn('id', [1, 3, 4])
    ->orderBy('id', 'ASC')
    ->limit(10)
    ->get()->getResultArray();

$duplicateNames = [];
foreach ($studentsToMakeDuplicate as $stu) {
    $db->table('students')->where('id', $stu['id'])->update([
        'file_name' => '競賽專題成果報告_相同副本.pdf',
        'file_content' => $identicalBase64,
        'uploaded_at' => date('Y-m-d H:i:s'),
    ]);
    $duplicateNames[] = "{$stu['name']}({$stu['student_id']})";
}
echo "已將以下 10 位學生的 PDF 設為完全一致：\n" . implode(', ', $duplicateNames) . "\n\n";

// 2. 更新志願集中度（讓 60% 學生優先填寫前 28 個熱門科系）
echo "正在調整所有學生的志願集中偏好（60% 優先填寫前 28 個熱門科系）...\n";
$allPreferences = $db->table('student_preferences')
    ->select('id, student_db_id')
    ->where('status', StudentPreferenceModel::STATUS_SUBMITTED)
    ->orderBy('id', 'ASC')
    ->get()->getResultArray();

$totalPrefCount = count($allPreferences);
$popularCountApplied = 0;
$db->transStart();

foreach ($allPreferences as $idx => $prefRow) {
    // 60% 的學生優先填寫熱門科系
    $preferPopular = ($idx % 100 < 60);

    $picked = [];
    if ($preferPopular) {
        $popularCountApplied++;
        // 隨機從熱門科系選 2~3 個放入前志願
        $numPop = random_int(2, 3);
        $keys = (array) array_rand($popularChoices, $numPop);
        shuffle($keys);
        foreach ($keys as $k) {
            $picked[] = $popularChoices[$k];
        }
    }

    // 隨機補齊至 6 個且不重複
    while (count($picked) < 6) {
        $rChoice = $allChoices[array_rand($allChoices)];
        if (!in_array($rChoice, $picked, true)) {
            $picked[] = $rChoice;
        }
    }

    $db->table('student_preferences')->where('id', $prefRow['id'])->update([
        'choice_1' => $picked[0],
        'choice_2' => $picked[1],
        'choice_3' => $picked[2],
        'choice_4' => $picked[3],
        'choice_5' => $picked[4],
        'choice_6' => $picked[5],
        'updated_at' => date('Y-m-d H:i:s'),
    ]);
}

$db->transComplete();
echo "志願更新完成：共 {$totalPrefCount} 筆志願，其中 {$popularCountApplied} 筆（" . round(($popularCountApplied / max(1, $totalPrefCount)) * 100, 1) . "%）優先集中填寫熱門科系。\n\n";

// 3. 重新計算分發結果（產生最新預覽批次）
echo "正在重新執行分發演算法，建立最新分發預覽...\n";
$adminId = 1;
$service = new AllocationService();
$newRunId = $service->createPreview($adminId);

echo "分發預覽批次已重新生成：Run #{$newRunId}\n";
echo "========================================================\n";
echo "所有任務執行完成！\n";

#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Config\Universities;
use App\Models\StudentModel;
use App\Models\StudentPreferenceModel;
use App\Models\StudentScoreModel;
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

// 讀取命令列參數，預設生成 4000 人
$studentCount = isset($argv[1]) ? (int) $argv[1] : 4000;
if ($studentCount <= 0) {
    fwrite(STDERR, "請輸入大於 0 的生成學生數量。\n");
    exit(1);
}

$password = 'Test1234!';
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$uploadDirectory = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads';

if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
    fwrite(STDERR, "無法建立履歷檔案資料夾：{$uploadDirectory}\n");
    exit(1);
}

// 姓氏庫（常見台灣 54 姓氏）
$surnames = [
    '陳', '林', '黃', '張', '李', '王', '吳', '劉', '蔡', '楊',
    '許', '鄭', '謝', '洪', '郭', '邱', '曾', '廖', '賴', '徐',
    '周', '葉', '蘇', '莊', '江', '呂', '何', '羅', '高', '蕭',
    '潘', '朱', '簡', '彭', '游', '詹', '胡', '施', '沈', '余',
    '盧', '梁', '趙', '顏', '柯', '翁', '魏', '孫', '樊', '方',
    '丁', '范', '汪', '宋'
];

// 名字庫（100+ 常見名字）
$givenNames = [
    '家豪', '志豪', '俊傑', '冠宇', '柏翰', '品睿', '承恩', '宥廷', '宇軒', '子軒',
    '承翰', '冠廷', '柏宇', '子豪', '宗翰', '建宏', '家瑋', '冠霖', '博元', '彥廷',
    '昱廷', '廷瑋', '宇傑', '哲瑋', '柏叡', '凱文', '秉勳', '皓宇', '翔宇', '銘軒',
    '雅婷', '詠晴', '子晴', '品妍', '詩涵', '郁婷', '欣怡', '雅筑', '冠伶', '雨萱',
    '恩綺', '羽彤', '予萱', '心妤', '羽涵', '品妤', '晨曦', '芷萱', '宥蓁', '婷萱',
    '佩珊', '佳穎', '怡萱', '雅雯', '鈺婷', '佩蓉', '宜蓁', '巧恩', '馨儀', '靜儀',
    '睿恩', '語晨', '樂天', '凱翔', '智傑', '書瑋', '靖遠', '天佑', '致遠', '宏偉',
    '浩然', '思齊', '逸凡', '明軒', '偉倫', '佳琪', '佩芬', '美玲', '雅芬', '慧萍',
    '淑芬', '淑君', '麗華', '靜文', '玉婷', '惠婷', '心怡', '依婷', '筱萱', '夢婷',
    '佑安', '弘毅', '思賢', '安平', '知行', '子謙', '哲瀚', '澤宇', '博軒', '廷恩',
    '冠霖', '維倫', '修齊', '耀廷', '辰宇'
];

// 取得所有 2,234 個合法「學校 - 科系」
$allChoices = array_keys(Universities::allCapacities());
$choiceCount = count($allChoices);
if ($choiceCount < StudentPreferenceModel::CHOICE_COUNT) {
    fwrite(STDERR, "可用校系列表不足 6 個，無法建立志願序。\n");
    exit(1);
}

// 前 28 個熱門指標科系庫（供 50% 以上學生優先選填）
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

/**
 * 快速生成支援繁體中文的輕量單頁 PDF（PDF 1.4 + Type0 / UniCNS-UTF16-H）
 */
function buildMockPdf(string $title, string $studentId, string $studentName): string
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

    // 查詢現有學生以 S112 開頭的最大流水號
    $rows = $db->table('students')->select('student_id')->like('student_id', 'S112', 'after')->get()->getResultArray();
    $maxSeq = 0;
    foreach ($rows as $r) {
        if (preg_match('/^S112(\d+)$/', $r['student_id'], $m)) {
            $num = (int) $m[1];
            if ($num > $maxSeq) {
                $maxSeq = $num;
            }
        }
    }

    $startSeq = $maxSeq + 1;
    $endSeq = $maxSeq + $studentCount;

    echo "========================================================\n";
    echo "開始生成 {$studentCount} 位測試學生資料\n";
    echo "學號接續範圍：S112" . sprintf('%04d', $startSeq) . " ～ S112" . sprintf('%04d', $endSeq) . "\n";
    echo "========================================================\n";

    $batchSize = 200;
    $createdCount = 0;
    $now = date('Y-m-d H:i:s');

    $studentModel = new StudentModel();
    $preferenceModel = new StudentPreferenceModel();
    $scoreModel = new StudentScoreModel();

    for ($i = 1; $i <= $studentCount; $i++) {
        // 每 batchSize 開啟一次交易
        if (($i - 1) % $batchSize === 0) {
            $db->transStart();
        }

        $currentSeq = $maxSeq + $i;
        $studentId = 'S112' . sprintf('%04d', $currentSeq);
        $name = $surnames[array_rand($surnames)] . $givenNames[array_rand($givenNames)];
        $email = strtolower($studentId) . '@example.com';
        $fileName = $studentId . '_resume.pdf';

        // 60%（50% 以上）學生優先填寫前 20~30 個熱門科系
        $preferPopular = ($i % 100 < 60);
        $picked = [];
        if ($preferPopular && count($popularChoices) >= 3) {
            $numPop = random_int(2, 3);
            $popKeys = (array) array_rand($popularChoices, $numPop);
            shuffle($popKeys);
            foreach ($popKeys as $pk) {
                $picked[] = $popularChoices[$pk];
            }
        }
        while (count($picked) < 6) {
            $rChoice = $allChoices[array_rand($allChoices)];
            if (!in_array($rChoice, $picked, true)) {
                $picked[] = $rChoice;
            }
        }
        $choices = $picked;

        // 產生 PDF 內容（前 10 位使用相同檔案供 PDF 重複比對）
        if ($i <= 10) {
            $pdfContent = buildMockPdf('【全國高中科技專案競賽】專題成果總結報告', 'SHARED_TEMPLATE_2026', '競賽專題成果報告_相同副本');
        } else {
            $pdfContent = buildMockPdf('Mock Resume', $studentId, $name);
        }

        // 寫入學生資料
        $studentDbId = (int) $studentModel->insert([
            'student_id'   => $studentId,
            'name'         => $name,
            'email'        => $email,
            'password'     => $passwordHash,
            'file_name'    => $fileName,
            'file_content' => base64_encode($pdfContent),
            'uploaded_at'  => $now,
            'created_at'   => $now,
            'updated_at'   => $now,
        ], true);

        // 寫入 6 個志願序（已送出）
        $preferenceModel->insert([
            'student_db_id' => $studentDbId,
            'choice_1'      => $choices[0],
            'choice_2'      => $choices[1],
            'choice_3'      => $choices[2],
            'choice_4'      => $choices[3],
            'choice_5'      => $choices[4],
            'choice_6'      => $choices[5],
            'status'        => StudentPreferenceModel::STATUS_SUBMITTED,
            'submitted_at'  => $now,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        // 寫入總分（0 到 100 分整數隨機，不含小數點）
        $score = mt_rand(0, 100);
        $scoreModel->insert([
            'student_db_id' => $studentDbId,
            'total_score'   => $score,
            'status'        => StudentScoreModel::STATUS_CONFIRMED,
            'comment'       => '自動批量生成測試評分',
            'confirmed_at'  => $now,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        $createdCount++;

        // 每 batchSize 或最後一筆完成時 commit
        if ($i % $batchSize === 0 || $i === $studentCount) {
            $db->transComplete();
            if (!$db->transStatus()) {
                throw new RuntimeException("寫入批次資料時失敗，於第 {$i} 筆中斷。");
            }
            $percent = number_format(($i / $studentCount) * 100, 1);
            echo "[ {$i} / {$studentCount} ] ({$percent}%) 寫入完成...\n";
        }
    }

    echo "\n🎉 4,000 位學生資料生成完畢！\n";

    // 重新執行分發並發布
    echo "正在重新計算全體學生（含本次 4,000 位）之校系分發...\n";
    $service = new AllocationService();
    $runId = $service->createPreview(1);
    $service->publish($runId, '115學年度4,000人規模完整校系正式分發');
    echo "最新分發批次 #{$runId} 已成功建立並正式發布！\n";

} catch (Throwable $e) {
    if (isset($db) && $db->transStatus() === false) {
        $db->transRollback();
    }
    fwrite(STDERR, "\n執行出錯：" . $e->getMessage() . "\n");
    exit(1);
}

#!/usr/bin/env php
<?php

declare(strict_types=1);

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

$db = db_connect();

// 1. 更新所有學生評分為 0~100 整數
$students = $db->table('students')->select('id, student_id')->get()->getResultArray();
echo "找到 " . count($students) . " 位學生，更新評分為 0~100 隨機整數（不帶小數點）...\n";

$db->transStart();
foreach ($students as $st) {
    $intScore = mt_rand(0, 100);
    $db->table('student_scores')
        ->where('student_db_id', $st['id'])
        ->update([
            'total_score' => $intScore,
            'status' => 'confirmed'
        ]);
}
$db->transComplete();

if (!$db->transStatus()) {
    echo "更新失敗！\n";
    exit(1);
}
echo "評分已全數成功更新為 0~100 隨機整數！\n";

// 2. 重新計算成績分發並發布
echo "正在重新執行分發...\n";
$service = new AllocationService();
$runId = $service->createPreview(1);
$service->publish($runId, '115學年度4,000人規模（0~100無小數點整數評分）正式分發');
echo "最新分發批次 #{$runId} 已發布！\n";

// 3. 清理 writable/uploads 中的測試實體檔案
$uploadsDir = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads';
$cleaned = 0;
foreach (glob($uploadsDir . '/*_resume.pdf') ?: [] as $file) {
    if (is_file($file)) {
        unlink($file);
        $cleaned++;
    }
}
echo "已從 writable/uploads 清理 {$cleaned} 個實體 PDF 檔案（PDF 完全由資料庫 file_content 管理）。\n";

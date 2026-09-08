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

$service = new AllocationService();
$runId = $service->createPreview(1);
echo "分發預覽批次建立成功，Run ID: {$runId}\n";

$published = $service->publish($runId, '115學年度大專校系分發結果（含完整學系）');
echo "分發發布狀態: " . ($published ? '成功' : '失敗') . "\n";

$db = db_connect();
$results = $db->table('allocation_results')
    ->where('allocation_run_id', $runId)
    ->limit(10)
    ->get()
    ->getResultArray();

echo "前 10 名錄取校系抽樣：\n";
foreach ($results as $row) {
    echo "#{$row['overall_rank']} (學生 ID: {$row['student_db_id']}): {$row['university_name_snapshot']} (第 {$row['preference_rank']} 志願, 狀態: {$row['result_status']})\n";
}

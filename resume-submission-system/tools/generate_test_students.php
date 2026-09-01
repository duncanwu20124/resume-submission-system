#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Config\Universities;
use App\Models\StudentModel;
use App\Models\StudentPreferenceModel;
use App\Models\StudentScoreModel;
use App\Tools\TestPdfGenerator;
use CodeIgniter\Boot;
use Config\Paths;

require_once __DIR__ . '/TestPdfGenerator.php';

define('FCPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
chdir(FCPATH);
$_SERVER['CI_ENVIRONMENT'] ??= 'development';
defined('ENVIRONMENT') || define('ENVIRONMENT', $_SERVER['CI_ENVIRONMENT']);
defined('CI_DEBUG') || define('CI_DEBUG', true);
require dirname(__DIR__) . '/app/Config/Paths.php';

$paths = new Paths();
require $paths->systemDirectory . '/Boot.php';
Boot::bootConsole($paths);

$prefix = 'TEST20260901-';
$studentCount = 50;
$password = 'Test1234!';
$now = date('Y-m-d H:i:s');
$stagingDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'resume-test-pdfs-' . bin2hex(random_bytes(8));
$uploadDirectory = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads';
$createdFiles = [];
$studentIds = [];

try {
    $db = db_connect();
    $existing = $db->table('students')->like('student_id', $prefix, 'after')->countAllResults();
    if ($existing > 0) {
        throw new RuntimeException("已存在 {$existing} 筆 {$prefix} 測試學生，為避免重複建立，本次停止。");
    }

    if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
        throw new RuntimeException('無法建立履歷檔案資料夾：' . $uploadDirectory);
    }

    $pdfPaths = TestPdfGenerator::generate($stagingDirectory, $studentCount, [1, 2, 3]);
    $universities = array_slice(Universities::names(), 0, StudentPreferenceModel::CHOICE_COUNT);
    if (count($universities) !== StudentPreferenceModel::CHOICE_COUNT) {
        throw new RuntimeException('可用校系列表不足 6 個，無法建立志願序測試資料。');
    }

    $studentModel = new StudentModel();
    $preferenceModel = new StudentPreferenceModel();
    $scoreModel = new StudentScoreModel();

    $db->transStart();
    for ($index = 1; $index <= $studentCount; $index++) {
        $studentId = $prefix . str_pad((string) $index, 3, '0', STR_PAD_LEFT);
        $fileName = $studentId . '_resume.pdf';
        $content = file_get_contents($pdfPaths[$index - 1]);
        if ($content === false) {
            throw new RuntimeException('無法讀取測試 PDF：' . $pdfPaths[$index - 1]);
        }

        $studentDbId = $studentModel->insert([
            'student_id' => $studentId,
            'name' => '測試學生' . str_pad((string) $index, 3, '0', STR_PAD_LEFT),
            'email' => 'test20260901_' . str_pad((string) $index, 3, '0', STR_PAD_LEFT) . '@example.com',
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'file_name' => $fileName,
            'file_content' => base64_encode($content),
            'uploaded_at' => $now,
        ], true);
        if (!$studentDbId) {
            throw new RuntimeException('建立學生資料失敗：' . $studentId);
        }

        $preferenceModel->insert([
            'student_db_id' => $studentDbId,
            'choice_1' => $universities[0],
            'choice_2' => $universities[1],
            'choice_3' => $universities[2],
            'choice_4' => $universities[3],
            'choice_5' => $universities[4],
            'choice_6' => $universities[5],
            'status' => StudentPreferenceModel::STATUS_SUBMITTED,
            'submitted_at' => $now,
        ]);

        $scoreModel->insert([
            'student_db_id' => $studentDbId,
            'total_score' => 50 + (($studentCount - $index) % 51),
            'status' => StudentScoreModel::STATUS_CONFIRMED,
            'comment' => '本機測試資料',
            'confirmed_at' => $now,
        ]);

        $destination = $uploadDirectory . DIRECTORY_SEPARATOR . $fileName;
        if (file_exists($destination) || !rename($pdfPaths[$index - 1], $destination)) {
            throw new RuntimeException('無法放置履歷檔案：' . $destination);
        }
        $createdFiles[] = $destination;
        $studentIds[] = $studentId;
    }
    $db->transComplete();

    if (!$db->transStatus()) {
        throw new RuntimeException('測試學生資料寫入失敗，資料庫已回復。');
    }

    $sharedHash = hash_file('sha256', $createdFiles[0]);
    if ($sharedHash === false || $sharedHash !== hash_file('sha256', $createdFiles[1]) || $sharedHash !== hash_file('sha256', $createdFiles[2])) {
        throw new RuntimeException('第 1、2、3 位學生的 PDF 雜湊值不一致。');
    }

    fwrite(STDOUT, "已建立 {$studentCount} 位測試學生。\n");
    fwrite(STDOUT, "學號範圍：{$studentIds[0]} ～ {$studentIds[count($studentIds) - 1]}\n");
    fwrite(STDOUT, "測試密碼：{$password}\n");
    fwrite(STDOUT, "履歷檔案：{$uploadDirectory}\n");
    fwrite(STDOUT, "第 1、2、3 位 PDF SHA-256：{$sharedHash}\n");
} catch (Throwable $exception) {
    if (isset($db) && $db->transStatus() === false) {
        $db->transRollback();
    }
    foreach ($createdFiles as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    if (is_dir($stagingDirectory)) {
        foreach (glob($stagingDirectory . DIRECTORY_SEPARATOR . '*') ?: [] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        rmdir($stagingDirectory);
    }
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
}

if (is_dir($stagingDirectory)) {
    rmdir($stagingDirectory);
}

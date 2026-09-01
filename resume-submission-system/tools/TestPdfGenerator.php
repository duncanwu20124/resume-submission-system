<?php

namespace App\Tools;

use InvalidArgumentException;
use RuntimeException;

final class TestPdfGenerator
{
    /**
     * Create deterministic mock PDF resumes without using external data.
     *
     * @param array<int> $duplicateIndices Exactly three 1-based student indices
     *                                      that should receive identical bytes.
     * @return array<int, string> Generated absolute paths.
     */
    public static function generate(
        string $outputDirectory,
        int $studentCount = 50,
        array $duplicateIndices = [1, 2, 3]
    ): array {
        if ($studentCount < 3) {
            throw new InvalidArgumentException('至少需要 3 位學生才能建立重複 PDF。');
        }

        $duplicateIndices = array_values(array_unique(array_map('intval', $duplicateIndices)));
        sort($duplicateIndices);
        if (count($duplicateIndices) !== 3 || $duplicateIndices[0] < 1 || $duplicateIndices[2] > $studentCount) {
            throw new InvalidArgumentException('duplicateIndices 必須包含 3 個有效的學生序號。');
        }

        if (!is_dir($outputDirectory) && !mkdir($outputDirectory, 0755, true) && !is_dir($outputDirectory)) {
            throw new RuntimeException('無法建立 PDF 輸出資料夾：' . $outputDirectory);
        }

        $sharedPdf = self::buildPdf([
            'Mock Resume',
            'Shared test document',
            'This PDF is intentionally identical for three test students.',
        ]);
        $paths = [];

        for ($index = 1; $index <= $studentCount; $index++) {
            $path = rtrim($outputDirectory, DIRECTORY_SEPARATOR)
                . DIRECTORY_SEPARATOR
                . sprintf('test-student-%03d.pdf', $index);

            if (is_file($path)) {
                throw new RuntimeException('檔案已存在，為避免覆蓋而停止：' . $path);
            }

            $pdf = in_array($index, $duplicateIndices, true)
                ? $sharedPdf
                : self::buildPdf([
                    'Mock Resume',
                    sprintf('Test student %03d', $index),
                    'Synthetic document for local testing only.',
                ]);

            if (file_put_contents($path, $pdf, LOCK_EX) === false) {
                throw new RuntimeException('無法寫入 PDF：' . $path);
            }

            $paths[] = $path;
        }

        return $paths;
    }

    /**
     * Build a small one-page PDF using only the PDF 1.4 syntax.
     * The generated text is ASCII so it renders consistently without embedding fonts.
     *
     * @param array<int, string> $lines
     */
    private static function buildPdf(array $lines): string
    {
        $stream = "BT\n/F1 16 Tf\n72 720 Td\n";
        foreach ($lines as $line) {
            $stream .= '(' . self::escapePdfText($line) . ") Tj\n0 -28 Td\n";
        }
        $stream .= "ET\n";

        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] '
                . '/Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>',
            '<< /Length ' . strlen($stream) . " >>\nstream\n" . $stream . 'endstream',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [0];
        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1) . " 0 obj\n" . $object . "\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($index = 1; $index < count($offsets); $index++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$index]);
        }
        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n" . $xrefOffset . "\n%%EOF\n";

        return $pdf;
    }

    private static function escapePdfText(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }
}

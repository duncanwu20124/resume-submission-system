<?php

require_once dirname(__DIR__, 2) . '/tools/TestPdfGenerator.php';

use App\Tools\TestPdfGenerator;
use PHPUnit\Framework\TestCase;

final class TestPdfGeneratorTest extends TestCase
{
    public function testCreatesValidPdfsAndThreeByteIdenticalDocuments(): void
    {
        $directory = sys_get_temp_dir() . '/resume-test-pdfs-' . bin2hex(random_bytes(4));
        mkdir($directory, 0755, true);

        try {
            $paths = TestPdfGenerator::generate($directory, 5, [1, 2, 3]);

            $this->assertCount(5, $paths);
            foreach ($paths as $path) {
                $this->assertFileExists($path);
                $contents = file_get_contents($path);
                $this->assertStringStartsWith('%PDF-', $contents);
                $this->assertStringContainsString('xref', $contents);
                $this->assertStringContainsString('%%EOF', $contents);
                $this->assertStringNotContainsString('\\n', $contents);
            }

            $hashes = array_map(
                static fn (string $path): string => hash_file('sha256', $path),
                $paths
            );
            $this->assertSame($hashes[0], $hashes[1]);
            $this->assertSame($hashes[1], $hashes[2]);
            $this->assertNotSame($hashes[0], $hashes[3]);
        } finally {
            foreach (glob($directory . '/*') ?: [] as $path) {
                unlink($path);
            }
            rmdir($directory);
        }
    }
}

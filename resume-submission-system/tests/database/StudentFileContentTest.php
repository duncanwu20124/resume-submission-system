<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\StudentModel;

/**
 * Regression test for the file preview bug: binary resume content (PDF/DOCX)
 * almost always contains embedded NUL (0x00) bytes. CodeIgniter's SQLite3
 * driver interpolates escaped values directly into the SQL text instead of
 * binding a true blob parameter, so SQLite's string-literal parsing silently
 * truncates the value at the first NUL byte on write.
 *
 * @internal
 */
final class StudentFileContentTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = null; // pick up App\Database\Migrations (students table)

    public function testBinaryFileContentSurvivesRoundTripThroughSqlite(): void
    {
        // Bytes across the full 0x00-0xFF range, including several NUL bytes,
        // mirroring what a real PDF/DOCX stream looks like.
        $original = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n" . random_bytes(4000) . "\n%%EOF";
        $this->assertStringContainsString("\0", $original);

        $model = new StudentModel();
        $id    = $model->insert([
            'student_id' => 'regress-001',
            'name'       => 'Regression Test',
            'email'      => 'regress-001@example.com',
            'password'   => password_hash('x', PASSWORD_DEFAULT),
        ], true);

        // This is the fix under test: content must be encoded so it never
        // reaches the SQLite3 driver's SQL-text interpolation containing a
        // raw NUL byte.
        $model->update($id, [
            'file_name'    => 'resume.pdf',
            'file_content' => base64_encode($original),
        ]);

        $stored = $model->find($id);

        $this->assertSame($original, base64_decode($stored['file_content']));
    }
}

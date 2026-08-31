<?php

use App\Models\FormalApplicationModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class FormalApplicationPersistenceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = false;
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = db_connect('tests');
        $this->db->query('CREATE TABLE db_formal_applications (id INTEGER PRIMARY KEY AUTOINCREMENT, student_id VARCHAR(20) NOT NULL UNIQUE, name VARCHAR(100) NOT NULL)');
    }

    protected function tearDown(): void
    {
        $this->db->query('DROP TABLE db_formal_applications');
        parent::tearDown();
    }

    public function testInitialApplicationsArePersistedAndNotDuplicated(): void
    {
        $model = new FormalApplicationModel(db_connect('tests'));

        $model->ensureInitialData();

        $this->assertSame(230, $model->countAllResults());
        $this->assertSame('S112001', $model->where('student_id', 'S112001')->first()['student_id']);

        $model->ensureInitialData();

        $this->assertSame(230, $model->countAllResults());
    }
}

<?php

use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class AdminNameSortTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = null;

    private array $studentIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        $model = new UserModel();
        foreach ([
            ['student_id' => 'stroke-three', 'name' => '三三'],
            ['student_id' => 'stroke-two', 'name' => '八八'],
        ] as $student) {
            $this->studentIds[] = $model->insert([
                'student_id' => $student['student_id'],
                'name'       => $student['name'],
                'email'      => $student['student_id'] . '@example.com',
                'password'   => password_hash('x', PASSWORD_DEFAULT),
            ], true);
        }
    }

    protected function tearDown(): void
    {
        (new UserModel())->whereIn('id', $this->studentIds)->delete();

        parent::tearDown();
    }

    public function testNameAscendingSortUsesTraditionalChineseStrokeOrder(): void
    {
        $page = $this->withSession(['admin_logged_in' => true])
            ->get('/AdminController?sort=name&direction=ASC&per_page=100');

        $page->assertOK();
        $page->assertSee('姓名筆畫少到多');
        $page->assertSee('姓名筆畫多到少');
        $body = $page->response()->getBody();

        $this->assertLessThan(strpos($body, '三三'), strpos($body, '八八'));
    }
}

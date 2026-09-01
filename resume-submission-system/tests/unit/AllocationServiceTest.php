<?php

use App\Services\AllocationService;
use CodeIgniter\Test\CIUnitTestCase;

final class AllocationServiceTest extends CIUnitTestCase
{
    public function testHigherScoreChoosesFirstEvenWhenSchoolIsLowerPreference(): void
    {
        $candidates = [
            ['student_db_id' => 1, 'total_score' => 90, 'choices' => ['B', 'A']],
            ['student_db_id' => 2, 'total_score' => 80, 'choices' => ['A', 'B']],
        ];
        $results = (new AllocationService())->allocate($candidates, ['A' => 1, 'B' => 0], 'fixed-seed');

        $this->assertSame(1, $results[0]['student_db_id']);
        $this->assertSame('A', $results[0]['university_name_snapshot']);
        $this->assertSame(2, $results[0]['preference_rank']);
        $this->assertSame('unassigned', $results[1]['result_status']);
    }

    public function testEqualScoreLotteryIsDeterministicForSameSeed(): void
    {
        $candidates = [
            ['student_db_id' => 10, 'total_score' => 90, 'choices' => ['A']],
            ['student_db_id' => 20, 'total_score' => 90, 'choices' => ['A']],
        ];
        $service = new AllocationService();

        $this->assertSame(
            $service->allocate($candidates, ['A' => 1], 'auditable-seed'),
            $service->allocate(array_reverse($candidates), ['A' => 1], 'auditable-seed')
        );
    }

    public function testCapacityIsNeverExceeded(): void
    {
        $candidates = [];
        for ($id = 1; $id <= 5; $id++) {
            $candidates[] = ['student_db_id' => $id, 'total_score' => 100 - $id, 'choices' => ['A']];
        }
        $results = (new AllocationService())->allocate($candidates, ['A' => 1], 'seed');

        $this->assertCount(1, array_filter($results, fn ($row) => $row['result_status'] === 'admitted'));
        $this->assertCount(4, array_filter($results, fn ($row) => $row['result_status'] === 'unassigned'));
    }
}

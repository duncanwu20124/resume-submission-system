<?php

namespace Tests\Unit;

use App\Support\PreferenceAnalytics;
use PHPUnit\Framework\TestCase;

final class PreferenceAnalyticsTest extends TestCase
{
    public function testFiltersRowsWhenSchoolAppearsInAnyPreferenceRank(): void
    {
        $rows = [
            $this->row('A001', '王小明', ['國立臺灣大學', '國立清華大學']),
            $this->row('A002', '李小華', ['國立政治大學', '國立臺灣大學']),
        ];

        $filtered = PreferenceAnalytics::filterAndSort($rows, '', '國立臺灣大學', 'submitted_at_desc');

        self::assertSame(['A001', 'A002'], array_column($filtered, 'student_number'));
    }

    public function testSortsRowsByRequestedField(): void
    {
        $rows = [
            $this->row('A002', '李小華', ['國立政治大學'], '2026-09-01 10:00:00'),
            $this->row('A001', '王小明', ['國立臺灣大學'], '2026-09-01 11:00:00'),
        ];

        $sorted = PreferenceAnalytics::filterAndSort($rows, '', '', 'student_number_asc');

        self::assertSame(['A001', 'A002'], array_column($sorted, 'student_number'));
    }

    public function testCountsEachSchoolByRankAndTotal(): void
    {
        $rows = [
            $this->row('A001', '王小明', ['國立臺灣大學', '國立清華大學']),
            $this->row('A002', '李小華', ['國立政治大學', '國立臺灣大學']),
        ];

        $counts = PreferenceAnalytics::schoolCounts($rows);

        self::assertSame([
            'school' => '國立臺灣大學',
            'rank_1' => 1,
            'rank_2' => 1,
            'rank_3' => 0,
            'rank_4' => 0,
            'rank_5' => 0,
            'rank_6' => 0,
            'total' => 2,
        ], $counts[0]);
    }

    private function row(string $number, string $name, array $choices, string $submittedAt = '2026-09-01 12:00:00'): array
    {
        $choices = array_pad($choices, 6, '');

        return [
            'student_number' => $number,
            'student_name'   => $name,
            'submitted_at'   => $submittedAt,
            'choice_1'       => $choices[0],
            'choice_2'       => $choices[1],
            'choice_3'       => $choices[2],
            'choice_4'       => $choices[3],
            'choice_5'       => $choices[4],
            'choice_6'       => $choices[5],
        ];
    }
}

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

    public function testSortsRowsBySubmittedTime(): void
    {
        $rows = [
            $this->row('A002', '李小華', ['國立政治大學'], '2026-09-01 10:00:00'),
            $this->row('A001', '王小明', ['國立臺灣大學'], '2026-09-01 11:00:00'),
        ];

        $sorted = PreferenceAnalytics::filterAndSort($rows, '', '', 'submitted_at_asc');

        self::assertSame(['A002', 'A001'], array_column($sorted, 'student_number'));
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

    public function testSortsSchoolCountsByTotal(): void
    {
        $counts = [
            ['school' => '甲大學', 'rank_1' => 0, 'rank_2' => 0, 'rank_3' => 0, 'rank_4' => 0, 'rank_5' => 0, 'rank_6' => 0, 'total' => 1],
            ['school' => '乙大學', 'rank_1' => 0, 'rank_2' => 0, 'rank_3' => 0, 'rank_4' => 0, 'rank_5' => 0, 'rank_6' => 0, 'total' => 3],
        ];

        self::assertSame(['乙大學', '甲大學'], array_column(
            PreferenceAnalytics::sortSchoolCounts($counts, 'school_count_desc'),
            'school'
        ));
        self::assertSame(['甲大學', '乙大學'], array_column(
            PreferenceAnalytics::sortSchoolCounts($counts, 'school_count_asc'),
            'school'
        ));
    }

    public function testFilterSchoolStatsReturnsOnlySelectedSchool(): void
    {
        $counts = [
            ['school' => '國立臺灣大學', 'rank_1' => 1, 'rank_2' => 1, 'rank_3' => 0, 'rank_4' => 0, 'rank_5' => 0, 'rank_6' => 0, 'total' => 2],
            ['school' => '國立政治大學', 'rank_1' => 1, 'rank_2' => 0, 'rank_3' => 0, 'rank_4' => 0, 'rank_5' => 0, 'rank_6' => 0, 'total' => 1],
        ];

        $filtered = PreferenceAnalytics::filterSchoolStats($counts, '國立臺灣大學');

        self::assertCount(1, $filtered);
        self::assertSame('國立臺灣大學', $filtered[0]['school']);
        self::assertSame(2, $filtered[0]['total']);
    }

    public function testFilterSchoolStatsReturnsZeroedStatsWhenSchoolNotFound(): void
    {
        $counts = [
            ['school' => '國立政治大學', 'rank_1' => 1, 'rank_2' => 0, 'rank_3' => 0, 'rank_4' => 0, 'rank_5' => 0, 'rank_6' => 0, 'total' => 1],
        ];

        $filtered = PreferenceAnalytics::filterSchoolStats($counts, '國立臺灣大學');

        self::assertCount(1, $filtered);
        self::assertSame('國立臺灣大學', $filtered[0]['school']);
        self::assertSame(0, $filtered[0]['total']);
    }

    public function testHandlesSchoolDepartmentFormatCorrectly(): void
    {
        $rows = [
            $this->row('A001', '王小明', ['國立臺灣大學 - 資訊工程學系', '國立清華大學 - 動力機械工程學系']),
            $this->row('A002', '李小華', ['國立政治大學 - 傳播學院', '國立臺灣大學 - 電機工程學系']),
        ];

        // 依學校名稱過濾
        $filtered = PreferenceAnalytics::filterAndSort($rows, '', '國立臺灣大學', 'submitted_at_desc');
        self::assertSame(['A001', 'A002'], array_column($filtered, 'student_number'));

        // 統計各校人數（台大應有兩位學生選填）
        $counts = PreferenceAnalytics::schoolCounts($rows);
        $ntuStat = array_values(array_filter($counts, fn ($s) => $s['school'] === '國立臺灣大學'))[0];
        self::assertSame(2, $ntuStat['total']);
        self::assertSame(1, $ntuStat['rank_1']);
        self::assertSame(1, $ntuStat['rank_2']);
    }

    public function testFiltersByDepartmentKeyword(): void
    {
        $rows = [
            $this->row('A001', '王小明', ['國立臺灣大學 - 資訊工程學系', '國立清華大學 - 動力機械工程學系']),
            $this->row('A002', '李小華', ['國立政治大學 - 傳播學院', '國立臺灣大學 - 電機工程學系']),
        ];

        // 依學系關鍵字過濾（例如「資訊工程」）
        $filtered = PreferenceAnalytics::filterAndSort($rows, '', '', 'submitted_at_desc', '資訊工程');
        self::assertSame(['A001'], array_column($filtered, 'student_number'));

        // 依學系關鍵字過濾（例如「電機」）
        $filtered2 = PreferenceAnalytics::filterAndSort($rows, '', '', 'submitted_at_desc', '電機');
        self::assertSame(['A002'], array_column($filtered2, 'student_number'));
    }

    public function testFiltersByNameOrStudentNumberKeyword(): void
    {
        $rows = [
            $this->row('A001', '王小明', ['國立臺灣大學 - 資訊工程學系']),
            $this->row('A002', '李小華', ['國立政治大學 - 傳播學院']),
        ];

        $byName = PreferenceAnalytics::filterAndSort($rows, '小明', '', 'submitted_at_desc');
        self::assertSame(['A001'], array_column($byName, 'student_number'));

        $byNumber = PreferenceAnalytics::filterAndSort($rows, 'A002', '', 'submitted_at_desc');
        self::assertSame(['A002'], array_column($byNumber, 'student_number'));
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

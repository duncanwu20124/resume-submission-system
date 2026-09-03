<?php

namespace App\Support;

final class PreferenceAnalytics
{
    /**
     * @var array<string, array{field: string, direction: string, label: string}>
     */
    private const SORT_OPTIONS = [
        'school_count_desc'  => ['field' => 'school_count', 'direction' => 'desc', 'label' => '填寫人數（多到少）'],
        'school_count_asc'   => ['field' => 'school_count', 'direction' => 'asc', 'label' => '填寫人數（少到多）'],
        'submitted_at_desc'  => ['field' => 'submitted_at', 'direction' => 'desc', 'label' => '送出時間（新到舊）'],
        'submitted_at_asc'   => ['field' => 'submitted_at', 'direction' => 'asc', 'label' => '送出時間（舊到新）'],
    ];

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    public static function filterAndSort(array $rows, string $keyword, string $school, string $sort): array
    {
        $keyword = mb_strtolower(trim($keyword));
        $school  = trim($school);

        $filtered = array_values(array_filter($rows, static function (array $row) use ($keyword, $school): bool {
            if ($keyword !== '') {
                $searchable = mb_strtolower((string) ($row['student_name'] ?? '') . ' ' . ($row['student_number'] ?? ''));

                if (mb_strpos($searchable, $keyword) === false) {
                    return false;
                }
            }

            if ($school !== '' && !self::containsSchool($row, $school)) {
                return false;
            }

            return true;
        }));

        $sortConfig = self::SORT_OPTIONS[self::normalizeSort($sort)];
        $field      = $sortConfig['field'];
        $direction  = $sortConfig['direction'] === 'desc' ? -1 : 1;

        $decorated = [];
        foreach ($filtered as $index => $row) {
            $decorated[] = ['row' => $row, 'index' => $index];
        }

        usort($decorated, static function (array $left, array $right) use ($field, $direction): int {
            $leftValue  = mb_strtolower((string) ($left['row'][$field] ?? ''));
            $rightValue = mb_strtolower((string) ($right['row'][$field] ?? ''));
            $comparison  = strnatcasecmp($leftValue, $rightValue);

            return $comparison !== 0
                ? $comparison * $direction
                : $left['index'] <=> $right['index'];
        });

        return array_column($decorated, 'row');
    }

    public static function normalizeSort(string $sort): string
    {
        return isset(self::SORT_OPTIONS[$sort]) ? $sort : 'school_count_desc';
    }

    /**
     * @return array<string, string>
     */
    public static function sortOptions(): array
    {
        $options = [];
        foreach (self::SORT_OPTIONS as $value => $config) {
            $options[$value] = $config['label'];
        }

        return $options;
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array{school: string, rank_1: int, rank_2: int, rank_3: int, rank_4: int, rank_5: int, rank_6: int, total: int}>
     */
    public static function schoolCounts(array $rows): array
    {
        $counts = [];

        foreach ($rows as $row) {
            $seenInRow = [];

            for ($rank = 1; $rank <= 6; $rank++) {
                $school = trim((string) ($row['choice_' . $rank] ?? ''));
                if ($school === '') {
                    continue;
                }

                if (!isset($counts[$school])) {
                    $counts[$school] = [
                        'school' => $school,
                        'rank_1' => 0,
                        'rank_2' => 0,
                        'rank_3' => 0,
                        'rank_4' => 0,
                        'rank_5' => 0,
                        'rank_6' => 0,
                        'total'  => 0,
                    ];
                }

                $counts[$school]['rank_' . $rank]++;

                if (!isset($seenInRow[$school])) {
                    $counts[$school]['total']++;
                    $seenInRow[$school] = true;
                }
            }
        }

        $counts = array_values($counts);
        usort($counts, static function (array $left, array $right): int {
            return $right['total'] <=> $left['total']
                ?: strcmp($left['school'], $right['school']);
        });

        return $counts;
    }

    /**
     * @param array<int, array{school: string, rank_1: int, rank_2: int, rank_3: int, rank_4: int, rank_5: int, rank_6: int, total: int}> $counts
     * @return array<int, array{school: string, rank_1: int, rank_2: int, rank_3: int, rank_4: int, rank_5: int, rank_6: int, total: int}>
     */
    public static function sortSchoolCounts(array $counts, string $sort): array
    {
        if (!in_array($sort, ['school_count_desc', 'school_count_asc'], true)) {
            return $counts;
        }

        $direction = $sort === 'school_count_desc' ? -1 : 1;
        usort($counts, static function (array $left, array $right) use ($direction): int {
            return (($left['total'] <=> $right['total']) * $direction)
                ?: strcmp($left['school'], $right['school']);
        });

        return $counts;
    }

    /**
     * @param array<int, array{school: string, rank_1: int, rank_2: int, rank_3: int, rank_4: int, rank_5: int, rank_6: int, total: int}> $counts
     * @return array<int, array{school: string, rank_1: int, rank_2: int, rank_3: int, rank_4: int, rank_5: int, rank_6: int, total: int}>
     */
    public static function filterSchoolStats(array $counts, string $school): array
    {
        $school = trim($school);
        if ($school === '') {
            return $counts;
        }

        $filtered = array_values(array_filter(
            $counts,
            static fn (array $stat): bool => $stat['school'] === $school
        ));

        if (!empty($filtered)) {
            return $filtered;
        }

        return [[
            'school' => $school,
            'rank_1' => 0,
            'rank_2' => 0,
            'rank_3' => 0,
            'rank_4' => 0,
            'rank_5' => 0,
            'rank_6' => 0,
            'total'  => 0,
        ]];
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function containsSchool(array $row, string $school): bool
    {
        for ($rank = 1; $rank <= 6; $rank++) {
            if (trim((string) ($row['choice_' . $rank] ?? '')) === $school) {
                return true;
            }
        }

        return false;
    }
}

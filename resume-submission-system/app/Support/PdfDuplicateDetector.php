<?php

namespace App\Support;

final class PdfDuplicateDetector
{
    /**
     * @param array<int, array<string, mixed>> $students
     * @return array<int, array{hash: string, file_size: int, students: array<int, array<string, mixed>>}>
     */
    public static function findDuplicateGroups(array $students): array
    {
        $groups = [];

        foreach ($students as $student) {
            $content = (string) ($student['pdf_content'] ?? '');
            if ($content === '') {
                continue;
            }

            $hash = hash('sha256', $content);
            $groups[$hash] ??= [
                'hash' => $hash,
                'file_size' => strlen($content),
                'students' => [],
            ];
            $groups[$hash]['students'][] = $student;
        }

        return array_values(array_filter(
            $groups,
            static fn (array $group): bool => count($group['students']) > 1
        ));
    }
}

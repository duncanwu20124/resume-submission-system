<?php

namespace App\Support;

final class FormalApplicationData
{
    /**
     * 提供初始正式報名資料，實際資料會由 FormalApplicationModel 寫入資料表。
     */
    public static function all(): array
    {
        $familyNames = [
            '陳', '林', '黃', '張', '李', '王', '吳', '劉', '蔡', '楊',
            '許', '鄭', '謝', '郭', '洪', '邱', '曾', '廖', '賴', '徐',
            '周', '葉', '蘇', '莊', '江', '何', '蕭', '羅', '高', '潘',
            '簡', '朱', '鍾', '彭', '游', '詹', '胡', '施', '沈', '余',
            '盧', '梁', '趙', '顏', '柯', '翁',
        ];
        $givenNames = ['子軒', '品妤', '宇翔', '語晴', '承恩'];
        $applications = [];

        foreach ($familyNames as $familyName) {
            foreach ($givenNames as $givenName) {
                $number = count($applications) + 1;
                $applications[] = [
                    'student_id' => 'S112' . str_pad((string) $number, 3, '0', STR_PAD_LEFT),
                    'name'       => $familyName . $givenName,
                ];
            }
        }

        return $applications;
    }
}

<?php

namespace App\Config;

class Universities
{
    /**
     * @var array<string, array<int, array{name: string, capacity: int}>>|null
     */
    private static ?array $bySchoolData = null;

    /**
     * @var array<string, int>|null
     */
    private static ?array $capacitiesData = null;

    private static function load(): void
    {
        if (self::$bySchoolData !== null) {
            return;
        }

        $jsonFile = __DIR__ . '/departments.json';
        if (file_exists($jsonFile)) {
            $raw = json_decode((string) file_get_contents($jsonFile), true);
            self::$bySchoolData   = $raw['bySchool'] ?? [];
            self::$capacitiesData = $raw['capacities'] ?? [];
        } else {
            self::$bySchoolData   = [];
            self::$capacitiesData = [];
        }
    }

    /**
     * 取得所有大專院校名稱（64 所）
     * @return string[]
     */
    public static function names(): array
    {
        self::load();
        return array_keys(self::$bySchoolData ?? []);
    }

    /**
     * 取得學校與對應科系之樹狀結構
     * @return array<string, array<int, array{name: string, capacity: int}>>
     */
    public static function bySchool(): array
    {
        self::load();
        return self::$bySchoolData ?? [];
    }

    /**
     * 取得指定學校的科系列表
     * @return array<int, array{name: string, capacity: int}>
     */
    public static function departmentsOf(string $school): array
    {
        self::load();
        return self::$bySchoolData[$school] ?? [];
    }

    /**
     * 取得所有校系容量對照表
     * @return array<string, int>
     */
    public static function allCapacities(): array
    {
        self::load();
        return self::$capacitiesData ?? [];
    }

    /**
     * 取得特定校系的招生名額
     */
    public static function capacityOf(string $choice): int
    {
        self::load();
        return self::$capacitiesData[$choice] ?? 0;
    }

    /**
     * 驗證志願是否為合法的「學校 - 科系」
     */
    public static function isValid(string $choice): bool
    {
        self::load();
        return isset(self::$capacitiesData[$choice]);
    }

    /**
     * 從「學校 - 科系」拆解出學校名稱
     */
    public static function extractSchool(string $choice): string
    {
        $parts = explode(' - ', $choice, 2);
        return trim($parts[0]);
    }

    /**
     * 從「學校 - 科系」拆解出科系名稱
     */
    public static function extractDepartment(string $choice): string
    {
        $parts = explode(' - ', $choice, 2);
        return isset($parts[1]) ? trim($parts[1]) : '';
    }
}


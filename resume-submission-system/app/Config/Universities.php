<?php

namespace App\Config;

class Universities
{
    /**
     * 供學生選填志願序的大學列表。city/type 為公開可查證的基本資訊，
     * 實際招生名額、學系與報名資格請以校方最新公告之招生簡章為準。
     *
     * @var array<int, array{name: string, city: string, type: string}>
     */
    public static array $list = [
        ['name' => '國立臺灣大學', 'city' => '臺北市', 'type' => '國立'],
        ['name' => '國立政治大學', 'city' => '臺北市', 'type' => '國立'],
        ['name' => '國立清華大學', 'city' => '新竹市', 'type' => '國立'],
        ['name' => '國立陽明交通大學', 'city' => '新竹市', 'type' => '國立'],
        ['name' => '國立成功大學', 'city' => '臺南市', 'type' => '國立'],
        ['name' => '國立中央大學', 'city' => '桃園市', 'type' => '國立'],
        ['name' => '國立中山大學', 'city' => '高雄市', 'type' => '國立'],
        ['name' => '國立中興大學', 'city' => '臺中市', 'type' => '國立'],
        ['name' => '國立臺灣師範大學', 'city' => '臺北市', 'type' => '國立'],
        ['name' => '國立臺灣科技大學', 'city' => '臺北市', 'type' => '國立'],
        ['name' => '國立臺北科技大學', 'city' => '臺北市', 'type' => '國立'],
        ['name' => '國立高雄科技大學', 'city' => '高雄市', 'type' => '國立'],
        ['name' => '國立中正大學', 'city' => '嘉義縣', 'type' => '國立'],
        ['name' => '國立東華大學', 'city' => '花蓮縣', 'type' => '國立'],
        ['name' => '國立暨南國際大學', 'city' => '南投縣', 'type' => '國立'],
        ['name' => '國立宜蘭大學', 'city' => '宜蘭縣', 'type' => '國立'],
        ['name' => '國立聯合大學', 'city' => '苗栗縣', 'type' => '國立'],
        ['name' => '國立雲林科技大學', 'city' => '雲林縣', 'type' => '國立'],
        ['name' => '國立虎尾科技大學', 'city' => '雲林縣', 'type' => '國立'],
        ['name' => '國立屏東大學', 'city' => '屏東縣', 'type' => '國立'],
        ['name' => '國立高雄師範大學', 'city' => '高雄市', 'type' => '國立'],
        ['name' => '國立彰化師範大學', 'city' => '彰化縣', 'type' => '國立'],
        ['name' => '國立臺北大學', 'city' => '新北市', 'type' => '國立'],
        ['name' => '國立臺北教育大學', 'city' => '臺北市', 'type' => '國立'],
        ['name' => '國立臺南大學', 'city' => '臺南市', 'type' => '國立'],
        ['name' => '國立嘉義大學', 'city' => '嘉義市', 'type' => '國立'],
        ['name' => '國立金門大學', 'city' => '金門縣', 'type' => '國立'],
        ['name' => '國立臺東大學', 'city' => '臺東縣', 'type' => '國立'],
        ['name' => '國立體育大學', 'city' => '桃園市', 'type' => '國立'],
        ['name' => '國立臺灣藝術大學', 'city' => '新北市', 'type' => '國立'],
        ['name' => '國立高雄大學', 'city' => '高雄市', 'type' => '國立'],
        ['name' => '國立臺北護理健康大學', 'city' => '臺北市', 'type' => '國立'],
        ['name' => '國立臺灣海洋大學', 'city' => '基隆市', 'type' => '國立'],
        ['name' => '國立高雄餐旅大學', 'city' => '高雄市', 'type' => '國立'],
        ['name' => '國防醫學院', 'city' => '臺北市', 'type' => '國立'],
        ['name' => '國立臺北藝術大學', 'city' => '臺北市', 'type' => '國立'],
        ['name' => '國立臺南藝術大學', 'city' => '臺南市', 'type' => '國立'],
        ['name' => '輔仁大學', 'city' => '新北市', 'type' => '私立'],
        ['name' => '東吳大學', 'city' => '臺北市', 'type' => '私立'],
        ['name' => '中原大學', 'city' => '桃園市', 'type' => '私立'],
        ['name' => '淡江大學', 'city' => '新北市', 'type' => '私立'],
        ['name' => '逢甲大學', 'city' => '臺中市', 'type' => '私立'],
        ['name' => '東海大學', 'city' => '臺中市', 'type' => '私立'],
        ['name' => '銘傳大學', 'city' => '臺北市', 'type' => '私立'],
        ['name' => '世新大學', 'city' => '臺北市', 'type' => '私立'],
        ['name' => '中國文化大學', 'city' => '臺北市', 'type' => '私立'],
        ['name' => '元智大學', 'city' => '桃園市', 'type' => '私立'],
        ['name' => '長庚大學', 'city' => '桃園市', 'type' => '私立'],
        ['name' => '中山醫學大學', 'city' => '臺中市', 'type' => '私立'],
        ['name' => '高雄醫學大學', 'city' => '高雄市', 'type' => '私立'],
        ['name' => '臺北醫學大學', 'city' => '臺北市', 'type' => '私立'],
        ['name' => '慈濟大學', 'city' => '花蓮縣', 'type' => '私立'],
        ['name' => '亞洲大學', 'city' => '臺中市', 'type' => '私立'],
        ['name' => '靜宜大學', 'city' => '臺中市', 'type' => '私立'],
        ['name' => '大葉大學', 'city' => '彰化縣', 'type' => '私立'],
        ['name' => '中國醫藥大學', 'city' => '臺中市', 'type' => '私立'],
        ['name' => '實踐大學', 'city' => '臺北市', 'type' => '私立'],
        ['name' => '開南大學', 'city' => '桃園市', 'type' => '私立'],
        ['name' => '真理大學', 'city' => '新北市', 'type' => '私立'],
        ['name' => '華梵大學', 'city' => '新北市', 'type' => '私立'],
        ['name' => '南華大學', 'city' => '嘉義縣', 'type' => '私立'],
        ['name' => '佛光大學', 'city' => '宜蘭縣', 'type' => '私立'],
        ['name' => '龍華科技大學', 'city' => '桃園市', 'type' => '私立'],
        ['name' => '明志科技大學', 'city' => '新北市', 'type' => '私立'],
        ['name' => '崑山科技大學', 'city' => '臺南市', 'type' => '私立'],
        ['name' => '南台科技大學', 'city' => '臺南市', 'type' => '私立'],
        ['name' => '正修科技大學', 'city' => '高雄市', 'type' => '私立'],
        ['name' => '樹德科技大學', 'city' => '高雄市', 'type' => '私立'],
        ['name' => '義守大學', 'city' => '高雄市', 'type' => '私立'],
    ];

    /**
     * @return string[]
     */
    public static function names(): array
    {
        return array_column(self::$list, 'name');
    }

    public static function isValid(string $name): bool
    {
        return in_array($name, self::names(), true);
    }

    /**
     * @return array{name: string, city: string, type: string}|null
     */
    public static function find(string $name): ?array
    {
        foreach (self::$list as $university) {
            if ($university['name'] === $name) {
                return $university;
            }
        }

        return null;
    }
}

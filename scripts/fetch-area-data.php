<?php
/**
 * 从 GitHub 开源数据集获取最新行政区划数据，与现有数据合并后更新 AreaData.php
 *
 * 使用方法: php scripts/fetch-area-data.php
 */

require_once __DIR__.'/../vendor/autoload.php';

use GuzzleHttp\Client;

$client = new Client([
    'timeout' => 30,
    'verify' => false,
]);

$baseUrl = 'https://raw.githubusercontent.com/modood/Administrative-divisions-of-China/master/dist/';

// 获取省级数据
echo "正在获取省级数据...\n";
$provincesJson = $client->get($baseUrl . 'provinces.json')->getBody()->getContents();
$provinces = json_decode($provincesJson, true);

// 获取市级数据
echo "正在获取市级数据...\n";
$citiesJson = $client->get($baseUrl . 'cities.json')->getBody()->getContents();
$cities = json_decode($citiesJson, true);

// 获取县级数据
echo "正在获取县级数据...\n";
$areasJson = $client->get($baseUrl . 'areas.json')->getBody()->getContents();
$areas = json_decode($areasJson, true);

// 构建新数据数组
$newData = [];

// 省级: code 是2位，需要补齐到6位 (末尾加0000)
foreach ($provinces as $item) {
    $code = intval($item['code']) * 10000;
    $newData[$code] = $item['name'];
}

// 市级: code 是4位，需要补齐到6位 (末尾加00)
foreach ($cities as $item) {
    $code = intval($item['code']) * 100;
    $newData[$code] = $item['name'];
}

// 县级: code 已经是6位
foreach ($areas as $item) {
    $code = intval($item['code']);
    $newData[$code] = $item['name'];
}

echo "从 GitHub 获取到 " . count($newData) . " 条数据\n";

// 读取现有数据
$existingData = \HughCube\IdCard\Data\AreaData::all();
echo "现有数据 " . count($existingData) . " 条\n";

// 合并: 新数据覆盖旧数据，但保留旧数据中新数据没有的条目(历史废弃码)
$mergedData = $existingData;
foreach ($newData as $code => $name) {
    $mergedData[$code] = $name;
}

// 按数字排序
ksort($mergedData, SORT_NUMERIC);

echo "合并后共 " . count($mergedData) . " 条数据\n";

// 生成 PHP 文件内容
$phpContent = <<<'HEADER'
<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2023/7/7
 * Time: 10:05.
 */

namespace HughCube\IdCard\Data;

class AreaData
{
    public static function all(): array
    {
        return static::$areas;
    }

    public static function exists($code): bool
    {
        return isset(static::$areas[$code]);
    }

    public static function name($code): ?string
    {
        return static::$areas[$code] ?? null;
    }

    protected static $areas = [

HEADER;

foreach ($mergedData as $code => $name) {
    $phpContent .= "        {$code} => '{$name}',\n";
}

$phpContent .= "    ];\n}\n";

// 写入文件
$targetFile = __DIR__ . '/../src/Data/AreaData.php';
file_put_contents($targetFile, $phpContent);

echo "已更新 {$targetFile}\n";
echo "完成!\n";

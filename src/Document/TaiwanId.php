<?php

namespace HughCube\IdCard\Document;

use Generator;
use HughCube\IdCard\Contract\DocumentInterface;
use HughCube\IdCard\Contract\GenderAwareInterface;
use HughCube\IdCard\Contract\TaiwanIssuedInterface;
use HughCube\IdCard\Document\Concerns\CartesianProduct;

class TaiwanId implements DocumentInterface, GenderAwareInterface, TaiwanIssuedInterface
{
    use CartesianProduct;

    /**
     * 字母对应数字映射表.
     */
    protected static $letterMap = [
        'A' => 10, 'B' => 11, 'C' => 12, 'D' => 13, 'E' => 14, 'F' => 15,
        'G' => 16, 'H' => 17, 'I' => 34, 'J' => 18, 'K' => 19, 'L' => 20,
        'M' => 21, 'N' => 22, 'O' => 35, 'P' => 23, 'Q' => 24, 'R' => 25,
        'S' => 26, 'T' => 27, 'U' => 28, 'V' => 29, 'W' => 32, 'X' => 30,
        'Y' => 31, 'Z' => 33,
    ];

    /**
     * 地区映射表.
     */
    protected static $regionMap = [
        'A' => '台北市', 'B' => '台中市', 'C' => '基隆市', 'D' => '台南市',
        'E' => '高雄市', 'F' => '新北市', 'G' => '宜兰县', 'H' => '桃园市',
        'I' => '嘉义市', 'J' => '新竹县', 'K' => '苗栗县', 'L' => '台中县',
        'M' => '南投县', 'N' => '彰化县', 'O' => '新竹市', 'P' => '云林县',
        'Q' => '嘉义县', 'R' => '台南县', 'S' => '高雄县', 'T' => '屏东县',
        'U' => '花莲县', 'V' => '台东县', 'W' => '金门县', 'X' => '澎湖县',
        'Y' => '阳明山', 'Z' => '连江县',
    ];

    /**
     * @var string
     */
    protected $code;

    public function __construct(string $code)
    {
        $this->code = $code;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * 计算前9位(字母+8位数字)的加权和, 不含校验码.
     *
     * @return int|null 加权和, 输入无效时返回 null
     */
    protected static function calculateWeightedSum(string $first9)
    {
        $first9 = strtoupper($first9);

        if (!preg_match('/^[A-Z]\d{8}$/', $first9)) {
            return null;
        }

        $letter = $first9[0];
        if (!isset(static::$letterMap[$letter])) {
            return null;
        }

        $mapped = static::$letterMap[$letter];
        $sum = intval($mapped / 10) * 1 + ($mapped % 10) * 9;

        $weights = [8, 7, 6, 5, 4, 3, 2, 1];
        for ($i = 0; $i < 8; $i++) {
            $sum += intval($first9[$i + 1]) * $weights[$i];
        }

        return $sum;
    }

    /**
     * 根据前9位计算校验码.
     *
     * @return int|null 校验码数字 (0-9), 输入无效时返回 null
     */
    protected static function calculateCheckDigit(string $first9)
    {
        $sum = static::calculateWeightedSum($first9);

        return $sum !== null ? (10 - ($sum % 10)) % 10 : null;
    }

    public function isValid(int $mode = 0): bool
    {
        $code = strtoupper($this->code);

        if (!preg_match('/^[A-Z]\d{9}$/', $code)) {
            return false;
        }

        $sum = static::calculateWeightedSum(substr($code, 0, 9));
        if ($sum === null) {
            return false;
        }

        return ($sum + intval($code[9])) % 10 === 0;
    }

    public function getGender(): ?int
    {
        $code = strtoupper($this->code);

        if (!preg_match('/^[A-Z]\d{9}$/', $code)) {
            return null;
        }

        $second = $code[1];

        if ($second === '1' || $second === '8') {
            return 1;
        }

        if ($second === '2' || $second === '9') {
            return 0;
        }

        return null;
    }

    /**
     * 获取首字母对应的地区名称.
     */
    public function getRegionCode(): ?string
    {
        $code = strtoupper($this->code);

        if (!preg_match('/^[A-Z]/', $code)) {
            return null;
        }

        $letter = $code[0];

        return isset(static::$regionMap[$letter]) ? static::$regionMap[$letter] : null;
    }

    /**
     * @inheritDoc
     */
    public static function complete(string $code, int $mode = 0): Generator
    {
        $normalized = strtoupper($code);

        if (!preg_match('/^[A-Z*][0-9*]{9}$/i', $normalized)) {
            return;
        }

        $letterPos = $normalized[0];
        $digitPositions = substr($normalized, 1, 8);
        $checkPos = $normalized[9];

        // 判断是否只有校验码位是通配符
        $onlyCheckWild = ($letterPos !== '*')
            && (strpos($digitPositions, '*') === false)
            && $checkPos === '*';

        if ($onlyCheckWild) {
            $first9 = $letterPos . $digitPositions;
            $checkDigit = static::calculateCheckDigit($first9);
            if ($checkDigit !== null) {
                yield $first9 . $checkDigit;
            }
            return;
        }

        // 展开字母位
        $letters = ($letterPos === '*') ? range('A', 'Z') : [$letterPos];

        // 展开8个数字位
        $digitChars = [];
        for ($i = 0; $i < 8; $i++) {
            if ($digitPositions[$i] === '*') {
                $digitChars[$i] = range(0, 9);
            } else {
                $digitChars[$i] = [intval($digitPositions[$i])];
            }
        }

        // 展开校验码位
        $checkChars = ($checkPos === '*') ? range(0, 9) : [intval($checkPos)];

        foreach ($letters as $letter) {
            foreach (static::cartesianProduct($digitChars) as $digitArr) {
                $digits = implode('', $digitArr);

                if ($checkPos === '*') {
                    $checkDigit = static::calculateCheckDigit($letter . $digits);
                    if ($checkDigit !== null) {
                        yield $letter . $digits . $checkDigit;
                    }
                } else {
                    foreach ($checkChars as $check) {
                        $candidate = new self($letter . $digits . $check);
                        if ($candidate->isValid()) {
                            yield $candidate->getCode();
                        }
                    }
                }
            }
        }
    }

}

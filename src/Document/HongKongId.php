<?php

namespace HughCube\IdCard\Document;

use Generator;
use HughCube\IdCard\Contract\DocumentInterface;
use HughCube\IdCard\Contract\HongKongIssuedInterface;
use HughCube\IdCard\Document\Concerns\CartesianProduct;

class HongKongId implements DocumentInterface, HongKongIssuedInterface
{
    use CartesianProduct;

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
     * 解析香港身份证号码, 返回 [prefix, digits, checkChar] 或 null.
     *
     * @return array|null
     */
    protected static function parse(string $code)
    {
        if (!preg_match('/^([A-Z]{1,2})(\d{6})\(?([0-9A])\)?$/i', $code, $matches)) {
            return null;
        }

        return [
            strtoupper($matches[1]),
            $matches[2],
            strtoupper($matches[3]),
        ];
    }

    /**
     * 计算校验码.
     *
     * @return string|null 校验码字符 (0-9 或 A), 输入无效时返回 null
     */
    protected static function calculateCheckChar(string $prefix, string $digits)
    {
        $prefix = strtoupper($prefix);
        $digits = (string) $digits;

        if (!preg_match('/^[A-Z]{1,2}$/', $prefix) || !preg_match('/^\d{6}$/', $digits)) {
            return null;
        }

        $sum = 0;

        // 字母映射: A=10, B=11, ..., Z=35, 空位=36
        if (strlen($prefix) === 2) {
            $sum += (ord($prefix[0]) - ord('A') + 10) * 9;
            $sum += (ord($prefix[1]) - ord('A') + 10) * 8;
        } else {
            $sum += 36 * 9; // 单字母时, 首位用 space(36) 填充
            $sum += (ord($prefix[0]) - ord('A') + 10) * 8;
        }

        for ($i = 0; $i < 6; $i++) {
            $sum += intval($digits[$i]) * (7 - $i);
        }

        $remainder = $sum % 11;
        $checkValue = (11 - $remainder) % 11;

        return $checkValue === 10 ? 'A' : (string) $checkValue;
    }

    public function isValid(int $mode = 0): bool
    {
        $parsed = static::parse($this->code);
        if ($parsed === null) {
            return false;
        }

        list($prefix, $digits, $checkChar) = $parsed;

        return static::calculateCheckChar($prefix, $digits) === $checkChar;
    }

    /**
     * @inheritDoc
     */
    public static function complete(string $code, int $mode = 0): Generator
    {
        $normalized = strtoupper($code);

        // 解析通配符模式, 匹配: prefix(1-2字母或*) + digits(6位数字或*) + 可选括号中的校验码(或*)
        if (!preg_match('/^([A-Z*]{1,2})([0-9*]{6})\(?([0-9A*])\)?$/i', $normalized, $matches)) {
            return;
        }

        $prefixPattern = $matches[1];
        $digitsPattern = $matches[2];
        $checkPattern = $matches[3];

        // 判断是否只有校验码位是通配符
        $onlyCheckWild = (strpos($prefixPattern, '*') === false)
            && (strpos($digitsPattern, '*') === false)
            && $checkPattern === '*';

        if ($onlyCheckWild) {
            $checkChar = static::calculateCheckChar($prefixPattern, $digitsPattern);
            if ($checkChar !== null) {
                yield $prefixPattern . $digitsPattern . '(' . $checkChar . ')';
            }
            return;
        }

        // 展开所有通配符位置
        $prefixChars = [];
        for ($i = 0; $i < strlen($prefixPattern); $i++) {
            if ($prefixPattern[$i] === '*') {
                $prefixChars[$i] = range('A', 'Z');
            } else {
                $prefixChars[$i] = [$prefixPattern[$i]];
            }
        }

        $digitChars = [];
        for ($i = 0; $i < 6; $i++) {
            if ($digitsPattern[$i] === '*') {
                $digitChars[$i] = range('0', '9');
            } else {
                $digitChars[$i] = [$digitsPattern[$i]];
            }
        }

        $checkChars = [];
        if ($checkPattern === '*') {
            $checkChars = array_merge(range('0', '9'), ['A']);
        } else {
            $checkChars = [$checkPattern];
        }

        // 笛卡尔积遍历
        foreach (static::cartesianProduct($prefixChars) as $prefixArr) {
            $prefix = implode('', $prefixArr);
            foreach (static::cartesianProduct($digitChars) as $digitArr) {
                $digits = implode('', $digitArr);
                foreach ($checkChars as $check) {
                    $candidate = new self($prefix . $digits . '(' . $check . ')');
                    if ($candidate->isValid()) {
                        yield $candidate->getCode();
                    }
                }
            }
        }
    }

}

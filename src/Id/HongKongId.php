<?php

namespace HughCube\IdCard\Id;

use Generator;
use HughCube\IdCard\IdType;

/**
 * 香港永久性居民身份证, 香港签发, 1-2位字母前缀+6位数字+1位校验码(0-9或A).
 */
class HongKongId extends AbstractId
{
    public function getType(): string
    {
        return IdType::HONG_KONG_ID;
    }

    public static function normalize(string $code): string
    {
        return strtoupper($code);
    }

    public static function getPattern(): string
    {
        return '/^([A-Z]{1,2})(\d{6})\(?([0-9A])\)?$/i';
    }

    public static function getCompletePattern(): string
    {
        return '/^([A-Z*]{1,2})([0-9*]{6})\(?([0-9A*])\)?$/i';
    }

    /**
     * 解析香港身份证号码, 返回 [prefix, digits, checkChar] 或 null.
     *
     * @param string $code
     * @return array|null
     */
    protected static function parse(string $code): ?array
    {
        if (!preg_match(static::getPattern(), $code, $matches)) {
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
    protected static function calculateCheckChar(string $prefix, string $digits): ?string
    {
        $prefix = strtoupper($prefix);
        $digits = (string)$digits;

        if (!preg_match('/^[A-Z]{1,2}$/', $prefix) || !preg_match('/^\d{6}$/', $digits)) {
            return null;
        }

        $sum = 0;

        if (strlen($prefix) === 2) {
            $sum += (ord($prefix[0]) - ord('A') + 10) * 9;
            $sum += (ord($prefix[1]) - ord('A') + 10) * 8;
        } else {
            $sum += 36 * 9;
            $sum += (ord($prefix[0]) - ord('A') + 10) * 8;
        }

        for ($i = 0; $i < 6; $i++) {
            $sum += intval($digits[$i]) * (7 - $i);
        }

        $remainder = $sum % 11;
        $checkValue = (11 - $remainder) % 11;

        return $checkValue === 10 ? 'A' : (string)$checkValue;
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
     * 掩码: 解析后掩码数字中间部分.
     * 如 G123456(A) → G1****6(A), AB123456(9) → AB1****6(9)
     */
    public function mask(): ?string
    {
        $parsed = static::parse($this->code);
        if ($parsed === null) {
            return null;
        }

        list($prefix, $digits, $checkChar) = $parsed;

        $maskedDigits = $digits[0] . str_repeat('*', 4) . $digits[5];

        return $prefix . $maskedDigits . '(' . $checkChar . ')';
    }

    /**
     * @inheritDoc
     */
    public static function complete(string $code, int $mode = 0): Generator
    {
        $normalized = static::normalize($code);

        if (!preg_match(static::getCompletePattern(), $normalized, $matches)) {
            return;
        }

        $prefixPattern = $matches[1];
        $digitsPattern = $matches[2];
        $checkPattern = $matches[3];

        $onlyCheckWild = !str_contains($prefixPattern, '*')
            && !str_contains($digitsPattern, '*')
            && $checkPattern === '*';

        if ($onlyCheckWild) {
            $checkChar = static::calculateCheckChar($prefixPattern, $digitsPattern);
            if ($checkChar !== null) {
                yield $prefixPattern . $digitsPattern . '(' . $checkChar . ')';
            }
            return;
        }

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

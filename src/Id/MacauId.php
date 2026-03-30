<?php

namespace HughCube\IdCard\Id;

use Generator;
use HughCube\IdCard\IdType;

/**
 * 澳门居民身份证, 澳门签发, 8位(首位1/5/7+6位数字+1位校验码), 支持斜杠和括号格式.
 */
class MacauId extends AbstractId
{
    public function getType(): string
    {
        return IdType::MACAU_ID;
    }

    /**
     * 去除斜杠和括号, 标准化.
     * 支持格式: 12345678, 1/234567/8, 1234567(8), 1234567(A).
     */
    public static function normalize(string $code): string
    {
        return strtoupper(str_replace(['/', '(', ')'], '', $code));
    }

    public static function getPattern(): string
    {
        return '/^[157]\d{6}[\dA]$/';
    }

    public static function getCompletePattern(): string
    {
        return '/^[157*][0-9*]{6}[0-9A*]$/i';
    }

    public function isValid(int $mode = 0): bool
    {
        $normalized = static::normalize($this->code);

        if (!preg_match(static::getPattern(), $normalized)) {
            return false;
        }

        return static::verifyCheckDigit($normalized);
    }

    /**
     * 掩码: normalize 后保留前1后2, 中间用*替换.
     */
    public function mask(): ?string
    {
        $normalized = static::normalize($this->code);
        if (strlen($normalized) !== 8) {
            return null;
        }

        return $normalized[0].str_repeat('*', 5).substr($normalized, -2);
    }

    /**
     * 验证校验码是否正确.
     */
    protected static function verifyCheckDigit(string $normalized8): bool
    {
        $weights = [8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 7; $i++) {
            $sum += intval($normalized8[$i]) * $weights[$i];
        }

        $remainder = $sum % 11;

        if ($remainder === 0) {
            return $normalized8[7] === '0';
        }

        $expected = 11 - $remainder;

        if ($expected === 10) {
            return $normalized8[7] === 'A';
        }

        return $normalized8[7] === (string) $expected;
    }

    /**
     * 根据前7位计算校验码字符.
     *
     * @return string|null 校验码 (0-9 或 A), null 表示输入无效
     */
    protected static function calculateCheckChar(string $first7)
    {
        if (!preg_match('/^[157]\d{6}$/', $first7)) {
            return null;
        }

        $weights = [8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 7; $i++) {
            $sum += intval($first7[$i]) * $weights[$i];
        }

        $remainder = $sum % 11;

        if ($remainder === 0) {
            return '0';
        }

        $check = 11 - $remainder;

        return $check === 10 ? 'A' : (string) $check;
    }

    /**
     * @inheritDoc
     */
    public static function complete(string $code, int $mode = 0): Generator
    {
        $normalized = static::normalize($code);

        if (strlen($normalized) !== 8) {
            return;
        }

        $positions = [];
        for ($i = 0; $i < 8; $i++) {
            if ($normalized[$i] === '*') {
                $positions[] = $i;
            }
        }

        if (empty($positions)) {
            $instance = new self($normalized);
            if ($instance->isValid()) {
                yield $normalized;
            }

            return;
        }

        yield from static::expand($normalized, $positions, 0);
    }

    /**
     * 递归展开通配符.
     */
    protected static function expand(string $code, array $positions, int $index): Generator
    {
        if ($index >= count($positions)) {
            $instance = new self($code);
            if ($instance->isValid()) {
                yield $code;
            }

            return;
        }

        $pos = $positions[$index];

        if ($pos === 7 && $index === count($positions) - 1) {
            $check = static::calculateCheckChar(substr($code, 0, 7));
            if ($check !== null) {
                $code[7] = $check;
                yield $code;
            }

            return;
        }

        if ($pos === 0) {
            $candidates = ['1', '5', '7'];
        } else {
            $candidates = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        }

        foreach ($candidates as $digit) {
            $code[$pos] = $digit;
            yield from static::expand($code, $positions, $index + 1);
        }
    }
}

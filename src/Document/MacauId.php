<?php

namespace HughCube\IdCard\Document;

use Generator;
use HughCube\IdCard\Contract\DocumentInterface;
use HughCube\IdCard\Contract\MacauIssuedInterface;

class MacauId implements DocumentInterface, MacauIssuedInterface
{
    /**
     * @var string
     */
    protected $code;

    /**
     * @param string $code
     */
    public function __construct(string $code)
    {
        $this->code = $code;
    }

    /**
     * @inheritDoc
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * 去除斜杠, 标准化为8位纯数字.
     */
    protected static function normalize(string $code): string
    {
        return str_replace('/', '', $code);
    }

    /**
     * @inheritDoc
     */
    public function isValid(int $mode = 0): bool
    {
        $normalized = static::normalize($this->code);

        if (!preg_match('/^[157]\d{6}\d$/', $normalized)) {
            return false;
        }

        return static::checksum($normalized) === 0;
    }

    /**
     * 计算加权和 mod 11.
     */
    protected static function checksum(string $digits): int
    {
        $weights = [8, 7, 6, 5, 4, 3, 2, 1];
        $sum = 0;
        for ($i = 0; $i < 8; $i++) {
            $sum += intval($digits[$i]) * $weights[$i];
        }

        return $sum % 11;
    }

    /**
     * 根据前7位计算校验码, 不存在合法校验码时返回 -1.
     */
    protected static function calculateCheckDigit(string $first7): int
    {
        $weights = [8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 7; $i++) {
            $sum += intval($first7[$i]) * $weights[$i];
        }

        $remainder = $sum % 11;
        if ($remainder === 0) {
            return 0;
        }

        $check = 11 - $remainder;

        return $check <= 9 ? $check : -1;
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

        // 最后一位是校验码位, 可以直接计算
        if ($pos === 7 && $index === count($positions) - 1) {
            $check = static::calculateCheckDigit(substr($code, 0, 7));
            if ($check >= 0) {
                $code[7] = (string) $check;
                yield $code;
            }
            return;
        }

        if ($pos === 0) {
            $candidates = [1, 5, 7];
        } else {
            $candidates = range(0, 9);
        }

        foreach ($candidates as $digit) {
            $code[$pos] = (string) $digit;
            yield from static::expand($code, $positions, $index + 1);
        }
    }
}

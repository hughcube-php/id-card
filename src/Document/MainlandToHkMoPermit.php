<?php

namespace HughCube\IdCard\Document;

use Generator;
use HughCube\IdCard\Contract\MainlandIssuedInterface;

class MainlandToHkMoPermit extends AbstractSimplePermit implements MainlandIssuedInterface
{
    protected static function getValidPrefixes(): array
    {
        return ['C', 'W'];
    }

    /**
     * 支持两种格式:
     * - 旧版(2018年12月前): C/W + 8位数字 (如 C12345678)
     * - 新版(2018年12月后): C + 1位字母(I/O除外) + 7位数字 (如 CA1234567)
     * - 本式(已失效): W + 8位数字
     */
    protected static function getPattern(): string
    {
        return '/^(C\d{8}|C[A-HJ-NP-Z]\d{7}|W\d{8})$/i';
    }

    /**
     * @inheritDoc
     */
    public static function complete(string $code, int $mode = 0): Generator
    {
        $normalized = strtoupper($code);

        // 旧版 C/W + 8位数字
        if (preg_match('/^([CW*])([0-9*]{8})$/i', $normalized, $matches)) {
            yield from static::completeSimple($matches[1], $matches[2], ['C', 'W']);
            return;
        }

        // 新版 C + 字母 + 7位数字
        if (preg_match('/^(C)([A-HJ-NP-Z*])([0-9*]{7})$/i', $normalized, $matches)) {
            $prefix = $matches[1];
            $letterPattern = strtoupper($matches[2]);
            $digitsPattern = $matches[3];

            // I 和 O 排除
            $validLetters = array_diff(range('A', 'Z'), ['I', 'O']);
            $letters = ($letterPattern === '*') ? $validLetters : [$letterPattern];

            $digitChars = [];
            for ($i = 0; $i < 7; $i++) {
                $digitChars[$i] = ($digitsPattern[$i] === '*') ? range('0', '9') : [$digitsPattern[$i]];
            }

            foreach ($letters as $letter) {
                foreach (static::cartesianProduct($digitChars) as $digitArr) {
                    yield $prefix . $letter . implode('', $digitArr);
                }
            }
            return;
        }
    }

    /**
     * 简单前缀+数字的补全.
     */
    protected static function completeSimple(string $letterPattern, string $digitsPattern, array $prefixes): Generator
    {
        $letters = ($letterPattern === '*') ? $prefixes : [$letterPattern];

        $digitChars = [];
        for ($i = 0; $i < 8; $i++) {
            $digitChars[$i] = ($digitsPattern[$i] === '*') ? range('0', '9') : [$digitsPattern[$i]];
        }

        foreach ($letters as $letter) {
            foreach (static::cartesianProduct($digitChars) as $digitArr) {
                yield $letter . implode('', $digitArr);
            }
        }
    }
}

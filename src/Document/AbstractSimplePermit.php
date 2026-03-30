<?php

namespace HughCube\IdCard\Document;

use Generator;
use HughCube\IdCard\Contract\DocumentInterface;
use HughCube\IdCard\Document\Concerns\CartesianProduct;

abstract class AbstractSimplePermit implements DocumentInterface
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
     * 返回合法的前缀字母数组, 如 ['H'] 或 ['C', 'W'].
     *
     * @return string[]
     */
    abstract protected static function getValidPrefixes(): array;

    /**
     * 返回验证用正则表达式.
     */
    protected static function getPattern(): string
    {
        return '/^[' . implode('', static::getValidPrefixes()) . ']\d{8}$/i';
    }

    /**
     * 返回 complete 用正则表达式(支持通配符).
     */
    protected static function getCompletePattern(): string
    {
        $prefixRegex = implode('', static::getValidPrefixes());

        return '/^([' . $prefixRegex . '*])([0-9*]{8})$/i';
    }

    /**
     * 返回 complete 中数字位长度.
     */
    protected static function getDigitLength(): int
    {
        return 8;
    }

    public function isValid(int $mode = 0): bool
    {
        return (bool) preg_match(static::getPattern(), $this->code);
    }

    /**
     * @inheritDoc
     */
    public static function complete(string $code, int $mode = 0): Generator
    {
        $normalized = strtoupper($code);

        if (!preg_match(static::getCompletePattern(), $normalized, $matches)) {
            return;
        }

        $letterPattern = $matches[1];
        $digitsPattern = $matches[2];
        $prefixes = static::getValidPrefixes();

        $letters = ($letterPattern === '*') ? $prefixes : [$letterPattern];

        $digitLen = static::getDigitLength();
        $digitChars = [];
        for ($i = 0; $i < $digitLen; $i++) {
            if ($digitsPattern[$i] === '*') {
                $digitChars[$i] = range('0', '9');
            } else {
                $digitChars[$i] = [$digitsPattern[$i]];
            }
        }

        foreach ($letters as $letter) {
            foreach (static::cartesianProduct($digitChars) as $digitArr) {
                yield $letter . implode('', $digitArr);
            }
        }
    }
}

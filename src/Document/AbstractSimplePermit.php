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

    public function isValid(int $mode = 0): bool
    {
        $pattern = '/^[' . implode('', static::getValidPrefixes()) . ']\d{8}$/i';

        return (bool) preg_match($pattern, $this->code);
    }

    /**
     * @inheritDoc
     */
    public static function complete(string $code, int $mode = 0): Generator
    {
        $normalized = strtoupper($code);
        $prefixes = static::getValidPrefixes();
        $prefixRegex = implode('', $prefixes);

        if (!preg_match('/^([' . $prefixRegex . '*])([0-9*]{8})$/i', $normalized, $matches)) {
            return;
        }

        $letterPattern = $matches[1];
        $digitsPattern = $matches[2];

        $letters = ($letterPattern === '*') ? $prefixes : [$letterPattern];

        $digitChars = [];
        for ($i = 0; $i < 8; $i++) {
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

<?php

namespace HughCube\IdCard\Id;

use Generator;
use HughCube\IdCard\IdType;

/**
 * 台湾居民来往大陆通行证(台胞证), 台湾签发, 8位纯数字.
 */
class TwToMainlandPermit extends AbstractId
{
    public function getType(): string
    {
        return IdType::TW_TO_MAINLAND_PERMIT;
    }

    public static function normalize(string $code): string
    {
        return $code;
    }

    public static function getPattern(): string
    {
        return '/^[0-9]{8}$/';
    }

    public static function getCompletePattern(): string
    {
        return '/^[0-9*]{8}$/';
    }

    /**
     * 台湾居民来往大陆通行证, 格式: 8位纯数字.
     */
    public function isValid(int $mode = 0): bool
    {
        return (bool) preg_match(static::getPattern(), $this->code);
    }

    /**
     * 掩码: 保留前2后2, 中间用****替换.
     */
    public function mask(): ?string
    {
        $code = $this->code;
        if (strlen($code) !== 8) {
            return null;
        }

        return substr($code, 0, 2) . '****' . substr($code, -2);
    }

    /**
     * @return Generator<string>
     */
    public static function complete(string $code, int $mode = 0): Generator
    {
        if (!preg_match(static::getCompletePattern(), $code)) {
            return;
        }

        $digitChars = [];
        for ($i = 0; $i < 8; $i++) {
            if ($code[$i] === '*') {
                $digitChars[$i] = range('0', '9');
            } else {
                $digitChars[$i] = [$code[$i]];
            }
        }

        foreach (static::cartesianProduct($digitChars) as $digitArr) {
            yield implode('', $digitArr);
        }
    }
}

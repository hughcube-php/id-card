<?php

namespace HughCube\IdCard\Id;

use Generator;
use HughCube\IdCard\IdType;

/**
 * 港澳居民来往内地通行证(回乡证·香港), 香港签发, H+8位数字(核心号)或H+10位数字(含换证次数).
 */
class HkToMainlandPermit extends AbstractSimplePermit
{
    public function getType(): string
    {
        return IdType::HK_TO_MAINLAND_PERMIT;
    }

    protected static function getValidPrefixes(): array
    {
        return ['H'];
    }

    /**
     * 支持9位(核心号 H12345678)和11位(完整号 H1234567890, 末2位换证次数).
     */
    public static function getPattern(): string
    {
        return '/^H\d{8}(\d{2})?$/i';
    }

    public static function getCompletePattern(): string
    {
        return '/^([H*])([0-9*]{8}|[0-9*]{10})$/i';
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

        $letterPattern = $matches[1];
        $digitsPattern = $matches[2];

        $letters = ($letterPattern === '*') ? ['H'] : [$letterPattern];

        $digitLen = strlen($digitsPattern);
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
                yield $letter.implode('', $digitArr);
            }
        }
    }
}

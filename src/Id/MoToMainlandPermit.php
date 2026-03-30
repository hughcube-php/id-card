<?php

namespace HughCube\IdCard\Id;

use Generator;
use HughCube\IdCard\Contract\MacauIssuedInterface;
use HughCube\IdCard\IdType;

class MoToMainlandPermit extends AbstractSimplePermit implements MacauIssuedInterface
{
    public function getType(): string
    {
        return IdType::MO_TO_MAINLAND_PERMIT;
    }

    protected static function getValidPrefixes(): array
    {
        return ['M'];
    }

    /**
     * 支持9位(核心号 M12345678)和11位(完整号 M1234567890, 末2位换证次数).
     */
    protected static function getPattern(): string
    {
        return '/^M\d{8}(\d{2})?$/i';
    }

    /**
     * @inheritDoc
     */
    public static function complete(string $code, int $mode = 0): Generator
    {
        $normalized = strtoupper($code);

        if (!preg_match('/^([M*])([0-9*]{8}|[0-9*]{10})$/i', $normalized, $matches)) {
            return;
        }

        $letterPattern = $matches[1];
        $digitsPattern = $matches[2];

        $letters = ($letterPattern === '*') ? ['M'] : [$letterPattern];

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
                yield $letter . implode('', $digitArr);
            }
        }
    }
}

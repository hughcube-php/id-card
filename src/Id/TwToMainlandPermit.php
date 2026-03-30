<?php

namespace HughCube\IdCard\Id;

use Generator;
use HughCube\IdCard\Contract\IdInterface;
use HughCube\IdCard\Contract\TaiwanIssuedInterface;
use HughCube\IdCard\Id\Concerns\CartesianProduct;
use HughCube\IdCard\IdType;

class TwToMainlandPermit implements IdInterface, TaiwanIssuedInterface
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

    public function getType(): string
    {
        return IdType::TW_TO_MAINLAND_PERMIT;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * 台湾居民来往大陆通行证, 格式: 8位纯数字.
     */
    public function isValid(int $mode = 0): bool
    {
        return (bool) preg_match('/^[0-9]{8}$/', $this->code);
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
        if (!preg_match('/^[0-9*]{8}$/', $code)) {
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

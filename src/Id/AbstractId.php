<?php

namespace HughCube\IdCard\Id;

use Carbon\Carbon;
use Generator;
use HughCube\IdCard\Area;
use HughCube\IdCard\Contract\IdInterface;
use HughCube\IdCard\Id\Concerns\CartesianProduct;

abstract class AbstractId implements IdInterface
{
    use CartesianProduct;

    /**
     * @var null|string
     */
    protected ?string $code;

    public function __construct(?string $code = null)
    {
        $this->code = $code;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    abstract public function getType(): string;

    abstract public function isValid(int $mode = 0): bool;

    abstract public function mask(): ?string;

    /**
     * @return Generator<null|string>
     */
    abstract public static function complete(string $code, int $mode = 0): Generator;

    public function getBirthday(): ?Carbon
    {
        return null;
    }

    public function getGender(): ?int
    {
        return null;
    }

    public function getProvince(): ?Area
    {
        return null;
    }

    public function getCity(): ?Area
    {
        return null;
    }

    public function getCounty(): ?Area
    {
        return null;
    }

    public function getAreaDescribe(): ?string
    {
        return null;
    }
}

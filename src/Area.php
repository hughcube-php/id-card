<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2023/7/6
 * Time: 20:51.
 */

namespace HughCube\IdCard;

use HughCube\IdCard\Data\AreaData;

class Area
{
    protected $code;

    public function __construct(string $code)
    {
        $this->code = $code;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getValidCode(): string
    {
        return str_pad($this->getCode(), 6, '0', STR_PAD_RIGHT);
    }

    public function getName(): ?string
    {
        return AreaData::$areas[$this->getValidCode()] ?? null;
    }

    public function isPH(): bool
    {
        return 0 === intval($this->getValidCode()) % 100
            && in_array($this->getName(), ['县', '省直辖县级行政区划', '市辖区']);
    }

    public function isExists(): bool
    {
        return isset(AreaData::$areas[$this->getValidCode()]);
    }
}

<?php

namespace HughCube\IdCard\Id;

use Generator;
use HughCube\IdCard\IdType;

/**
 * 未设置证件类型.
 */
class NoneId extends AbstractId
{
    public function getType(): string
    {
        return IdType::NONE;
    }

    public static function normalize(string $code): string
    {
        return $code;
    }

    public static function getPattern(): string
    {
        return '/^.*$/s';
    }

    public static function getCompletePattern(): string
    {
        return '/^.*$/s';
    }

    public function isValid(int $mode = 0): bool
    {
        return $this->code === null;
    }

    public function mask(): ?string
    {
        return null;
    }

    /**
     * @return Generator<null>
     */
    public static function complete(string $code, int $mode = 0): Generator
    {
        yield null;
    }
}

<?php

namespace HughCube\IdCard\Contract;

use Generator;

interface IdInterface
{
    public function getType(): string;

    public function getCode(): string;

    public function isValid(int $mode = 0): bool;

    public function mask(): ?string;

    /**
     * @return Generator<string>
     */
    public static function complete(string $code, int $mode = 0): Generator;
}

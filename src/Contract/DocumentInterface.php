<?php

namespace HughCube\IdCard\Contract;

use Generator;

interface DocumentInterface
{
    /**
     * 获取原始证件号码.
     */
    public function getCode(): string;

    /**
     * 校验证件号码是否合法.
     *
     * @param int $mode 校验模式位掩码, 具体常量由各实现类定义
     */
    public function isValid(int $mode = 0): bool;

    /**
     * 补全含通配符(*)的证件号码, 返回所有可能的合法号码.
     *
     * @return Generator<string>
     */
    public static function complete(string $code, int $mode = 0): Generator;
}

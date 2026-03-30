<?php

namespace HughCube\IdCard\Id\Concerns;

use Generator;

trait CartesianProduct
{
    /**
     * 计算多个数组的笛卡尔积.
     *
     * @param array $arrays
     * @return Generator
     */
    protected static function cartesianProduct(array $arrays): Generator
    {
        if (empty($arrays)) {
            yield [];
            return;
        }

        $first = array_shift($arrays);
        foreach ($first as $value) {
            foreach (static::cartesianProduct($arrays) as $rest) {
                yield array_merge([$value], $rest);
            }
        }
    }
}

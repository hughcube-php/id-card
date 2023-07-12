<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2023/7/12
 * Time: 10:47
 */

namespace HughCube\IdCard\Enum;

use HughCube\Enum\Enum;

class GenderEnum extends Enum
{
    const MALE = 1;
    const FEMALE = 0;

    /**
     * @inheritDoc
     */
    public static function labels(): array
    {
        return [
            static::MALE => ['title' => '男', 'name' => 'male'],
            static::FEMALE => ['title' => '女', 'name' => 'female'],
        ];
    }
}

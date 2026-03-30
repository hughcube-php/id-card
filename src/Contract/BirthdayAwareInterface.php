<?php

namespace HughCube\IdCard\Contract;

use Carbon\Carbon;

interface BirthdayAwareInterface
{
    /**
     * 获取出生日期.
     */
    public function getBirthday(): ?Carbon;
}

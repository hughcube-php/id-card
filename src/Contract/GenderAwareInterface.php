<?php

namespace HughCube\IdCard\Contract;

interface GenderAwareInterface
{
    /**
     * 获取性别, 0=女, 1=男.
     */
    public function getGender(): ?int;
}

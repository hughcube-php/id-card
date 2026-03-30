<?php

namespace HughCube\IdCard\Id;

use HughCube\IdCard\IdType;

/**
 * 大陆居民往来台湾通行证, 大陆签发, L/T+8位数字.
 */
class MainlandToTwPermit extends AbstractSimplePermit
{
    public function getType(): string
    {
        return IdType::MAINLAND_TO_TW_PERMIT;
    }

    protected static function getValidPrefixes(): array
    {
        return ['L', 'T'];
    }
}

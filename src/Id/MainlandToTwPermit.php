<?php

namespace HughCube\IdCard\Id;

use HughCube\IdCard\Contract\MainlandIssuedInterface;
use HughCube\IdCard\IdType;

class MainlandToTwPermit extends AbstractSimplePermit implements MainlandIssuedInterface
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

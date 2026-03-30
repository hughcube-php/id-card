<?php

namespace HughCube\IdCard\Document;

use HughCube\IdCard\Contract\HongKongIssuedInterface;

class HkToMainlandPermit extends AbstractSimplePermit implements HongKongIssuedInterface
{
    protected static function getValidPrefixes(): array
    {
        return ['H'];
    }
}

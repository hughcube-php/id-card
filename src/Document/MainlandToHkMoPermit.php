<?php

namespace HughCube\IdCard\Document;

use HughCube\IdCard\Contract\MainlandIssuedInterface;

class MainlandToHkMoPermit extends AbstractSimplePermit implements MainlandIssuedInterface
{
    protected static function getValidPrefixes(): array
    {
        return ['C', 'W'];
    }
}

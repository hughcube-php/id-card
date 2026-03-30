<?php

namespace HughCube\IdCard\Document;

use HughCube\IdCard\Contract\MainlandIssuedInterface;

class MainlandToTwPermit extends AbstractSimplePermit implements MainlandIssuedInterface
{
    protected static function getValidPrefixes(): array
    {
        return ['L', 'T'];
    }
}

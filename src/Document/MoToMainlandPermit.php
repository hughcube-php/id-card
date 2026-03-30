<?php

namespace HughCube\IdCard\Document;

use HughCube\IdCard\Contract\MacauIssuedInterface;

class MoToMainlandPermit extends AbstractSimplePermit implements MacauIssuedInterface
{
    protected static function getValidPrefixes(): array
    {
        return ['M'];
    }
}

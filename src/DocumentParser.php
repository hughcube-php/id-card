<?php

namespace HughCube\IdCard;

use HughCube\IdCard\Contract\DocumentInterface;
use HughCube\IdCard\Document\HkToMainlandPermit;
use HughCube\IdCard\Document\HongKongId;
use HughCube\IdCard\Document\MacauId;
use HughCube\IdCard\Document\MainlandId;
use HughCube\IdCard\Document\MainlandToHkMoPermit;
use HughCube\IdCard\Document\MainlandToTwPermit;
use HughCube\IdCard\Document\MoToMainlandPermit;
use HughCube\IdCard\Document\TaiwanId;

class DocumentParser
{
    /**
     * 自动识别证件号码, 返回对应的证件实例.
     * 按格式特异性从高到低尝试匹配, 返回第一个 isValid() 通过的实例.
     * 无法识别返回 null.
     */
    public static function parse(string $code): ?DocumentInterface
    {
        $classes = [
            MainlandId::class,
            TaiwanId::class,
            HongKongId::class,
            MacauId::class,
            HkToMainlandPermit::class,
            MoToMainlandPermit::class,
            MainlandToHkMoPermit::class,
            MainlandToTwPermit::class,
        ];

        foreach ($classes as $class) {
            $instance = new $class($code);
            if ($instance->isValid()) {
                return $instance;
            }
        }

        return null;
    }
}

<?php

namespace HughCube\IdCard;

use HughCube\IdCard\Contract\IdInterface;
use HughCube\IdCard\Id\HkToMainlandPermit;
use HughCube\IdCard\Id\HongKongId;
use HughCube\IdCard\Id\MacauId;
use HughCube\IdCard\Id\MainlandId;
use HughCube\IdCard\Id\MainlandToHkMoPermit;
use HughCube\IdCard\Id\MainlandToTwPermit;
use HughCube\IdCard\Id\MoToMainlandPermit;
use HughCube\IdCard\Id\TaiwanId;
use HughCube\IdCard\Id\TwToMainlandPermit;

class IdParser
{
    /**
     * 自动识别证件号码, 返回对应的证件实例.
     * 按格式特异性从高到低尝试匹配, 返回第一个 isValid() 通过的实例.
     * 无法识别返回 null.
     */
    public static function parse(string $code): ?IdInterface
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
            TwToMainlandPermit::class,
        ];

        foreach ($classes as $class) {
            $instance = new $class($code);
            if ($instance->isValid()) {
                return $instance;
            }
        }

        return null;
    }

    /**
     * 通过类型和号码创建证件实例.
     */
    public static function create(string $type, string $code): ?IdInterface
    {
        $class = IdType::getClass($type);
        if ($class === null) {
            return null;
        }

        return new $class($code);
    }

    /**
     * 创建大陆身份证实例.
     */
    public static function mainlandId(string $code): MainlandId
    {
        return new MainlandId($code);
    }

    /**
     * 创建台湾身份证实例.
     */
    public static function taiwanId(string $code): TaiwanId
    {
        return new TaiwanId($code);
    }

    /**
     * 创建香港身份证实例.
     */
    public static function hongKongId(string $code): HongKongId
    {
        return new HongKongId($code);
    }

    /**
     * 创建澳门身份证实例.
     */
    public static function macauId(string $code): MacauId
    {
        return new MacauId($code);
    }

    /**
     * 创建港澳居民来往内地通行证(香港)实例.
     */
    public static function hkToMainlandPermit(string $code): HkToMainlandPermit
    {
        return new HkToMainlandPermit($code);
    }

    /**
     * 创建澳门居民来往内地通行证实例.
     */
    public static function moToMainlandPermit(string $code): MoToMainlandPermit
    {
        return new MoToMainlandPermit($code);
    }

    /**
     * 创建往来港澳通行证实例.
     */
    public static function mainlandToHkMoPermit(string $code): MainlandToHkMoPermit
    {
        return new MainlandToHkMoPermit($code);
    }

    /**
     * 创建大陆居民往来台湾通行证实例.
     */
    public static function mainlandToTwPermit(string $code): MainlandToTwPermit
    {
        return new MainlandToTwPermit($code);
    }

    /**
     * 创建台湾居民来往大陆通行证实例.
     */
    public static function twToMainlandPermit(string $code): TwToMainlandPermit
    {
        return new TwToMainlandPermit($code);
    }
}

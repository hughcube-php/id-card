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

class IdType
{
    /** 中华人民共和国居民身份证, 大陆签发, 18位 */
    const MAINLAND_ID = 'mainland_id';

    /** 中华民国国民身份证(台湾身份证), 台湾签发, 1位字母+9位数字 */
    const TAIWAN_ID = 'taiwan_id';

    /** 香港永久性居民身份证, 香港签发, 1-2位字母+6位数字+1位校验码 */
    const HONG_KONG_ID = 'hong_kong_id';

    /** 澳门居民身份证, 澳门签发, 1位首数字(1/5/7)+6位数字+1位校验码 */
    const MACAU_ID = 'macau_id';

    /** 港澳居民来往内地通行证(回乡证), 香港签发, H+8位数字(或11位) */
    const HK_TO_MAINLAND_PERMIT = 'hk_to_mainland_permit';

    /** 澳门居民来往内地通行证(回乡证), 澳门签发, M+8位数字(或11位) */
    const MO_TO_MAINLAND_PERMIT = 'mo_to_mainland_permit';

    /** 往来港澳通行证, 大陆签发, C/W+8位数字 或 C+字母+7位数字(新版) */
    const MAINLAND_TO_HK_MO_PERMIT = 'mainland_to_hk_mo_permit';

    /** 大陆居民往来台湾通行证, 大陆签发, L/T+8位数字 */
    const MAINLAND_TO_TW_PERMIT = 'mainland_to_tw_permit';

    /** 台湾居民来往大陆通行证(台胞证), 台湾签发, 8位纯数字 */
    const TW_TO_MAINLAND_PERMIT = 'tw_to_mainland_permit';

    /**
     * @var array<string, string>
     */
    protected static array $titles = [
        self::MAINLAND_ID => '居民身份证',
        self::TAIWAN_ID => '台湾身份证',
        self::HONG_KONG_ID => '香港身份证',
        self::MACAU_ID => '澳门身份证',
        self::HK_TO_MAINLAND_PERMIT => '港澳居民来往内地通行证(香港)',
        self::MO_TO_MAINLAND_PERMIT => '澳门居民来往内地通行证',
        self::MAINLAND_TO_HK_MO_PERMIT => '往来港澳通行证',
        self::MAINLAND_TO_TW_PERMIT => '大陆居民往来台湾通行证',
        self::TW_TO_MAINLAND_PERMIT => '台湾居民来往大陆通行证',
    ];

    /**
     * @var array<string, class-string<IdInterface>>
     */
    protected static array $classMap = [
        self::MAINLAND_ID => MainlandId::class,
        self::TAIWAN_ID => TaiwanId::class,
        self::HONG_KONG_ID => HongKongId::class,
        self::MACAU_ID => MacauId::class,
        self::HK_TO_MAINLAND_PERMIT => HkToMainlandPermit::class,
        self::MO_TO_MAINLAND_PERMIT => MoToMainlandPermit::class,
        self::MAINLAND_TO_HK_MO_PERMIT => MainlandToHkMoPermit::class,
        self::MAINLAND_TO_TW_PERMIT => MainlandToTwPermit::class,
        self::TW_TO_MAINLAND_PERMIT => TwToMainlandPermit::class,
    ];

    /**
     * 返回所有证件类型常量.
     *
     * @return string[]
     */
    public static function all(): array
    {
        return array_keys(static::$titles);
    }

    /**
     * 判断给定类型是否存在.
     */
    public static function has(string $type): bool
    {
        return isset(static::$titles[$type]);
    }

    /**
     * 获取证件类型的中文标题.
     */
    public static function title(string $type): ?string
    {
        return static::$titles[$type] ?? null;
    }

    /**
     * 获取证件类型对应的类名.
     *
     * @return class-string<IdInterface>|null
     */
    public static function getClass(string $type): ?string
    {
        return static::$classMap[$type] ?? null;
    }

    /**
     * 校验指定类型的证件号码是否合法.
     */
    public static function isValid(string $type, string $code): bool
    {
        $class = static::getClass($type);
        if ($class === null) {
            return false;
        }

        /** @var IdInterface $instance */
        $instance = new $class($code);

        return $instance->isValid();
    }

    /**
     * 对指定类型的证件号码进行掩码.
     */
    public static function mask(string $type, string $code): ?string
    {
        $class = static::getClass($type);
        if ($class === null) {
            return null;
        }

        /** @var IdInterface $instance */
        $instance = new $class($code);

        return $instance->mask();
    }
}

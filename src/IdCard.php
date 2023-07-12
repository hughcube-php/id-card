<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2022/9/16
 * Time: 22:10
 */

namespace HughCube\IdCard;

/**
 * @deprecated
 */
class IdCard
{
    use Code;

    /**
     * 身份证每位对应的系数,用于判断身份证是否合法;
     *
     * @var array
     */
    protected static $factor = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];

    /**
     * 身份证最后一位可能出现的字符;
     *
     * @var array
     */
    protected static $refer = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];

    public static function check($idCard, $verifyArea = true): bool
    {
        $idCard = strtoupper(strval($idCard));

        if (!preg_match("/^([0-9]{17}[0-9X])$/i", $idCard)) {
            return false;
        }

        /**
         * 身份证规则一, 把前十七位和系数相乘,然后相加对11取余,对应第十八位
         */
        $sum = 0;
        for ($i = 0; $i < 17; $i++) {
            $sum += intval(substr($idCard, $i, 1)) * static::$factor[$i];
        }
        if (substr($idCard, 17, 1) !== static::$refer[$sum % 11]) {
            return false;
        }

        /**
         * 身份证规则二, 身份证的前六位是地区的代码
         */
        if ($verifyArea && !in_array(intval(substr($idCard, 0, 6)), static::$codes)) {
            return false;
        }

        /**
         * 身份证规则三, 身份证的七到十二位是出生日期
         */
        if (!checkdate(substr($idCard, 10, 2), substr($idCard, 12, 2), substr($idCard, 6, 4))) {
            return false;
        }

        return true;
    }

    /**
     * 0: 女性
     * 1: 男性
     */
    public static function getGender($idCard)
    {
        if (!static::check($idCard, false)) {
            return null;
        }

        return intval(substr($idCard, 16, 1)) % 2;
    }

    /**
     * 格式如2013-8-13
     */
    public static function getBirthday($idCard)
    {
        if (!static::check($idCard, false)) {
            return null;
        }

        return sprintf("%s-%s-%s", substr($idCard, 6, 4), substr($idCard, 10, 2), substr($idCard, 12, 2));
    }
}
